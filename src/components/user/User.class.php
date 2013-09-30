<?php
/**
 * Handles most of the user interaction and acts as a base for the
 * various specific User-backends to extend from.
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

class User {

	/**
	 * Default number of iterations to hash the password with.
	 * *WARNING* Setting this to 15 will take about 3 seconds on an 8-core system and 10 seconds on a 2-core system!
	 *
	 * @var int
	 */
	const HASH_ITERATIONS = 11;

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
	 * Cache of the resolved permissions for this user.
	 *
	 * @var null|array
	 */
	protected $_resolvedpermissions = null;

	/**
	 * Cache of user objects, used to minimize lookups of users.
	 * @var array
	 */
	private static $_Cache = array();

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
			if(array_key_exists($k, $c)){
				$this->_configs[$k]->set('value', $v);
			}
		}
	}

	/**
	 * Set the arguments on this user object from an array.
	 * @param $array
	 */
	public function setFromArray($array){
		foreach($array as $k => $v){
			$this->set($k, $v);
		}
	}

	/**
	 * Populate this user object from a form
	 *
	 * @param Form $form
	 * @param null $prefix
	 *
	 * @return bool
	 * @throws ModelValidationException
	 */
	public function setFromForm(Form $form, $prefix = null){
		// Sanity checks and validation passed, (right?...), now create the actual account.
		// For that, I need to assemble clean data to send to the appropriate backend, (in this case datamodel).
		$attributes = array();
		foreach($form->getElements() as $el){
			/** @var $el FormElement */

			$name = $el->get('name');

			// If a prefix was requested and it doesn't match, skip this element.
			if($prefix && strpos($name, $prefix . '[') !== 0) continue;

			// Otherwise if there is a prefix, trim it off from the name.
			if($prefix){
				// Some of the options may be nested arrays, they'll need to be treated differently since the format is different,
				// prefix[option][phone] vs prefix[email]
				if(strpos($name, '][')){
					$name = str_replace('][', '[', substr($name, strlen($prefix) + 1));
				}
				else{
					$name = substr($name, strlen($prefix) + 1, -1);
				}
			}


			// Email?
			if($name == 'email'){
				$v = $el->get('value');

				if($v != $this->get('email')){
					// Try to retrieve the user data from the database based on the email.
					// Email is a unique key, so there can only be 1 in the system.
					if(UserModel::Find(array('email' => $v), 1)){
						$el->setError(true, false);
						throw new ModelValidationException('Requested email is already registered');
						return false;
					}

					$this->set('email', $v);
				}
			}

			// Is this element a config option?
			elseif(strpos($name, 'option[') === 0){
				$k = substr($el->get('name'), 7, -1);
				$v = $el->get('value');
				$obj = UserConfigModel::Construct($k);


				if($v === null && $obj->get('formtype') == 'checkbox'){
					// Checkboxes behave slightly differently.
					// null here just means that it was unchecked.
					$v = 0;
				}

				// Some attributes require some modifications.
				if($el instanceof FormFileInput){
					$v = 'public/user/' . $v;
				}

				$this->set($k, $v);
			}

			// Is this element the group definition?
			elseif($name == 'groups[]'){
				$v = $el->get('value');

				$this->setGroups($v);
			}

			elseif($name == 'active'){
				$this->set('active', $el->get('value') ? 1 : 0);
			}

			elseif($name == 'admin'){
				$this->set('admin', $el->get('value'));
			}

			elseif($name == 'avatar'){
				$this->set('avatar', $el->get('value'));
			}

			else{
				// I don't care.
			}
		} // foreach(elements)

	}

	/**
	 * Update the password for this user.
	 *
	 * This will make use of the underlying model setPassword function.
	 *
	 * @param $password
	 * @param $confirm
	 *
	 * @throws ModelException
	 * @throws ModelValidationException
	 *
	 * @return bool
	 */
	public function setPassword($password, $confirm){
		if($password != $confirm){
			throw new ModelValidationException('Passwords do not match');
			return false;
		}

		$this->_getModel()->setPassword($password);
		return true;
	}

	/**
	 * Save this user and all of its metadata, including configs and groups.
	 *
	 * @return bool
	 */
	public function save() {
		// No user object, no need to do anything.
		if($this->_model === null) return false;

		// Set to true if something changed.
		$status  = false;
		$isnew   = $this->_getModel()->isnew();
		$manager = \Core\user()->checkAccess('p:/user/users/manage');
		$admin   = \Core\user()->checkAccess('g:admin');

		if($this->_getModel()->changed()){
			//if(!$manager && !$isnew && ConfigHandler::Get('/user/profileedits/requireapproval') && Core::IsComponentAvailable('model-audit')){
			//	// If the option to require administrative approval is checked, any existing user change must be approved.
			//	\ModelAudit\Helper::SaveDraftOnly($this->_getModel());
			//}
			//else{
			$this->_getModel()->save();
			//}

			$status = true;
		}



		// also update any/all config options.
		if($this->_configs){
			foreach($this->_configs as $c){
				/** @var $c UserUserConfigModel */
				$c->set('user_id', $this->get('id'));

				if($c->changed()){
					if(!$manager && !$isnew && ConfigHandler::Get('/user/profileedits/requireapproval') && Core::IsComponentAvailable('model-audit')){
						\ModelAudit\Helper::SaveDraftOnly($c);
					}
					else{
						$c->save();
					}

					$status = true;
				}
			} // foreach($this->_configs as $c)
		} // if($this->_configs)

		// Fire off the hook!
		HookHandler::DispatchHook('/user/postsave', $this);

		return $status;
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
	 * Get a single user config object
	 *
	 * @param $key
	 * @return UserUserConfigModel|null
	 */
	public function getConfigObject($key){
		$configs = $this->getConfigs();

		return (isset($this->_configs[$key])) ? $this->_configs[$key] : null;
	}

	/**
	 * Get all the config objects associated to this user.
	 *
	 * @return array
	 */
	public function getConfigObjects(){
		$this->getConfigs();
		return $this->_configs;
	}

	/**
	 * Clear out the access string cache.
	 *
	 * This is useful if groups on a given user change.
	 */
	public function clearAccessStringCache(){
		$this->_accessstringchecks = array();
		$this->_resolvedpermissions = null;
	}


	/**
	 * Check access for a given access string against this user.
	 *
	 * The access string is the core component to Core+ authentication.
	 *
	 * @since 2011.08
	 * @param type $accessstring
	 * @return bool
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
			// Sometimes the type has the '!'... this is acceptable too.
			elseif($type{0} == '!'){
				$ret = false;
				$type = substr($type, 1);
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
				// All the other groups will be ID based, yayz!
				if(in_array($dat, $this->getGroups())){
					$cache = $ret;
					return $ret;
				}
			}
			elseif($type == 'p'){
				if(in_array($dat, $this->_getResolvedPermissions())){
					$cache = $ret;
					return $ret;
				}
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

	/**
	 * Set all groups for a given user on the current site.
	 *
	 * @param array $groups
	 */
	public function setGroups($groups){
		die('Please extend ' . __METHOD__ . ' in ' . get_called_class() . '!');
	}

	public function getGroups(){
		throw new Exception('getGroups must be extended in the specific backend.');
	}

	/**
	 * Get the display name for this user, based on the configuration settings.
	 *
	 * @return string
	 */
	public function getDisplayName(){

		// Anonymous users don't have all this fancy logic.
		if(!$this->exists()){
			return ConfigHandler::Get('/user/displayname/anonymous');
		}

		$displayas = ConfigHandler::Get('/user/displayas');

		switch($displayas){
			case 'username':
				return $this->get('username');
			case 'firstname':
				return $this->get('first_name');
			case 'emailfull':
				return $this->get('email');
			case 'emailbase':
			default:
				return strstr($this->get('email'), '@', true);
		}
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



	//////////  PRIVATE METHODS \\\\\\\\\\\\\


	//////////  PROTECTED METHODS  \\\\\\\\\\\

	protected function __construct(){
		// This cannot be called directly, it should be extended with the appropriate backend.
	}

	/**
	 * Get the bound user model.
	 *
	 * @since 2011.07
	 * @return UserModel
	 */
	public function _getModel(){
		if($this->_model === null){
			$this->_model = new UserModel();
		}

		return $this->_model;
	}

	/**
	 * Lookup and see if this model instance has a draft saved for it.
	 *
	 * @return bool
	 */
	public function hasDraft(){
		if(!Core::IsComponentAvailable('model-audit')){
			// If the underlying component is not available, drafts cannot be enabled!
			return false;
		}
		else{
			if(ModelAudit\Helper::ModelHasDraft($this->_getModel())) return true;

			// This needs to include the config options too.
			foreach($this->getConfigObjects() as $c){
				if(ModelAudit\Helper::ModelHasDraft($c)) return true;
			}

			return false;
		}
	}

	/**
	 * Get the draft ID of this user, (or its config)
	 *
	 * @return string
	 */
	public function getDraftID(){
		if(!Core::IsComponentAvailable('model-audit')){
			// If the underlying component is not available, drafts cannot be enabled!
			return '';
		}
		else{
			if(ModelAudit\Helper::ModelHasDraft($this->_getModel())) return $this->_getModel()->get('___auditmodel')->get('revision');

			// This needs to include the config options too.
			foreach($this->getConfigObjects() as $c){
				if(ModelAudit\Helper::ModelHasDraft($c)){
					return $c->get('___auditmodel')->get('revision');
				}
			}

			return false;
		}
	}

	/**
	 * Get the draft status of this model.
	 *
	 * @return string
	 */
	public function getDraftStatus(){
		$this->hasDraft();
		if(!$this->exists()){
			// If it's here, it must be a draft creation :p
			return 'pending_creation';
		}
		elseif($this->hasDraft() && $this->get('___auditmodel') && $this->get('___auditmodel')->get('data') == '[]'){
			// A blank data record on the audit model indicates that the request is to be deleted.
			return 'pending_deletion';
		}
		elseif($this->hasDraft()){
			// Otherwise, just changes were performed.
			return 'pending_update';
		}
		else{
			// And if it exists and no draft object attached... then this doesn't have one.
			return '';
		}
	}

	/**
	 * Find the user for this object based on given criteria.
	 * This is open for the extending backends to utilize.
	 *
	 * @since 2011.07
	 *
	 * @param array       $where
	 * @param int         $limit
	 * @param string|null $order
	 */
	protected function _find($where = array(), $limit = 1, $order = null){
		$this->_model = UserModel::Find($where, $limit, $order);
	}

	/**
	 * Get an array of resolved permissions for this user using the group membership.
	 *
	 * @return array
	 */
	protected function _getResolvedPermissions(){
		if($this->_resolvedpermissions === null){
			$this->_resolvedpermissions = array();

			foreach($this->getGroups() as $groupid){
				$group = new UserGroupModel($groupid);
				$this->_resolvedpermissions = array_merge($this->_resolvedpermissions, $group->getPermissions());
			}
		}

		return $this->_resolvedpermissions;
	}

	/////////  PUBLIC STATIC FUNCTIONS  \\\\\\\\\

	/**
	 * Create a User object of the appropriate backend.
	 *
	 * @param string $backend
	 * @return User_Backend
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
	 * DOES NOT make use of the system cache!
	 *
	 * @param array $where
	 * @param int $limit
	 * @param type $order
	 *
	 * @return array|User_Backend|null
	 *         if $limit of 1 is given, a single User object or null is returned
	 *         else an array of User objects is returned, or an array with null.
	 */
	public static function Find($where = array(), $limit = 1, $order = null){
		// Will return the core of the user object, but I still need the appropriate container.
		$res = UserModel::Find($where, $limit, $order);

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

	/**
	 * Search for a user based on a search criteria.  This has functionality above and beyond just a simple Find
	 * because it will search the email and any custom fields that are marked as searchable.
	 *
	 * @param string $term The term to search for
	 *
	 * @return array An array of UserBackend objects
	 */
	public static function Search($term){
		// An array of IDs that have been matched.
		$matches = array();
		$users = array();

		// First is email, it's the simpliest.
		$emails = UserModel::FindRaw(array('email LIKE ' . $term . '%'));
		foreach($emails as $match){
			$matches[] = $match['id'];
		}

		// Next it gets more challenging... grab any "searchable" user config and do a search on that table.
		$configs = UserConfigModel::FindRaw(array('searchable = 1'));
		foreach($configs as $c){
			$uucfac = new ModelFactory('UserUserConfigModel');
			$uucfac->where('key = ' . $c['key']);
			$uucfac->where('value LIKE ' . $term . '%');
			$uuc = $uucfac->getRaw();
			foreach($uuc as $match){
				$matches[] = $match['user_id'];
			}
		}

		// Strip duplicates.
		$matches = array_unique($matches);

		// And now this array is what I'll be returning.
		foreach($matches as $id){
			$users[] = User::Construct($id);
		}

		return $users;
	}

	/**
	 * Get a user object from is id, or null if that user does not exist.
	 *
	 * This method DOES make use of the system cache for data!
	 *
	 * @param $userid
	 * @return UserBackend|null
	 */
	public static function Construct($userid){
		if(!array_key_exists($userid, self::$_Cache)){
			self::$_Cache[$userid] = self::Find(array('id' => $userid), 1);
		}

		return self::$_Cache[$userid];
	}
}

class UserException extends Exception{

}
