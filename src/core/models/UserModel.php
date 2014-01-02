<?php
/**
 * Model for UserModel
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

class UserModel extends Model {

	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_UUID,
			'required' => true,
			'null' => false,
		),
		'email' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'null' => false,
			'validation' => ['this', 'validateEmail'],
			'required' => true,
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
		'avatar' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => '64',
			'form' => array(
				'type' => 'file',
				'accept' => 'image/*',
				'basedir' => 'public/user/avatar',
			),
		),
		'registration_ip' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => '24',
			'comment' => 'The original IP of the user registration',
		),
		'registration_source' => array(
			'type' => Model::ATT_TYPE_STRING,
			'default' => 'self',
			'comment' => 'The source of the user registration, either self, admin, or other.'
		),
		'registration_invitee' => array(
			'type' => Model::ATT_TYPE_INT,
			'comment' => 'If invited/created by a user, this is the ID of that user.',
		),
		'last_login' => array(
			'type' => Model::ATT_TYPE_INT,
			'comment' => 'The timestamp of the last login of this user',
		),
		'last_password' => array(
			'type' => Model::ATT_TYPE_INT,
			'comment' => 'The timestamp of the last password reset of this user',
		),
	);

	public static $Indexes = array(
		'primary' => array('id'),
		'unique:email' => array('email'),
	);

	public static $HasSearch  = true;
	public static $HasCreated = true;
	public static $HasUpdated = true;





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
	 * @var null|array Cache of config options
	 */
	protected $_configs = null;

	/**
	 * @var null|\Core\User\AuthDriverInterface The AuthDriver backend for this user
	 */
	protected $_authdriver = null;

	public function __construct($id = null){
		$this->_linked['UserUserConfig'] = [
			'link' => Model::LINK_HASMANY,
			'on' => ['user_id' => 'id'],
		];
		$this->_linked['UserUserGroup'] = [
			'link' => Model::LINK_HASMANY,
			'on' => ['user_id' => 'id'],
		];

		parent::__construct($id);
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
		if(array_key_exists($key, $this->_data)){
			return parent::get($key);
		}
		elseif(($c = $this->getConfigObject($key)) !== null){
			return $c->get('value');
		}
		elseif(array_key_exists($key, $this->_dataother)){
			return $this->_dataother[$key];
		}
		else{
			return null;
		}
	}

	/**
	 * Get the human-readable label for this record.
	 *
	 * By default, it will sift through the schema looking for keys that appear to be human-readable terms,
	 * but for best results, please extend this method and have it return what's necessary for the given Model.
	 *
	 * @return string
	 */
	public function getLabel(){
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
	 * Alias of getLabel().
	 *
	 * Gets the label/display name of this user.
	 *
	 * @return string
	 */
	public function getDisplayName(){
		return $this->getLabel();
	}

	/**
	 * Get all user configs for this given user.
	 *
	 * These options will be populated with the default values if none exist.
	 *
	 * @since 2011.08
	 * @return array Key/value pair for each config option.
	 */
	public function getConfigs(){
		if($this->_configs === null){
			$this->_configs = [];
			$uucrecords     = $this->getLink('UserUserConfig');

			$fac = UserConfigModel::Find();
			foreach($fac as $f){
				/** @var UserConfigModel $f */
				$key     = $f->get('key');
				$default = $f->get('default_value');

				// Look for this UUC from the list of records.
				foreach($uucrecords as $uuc){
					/** @var UserUserConfigModel $uuc */
					if($uuc->get('key') == $key){
						// Yay, it exists and is ready to go.
						$this->_configs[$key] = $uuc;
						// Skip to the next UserConfig object!
						continue 2;
					}
				}

				// If it's still here, the previous UUC logic didn't break out,
				// which means the uuc doesn't exist.... yet :p
				try{
					$uuc = new UserUserConfigModel($this->get('id'), $key);
					$uuc->set('value', $default);
					// Add this object to the list set of child models, just in case it's saved.
					$this->setLink('UserUserConfig', $uuc);
					// And add to the stack.
					$this->_configs[$key] = $uuc;
				}
				catch(Exception $e){
					trigger_error('Invalid UserConfig [' . $f->get('key') . '], ' . $e->getMessage(), E_USER_NOTICE);
					// And simply don't add this one onto the user stack.
					// This is allowed because if the default isn't valid, then the user config itself isn't valid.
				}
			}
		}

		// Iterate over each set and just return a simple array.
		$ret = array();
		foreach($this->_configs as $k => $obj){
			/** @var UserUserConfigModel $obj */
			$ret[$k] = $obj->get('value');
		}
		return $ret;
	}

	/**
	 * Get a single user config object
	 *
	 * @param string $key The UserConfig key to lookup
	 * @return UserUserConfigModel|null
	 */
	public function getConfigObject($key){
		// Ensure the cache is populated.
		$this->getConfigs();
		return (isset($this->_configs[$key])) ? $this->_configs[$key] : null;
	}

	/**
	 * Get all the config objects associated to this user.
	 *
	 * @return array Array of UserUserConfigModels.
	 */
	public function getConfigObjects(){
		// Ensure the cache is populated.
		$this->getConfigs();
		return $this->_configs;
	}

	/**
	 * Get an array of the group IDs this user is a member of.
	 *
	 * This will only return standard groups, context groups WILL NOT BE RETURNED.
	 *
	 * @return array
	 */
	public function getGroups() {
		$out  = [];
		$uugs = $this->getLink('UserUserGroup');
		foreach($uugs as $uug){
			/** @var UserUserGroupModel $uug */

			// Skip context groups.
			// These are a little more complex.
			if($uug->get('context')) continue;

			if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
				// Only return this site's groups if in multisite mode
				$g = $uug->getLink('UserGroup');
				if($g->get('site') == MultiSiteHelper::GetCurrentSiteID()){
					$out[] = $g->get('id');
				}
			}
			else{
				// Else I can just return all the groups.
				$out[] = $uug->get('group_id');
			}
		}
		return $out;
	}

	/**
	 * Get an array of the group data this user is a member of.
	 *
	 * This will only return context groups, regular groups WILL NOT BE RETURNED.
	 *
	 * @param null|Model|string $context        The context to return groups of, optionally provided
	 * @param bool              $return_objects Set to true to return an array of UserUserGroup objects instead of a flat array of IDs.
	 *
	 * @return array
	 */
	public function getContextGroups($context = null, $return_objects = false) {
		$out  = [];
		$uugs = $this->getLink('UserUserGroup');


		if($context && $context instanceof Model){
			// If there was a context requested, only return that context.
			$contextname = substr(get_class($context), 0, -5);
			$contextpk   = $context->getPrimaryKeyString();
		}
		elseif(is_scalar($context)){
			// If a context name was provided, search for just that model.
			$contextname = $context;
			$contextpk   = null;
		}
		else{
			// No parameters provided, just return everything!
			$contextname = null;
			$contextpk   = null;
		}

		foreach($uugs as $uug){
			/** @var UserUserGroupModel $uug */

			// Skip regular groups.
			if(!$uug->get('context')) continue;

			if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
				// Only return this site's groups if in multisite mode
				$g = $uug->getLink('UserGroup');
				$gsite = $g->get('site');
				if(
					!($gsite == '-1' || $gsite == MultiSiteHelper::GetCurrentSiteID())
				){
					continue;
				}
			}

			if($contextname && $uug->get('context') != $contextname) continue;
			if($contextpk && $uug->get('context_pk') != $contextpk) continue;


			// If it's gotten here, I can return this group!
			if($return_objects){
				$out[] = $uug;
			}
			else{
				$out[] = [
					'group_id'   => $uug->get('group_id'),
					'context'    => $uug->get('context'),
					'context_pk' => $uug->get('context_pk'),
				];
			}
		}
		return $out;
	}

	/**
	 * Get the auth driver for this usermodel.
	 *
	 * @return \Core\User\AuthDriverInterface
	 * @throws Exception
	 */
	public function getAuthDriver(){
		if($this->_authdriver === null){
			$driver = $this->get('backend');
			if(!class_exists('\\Core\\User\\AuthDrivers\\' . $driver)){
				throw new Exception('Invalid auth backend for user, ' . $driver);
			}

			$ref = new ReflectionClass('\\Core\\User\\AuthDrivers\\' . $driver);
			$this->_authdriver = $ref->newInstance($this);
		}

		return $this->_authdriver;
	}

	/**
	 * Get a textual representation of this Model as a flat string.
	 *
	 * Used by the search systems to index the model, (or multiple models into one).
	 *
	 * @return string
	 */
	public function getSearchIndexString(){
		// The default behaviour is to sift through the records on this model itself.
		$strs = [];

		// The user account only has an email address
		$strs[] = $this->get('email');

		// I also need to sift over the user config options, since they relate to this object too.
		$opts = UserConfigModel::Find();
		foreach($opts as $uc){
			/** @var UserConfigModel $uc */
			if($uc->get('searchable')){
				$strs[] = $this->get($uc->get('key'));
			}
		}

		return implode(' ', $strs);
	}

	public function validateEmail($email){
		if($email == $this->get('email')){
			// If the email is currently the user's email, then it's allowed.
			return true;
		}

		if(!Core::CheckEmailValidity($email)){
			return 'Does not appear to be a valid email address';
		}

		// Try to retrieve the user data from the database based on the email.
		// Email is a unique key, so there can only be 1 in the system.
		if(UserModel::Find(array('email' => $email), 1)){
			// Another user was located with the same email.... tsk tsk
			return 'Requested email is already registered';
		}

		// Must be ok!
		return true;
	}

	/**
	 * Use Core's configurable logic to validate the password.
	 *
	 * Please note that not all auth backends may use this!!!
	 *
	 * @param string $password
	 *
	 * @return bool|string
	 * @throws ModelValidationException
	 */
	public function validatePassword($password){
		$valid = true;
		// complexity check from the config
		if(strlen($password) < ConfigHandler::Get('/user/password/minlength')){
			$valid = 'Please ensure that the password is at least ' . ConfigHandler::Get('/user/password/minlength') . ' characters long.';
		}

		// complexity check from the config
		if(ConfigHandler::Get('/user/password/requiresymbols') > 0){
			preg_match_all('/[^a-zA-Z0-9]/', $password, $matches);
			if(sizeof($matches[0]) < ConfigHandler::Get('/user/password/requiresymbols')){
				$valid = 'Please ensure that the password has at least ' . ConfigHandler::Get('/user/password/requiresymbols') . ' symbol(s).';
			}
		}

		// complexity check from the config
		if(ConfigHandler::Get('/user/password/requirecapitals') > 0){
			preg_match_all('/[A-Z]/', $password, $matches);
			if(sizeof($matches[0]) < ConfigHandler::Get('/user/password/requirecapitals')){
				$valid = 'Please ensure that the password has at least ' . ConfigHandler::Get('/user/password/requirecapitals') . ' capital letter(s).';
			}
		}

		// complexity check from the config
		if(ConfigHandler::Get('/user/password/requirenumbers') > 0){
			preg_match_all('/[0-9]/', $password, $matches);
			if(sizeof($matches[0]) < ConfigHandler::Get('/user/password/requirenumbers')){
				$valid = 'Please ensure that the password has at least ' . ConfigHandler::Get('/user/password/requirenumbers') . ' number(s).';
			}
		}

		return $valid;
	}

	/**
	 * Set a key or config option on this user.
	 *
	 * @param string $k
	 * @param mixed  $v
	 *
	 * @return bool
	 */
	public function set($k, $v) {
		if(array_key_exists($k, $this->_data)){
			// The key exists, it's a standard set.
			return parent::set($k, $v);
		}
		elseif(($c = $this->getConfigObject($k)) !== null){
			return $c->set('value', $v);
		}
		else{
			$this->_dataother[$k] = $v;
			return true;
		}
	}

	/**
	 * Set all groups for a given user on the current site from a set of IDs.
	 *
	 * This method ONLY supports non-context groups.
	 *
	 * @param array $groups
	 */
	public function setGroups($groups) {
		if(!is_array($groups)) $groups = [];
		$this->_setGroups($groups, false);
	}

	/**
	 * Set all groups for a given user on the current site from a set of IDs.
	 *
	 * This method ONLY supports context groups.
	 *
	 * @param array             $groups
	 * @param null|Model|string $context The context to overwrite groups to, optional
	 */
	public function setContextGroups($groups, $context = null) {
		if(!is_array($groups)) $groups = [];

		// If no context was provided, the default is to override them all!
		$this->_setGroups($groups, $context === null ? true : $context);
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
		foreach($form->getElements() as $el){
			/** @var $el FormElement */

			$name  = $el->get('name');
			$value = $el->get('value');

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
				// Validation handled internally!
				$this->set('email', $value);
			}

			// Is this element a config option?
			elseif(strpos($name, 'option[') === 0){
				$k = substr($el->get('name'), 7, -1);
				$obj = $this->getConfigObject($k)->getLink('UserConfig');

				if($value === null && $obj->get('formtype') == 'checkbox'){
					// Checkboxes behave slightly differently.
					// null here just means that it was unchecked.
					$value = 0;
				}

				// Some attributes require some modifications.
				if($el instanceof FormFileInput){
					$value = 'public/user/config/' . $value;
				}

				$this->set($k, $value);
			}

			// Is this element the group definition?
			elseif($name == 'groups[]'){
				$this->setGroups($value);
			}

			elseif($name == 'active'){
				$this->set('active', $value ? 1 : 0);
			}

			elseif($name == 'admin'){
				$this->set('admin', $value);
			}

			elseif($name == 'avatar'){
				$this->set('avatar', $value);
			}

			elseif($name == 'contextgroup[]'){
				// This is a two-part system with data pulling from contextgroup and contextgroupcontext.
				$gids       = $value;
				$contextpks = $form->getElement('contextgroupcontext[]')->get('value');
				$groups     = [];

				foreach($gids as $key => $gid){
					// Skip blank group selections.
					if(!$gid) continue;

					// Pull the group information for this gid since that will contain the context.
					$group = UserGroupModel::Construct($gid);

					$context   = $group->get('context');
					$contextpk = $contextpks[$key];

					$groups[] = [
						'group_id'   => $gid,
						'context'    => $context,
						'context_pk' => $contextpk,
					];
				}

				$this->setContextGroups($groups);
			}

			else{
				// I don't care.
			}
		} // foreach(elements)
	}

	public function save() {
		// Every usermodel needs to have an apikey set prior to saving.
		if(!$this->_data['apikey']){
			$this->generateNewApiKey();
		}

		// The parent save system will handle all child objects and everything.
		$status = parent::save();

		// Fire off the hook!
		HookHandler::DispatchHook('/user/postsave', $this);

		return $status;
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
	 * @param string $accessstring The access string to parse.
	 * @param null|Model $context  The context to search for access within.
	 *
	 * @return bool
	 */
	public function checkAccess($accessstring, $context = null){

		$findkey = $accessstring . '-' . $this->_getContextKey($context);

		// And gogo caching lookups!
		// DEVELOPMENT NOTE -- If you're working on this function,
		//  it might be best to disable the return here!...
		if(isset($this->_accessstringchecks[$findkey])){
			// :)
			return $this->_accessstringchecks[$findkey];
		}

		// Default behaviour (also set from * or !* flags).
		$default  = false;
		// Lookup some common variables first.
		$loggedin = $this->exists();
		$isadmin  = $this->get('admin');
		$cache    =& $this->_accessstringchecks[$findkey];

		// All checks are case insensitive
		$accessstring = strtolower($accessstring);


		// Check if the current user is an admin... if so and there is no
		// "g:!admin" flag, automatically set it to true.
		if($isadmin && strpos($accessstring, 'g:!admin') === false){
			$cache = true;
			return true;
		}

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
				if(in_array($dat, $this->_getResolvedPermissions($context))){
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

	/**
	 * Get an array of resolved permissions for this user using the group membership.
	 *
	 * @param null|Model $context The context to search for.
	 * @return array
	 */
	protected function _getResolvedPermissions($context = null){

		$findkey = $this->_getContextKey($context);

		if($this->_resolvedpermissions === null){
			$this->_resolvedpermissions = array();

			foreach($this->getLink('UserUserGroup') as $uug){
				/** @var UserUserGroupModel $uug */

				$key = $uug->get('context') ? $uug->get('context') . ':' . $uug->get('context_pk') : '';
				if(!isset($this->_resolvedpermissions[$key])){
					$this->_resolvedpermissions[$key] = [];
				}

				/** @var UserGroupModel $group */
				$group = $uug->getLink('UserGroup');
				$this->_resolvedpermissions[$key] = array_merge($this->_resolvedpermissions[$key], $group->getPermissions());
			}
		}

		return isset($this->_resolvedpermissions[$findkey]) ? $this->_resolvedpermissions[$findkey] : [];
	}

	/**
	 * Set all groups for a given user on the current site from a set of IDs.
	 *
	 * @param array             $groups
	 * @param bool|Model|string $context True to set all context groups, false to ignore, a string or model for the specific context.
	 *
	 * @throws Exception
	 */
	protected function _setGroups($groups, $context) {

		// Map the groups to a complex array if necessary.
		foreach($groups as $key => $data){
			if(!is_array($data)){
				$groups[$key] = [
					'group_id'   => $data,
					'context'    => '',
					'context_pk' => '',
				];
			}
		}

		if($context === false){
			// Skip all context groups.
			$contextname = null;
			$contextpk   = null;
		}
		elseif($context === true){
			// Skip regular groups, but include all context groups.
			$contextname = null;
			$contextpk   = null;
		}
		elseif($context instanceof Model){
			$contextname = substr(get_class($context), 0, -5);
			$contextpk   = $context->getPrimaryKeyString();
			$context     = true;
		}
		elseif(is_scalar($context)){
			$contextname = $context;
			$contextpk   = null;
			$context     = true;
		}
		else{
			throw new Exception('If a context is provided, please ensure it is either a model or model name');
		}

		$uugs = $this->getLink('UserUserGroup');
		foreach($uugs as $uug){
			/** @var UserUserGroupModel $uug */

			// Only process the requested group types.
			if($context && !$uug->get('context')){
				// A context option was selected, but this is a regular group, skip it.
				continue;
			}
			elseif(!$context && $uug->get('context')){
				// Similarly, no context was requested, but this group has one.
				continue;
			}
			elseif($context && $contextname && $uug->get('context') != $contextname){
				// A context was requested, and a specific context name was set also!
				// But it doesn't match.... SKIP!
				continue;
			}
			elseif($context && $contextpk && $uug->get('context_pk') != $contextpk){
				// A context was requested, and a specific context name was set also!
				// But it doesn't match.... SKIP!
				continue;
			}

			if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
				// Only return this site's groups if in multisite mode
				$ugsite = $uug->getLink('UserGroup')->get('site');
				if(!($ugsite == -1 || $ugsite == MultiSiteHelper::GetCurrentSiteID())){
					/// Skip any group not on this site... they'll simply be ignored.
					continue;
				}
			}

			$gid        = $uug->get('group_id');
			$gcontext   = $uug->get('context');
			$gcontextpk = $uug->get('context_pk');
			foreach($groups as $key => $data){
				if(
					$data['group_id'] == $gid &&
					$data['context'] == $gcontext &&
					$data['context_pk'] == $gcontextpk
				){
					// Yay, group matches up with both!
					// Unlink it from the groups array so it doesn't try to get recreated.
					unset($groups[$key]);
					continue 2;
				}
			}

			// This group isn't in the new list, unset it!
			$this->deleteLink($uug);
		}

		// Any new groups remaining?
		foreach($groups as $data){
			$this->setLink(
				'UserUserGroup',
				new UserUserGroupModel(
					$this->get('id'),
					$data['group_id'],
					$data['context'],
					$data['context_pk']
				)
			);
		}

		// And clear the cache!
		$this->clearAccessStringCache();
	}

	/**
	 * Resolve a context object to its cacheable key.
	 *
	 * Used internally.
	 *
	 * @param $context
	 *
	 * @return string
	 * @throws Exception
	 */
	protected function _getContextKey($context){
		if($context === null || $context === ''){
			// OK, allowed.  This is the global context.
			return '';
		}
		elseif($context instanceof Model){
			return substr(get_class($context), 0, -5) . ':' . $context->getPrimaryKeyString();
		}
		else{
			throw new Exception('Invalid context provided for _getResolvedPermissions!');
		}
	}

	/**
	 * Search for a user based on a search criteria.  This has functionality above and beyond just a simple Find
	 * because it will search the email and any custom fields that are marked as searchable.
	 *
	 * @param string $query The term to search for
	 * @param array $where Any additional where clause to tack on.
	 *
	 * @return array An array of UserModel objects
	 */
	public static function Search($query, $where = array()){

		$ret = [];
		$schema = self::GetSchema();
		$configwheres = [];

		// If this object does not support searching, simply return an empty array.
		$ref = new ReflectionClass(get_called_class());

		if(!$ref->getProperty('HasSearch')->getValue()){
			return $ret;
		}

		$fac = new ModelFactory(get_called_class());

		if(sizeof($where)){
			$clause = new \Core\Datamodel\DatasetWhereClause();
			$clause->addWhere($where);
			// If this isn't actually a column present, maybe it's a user user config option instead.
			foreach($clause->getStatements() as $statement){
				/** @var \Core\Datamodel\DatasetWhere $statement */
				if(isset($schema[$statement->field])){
					$fac->where($statement);
				}
				else{
					$configwheres[] = $statement;
				}
			}
		}

		if($ref->getProperty('HasDeleted')->getValue()){
			$fac->where('deleted = 0');
		}

		$fac->where(\Core\Search\Helper::GetWhereClause($query));
		foreach($fac->get() as $m){
			/** @var UserModel $m */

			$add = true;

			// If this user has configs that don't match the userconfig where requested, skip it.
			foreach($configwheres as $statement){
				/** @var \Core\Datamodel\DatasetWhere $statement */
				if(($config = $m->getConfigObject($statement->field))){
					switch($statement->op){
						case '=':
							if($config->get('value') != $statement->value){
								$add = false;
								break 2;
							}
							break;
						default:
							// @todo.
					}
				}
			}

			if($add){
				$sr = new \Core\Search\ModelResult($query, $m);

				// This may happen since the where clause can be a little open-ended.
				if($sr->relevancy < 1) continue;
				$sr->title = $m->getLabel();
				$sr->link  = $m->get('baseurl');

				$ret[] = $sr;
			}
		}
		return $ret;
	}
}