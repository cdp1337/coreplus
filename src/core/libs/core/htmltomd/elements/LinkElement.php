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
use League\HTMLToMarkdown\Converter\DefaultConverter;


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
class LinkElement extends DefaultElement implements ElementInterface {
	/**
	 * @return string
	 */
	public function convert() {
		$output        = $this->_getContent();
		$href          = $this->getAttribute('href', '');
		$title         = $this->getAttribute('title', '');
		$useReferences = true; // @todo Make this configurable!

		if(strpos($output, "\n") !== false){
			// A tags are only meant to wrap inline elements!
			$output = '';
		}

		if($href == '' || $href == '#' || $output == ''){
			return $output;
		}

		if($useReferences){
			// Lookup this link in the persistent cache.
			if(!isset($this->_parentConverter->persistentData['Links'])){
				$this->_parentConverter->persistentData['Links'] = [];
			}

			// Shortcut
			$l =& $this->_parentConverter->persistentData['Links'];
			if(!isset($l[$href])){
				$idx = sizeof($l) + 1;
				$l[$href] = [
					'index' => $idx,
					'title' => str_replace('"', '\"', $title),
				];
			}
			else{
				$idx = $l[$href]['index'];
			}

			$output = '[' . trim($output) . '][' . $idx . ']';
		}
		else{
			if($title){
				$output = '[' . trim($output) . '](' . $href . ' "' . str_replace('"', '\"', $title) . '")';
			}
			else{
				$output = '[' . trim($output) . '](' . $href . ')';
			}
		}

		return $output;
	}

	/**
	 * Get the page footer text, (if any).
	 *
	 * @return string
	 */
	public function getPageFooter(){
		if(!isset($this->_parentConverter->persistentData['Links'])){
			return '';
		}

		$output = '';
		foreach($this->_parentConverter->persistentData['Links'] as $url => $l){
			$output .= '[' . $l['index'] . ']: ' . $url . ($l['title'] ? ' "' . $l['title'] . '"' : '') . "\n";
		}

		// Cleanup
		unset($this->_parentConverter->persistentData['Links']);

		return "\n" . $output . "\n";
	}
}