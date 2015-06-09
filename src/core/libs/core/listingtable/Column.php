<?php
/**
 * File for class Column definition in the coreplus project
 * 
 * @package Core\ListingTable
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140406.2005
 * @copyright Copyright (C) 2009-2015  Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
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

namespace Core\ListingTable;


/**
 * A short teaser of what Column does.
 *
 * More lengthy description of what Column does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for Column
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
 * @package Core\ListingTable
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class Column {
	/** @var string The title of this column */
	public $title;
	/** @var string The model column name used for sorting */
	public $sortkey;
	/** @var boolean T/F if this column is hidden by default. */
	public $visible = true;

	public function getClass(){
		$classes = [];

		$classes[] = 'column-name-' . \Core\str_to_url($this->title);
		if(!$this->visible){
			$classes[] = 'column-optional';
		}

		return implode(' ', $classes);
	}

	public function getTH(){
		$out = '';

		$atts = [];
		if($this->sortkey){
			$atts['data-sortkey'] = $this->sortkey;
			$atts['title'] = 'Sort By ' . str_replace('"', '&quot;', $this->title);
		}
		$atts['class'] = $this->getClass();
		$atts['data-viewkey'] = 'column-name-' . \Core\str_to_url($this->title);
		$atts['data-viewtitle'] = $this->title;

		$out .= '<th';
		foreach($atts as $k => $v){
			$out .= ' ' . $k . '="' . $v . '"';
		}
		$out .= '>' . $this->title . '</th>';

		return $out;
	}
}