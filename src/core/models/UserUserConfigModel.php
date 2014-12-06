<?php
/**
 * DESCRIPTION
 *
 * @package User
 * @since 1.9
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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
			'type' => Model::ATT_TYPE_UUID_FK,
			'required' => true,
			'null' => false,
			'link' => [
				'model' => 'User',
				'type' => Model::LINK_BELONGSTOONE,
				'on' => 'id',
			],
		),
		'key' => array(
			'type' => Model::ATT_TYPE_STRING,
			'required' => true,
			'null' => false,
			'maxlength' => 64,
			'link' => [
				'model' => 'UserConfig',
				'type' => Model::LINK_BELONGSTOONE,
				'on' => 'key',
			],
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
					$ref = new ReflectionClass(substr($check, 0, strpos($check, ':')));
					$checklast = substr($check, strrpos($check, ':')+1);

					if($ref->hasMethod($checklast)){
						// the method can either be true, false or a string.
						// Only if true is returned will that be triggered as success.
						$valid = call_user_func($check, $v, $this);
					}
					elseif($ref->hasProperty($checklast)){
						// Allow a class's static property to be used,
						// EX: Model::VALIDATION_EMAIL.
						// This property contains a string of the regex.
						$check = $ref->getProperty($checklast)->getValue();

						if (
							($check{0} == '/' && !preg_match($check, $v)) ||
							($check{0} == '#' && !preg_match($check, $v))
						) {
							$valid = false;
						}
					}
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

					if($valid === false){
						$msg = $this->_data['key'] . ' fails validation!';
					}
					elseif($valid === null){
						$msg = $this->_data['key'] . ' fails validation! (no reason given though)';
					}
					else{
						$msg = $valid;
					}

					throw new ModelValidationException($msg);
				}
			}
		}
		else{
			return parent::set($k, $v);
		}
	}
}
