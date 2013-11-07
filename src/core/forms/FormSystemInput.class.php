<?php
/**
 * Class file for FormSystemInput
 *
 * @package Core\Forms
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2013  Charlie Powell
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
 * Class FormSystemInput provides a system input.
 *
 * These inputs never get rendered by the user agent, nor can they be changed by the user agent.
 *
 * @package Core\Forms
 */
class FormSystemInput extends FormElement{

	/**
	 * "System" inputs do not get sent to the browser, so they have no rendered text.
	 * @return string
	 */
	public function render(){
		return '';
	}

	public function lookupValueFrom(&$src){
		// Since system inputs don't actually change based on user input... just return this value.
		return $this->get('value');
	}
}
