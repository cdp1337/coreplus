<?php
/**
 * File for class Converter definition in the Agency-Portal project
 *
 * @package   HTMLToMD
 * @author    Charlie Powell <charlie@evalagency.com>
 * @date      20151017.1820
 * @copyright Copyright (C) 2009-2016  Charlie Powell
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

namespace HTMLToMD;
use HTMLToMD\Elements\ElementInterface;


/**
 * A short teaser of what Converter does.
 *
 * More lengthy description of what Converter does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo    Write documentation for Converter
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
 * @package HTMLToMD
 * @author  Charlie Powell <charlie@evalagency.com>
 *
 */
class Converter {

	/** @var array Generic space available for elements to save whatever into! */
	public $persistentData = [];

	private $_html;
	private $_dom;

	private $_tagMap = [
		// Default inline elements, (applies to everything unless specified otherwise)
		'__default__' => 'HTMLToMD\\Elements\\DefaultElement',

		// Elements that are skipped
	    'head'        => 'HTMLToMD\\Elements\\SkipElement',
	    'style'       => 'HTMLToMD\\Elements\\SkipElement',
	    'script'      => 'HTMLToMD\\Elements\\SkipElement',
	    'object'      => 'HTMLToMD\\Elements\\SkipElement',
	    'meta'        => 'HTMLToMD\\Elements\\SkipElement',
		'#comment'    => 'HTMLToMD\\Elements\\SkipElement',

		// List elements
	    'ol'          => 'HTMLToMD\\Elements\\ListElement',
	    'ul'          => 'HTMLToMD\\Elements\\ListElement',

	    // Block level elements
	    'p'           => 'HTMLToMD\\Elements\\BlockElement',
	    'div'         => 'HTMLToMD\\Elements\\BlockElement',
	    'article'     => 'HTMLToMD\\Elements\\BlockElement',
	    'section'     => 'HTMLToMD\\Elements\\BlockElement',

	    // Headers
	    'h1'          => 'HTMLToMD\\Elements\\HeaderElement',
	    'h2'          => 'HTMLToMD\\Elements\\HeaderElement',
	    'h3'          => 'HTMLToMD\\Elements\\HeaderElement',
	    'h4'          => 'HTMLToMD\\Elements\\HeaderElement',
	    'h5'          => 'HTMLToMD\\Elements\\HeaderElement',
	    'h6'          => 'HTMLToMD\\Elements\\HeaderElement',

	    //++ Special Elements and 1-Offs ++\\

		// A Tags
		'a'           => 'HTMLToMD\\Elements\\LinkElement',

	    // Tables
	    'table'       => 'HTMLToMD\\Elements\\TableElement',

	    // Preformatted Code
		'code'        => 'HTMLToMD\\Elements\\PreElement',
	    'pre'         => 'HTMLToMD\\Elements\\PreElement',
	];

	/** @var array Local cache of elements created, used for post-execution callbacks! */
	private $_elementsCreated = [];


	public function convert($html = null){
		if($html !== null){
			$this->setHTML($html);
		}

		if($this->_html === null || trim($this->_html) === ''){
			return '';
		}

		$this->_dom = new \DOMDocument();
		libxml_use_internal_errors(true);

		// http://php.net/manual/en/domdocument.loadhtml.php#95251
		$this->_dom->loadHTML('<?xml encoding="UTF-8">' . $this->_html);
		$this->_dom->encoding = 'UTF-8';

		libxml_clear_errors();

		// And go!
		$root = $this->_dom->getElementsByTagName('html')->item(0);
		$element = $this->_resolveNodeToElement($root);

		$output = $element->convert();

		// Provide a mechanism for post-content callbacks.
		// Used by elements that add anything into the footer.
		foreach($this->_elementsCreated as $el){
			/** @var ElementInterface $el */
			$output .= $el->getPageFooter();
		}

		// Trim extra whitespace.
		$output = preg_replace("#\n[\n]+#", "\n\n", $output);

		return $output;
	}

	public function setHTML($html){
		$this->_html = $html;
	}

	/**
	 * Set a handler for a requested tag.
	 *
	 * @param string $tag     The tag name to target
	 * @param string $handler The class name of the handler to use for the tag
	 *
	 * @throws \Exception
	 */
	public function setTagHandler($tag, $handler){
		if(!class_exists($handler)){
			throw new \Exception('Unable to set handler for tag [' . $tag . '], class [' . $handler . '] not found!');
		}

		$this->_tagMap[$tag] = $handler;
	}

	/**
	 * Get the root DOM for this request.
	 *
	 * @return \DOMDocument
	 */
	public function getRootDOM(){
		return $this->_dom;
	}

	/**
	 * @param \DOMNode $node
	 *
	 * @return ElementInterface
	 */
	public function _resolveNodeToElement(\DOMNode $node){
		$tagname = $node->nodeName;

		if(!isset($this->_tagMap[ $tagname ])){
			$tagname = '__default__';
		}

		$ref = new \ReflectionClass($this->_tagMap[ $tagname ]);

		$instance = $ref->newInstance($node, $this);

		// Cache and return.
		$this->_elementsCreated[] = $instance;
		return $instance;
	}
}