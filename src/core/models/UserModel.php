<?php

/**
 * Model for UserModel
 *
 * @package   User
 * @since     1.9
 * @author    Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2016  Charlie Powell
 * @license   GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
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

	public static $Schema = [
		'id'                   => [
			'type'     => Model::ATT_TYPE_UUID,
			'required' => true,
			'null'     => false,
		],
		'email'                => [
			'type'       => Model::ATT_TYPE_STRING,
			'maxlength'  => 64,
			'null'       => false,
			'formtype' => 'disabled',
			'validation' => ['this', 'validateEmail'],
			'required'   => true,
		],
		'backend'              => [
			'type'     => Model::ATT_TYPE_STRING,
			'formtype' => 'disabled',
			'default'  => 'datastore',
			'comment'  => 'Pipe-delimited list of authentication drivers on this user'
		],
		'password'             => [
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 60,
			'null'      => false,
			'formtype'  => 'disabled',
		],
		'apikey'               => [
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'null'      => false,
			'formtype'  => 'disabled',
		],
		'active'               => [
			'type'    => Model::ATT_TYPE_ENUM,
			'default' => '1',
			'options' => ['-1', '0', '1'],
			'null'    => false,
			'form'    => [
				'type' => 'disabled',
				'title'   => 'User Status',
				'options' => [
					'-1' => 'Disabled',
					'0'  => 'Not Activated Yet',
					'1'  => 'Active',
				],
			],
		],
		'admin'                => [
			'type'    => Model::ATT_TYPE_BOOL,
			'default' => '0',
			'null'    => false,
			'formtype'  => 'disabled',
		],
		'avatar'               => [
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => '128',
			'form'      => [
				'type'    => 'file',
				'accept'  => 'image/*',
				'basedir' => 'public/user/avatar',
			],
		],
		'gpgauth_pubkey' => [
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 40,
			'formtype'  => 'disabled',
		],
		'external_profiles' => [
			'type' => Model::ATT_TYPE_DATA,
			'encoding' => Model::ATT_ENCODING_JSON,
			'formtype'  => 'disabled',
		],
		'registration_ip'      => [
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => '24',
			'comment'   => 'The original IP of the user registration',
			'formtype'  => 'disabled',
		],
		'registration_source'  => [
			'type'    => Model::ATT_TYPE_STRING,
			'default' => 'self',
			'comment' => 'The source of the user registration, either self, admin, or other.',
			'formtype'  => 'disabled',
		],
		'registration_invitee' => [
			'type'    => Model::ATT_TYPE_UUID_FK,
			'comment' => 'If invited/created by a user, this is the ID of that user.',
			'formtype'  => 'disabled',
		],
		'last_login'           => [
			'type'    => Model::ATT_TYPE_INT,
			'default' => 0,
			'comment' => 'The timestamp of the last login of this user',
			'formtype'  => 'disabled',
		],
		'last_password'        => [
			'type'    => Model::ATT_TYPE_INT,
			'default' => 0,
			'comment' => 'The timestamp of the last password reset of this user',
			'formtype'  => 'disabled',
		],
	];

	public static $Indexes = [
		'primary'      => ['id'],
		'unique:email' => ['email'],
	];

	public static $HasSearch = true;
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
	protected $_accessstringchecks = [];

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
	 * @var array Array of \Core\User\AuthDriverInterface that this user has enabled.
	 */
	protected $_authdriver = [];

	public function __construct($id = null) {
		$this->_linked['UserUserConfig'] = [
			'link' => Model::LINK_HASMANY,
			'on'   => ['user_id' => 'id'],
		];
		$this->_linked['UserUserGroup']  = [
			'link' => Model::LINK_HASMANY,
			'on'   => ['user_id' => 'id'],
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
	 *
	 * @param string $key
	 *
	 * @return mixed String, boolean, int or float if exists, null if otherwise.
	 */
	/*public function get($key) {
		if($this->getKeySchema($key) !== null){
			return parent::get($key);
		}
		elseif(($c = $this->getConfigObject($key)) !== null) {
			return $c->get('value');
		}
		elseif(array_key_exists($key, $this->_dataother)) {
			return $this->_dataother[ $key ];
		}
		else {
			return null;
		}
	}*/

	/**
	 * Get the human-readable label for this record.
	 *
	 * By default, it will sift through the schema looking for keys that appear to be human-readable terms,
	 * but for best results, please extend this method and have it return what's necessary for the given Model.
	 *
	 * @return string
	 */
	public function getLabel() {
		// Anonymous users don't have all this fancy logic.
		if(!$this->exists()) {
			return ConfigHandler::Get('/user/displayname/anonymous');
		}

		$displayas = ConfigHandler::Get('/user/displayas');

		switch($displayas) {
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
	public function getDisplayName() {
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
	public function getConfigs() {
		if($this->_configs === null) {
			$this->_configs = [];
			$uucrecords     = $this->getLink('UserUserConfig');

			$fac = UserConfigModel::Find();
			foreach($fac as $f) {
				/** @var UserConfigModel $f */
				$key     = $f->get('key');
				$default = $f->get('default_value');

				// Look for this UUC from the list of records.
				foreach($uucrecords as $uuc) {
					/** @var UserUserConfigModel $uuc */
					if($uuc->get('key') == $key) {
						// Yay, it exists and is ready to go.
						$this->_configs[ $key ] = $uuc;
						// Skip to the next UserConfig object!
						continue 2;
					}
				}

				// If it's still here, the previous UUC logic didn't break out,
				// which means the uuc doesn't exist.... yet :p
				try {
					$uuc = new UserUserConfigModel($this->get('id'), $key);
					$uuc->set('value', $default);
					// Add this object to the list set of child models, just in case it's saved.
					$this->setLink('UserUserConfig', $uuc);
					// And add to the stack.
					$this->_configs[ $key ] = $uuc;
				}
				catch(Exception $e) {
					trigger_error('Invalid UserConfig [' . $f->get('key') . '], ' . $e->getMessage(), E_USER_NOTICE);
					// And simply don't add this one onto the user stack.
					// This is allowed because if the default isn't valid, then the user config itself isn't valid.
				}
			}
		}

		// Iterate over each set and just return a simple array.
		$ret = [];
		foreach($this->_configs as $k => $obj) {
			/** @var UserUserConfigModel $obj */
			$ret[ $k ] = $obj->get('value');
		}

		return $ret;
	}

	/**
	 * Get a single user config object
	 *
	 * @param string $key The UserConfig key to lookup
	 *
	 * @return UserUserConfigModel|null
	 */
	public function getConfigObject($key) {
		// Ensure the cache is populated.
		$this->getConfigs();

		return (isset($this->_configs[ $key ])) ? $this->_configs[ $key ] : null;
	}

	/**
	 * Get all the config objects associated to this user.
	 *
	 * @return array Array of UserUserConfigModels.
	 */
	public function getConfigObjects() {
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
		foreach($uugs as $uug) {
			/** @var UserUserGroupModel $uug */

			// Skip context groups.
			// These are a little more complex.
			if($uug->get('context')) continue;

			if(Core::IsComponentAvailable('multisite') && MultiSiteHelper::IsEnabled()) {
				// Only return this site's groups if in multisite mode
				$g = $uug->getLink('UserGroup');
				if($g->get('site') == MultiSiteHelper::GetCurrentSiteID()) {
					$out[] = $g->get('id');
				}
			}
			else {
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
	 * @param bool              $return_objects Set to true to return an array of UserUserGroup objects instead of a
	 *                                          flat array of IDs.
	 *
	 * @return array
	 */
	public function getContextGroups($context = null, $return_objects = false) {
		$out  = [];
		$uugs = $this->getLink('UserUserGroup');


		if($context && $context instanceof Model) {
			// If there was a context requested, only return that context.
			$contextname = substr(get_class($context), 0, -5);
			$contextpk   = $context->getPrimaryKeyString();
		}
		elseif(is_scalar($context)) {
			// If a context name was provided, search for just that model.
			$contextname = $context;
			$contextpk   = null;
		}
		else {
			// No parameters provided, just return everything!
			$contextname = null;
			$contextpk   = null;
		}

		foreach($uugs as $uug) {
			/** @var UserUserGroupModel $uug */

			// Skip regular groups.
			if(!$uug->get('context')) continue;

			if(Core::IsComponentAvailable('multisite') && MultiSiteHelper::IsEnabled()) {
				// Only return this site's groups if in multisite mode
				$g     = $uug->getLink('UserGroup');
				$gsite = $g->get('site');
				if(!($gsite == '-1' || $gsite == MultiSiteHelper::GetCurrentSiteID())
				) {
					continue;
				}
			}

			if($contextname && $uug->get('context') != $contextname) continue;
			if($contextpk && $uug->get('context_pk') != $contextpk) continue;


			// If it's gotten here, I can return this group!
			if($return_objects) {
				$out[] = $uug;
			}
			else {
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
	 * @param string|null $driver The backend driver to query for, leave null to return the first one selected.
	 *
	 * @return \Core\User\AuthDriverInterface
	 *
	 * @throws Exception
	 */
	public function getAuthDriver($driver = null) {

		$enabled = explode('|', $this->get('backend'));

		if(!sizeof($enabled)) {
			throw new Exception('There are no enabled authentication drivers for this user!');
		}

		if(!$driver) {
			$driver = $enabled[0];
		}
		elseif(!in_array($driver, $enabled)) {
			throw new Exception('The ' . $driver . ' authentication driver is not enabled for this user!');
		}
		// No else required, the driver is (presumably) valid and enabled on this user.


		if(!isset($this->_authdriver[ $driver ])) {
			if(!isset(\Core\User\Helper::$AuthDrivers[ $driver ])) {
				throw new Exception('Invalid auth backend for user, ' . $driver . '.  Auth driver is not registered.');
			}

			$classname = \Core\User\Helper::$AuthDrivers[ $driver ];

			if(!class_exists($classname)) {
				throw new Exception(
					'Invalid auth backend for user, ' . $driver . '.  Auth driver class was not found.'
				);
			}

			$ref                          = new ReflectionClass($classname);
			$this->_authdriver[ $driver ] = $ref->newInstance($this);
		}

		return $this->_authdriver[ $driver ];
	}

	/**
	 * Simple check to see if a given driver is installed.
	 * 
	 * @param string $driver
	 * 
	 * @return bool
	 */
	public function isAuthDriverEnabled($driver){
		try{
			// Will trigger an exception if it's not enabled! :)
			$this->getAuthDriver($driver);
			return true;
		}
		catch(Exception $e){
			return false;
		}
	}

	/**
	 * Get all enabled authentication drivers for this user.
	 *
	 * @return array
	 */
	public function getEnabledAuthDrivers() {
		$enabled = explode('|', $this->get('backend'));
		$ret     = [];

		foreach($enabled as $name) {
			try {
				$ret[] = $this->getAuthDriver($name);
			}
			catch(Exception $e) {
				// meh, if an exception was thrown here, then it's a disabled driver or something.
			}
		}

		return $ret;
	}

	/**
	 * Enable a given authentication driver for this user account.
	 *
	 * Will verify that the auth driver is valid before setting.
	 *
	 * Will NOT save the user, that still needs to be done externally!
	 *
	 * @param $driver
	 *
	 * @return boolean
	 */
	public function enableAuthDriver($driver) {
		$enabled = explode('|', $this->get('backend'));

		$drivers = \Core\User\Helper::GetEnabledAuthDrivers();
		if(!isset($drivers[ $driver ])) {
			return false;
		}

		if(in_array($driver, $enabled)) {
			return false;
		}

		$enabled[] = $driver;
		$this->set('backend', implode('|', $enabled));

		return true;
	}

	/**
	 * Disable a given authentication driver for this user account.
	 *
	 * Will verify that the auth driver is valid before setting.
	 *
	 * Will NOT save the user, that still needs to be done externally!
	 *
	 * @param $driver
	 *
	 * @return boolean
	 */
	public function disableAuthDriver($driver) {
		$enabled = explode('|', $this->get('backend'));

		$drivers = \Core\User\Helper::GetEnabledAuthDrivers();
		if(!isset($drivers[ $driver ])) {
			return false;
		}

		if(!in_array($driver, $enabled)) {
			return false;
		}

		unset($enabled[ array_search($driver, $enabled) ]);

		if(sizeof($enabled) == 0) {
			$enabled = ['datastore'];
		}

		$this->set('backend', implode('|', $enabled));

		return true;
	}

	/**
	 * Get a textual representation of this Model as a flat string.
	 *
	 * Used by the search systems to index the model, (or multiple models into one).
	 *
	 * @return string
	 */
	public function getSearchIndexString() {
		// The default behaviour is to sift through the records on this model itself.
		$strs = [];

		// The user account only has an email address
		$strs[] = $this->get('email');

		// I also need to sift over the user config options, since they relate to this object too.
		$opts = UserConfigModel::Find();
		foreach($opts as $uc) {
			/** @var UserConfigModel $uc */
			if($uc->get('searchable')) {
				$val = $this->get($uc->get('key'));

				if(preg_match('/^[0-9\- \.\(\)]*$/', $val) && trim($val) != ''){
					// If this is a numeric-based value, compress all the numbers without formatting.
					// This is to support phone numbers that may have arbitrary formatting applied.
					$val = preg_replace('/[ \-\.\(\)]/', '', $val);
				}
				if($val){
					$strs[] = $val;
				}
			}
		}

		return implode(' ', $strs);
	}

	/**
	 * Validate a new email for this user account.
	 *
	 * Emails must be unique on the system and valid.  This method checks both.
	 *
	 * @param string $email The email to validate.
	 *
	 * @return bool|string True if good, a string if bad.
	 */
	public function validateEmail($email) {
		if($email == $this->get('email')) {
			// If the email is currently the user's email, then it's allowed.
			return true;
		}

		if(!Core::CheckEmailValidity($email)) {
			return 'Does not appear to be a valid email address';
		}

		// Try to retrieve the user data from the database based on the email.
		// Email is a unique key, so there can only be 1 in the system.
		if(UserModel::Find(['email' => $email], 1)) {
			// Another user was located with the same email.... tsk tsk
			return 'Requested email is already registered';
		}

		// Must be ok!
		return true;
	}

	/**
	 * Simple check to see if this user is currently activated on the system.
	 *
	 * @return bool
	 */
	public function isActive() {
		if(!$this->exists()) {
			return false;
		}
		elseif($this->get('active') == 1) {
			return true;
		}
		else {
			return false;
		}
	}


	/**
	 * Set a key or config option on this user.
	 *
	 * @param string $k
	 * @param mixed  $v
	 *
	 * @return bool
	 */
	/*public function set($k, $v) {
		if(array_key_exists($k, $this->_data)) {
			// The key exists, it's a standard set.
			return parent::set($k, $v);
		}
		elseif(($c = $this->getConfigObject($k)) !== null) {
			return $c->set('value', $v);
		}
		else {
			$this->_dataother[ $k ] = $v;

			return true;
		}
	}*/

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
	public function setFromForm(Form $form, $prefix = null) {
		foreach($form->getElements() as $el) {
			/** @var $el FormElement */

			$name  = $el->get('name');
			$value = $el->get('value');

			// If a prefix was requested and it doesn't match, skip this element.
			if($prefix && strpos($name, $prefix . '[') !== 0){
				continue;
			}

			// Otherwise if there is a prefix, trim it off from the name.
			if($prefix) {
				// Some of the options may be nested arrays, they'll need to be treated differently since the format is different,
				// prefix[option][phone] vs prefix[email]
				if(strpos($name, '][')) {
					$name = str_replace('][', '[', substr($name, strlen($prefix) + 1));
				}
				else {
					$name = substr($name, strlen($prefix) + 1, -1);
				}
			}


			if($name == 'groups[]') {
				$this->setGroups($value);
			}
			elseif($name == 'contextgroup[]') {
				// This is a two-part system with data pulling from contextgroup and contextgroupcontext.
				$gids       = $value;
				$contextpks = $form->getElement('contextgroupcontext[]')->get('value');
				$groups     = [];

				foreach($gids as $key => $gid) {
					// Skip blank group selections.
					if(!$gid) continue;

					// Pull the group information for this gid since that will contain the context.
					$group = UserGroupModel::Construct($gid);

					$context   = $group->get('context');
					$contextpk = $contextpks[ $key ];

					$groups[] = [
						'group_id'   => $gid,
						'context'    => $context,
						'context_pk' => $contextpk,
					];
				}

				$this->setContextGroups($groups);
			}
			elseif($name == 'active'){
				$current = $this->get('active');
				// The incoming value will probably be 'on' or NULL.
				// This is because the form displays as a BOOL even though the backend field is an ENUM.
				$new = ($value) ? '1' : '0';
				
				// -1 => 0 = -1 (Disabled to unchecked, no change)
				// -1 => 1 =  1 (Disabled to checked, activate)
				//  0 => 0 =  0 (New to unchecked, wot?)
				//  0 => 1 =  1 (New to checked, activate... still shouldn't happen though)
				//  1 => 0 = -1 (Enabled to unchecked, disable)
				//  1 => 1 =  1 (Enabled to checked, no change)
				
				if($current == '1' && $new == '0'){
					// User was set from active to inactive.
					// Instead of setting to a new account, set to deactivated.
					$this->set('active', '-1');
				}
				elseif($current == '-1' && $new == '0'){
					// No change!
				}
				else{
					// Otherwise, allow the change to go through.
					$this->set('active', $new);
				}
			}
			elseif($name != 'user'){
				// Skip the user record,
				// otherwise Default behaviour
				$this->set($name, $value);
			}
		} // foreach(elements)
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
	public function generateNewApiKey() {
		$this->set('apikey', Core::RandomHex(64, true));
	}

	/**
	 * Clear out the access string cache.
	 *
	 * This is useful if groups on a given user change.
	 */
	public function clearAccessStringCache() {
		$this->_accessstringchecks  = [];
		$this->_resolvedpermissions = null;
	}

	/**
	 * Check access for a given access string against this user.
	 *
	 * The access string is the core component to Core+ authentication.
	 *
	 * @since 2011.08
	 *
	 * @param string     $accessstring The access string to parse.
	 * @param null|Model $context      The context to search for access within.
	 *
	 * @return bool
	 */
	public function checkAccess($accessstring, $context = null) {

		$findkey = $accessstring . '-' . $this->_getContextKey($context);

		// And gogo caching lookups!
		// DEVELOPMENT NOTE -- If you're working on this function,
		//  it might be best to disable the return here!...
		if(isset($this->_accessstringchecks[ $findkey ])) {
			// :)
			return $this->_accessstringchecks[ $findkey ];
		}

		// Default behaviour (also set from * or !* flags).
		$default = false;
		// Lookup some common variables first.
		$loggedin = $this->exists();
		$isadmin  = $this->get('admin');
		$cache    =& $this->_accessstringchecks[ $findkey ];
		$isactive = $this->isActive();

		// All checks are case insensitive
		$accessstring = strtolower($accessstring);


		// Check if the current user is an admin... if so and there is no
		// "g:!admin" flag, automatically set it to true.
		if($isadmin && strpos($accessstring, 'g:!admin') === false) {
			$cache = true;

			return true;
		}

		// Explode on a semicolon(;), with string trimming.
		$parts = array_map('trim', explode(';', $accessstring));
		foreach($parts as $p) {
			// This can happen if there is an access string such as 'g:authenticated;'.
			if($p == '') continue;

			// Wildcard is the exception, as it does not require a type:dat set.
			if($p == '*' || $p == '!*') {
				$type = '*';
				$dat  = $p;
			}
			// Everything else is in the format of p:blah, g:my_group, etc.
			else {
				list($type, $dat) = array_map('trim', explode(':', $p));
			}

			// Each check can either be an 'ALLOW' or 'DENY'.
			// This is toggled by the presence of a '!'
			if($dat{0} == '!') {
				$ret = false;
				$dat = substr($dat, 1);
			}
			// Sometimes the type has the '!'... this is acceptable too.
			elseif($type{0} == '!') {
				$ret  = false;
				$type = substr($type, 1);
			}
			else {
				$ret = true;
				// No trim is needed.
			}

			// A few "special" checks.
			if($type == '*') {
				// This sets the default instead of returning immediately.
				$default = $ret;
				continue;
			}
			elseif($type == 'g' && $dat == 'anonymous') {
				if(!$loggedin) {
					$cache = $ret;

					return $ret;
				}
			}
			elseif($type == 'g' && $dat == 'authenticated') {
				if($loggedin && $isactive) {
					$cache = $ret;

					return $ret;
				}
			}
			elseif($type == 'g' && $dat == 'admin') {
				if($isadmin) {
					$cache = $ret;

					return $ret;
				}
			}
			elseif($type == 'g') {
				// All the other groups will be ID based, yayz!
				if(in_array($dat, $this->getGroups())) {
					$cache = $ret;

					return $ret;
				}
			}
			elseif($type == 'p') {
				if(in_array($dat, $this->_getResolvedPermissions($context))) {
					$cache = $ret;

					return $ret;
				}
			}
			elseif($type == 'u') {
				var_dump($type, $dat, $ret);
				die('@todo Finish the user lookup logic in User::checkAccess()');
			}
			else {
				var_dump($type, $dat, $ret);
				die('Implement that access string check!');
			}
		}

		// Not found... return the default, (which is deny by default).
		$cache = $default;

		return $default;
	}

	/**
	 * Get the control links for a given user based on the current user's access permissions.
	 *
	 * @return array
	 */
	public function getControlLinks(){
		$a = array();

		$userid      = $this->get('id');
		$usersudo    = \Core\user()->checkAccess('p:/user/users/sudo');
		$usermanager = \Core\user()->checkAccess('p:/user/users/manage');
		$selfaccount = \Core\user()->get('id') == $userid;

		if($usersudo && !$selfaccount){
			$a[] = array(
				'title' => 'Switch To User',
				'icon' => 'bullseye',
				'link' => '/user/sudo/' . $userid,
				'confirm' => 'By switching, (or SUDOing), to a user, you inherit that user permissions.',
			);
		}

		if($usermanager){
			$a[] = array(
				'title' => t('STRING_VIEW'),
				'icon' => 'view',
				'link' => '/user/view/' . $userid,
			);
		}
		elseif($selfaccount){
			$a[] = array(
				'title' => t('STRING_VIEW'),
				'icon' => 'view',
				'link' => '/user/me',
			);
		}

		if($usermanager || $selfaccount){
			$a[] = array(
				'title' => t('STRING_EDIT'),
				'icon' => 'edit',
				'link' => '/user/edit/' . $userid,
			);

			$a[] = array(
				'title' => 'Public Profiles',
				'icon' => 'link',
				'link' => '/user/connectedprofiles/' . $userid,
			);

			// Even though this user has admin access, he/she cannot remove his/her own account!
			if(!$selfaccount){
				$a[] = array(
					'title' => 'Delete',
					'icon' => 'remove',
					'link' => '/user/delete/' . $userid,
					'confirm' => 'Are you sure you want to delete user ' . $this->getDisplayName() . '?',
				);
			}
		}

		// Merge any parent links.
		return array_merge($a, parent::getControlLinks());
	}

	/**
	 * Get an array of resolved permissions for this user using the group membership.
	 *
	 * @param null|Model $context The context to search for.
	 *
	 * @return array
	 */
	protected function _getResolvedPermissions($context = null) {

		if(!$this->isActive()) {
			// Inactive users have no permissions.
			return [];
		}

		$findkey = $this->_getContextKey($context);

		if($this->_resolvedpermissions === null) {
			$this->_resolvedpermissions = [];

			foreach($this->getLink('UserUserGroup') as $uug) {
				/** @var UserUserGroupModel $uug */

				$key = $uug->get('context') ? $uug->get('context') . ':' . $uug->get('context_pk') : '';
				if(!isset($this->_resolvedpermissions[ $key ])) {
					$this->_resolvedpermissions[ $key ] = [];
				}

				/** @var UserGroupModel $group */
				$group = $uug->getLink('UserGroup');

				if(Core::IsComponentAvailable('multisite') && MultiSiteHelper::IsEnabled()) {
					// Only return this site's groups if in multisite mode
					if(!($group->get('site') == -1 || $group->get('site') == MultiSiteHelper::GetCurrentSiteID())) {
						continue;
					}
				}

				$this->_resolvedpermissions[ $key ] =
					array_merge($this->_resolvedpermissions[ $key ], $group->getPermissions());
			}
		}

		return isset($this->_resolvedpermissions[ $findkey ]) ? $this->_resolvedpermissions[ $findkey ] : [];
	}

	/**
	 * Set all groups for a given user on the current site from a set of IDs.
	 *
	 * @param array             $groups
	 * @param bool|Model|string $context True to set all context groups, false to ignore, a string or model for the
	 *                                   specific context.
	 *
	 * @throws Exception
	 */
	protected function _setGroups($groups, $context) {

		// Map the groups to a complex array if necessary.
		foreach($groups as $key => $data) {
			if(!is_array($data)) {
				$groups[ $key ] = [
					'group_id'   => $data,
					'context'    => '',
					'context_pk' => '',
				];
			}
		}

		if($context === false) {
			// Skip all context groups.
			$contextname = null;
			$contextpk   = null;
		}
		elseif($context === true) {
			// Skip regular groups, but include all context groups.
			$contextname = null;
			$contextpk   = null;
		}
		elseif($context instanceof Model) {
			$contextname = substr(get_class($context), 0, -5);
			$contextpk   = $context->getPrimaryKeyString();
			$context     = true;
		}
		elseif(is_scalar($context)) {
			$contextname = $context;
			$contextpk   = null;
			$context     = true;
		}
		else {
			throw new Exception('If a context is provided, please ensure it is either a model or model name');
		}

		$uugs = $this->getLink('UserUserGroup');
		foreach($uugs as $uug) {
			/** @var UserUserGroupModel $uug */

			// Only process the requested group types.
			if($context && !$uug->get('context')) {
				// A context option was selected, but this is a regular group, skip it.
				continue;
			}
			elseif(!$context && $uug->get('context')) {
				// Similarly, no context was requested, but this group has one.
				continue;
			}
			elseif($context && $contextname && $uug->get('context') != $contextname) {
				// A context was requested, and a specific context name was set also!
				// But it doesn't match.... SKIP!
				continue;
			}
			elseif($context && $contextpk && $uug->get('context_pk') != $contextpk) {
				// A context was requested, and a specific context name was set also!
				// But it doesn't match.... SKIP!
				continue;
			}

			if(Core::IsComponentAvailable('multisite') && MultiSiteHelper::IsEnabled()) {
				// Only return this site's groups if in multisite mode
				$ugsite = $uug->getLink('UserGroup')->get('site');
				if(!($ugsite == -1 || $ugsite == MultiSiteHelper::GetCurrentSiteID())) {
					/// Skip any group not on this site... they'll simply be ignored.
					continue;
				}
			}

			$gid        = $uug->get('group_id');
			$gcontext   = $uug->get('context');
			$gcontextpk = $uug->get('context_pk');
			foreach($groups as $key => $data) {
				if($data['group_id'] == $gid && $data['context'] == $gcontext && $data['context_pk'] == $gcontextpk
				) {
					// Yay, group matches up with both!
					// Unlink it from the groups array so it doesn't try to get recreated.
					unset($groups[ $key ]);
					continue 2;
				}
			}

			// This group isn't in the new list, unset it!
			$this->deleteLink($uug);
		}

		// Any new groups remaining?
		foreach($groups as $data) {
			$this->setLink(
				'UserUserGroup', new UserUserGroupModel(
					$this->get('id'), $data['group_id'], $data['context'], $data['context_pk']
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
	protected function _getContextKey($context) {
		if($context === null || $context === '') {
			// OK, allowed.  This is the global context.
			return '';
		}
		elseif($context instanceof Model) {
			return substr(get_class($context), 0, -5) . ':' . $context->getPrimaryKeyString();
		}
		else {
			throw new Exception('Invalid context provided for _getResolvedPermissions!');
		}
	}

	/**
	 * Search for a user based on a search criteria.  This has functionality above and beyond just a simple Find
	 * because it will search the email and any custom fields that are marked as searchable.
	 *
	 * @param string $query The term to search for
	 * @param array  $where Any additional where clause to tack on.
	 *
	 * @return array An array of UserModel objects
	 */
	public static function Search($query, $where = []) {

		$ret          = [];
		$schema       = self::GetSchema();
		$configwheres = [];

		// If this object does not support searching, simply return an empty array.
		$ref = new ReflectionClass(get_called_class());

		if(!$ref->getProperty('HasSearch')->getValue()) {
			return $ret;
		}

		$fac = new ModelFactory(get_called_class());

		if(sizeof($where)) {
			$clause = new \Core\Datamodel\DatasetWhereClause();
			$clause->addWhere($where);
			// If this isn't actually a column present, maybe it's a user user config option instead.
			foreach($clause->getStatements() as $statement) {
				/** @var \Core\Datamodel\DatasetWhere $statement */
				if(isset($schema[ $statement->field ])) {
					$fac->where($statement);
				}
				else {
					$configwheres[] = $statement;
				}
			}
		}

		if($ref->getProperty('HasDeleted')->getValue()) {
			$fac->where('deleted = 0');
		}

		$fac->where(\Core\Search\Helper::GetWhereClause($query));
		foreach($fac->get() as $m) {
			/** @var UserModel $m */

			$add = true;

			// If this user has configs that don't match the userconfig where requested, skip it.
			foreach($configwheres as $statement) {
				/** @var \Core\Datamodel\DatasetWhere $statement */
				if(($config = $m->getConfigObject($statement->field))) {
					switch($statement->op) {
						case '=':
							if($config->get('value') != $statement->value) {
								$add = false;
								break 2;
							}
							break;
						default:
							// @todo.
					}
				}
			}

			if($add) {
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

	/**
	 * Import the given data into the destination Model.
	 *
	 * @param array   $data            Indexed array of records to import/merge from the external source.
	 * @param array   $options         Any options required for the import, such as merge, key, etc.
	 * @param boolean $output_realtime Set to true to output the log in real time as the import happens.
	 *
	 * @throws Exception
	 *
	 * @return \Core\ModelImportLogger
	 */
	public static function Import($data, $options, $output_realtime = false) {
		$log = new \Core\ModelImportLogger('User Importer', $output_realtime);

		$merge = isset($options['merge']) ? $options['merge'] : true;
		$pk    = isset($options['key']) ? $options['key'] : null;

		if(!$pk) {
			throw new Exception(
				'Import requires a "key" field on options containing the primary key to compare against locally.'
			);
		}

		// Load in members from the group

		// Set the default group on new accounts, if a default is set.
		$defaultgroups = \UserGroupModel::Find(["default = 1"]);
		$groups        = [];
		$gnames        = [];
		foreach($defaultgroups as $g) {
			/** @var \UserGroupModel $g */
			$groups[] = $g->get('id');
			$gnames[] = $g->get('name');
		}
		if(sizeof($groups)) {
			$log->log('Found ' . sizeof($groups) . ' default groups for new users: ' . implode(', ', $gnames));
		}
		else {
			$log->log('No groups set as default, new users will not belong to any groups.');
		}
		
		$log->log('Starting ' . ($merge ? '*MERGE*' : '*skipping*' ) . ' import of ' . sizeof($data) . ' users');

		foreach($data as $dat) {

			if(isset($dat[$pk])){
				// Only check the information if the primary key is set on this record.
				if($pk == 'email' || $pk == 'id') {
					// These are the only two fields on the User object itself.
					$user = UserModel::Find([$pk . ' = ' . $dat[ $pk ]], 1);
				}
				else {
					$uucm = UserUserConfigModel::Find(['key = ' . $pk, 'value = ' . $dat[ $pk ]], 1);

					if($uucm) {
						$user = $uucm->getLink('UserModel');
					}
					else {

						// Try the lookup from the email address instead.
						// This will force accounts that exist to be synced up correctly.
						// The only caveat to this is that users will not be updated with the foreign key if merge is disabled.
						$user = UserModel::Find(['email = ' . $dat['email']], 1);
					}
				}
			}
			else{
				$user = null;
			}
			

			$status_type = $user ? 'Updated' : 'Created';

			if($user && !$merge) {
				$log->duplicate('Skipped user ' . $user->getLabel() . ', already exists and merge not requested');
				// Skip to the next record.
				continue;
			}

			if(!$user) {
				// All incoming users must have an email address!
				if(!isset($dat['email'])) {
					$log->error('Unable to import user without an email address!');
					// Skip to the next record.
					continue;
				}

				// Meta fields that may or may not be present, but should be for reporting purposes.
				if(!isset($dat['registration_ip'])) {
					$dat['registration_ip'] = REMOTE_IP;
				}
				if(!isset($dat['registration_source'])) {
					$dat['registration_source'] = \Core\user()->exists() ? 'admin' : 'self';
				}
				if(!isset($dat['registration_invitee'])) {
					$dat['registration_invitee'] = \Core\user()->get('id');
				}

				// New user!
				$user = new UserModel();
			}
			// No else needed, else is there IS a valid $user object and it's setup ready to go.

			
			// Handle all the properties for this user!
			foreach($dat as $key => $val){
				
				if($key == 'avatar' && strpos($val, '://') !== false){
					// Sync the user avatar.
					$log->actionStart('Downloading ' . $dat['avatar']);
					$f    = new \Core\Filestore\Backends\FileRemote($dat['avatar']);
					$dest = \Core\Filestore\Factory::File('public/user/avatar/' . $f->getBaseFilename());
					if($dest->identicalTo($f)) {
						$log->actionSkipped();
					}
					else {
						$f->copyTo($dest);
						$user->set('avatar', 'public/user/avatar/' . $dest->getBaseFilename());
						$log->actionSuccess();
					}
				}
				elseif($key == 'profiles' && is_array($val)) {
					$new_profiles = $val;

					// Pull the current profiles from the account
					$profiles = $user->get('external_profiles');
					if($profiles && is_array($profiles)) {
						$current_flat = [];
						foreach($profiles as $current_profile) {
							$current_flat[] = $current_profile['url'];
						}

						// Merge in any *actual* new profile
						foreach($new_profiles as $new_profile) {
							if(!in_array($new_profile['url'], $current_flat)) {
								$profiles[] = $new_profile;
							}
						}

						unset($new_profile, $new_profiles, $current_flat, $current_profile);
					}
					else {
						$profiles = $new_profiles;
						unset($new_profiles);
					}

					$user->set('external_profiles', $profiles);
				}
				elseif($key == 'backend'){
					// Was a backend requested?
					// This gets merged instead of replaced entirely.
					$user->enableAuthDriver($val);
				}
				elseif($key == 'groups'){
					$user->setGroups($val);
				}
				else{
					// Default Behaviour,
					// save the key into whatever field it was set to go to.
					$user->set($key, $val);
				}
			}

			try {
				// Set the default groups loaded from the system.
				if(!$user->exists()){
					$user->setGroups($groups);	
				}

				$status = $user->save();
			}
			catch(Exception $e) {
				$log->error($e->getMessage());
				// Skip to the next.
				continue;
			}
			
			if($status) {
				$log->success($status_type . ' user ' . $user->getLabel() . ' successfully!');
			}
			else {
				$log->skip('Skipped user ' . $user->getLabel() . ', no changes detected.');
			}
		}

		$log->finalize();

		return $log;
	}
}