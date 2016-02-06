<?php
/**
 * File for class DefaultElement definition in the Agency-Portal project
 *
 * @package   HTMLToMD\Elements
 * @author    Charlie Powell <charlie@evalagency.com>
 * @date      20151017.1825
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
class ListElement extends DefaultElement implements ElementInterface {
	/**
	 * @return string
	 */
	public function convert() {
		$output      = '';
		$type        = $this->_node->nodeName;
		$numeric     = $type == 'ol';
		$counter     = 0;
		$parentCount = $this->_getParentCount();
		$linePrefix  = str_repeat('    ', $parentCount);

		if($this->_node->hasChildNodes()){
			$nodes = $this->_node->childNodes;
			for($i = 0; $i < $nodes->length; $i++){
				/** @var \DOMNode $node */
				$node = $nodes->item($i);

				if($node->nodeName == 'li'){
					++$counter;

					$prefix = $numeric ? $counter : '-';
					$subElement = $this->_parentConverter->_resolveNodeToElement($node);
					$content = $subElement->convert();
					$output .= $linePrefix . $prefix . ' ' . trim($content) . "\n";
				}
			}
		}

		return "\n" . $output . "\n";
	}

	/**
	 * Get the number of "UL" or "OL" parents above this element.
	 * Used for prefix spacing.
	 *
	 * @return int
	 */
	private function _getParentCount(){
		$node = $this->_node;
		$count = 0;
		while(($parent = $node->parentNode) !== null){
			$nodeName = $parent->nodeName;

			if($nodeName == 'ol' || $nodeName == 'ul'){
				++$count;
			}
			// Skip when the body is hit!
			if($nodeName == 'body'){
				break;
			}
			$node = $parent;
		}

		return $count;
	}
}