<?php
/**
 * DESCRIPTION
 *
 * @package User
 * @since 1.9
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

class UserUserConfigModel extends Model{
	public static $Schema = array(
		'user_id' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
			'null' => false,
		),
		'key' => array(
			'type' => Model::ATT_TYPE_STRING,
			'required' => true,
			'null' => false,
			'maxlength' => 64,
		),
		'value' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'required' => false,
			'null' => true
		),
		'created' => array(
			'type' => Model::ATT_TYPE_CREATED,
			'null' => false,
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_UPDATED,
			'null' => false,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('user_id', 'key'),
	);

	public function set($k, $v){
		if($k == 'value'){
			// I need a custom function here because of the unique validation for this model.
			// components can request custom validation in their component.xml files.

			$config = UserConfigModel::Construct($this->_data['key']);
			if(!$config->get('validation')){
				// Ok, simple enough.
				return parent::set($k, $v);
			}
			else{
				$check = $config->get('validation');
				$valid = true;
				if (strpos($check, '::') !== false) {
					// the method can either be true, false or a string.
					// Only if true is returned will that be triggered as success.
					$valid = call_user_func($check, $v, $this);
				}
				// regex-based validation.  These don't have any return strings so they're easier.
				elseif (
					($check{0} == '/' && !preg_match($check, $v)) ||
					($check{0} == '#' && !preg_match($check, $v))
				) {
					$valid = false;
				}

				if($valid === true){
					return parent::set($k, $v);
				}
				else{
					throw new ModelValidationException(($valid === false) ? $this->_data['key'] . ' fails validation!' : $valid);
				}
			}
		}
		else{
			return parent::set($k, $v);
		}
	}
}
