<?php
/**
 * Model for UserModel
 * 
 * @package User
 * @since 1.9
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

class UserModel extends Model {
	
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'email' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'null' => false,
			'validation' => Model::VALIDATION_EMAIL,
		),
		'backend' => array(
			'type' => Model::ATT_TYPE_STRING,
			'formtype' => 'hidden',
			'default' => 'datastore'
		),
		'password' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 60,
			'null' => false,
		),
		'apikey' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'null' => false,
		),
		'active' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => '1',
			'null' => false,
		),
		'admin' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => '0',
			'null' => false,
		),
		'groups' => array(
			'type' => Model::ATT_TYPE_STRING,
			'formtype' => 'system',
			'comment' => 'json-encoded array of all groups this user belongs to'
		),
		'avatar' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => '64',
			'form' => array(
				'type' => 'file',
				'accept' => 'image/*',
				'basedir' => 'public/user/avatar',
			),
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
		'primary' => array('id'),
		'unique:email' => array('email'),
	);
	
	
	public function validate($k, $v, $throwexception = false) {
		if($k == 'password'){
			$valid = true;
			// complexity check from the config
			if(strlen($v) < ConfigHandler::Get('/user/password/minlength')){
				$valid = 'Please ensure that the password is at least ' . ConfigHandler::Get('/user/password/minlength') . ' characters long.';
			}

			// complexity check from the config
			if(ConfigHandler::Get('/user/password/requiresymbols') > 0){
				preg_match_all('/[^a-zA-Z]/', $v, $matches); // Count a number as a symbol.  Close enough :/
				if(sizeof($matches[0]) < ConfigHandler::Get('/user/password/requiresymbols')){
					$valid = 'Please ensure that the password has at least ' . ConfigHandler::Get('/user/password/requiresymbols') . ' symbol(s) or number(s).';
				}
			}

			// complexity check from the config
			if(ConfigHandler::Get('/user/password/requirecapitals') > 0){
				preg_match_all('/[A-Z]/', $v, $matches);
				if(sizeof($matches[0]) < ConfigHandler::Get('/user/password/requirecapitals')){
					$valid = 'Please ensure that the password has at least ' . ConfigHandler::Get('/user/password/requirecapitals') . ' capital letter(s).';
				}
			}
			
			// Validation's good, return true!
			if($valid === true) return true;
			// Validation failed and an Exception was requested.
			elseif($throwexception) throw new ModelValidationException($valid);
			// Validation failed, but just return the message.
			else return $valid;
		}
		else{
			return parent::validate($k, $v, $throwexception);
		}
	}
	
	public function set($k, $v) {
		if($k == 'password'){
			// Password skips the validation check, as it should be hashed 
			// when it gets to this stage.
			
			$this->_data[$k] = $v;
			$this->_dirty = true;
		
			return true;
		}
		else{
			return parent::set($k, $v);
		}
	}
	
	/**
	 * Set the password for this user, automatically hashing it.
	 * 
	 * @param string $v plain text password
	 * @return boolean
	 * @throws ModelException
	 * @throws ModelValidationException
	 */
	public function setPassword($v){
		// Quick validation (since the setter ignores validation)
		// Will throw an exception and leave this script if it fails.
		$this->validate('password', $v, true);
		
		// hash the password.
		$hasher = new PasswordHash(15);
		$password = $hasher->hashPassword($v);

		// Same?
		if($this->_data['password'] == $password) return false;
		
		// Still here?  Then try to set it.
		return $this->set('password', $password);
	}
	
	public function save() {
		// Every usermodel needs to have an apikey set prior to saving.
		if(!$this->_data['apikey']){
			$this->generateNewApiKey();
		}
		
		return parent::save();
	}
	
	/**
	 * Generate a new secure API key for this user.
	 * 
	 * This is a built-in function that can be used for automated access to
	 * secured resources on the application/site. 
	 * 
	 * Will only set the config, save() still needs to be called externally.
	 * 
	 * @since 2011.08
	 */
	public function generateNewApiKey(){
		$this->set('apikey', Core::RandomHex(64, true));
	}

} // END class UserModel extends Model
