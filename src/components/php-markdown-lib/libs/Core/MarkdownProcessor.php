<?php
/**
 * Customized Core-specific processor for markdown files.
 * 
 * Adds some minor functionality such as ToC, meta tags, and Core-aware links.
 *
 * @package   php-markdown
 * @author    Charlie Powell <charlie@evalagency.com>
 * @date      20150820.1027
 * @copyright Copyright (C) 2009-2017  Charlie Powell
 * @license   BSD-3-Clause <https://opensource.org/licenses/BSD-3-Clause>
 */

namespace Core;
use Michelf\Markdown;
use Michelf\MarkdownExtra;


/**
 * A short teaser of what MarkdownProcessor does.
 *
 * More lengthy description of what MarkdownProcessor does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo    Write documentation for MarkdownProcessor
 * <h4>Example 1</h4>
 * <p>Description 1</p>
 * <code>
 * // Some code for example 1
 * $a = $b;
 * </code>
 *
 *
 * <h4>Example 2</h4>
 * <p>Description 2</p>
 * <code>
 * // Some code for example 2
 * $b = $a;
 * </code>
 *
 *
 * @package Core
 * @author  Charlie Powell <charlie@evalagency.com>
 *
 */
class MarkdownProcessor extends MarkdownExtra {
	
	public $urlCallback = null;
	
	/** @var array Contains all the metafields in this document */
	private $meta = [];
	
	private $_headerCount = 0;
	
	/** @var array Array of any field that's allowed to return multiple values */
	private $_metasPlural = [
		'author', 'keywords'
	];
	
	private $_metasSynonyms = [
		'summary' => 'description',
		'subject' => 'title',
	    'authors' => 'author',
	];

	public function __construct(){

		// Add a few features to the default Markdown formatter.

		// This will use Core to generate a unique ID for header fields.
		$this->header_id_func = [$this, 'generateHeaderID'];

		// This will add the [TOC] tag to MD.
		$this->document_gamut += ['doTOC' => 55];
		
		// Process all metadata on this document
		$this->document_gamut += ['doMeta' => 1];
		$this->document_gamut += ['doMetaPost' => 99];
		
		$this->url_filter_func = [$this, 'doLink'];
		
		parent::__construct();
	}

	/**
	 * Get a requested metafield for this document.
	 * 
	 * A meta field is any line of text *at the beginning* of the document
	 * that has string: some value.
	 * 
	 * Where the string before the ':' is the "meta" keyword and the value is anything between
	 * the ':' and the newline.
	 * 
	 * Common values for this are:
	 * * description
	 * * author
	 * * authors
	 * * keyword
	 * * keywords
	 * * title
	 * 
	 * @param $key string The meta field to retrieve
	 *
	 * @return null|string|array
	 */
	public function getMeta($key){
		$key = strtolower($key);
		
		// Allow for aliases
		if(isset($this->_metasSynonyms[$key])){
			$key = $this->_metasSynonyms[$key];
		}
		
		if(isset($this->meta[$key])){
			return $this->meta[$key];
		}
		else{
			// Else?  The field isn't set, just return null.
			return null;	
		}
	}

	/**
	 * Generate an ID from the text contents
	 *
	 * @param $text string
	 *
	 * @return string
	 */
	public function generateHeaderID($text){
		++$this->_headerCount;

		$id = 'md' . $this->_headerCount . '_' . \Core\str_to_url($text);
		return $id;
	}

	/**
	 * Process any metafields in this document
	 * 
	 * @param $text string The original document
	 *
	 * @return string The rest of the document minus any metafields
	 */
	public function doMeta($text){
		// If the first line is text with colon-separated tags,
		// read the document until a hard break is found.
		$lines = explode("\n", $text);
		
		$previous = null;
		$fields   = [];
		$x        = 0;
		foreach($lines as $l){
			$lineWidth = strlen($l) + 1;
			if(($pos = strpos($l, ':')) !== false){
				$type     = strtolower(substr($l, 0, $pos));
				$content  = substr($l, $pos+1);
				
				// Is this field an alias?
				if(isset($this->_metasSynonyms[$type])){
					$type = $this->_metasSynonyms[$type];
				}
				
				if($previous !== null){
					// There was a previous header, save that before continuing on.
					$this->meta[ $previous ] = (in_array($previous, $this->_metasPlural)) ? $fields : $fields[0];
					// Clear out those previous values.
					$fields = [];
				}
				$previous = $type;
				$fields[] = trim($content);
				$x += $lineWidth;
			}
			elseif(trim($l) == ''){
				// It's a blank line, this indicates the end of the headers.
				if($previous !== null){
					// There was a previous header, save that before continuing on.
					$this->meta[ $previous ] = (in_array($previous, $this->_metasPlural)) ? $fields : $fields[0];
				}
				return substr($text, $x);
			}
			elseif($previous){
				$fields[] = trim($l);
				$x += $lineWidth;
			}
		}
	}

	/**
	 * Final checks for any meta fields that may be derived from the data.
	 * 
	 * @param $text string
	 * 
	 * @return string
	 */
	public function doMetaPost($text){
		
		// If the title is not set yet, pull it from the content.
		if(!isset($this->meta['title'])){
			preg_match('/<h1[^>]*>(.*)<\/h1>/isU', $text, $h);
			if(isset($h[1])){
				$this->meta['title'] = $h[1];
			}
		}
		
		return $text;
	}

	/**
	 * Resolve a URL to its Core version, (or call the urlCallback function if defined).
	 * 
	 * @param string $url
	 *
	 * @return string
	 */
	public function doLink($url){
		// In this method, $url may be an alias for a registered footnote.
		// If it is, pull that footnote source instead of the alias.
		if(isset($this->urls[$url])){
			$url = $this->urls[$url];
		}
		
		if(strpos($url, '://') !== false){
			// Skip translation for fully resolved links.
			return $url;
		}
		else{
			// Is there a supplemental method to handle these?
			if ($this->urlCallback){
				$url = call_user_func($this->urlCallback, $url);
			}
			else{
				// Try to use Core to resolve this URL.
				if(($resolved = \Core\resolve_link($url)) !== null){
					$url = $resolved;
				}
			}
			
			return $url;
		}
	}

	/**
	 * Adds TOC support by including the following on a single line:
	 *
	 * [TOC]
	 *
	 * TOC Requirements:
	 * * Only headings 2-6
	 * * Headings must have an ID
	 * * Builds TOC with headings _after_ the [TOC] tag
	 *
	 * @param $text
	 *
	 * @return string
	 */
	public function doTOC($text){
		$toc = '';
		if (preg_match ('/\[TOC\]/m', $text, $i, PREG_OFFSET_CAPTURE)) {
			// $i contains an array with 0 => '[TOC]' and 1 => the position of the character in the main text.
			preg_match_all ('/<h([2-6]) id="([0-9a-z_-]+)">(.*?)<\/h\1>/i', $text, $h, PREG_SET_ORDER, $i[0][1]);
			foreach ($h as &$m){
				$toc .= str_repeat ("\t", (int) $m[1]-2)."*\t [${m[3]}](#${m[2]})\n";
			}
			
			// We use this method instead of preg_replace so that only the first instance is replaced!
			$textPre = substr($text, 0, $i[0][1]);
			$textPost = substr($text, $i[0][1] + 5);
			
			$text = $textPre . '<aside class="markdown-toc">' . Markdown::defaultTransform($toc) . '</aside>' . $textPost;
			//var_dump($textPre, $textPost); die();
			//$text = preg_replace('/\[TOC\]/m', '<aside class="markdown-toc">' . Markdown::defaultTransform($toc) . '</aside>', $text);
			//$text = preg_replace ('/\[TOC\]/m', Markdown($toc), $text);
		}
		return trim ($text, "\n");
	}
}