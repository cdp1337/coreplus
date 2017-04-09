<?php
/**
 * Class file for FormCheckboxInput
 *
 * @package Core\Forms
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2017  Charlie Powell
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

namespace Core\Forms;

/**
 * Class FormCheckboxInput provides a single checkbox with one value that's either checked or not.
 *
 * @package Core\Forms
 */
class CheckboxInput extends FormElement {
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Some defaults
		$this->_attributes['class'] = 'formelement formcheckboxinput';
		$this->_validattributes     = array('accesskey', 'checked', 'dir', 'disabled', 'id', 'lang', 'name', 'required', 'tabindex', 'style', 'value');
	}

	/*public function get($key) {
		if($key == 'value' && sizeof($this->_attributes['options']) > 1){
			// This should return an array if there are more than 1 option.
			if(!$this->_attributes['value']) return array();
			else return $this->_attributes['value'];
		}
		else{
			return parent::get($key);
		}
	}*/

	/*public function set($key, $value) {
		if($key == 'value'){
			if($value) $this->_attributes['value'] = true;
			else $this->_attributes['value'] = false;
		}
		else{
			return parent::set($key, $value);
		}
	}*/

}