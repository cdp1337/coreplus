<?php
/**
 * File for class MarkdownProcessor definition in the Agency-Portal project
 *
 * @package   Core
 * @author    Charlie Powell <charlie@eval.bz>
 * @date      20150820.1027
 * @copyright Copyright (C) 2009-2015  Charlie Powell
 * @license   GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/agpl-3.0.txt.
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
 * @author  Charlie Powell <charlie@eval.bz>
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
	
	public function doLink($url){
		if(isset($this->urls[$url])){
			return $this->urls[$url];
		}
		elseif(strpos($url, '://') !== false){
			// Skip translation for fully resolved links.
			return $url;
		}
		else{
			// Try to use Core to resolve this URL.
			$resolved = \Core\resolve_link($url);
			if($resolved !== null){
				return $resolved;
			}
			
			// Is there a supplemental method to handle these?
			if ($this->urlCallback)
				$url = call_user_func($this->urlCallback, $url);
			
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
			preg_match_all ('/<h([2-6]) id="([0-9a-z_-]+)">(.*?)<\/h\1>/i', $text, $h, PREG_SET_ORDER, $i[0][1]);
			foreach ($h as &$m){
				$toc .= str_repeat ("\t", (int) $m[1]-2)."*\t [${m[3]}](#${m[2]})\n";
			}
			$text = preg_replace('/\[TOC\]/m', '<aside class="markdown-toc">' . Markdown::defaultTransform($toc) . '</aside>', $text);
			//$text = preg_replace ('/\[TOC\]/m', Markdown($toc), $text);
		}
		return trim ($text, "\n");
	}
}