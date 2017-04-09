<?php
/**
 * File for class DefaultElement definition in the Agency-Portal project
 *
 * @package   HTMLToMD\Elements
 * @author    Charlie Powell <charlie@evalagency.com>
 * @date      20151017.1825
 * @copyright Copyright (C) 2009-2017  Charlie Powell
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
class HeaderElement extends DefaultElement implements ElementInterface {
	/**
	 * @return string
	 */
	public function convert() {
		$output = $this->_getContent();

		switch($this->_node->nodeName){
			case 'h1':
				$prefix = '# ';
				break;
			case 'h2':
				$prefix = '## ';
				break;
			case 'h3':
				$prefix = '### ';
				break;
			case 'h4':
				$prefix = '#### ';
				break;
			case 'h5':
				$prefix = '##### ';
				break;
			default:
				$prefix = '###### ';
				break;
		}

		return "\n\n" . $prefix . $output . "\n\n";
	}
}