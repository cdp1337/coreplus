<?php
/**
 * Class file for FormButtonInput
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
 * Class FormButtonInput provides a button
 *
 * @package Core\Forms
 */
class ButtonInput extends FormElement {
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Some defaults
		$defaults = [
			'class' => 'formelement formbuttoninput',
		];
		foreach($defaults as $k => $v){
			if(!isset($this->_attributes[$k])){
				$this->_attributes[$k] = $v;
			}
		}

		$this->_validattributes     = array('accesskey', 'dir', 'disabled', 'id', 'lang', 'name', 'size', 'tabindex', 'width', 'height', 'value', 'style');
	}
}
