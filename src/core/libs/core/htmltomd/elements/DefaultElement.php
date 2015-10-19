<?php
/**
 * File for class DefaultElement definition in the Agency-Portal project
 *
 * @package   HTMLToMD\Elements
 * @author    Charlie Powell <charlie@evalagency.com>
 * @date      20151017.1825
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

namespace HTMLToMD\Elements;
use HTMLToMD\Converter;


/**
 * A short teaser of what DefaultElement does.
 *
 * More lengthy description of what DefaultElement does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo    Write documentation for DefaultElement
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
 * @package HTMLToMD\Elements
 * @author  Charlie Powell <charlie@evalagency.com>
 *
 */
class DefaultElement implements ElementInterface {
	/** @var Converter  */
	protected $_parentConverter;

	/** @var \DOMNode  */
	protected $_node;

	public function __construct(\DOMNode $node, Converter $converter){
		$this->_node = $node;
		$this->_parentConverter = $converter;
	}

	/**
	 * @return string
	 */
	public function convert() {
		return $this->_getContent();
	}

	/**
	 * @param string $attribute
	 * @param mixed $default
	 *
	 * @return null|string
	 */
	public function getAttribute($attribute, $default = null){
		$a = $this->_node->attributes->getNamedItem($attribute);

		return $a === null ? $default : $a->nodeValue;
	}

	/**
	 * Get the standard child content for this node, (or nodeValue if no children).
	 *
	 * @return string
	 */
	protected function _getContent(){
		$output = '';
		if($this->_node->hasChildNodes()){
			$nodes = $this->_node->childNodes;
			for($i = 0; $i < $nodes->length; $i++){
				$subElement = $this->_parentConverter->_resolveNodeToElement(
					$nodes->item($i)
				);
				$output .= $subElement->convert();
			}
		}
		else{
			$output .= $this->_node->nodeValue;
		}

		return trim($output) . ' ';
	}

	/**
	 * Get the page footer text, (if any).
	 *
	 * @return string
	 */
	public function getPageFooter(){
		return '';
	}
}