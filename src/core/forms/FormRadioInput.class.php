<?php
/**
 * Class file for FormRadioInput
 *
 * @package Core\Forms
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
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

/**
 * Class FormRadioInput provides a set of radio inputs with a single name.
 *
 * @package Core\Forms
 */
class FormRadioInput extends FormElement {
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Some defaults
		$this->_attributes['class'] = 'formelement formradioinput';
		$this->_validattributes     = array('accesskey', 'dir', 'disabled', 'id', 'lang', 'name', 'required', 'tabindex', 'style');
	}

	/**
	 * Return the key of the currently checked value.
	 * This will intelligently scan for Yes/No values.
	 */
	public function getChecked() {
		// If this is a boolean (yes/no) radio option and a true or false
		// is set to the value, it should correctly propagate to "Yes" or "No"
		if (!isset($this->_attributes['value'])) {
			return null;
		}
		elseif (
			isset($this->_attributes['options']) &&
			is_array($this->_attributes['options']) &&
			sizeof($this->_attributes['options']) == 2 &&
			isset($this->_attributes['options']['Yes']) &&
			isset($this->_attributes['options']['No'])
		) {
			// Running strtolower on a boolean will result in either "1" or "".
			switch (strtolower($this->_attributes['value'])) {
				case '1':
				case 'true':
				case 'yes':
					return 'Yes';
					break;
				default:
					return 'No';
					break;
			}
		}
		else {
			return $this->_attributes['value'];
		}
	}

}