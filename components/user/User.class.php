<?php
/**
 * Handles most of the user interaction and acts as a base for the
 * various specific User-backends to extend from.
 *
 * @package
 * @since 0.1
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

class User {
	/**
	 * The user model attached to this user.
	 * @var UserModel
	 */
	protected $_model = null;
	
	private $_configs = null;
	
	/**
	 * Cache of the results of checkAccess.
	 * 
	 * This is just an internal convenience thing to increase performance slightly on
	 * repeated checks.
	 * 
	 * ie: First time checkAccess('p:blah;p:blah2') is called, the result is cached,
	 *     so if a second call is requested with the same data, the lookup array
	 *     will contain the result of the last run.
	 * 
	 * @var array
	 */
	protected $_accessstringchecks = array();
	
	
	/**
	 * Simple check if this user exists in the database.
	 * 
	 * @since 2011.08
	 * @return boolean
	 */
	public function exists(){
		return $this->_getModel()->exists();
	}
	
	/**
	 * Get a key from this current user either from the core
	 * user table or from the config options.
	 * 
	 * Will try the core table first, then check for a config key name
	 * that matches.
	 * 
	 * @since 2011.08
	 * @param string $key
	 * @return mixed String, boolean, int or float if exists, null if otherwise.
	 */
	public function get($key){
		$d = $this->_getModel()->getAsArray();
		if(array_key_exists($key, $d)){
			return $d[$key];
		}
		else{
			$c = $this->getConfigs();
			if(array_key_exists($key, $c)) return $this->_configs[$key]->get('value');
			else return null;
		}
	}
	
	/**
	 * Set a key on either the core user table or its config options.
	 * 
	 * Will try the core table first, then the config key.
	 * 
	 * @since 2011.08
	 * @param string $k
	 * @param mixed $v 
	 */
	public function set($k, $v) {
		$d = $this->_getModel()->getAsArray();
		
		if($k == 'password'){
			// Password gets set using the setPassword function due to the 
			// additional hashing requirements.
			$this->_getModel()->setPassword($v);
		}
		elseif(array_key_exists($k, $d)){
			$this->_getModel()->set($k, $v);
		}
		else{
			// Check the user config options.
			$c = $this->getConfigs();
			if(array_key_exists($k, $c)) $this->_configs[$k]->set('value', $v);
		}
	}
	
	/**
	 * Save this user and all of its metadata, including configs and groups.
	 * 
	 * @return void (TODO - return something meaningful)
	 */
	public function save() {
		// No user object, no need to do anything.
		if($this->_model === null) return false;
		
		$this->_getModel()->save();
		
		// also update any/all config options.
		if($this->_configs){
			foreach($this->_configs as $c){
				$c->set('user_id', $this->get('id'));
				$c->save();
			}
		}
	}

	
	/**
	 * Get all user configs for this given user
	 * 
	 * @since 2011.08
	 * @return array Key/value pair fo the configs. 
	 */
	public function getConfigs(){
		if($this->_configs === null){
			$this->_configs = array();
			$fac = UserConfigModel::Find();
			foreach($fac as $f){
				$uucm = new UserUserConfigModel($this->get('id'), $f->get('key'));
				
				// Handle the default value for the userconfig.
				if(!$uucm->exists()){
					$uucm->set('value', $f->get('default_value'));
				}
				$this->_configs[$f->get('key')] = $uucm;
			}
		}
		
		// Iterate over each set and just return a simple array.
		$ret = array();
		foreach($this->_configs as $k => $obj){
			$ret[$k] = $obj->get('value');
		}
		return $ret;
	}
	
	/**
	 * Check access for a given access string against this user.
	 * 
	 * The access string is the core component to Core+ authentication.
	 * 
	 * @since 2011.08
	 * @param type $accessstring 
	 */
	public function checkAccess($accessstring){
		
		// And gogo caching lookups!
		// DEVELOPMENT NOTE -- If you're working on this function,
		//  it might be best to disable the return here!...
		if(isset($this->_accessstringchecks[$accessstring])){
			// :)
			return $this->_accessstringchecks[$accessstring];
		}
		
		// Default behaviour (also set from * or !* flags).
		$default = false;
		
		// Lookup some common variables first.
		$loggedin = $this->exists();
		$isadmin = $this->get('admin');
		$cache =& $this->_accessstringchecks[$accessstring];
		
		// Case insensitive
		$accessstring = strtolower($accessstring);
		
		
		// Check if the current user is an admin... if so and there is no 
		// "g:!admin" flag, automatically set it to true.
		if($isadmin && strpos($accessstring, 'g:!admin') === false){
			$cache = true;
			return true;
		}
		
		// Default action is to be explicit.
		$default = false;
		
		// Explode on a semicolon(;), with string trimming.
		$parts = array_map('trim', explode(';', $accessstring));
		foreach($parts as $p){
			// This can happen if there is an access string such as 'g:authenticated;'.
			if($p == '') continue;
			
			// Wildcard is the exception, as it does not require a type:dat set.
			if($p == '*' || $p == '!*'){
				$type = '*';
				$dat = $p;
			}
			// Everything else is in the format of p:blah, g:my_group, etc.
			else{
				list($type, $dat) = array_map('trim', explode(':', $p));
			}
			
			// Each check can either be an 'ALLOW' or 'DENY'.
			// This is toggled by the presence of a '!'
			if($dat{0} == '!'){
				$ret = false;
				$dat = substr($dat, 1);
			}
			else{
				$ret = true;
				// No trim is needed.
			}
			
			
			
			// A few "special" checks.
			if($type == '*'){
				// This sets the default instead of returning immediately.
				$default = $ret;
				continue;
			}
			elseif($type == 'g' && $dat == 'anonymous'){
				if(!$loggedin){
					$cache = $ret;
					return $ret;
				}
			}
			elseif($type == 'g' && $dat == 'authenticated'){
				if($loggedin){
					$cache = $ret;
					return $ret;
				}
			}
			elseif($type == 'g' && $dat == 'admin'){
				if($isadmin){
					$cache = $ret;
					return $ret;
				}
			}
			elseif($type == 'g'){
				var_dump($type, $dat, $ret);
				die('@todo Finish the group lookup logic in User::checkAccess()');
			}
			elseif($type == 'p'){
				var_dump($type, $dat, $ret);
				die('@todo Finish the permission lookup logic in User::checkAccess()');
			}
			elseif($type == 'u'){
				var_dump($type, $dat, $ret);
				die('@todo Finish the user lookup logic in User::checkAccess()');
			}
			else{
				var_dump($type, $dat, $ret);
				die('Implement that access string check!');
			}
		}
		
		// Not found... return the default, (which is deny by default).
		$cache = $default;
		return $default;
	}
	
	public function checkPassword($password){
		die('Please extend ' . __METHOD__ . ' in ' . get_called_class() . '!');
	}
	
	public function getDisplayName(){
		
		// Anonymous users don't have all this fancy logic.
		if(!$this->exists()){
			return ConfigHandler::Get('/user/displayname/anonymous');
		}
		
		$displayopts = ConfigHandler::Get('/user/displayname/displayoptions');
		
		// Simple enough.
		if( ($u = $this->get('username')) && in_array('username', $displayopts)) return $u;
		// Next, the first name.
		elseif( ($n = $this->get('first_name')) && in_array('firstname', $displayopts)) return $n;
		// Next, the email base.
		elseif( ($e = $this->get('email')) && in_array('emailbase', $displayopts)) return strstr($e, '@', true);
		// Next, the email in full.
		elseif( ($e = $this->get('email')) && in_array('emailfull', $displayopts)) return $e;
		// Still no?!?
		else return ConfigHandler::Get('/user/displayname/authenticated');
	}
	
	/**
	 * Simple function that can be used to return either true if this backend
	 * supports resetting the password, or a string to display as an error message
	 * if it cannot.
	 * 
	 * Useful for facebook-type accounts, where an external system manages the password.
	 * 
	 * @return true | string
	 */
	public function canResetPassword(){
		return true;
	}
	
	
	//////////  PROTECTED METHODS  \\\\\\\\\\\
	
	
	/**
	 * Get the bound user model.
	 * 
	 * @since 2011.07
	 * @return UserModel
	 */
	protected function _getModel(){
		if($this->_model === null){
			$this->_model = new UserModel();
		}
		
		return $this->_model;
	}
	
	/**
	 * Find the user for this object based on given criteria.
	 * This is open for the extending backends to utilize.
	 * 
	 * @since 2011.07
	 * @param array $where
	 */
	protected function _find($where = array()){
		$this->_model = UserModel::Find($where, 1);
	}
	
	/////////  PUBLIC STATIC FUNCTIONS  \\\\\\\\\
	
	/**
	 * Create a User object of the appropriate backend.
	 * 
	 * @param string $backend
	 * @return User
	 */
	public static function Factory($backend = null){
		// Default to the Datamodel user object.
		if(!$backend) $backend = 'datamodel';
		if(!class_exists('User_' . $backend . '_Backend')) $backend = 'datamodel';
		
		$c = 'User_' . $backend . '_Backend';
		return new $c();
	}
	
	/**
	 * The externally usable method to find and return the appropriate user backend.
	 * 
	 * @param array $where
	 * @param int $limit 
	 * @return mixed Array, User, or null.
	 *         if $limit of 1 is given, a single User object or null is returned
	 *         else an array of User objects is returned, or an array with null.
	 */
	public static function Find($where = array(), $limit = 1){
		// Will return the core of the user object, but I still need the appropriate container.
		$res = UserModel::Find($where, $limit);
		
		if(!is_array($res)) $res = array($res);
		
		$return = array();
		foreach($res as $model){
			if($model){
				$o = self::Factory($model->get('backend'));
				$o->_model = $model;
			}
			else{
				$o = null;
			}
			$return[] = $o;
		}
		
		return ($limit == 1)? $return[0] : $return;
	}
}

class UserException extends Exception{
	
}