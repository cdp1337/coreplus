<?php
/**
 * File for class Column definition in the coreplus project
 * 
 * @package Core\ListingTable
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140406.2005
 * @copyright Copyright (C) 2009-2016  Charlie Powell
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
	
	/** @var string The model's view name for rendering output */
	public $renderkey;
	
	/** @var string Abbreviation for this column */
	public $abbr = '';
	
	/** @var string|null Set to a string to group this column with other like-named columns */
	public $group = null;
	
	/** @var string System name of this column, useful for CSS overrides and javascript calls. */
	public $name = null;
	
	private $_title = null;
	private $_titleProcessed = null;
	private $_titleEscaped = null;

	public function getClass(){
		$classes = [];
		
		// Everything here is a cell.
		$classes[] = 'listing-table-cell';
		
		// Include the name for styling from CSS and JS calls!
		$classes[] = 'column-name-' . $this->name;

		if(!$this->visible){
			$classes[] = 'column-optional';
		}
		
		if($this->group){
			$classes[] = 'column-group-' . $this->group;
		}

		return implode(' ', $classes);
	}

	public function getTH(){
		return $this->_render('th');
	}
	
	public function getDIV(){
		return $this->_render('div');
	}
	
	/**
	 * Get the title attribute for this column, optionally escaped for HTML tags.
	 * 
	 * @param bool $escaped
	 * @return string
	 */
	public function getTitle($escaped = false){
		if($this->_title !== $this->title){
			// Calculate if this title needs some I18N work.
			// This is done to save execution calls for strpos, substr, and t.
			$this->_title = $this->title;
			// The title supports I18N!
			if(strpos($this->_title, 't:') === 0){
				$this->_titleProcessed = t(substr($this->_title, 2));
			}
			else{
				$this->_titleProcessed = $this->_title;
			}
			
			$this->_titleEscaped = str_replace('"', '&quot;', $this->_titleProcessed);
		}
		
		return $escaped ? $this->_titleEscaped : $this->_titleProcessed;
	}
	
	private function _render($tag){
		$out = '';
		
		if($this->abbr){
			$label = '<abbr title="' . $this->getTitle(true) . '">' . $this->abbr . '</abbr>';
		}
		else{
			$label = $this->getTitle();
		}

		$atts = [];
		if($this->sortkey){
			$atts['data-sortkey'] = $this->sortkey;
			// The title for sort by is no longer needed; that is added to the icon itself.
			//$atts['title'] = 'Sort By ' . str_replace('"', '&quot;', $title);
		}
		if($this->renderkey){
			$atts['data-renderkey'] = $this->renderkey;
		}
		$atts['class'] = 'listing-table-cell-header ' . $this->getClass();
		$atts['data-viewkey'] = 'column-name-' . $this->name;
		$atts['data-viewtitle'] = $this->getTitle();

		$out .= '<' . $tag;
		foreach($atts as $k => $v){
			$out .= ' ' . $k . '="' . $v . '"';
		}
		$out .= '>' . $label . '</' . $tag . '>';

		return $out;
	}
}