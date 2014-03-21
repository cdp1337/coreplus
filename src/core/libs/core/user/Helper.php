<?php
/**
 * Provides common user functionality, such as registration form generation and any logic required by
 * both widget and controller.
 *
 * @package User
 * @since 2.0
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
 *
 */
namespace Core\User;


use Core\Datamodel\DatasetWhereClause;

abstract class Helper{

	/**
	 * @var array Array of User Auth drivers installed and available.
	 */
	public static $AuthDrivers = [
		'datastore' => '\\Core\\User\\AuthDrivers\\datastore',
	];

	/**
	 * Function to record activity, ie: a page view.
	 *
	 * @static
	 *
	 */
	public static function RecordActivity(){

		$request = \PageRequest::GetSystemRequest();
		$view = $request->getView();

		if(!$view->record) return;

		$log = new \UserActivityModel();
		$log->setFromArray(
			array(
				'session_id' => session_id(),
				'user_id' => \Core\user()->get('id'),
				'ip_addr' => REMOTE_IP,
				'useragent' => $_SERVER['HTTP_USER_AGENT'],
				'referrer' => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''),
				'type' => $_SERVER['REQUEST_METHOD'],
				'request' => $_SERVER['REQUEST_URI'],
				'baseurl' => $request->getBaseURL(),
				'status' => $view->error,
				'db_reads' => \Core::DB()->readCount(),
				'db_writes' => (\Core::DB()->writeCount() + 1),
				'processing_time' => (round(\Core\Utilities\Profiler\Profiler::GetDefaultProfiler()->getTime(), 4) * 1000)
			)
		);
		try{
			$log->save();
		}
		catch(\Exception $e){
			// I don't actually care if it couldn't save.
			// This could happen if the user refreshes the page twice with in a second.
			// (and with a system that responds in about 100ms, it's very possible).
		}
	}

	/**
	 * Form handler for the rest of the user system, (auth handler has already been executed).
	 *
	 * @param \Form $form
	 *
	 * @return bool|string
	 */
	public static function RegisterHandler(\Form $form){

		///////       VALIDATION     \\\\\\\\

		// All other validation can be done from the model.
		// All set calls will throw a ModelValidationException if the validation fails.
		try{
			/** @var \UserModel $user */
			$user = $form->getElement('user')->get('value');

			// setFromForm will handle all attributes and custom values.
			$user->setFromForm($form);
		}
		catch(\ModelValidationException $e){
			// Make a note of this!
			\SystemLogModel::LogSecurityEvent('/user/register', $e->getMessage());

			\Core::SetMessage($e->getMessage(), 'error');
			return false;
		}
		catch(\Exception $e){
			// Make a note of this!
			\SystemLogModel::LogSecurityEvent('/user/register', $e->getMessage());

			if(DEVELOPMENT_MODE) \Core::SetMessage($e->getMessage(), 'error');
			else \Core::SetMessage('An unknown error occured', 'error');

			return false;
		}

		// Check if there are no users already registered on the system.
		// This determines how the admin and active flags are handled.
		if(\UserModel::Count() == 0){
			// If none, register this user as an admin automatically.
			$user->set('admin', true);
			$user->set('active', true);
		}
		else{
			// There is at least one user on the system, use the standard logic.

			// if a super admin is registering an account, it should use the value of the active checkbox!
			if( \Core\user()->checkAccess('g:admin') ) {
				$activeElement = $form->getElement('active')->get('value');
				$active = ($activeElement === "on" ? 1 : 0);
				$user->set('active', $active);
			}
			elseif(\ConfigHandler::Get('/user/register/requireapproval')){
				$user->set('active', false);
			}
			else{
				$user->set('active', true);
			}
		}

		// Set the default group on new accounts, if a default is set.
		$defaultgroups = \UserGroupModel::Find(array("default = 1"));
		$gs = [];
		foreach($defaultgroups as $g){
			/** @var \UserGroupModel $g */
			$gs[] = $g->get('id');
		}
		$user->setGroups($gs);

		// Record some more meta information about this user.
		$user->set('registration_ip', REMOTE_IP);
		$user->set('registration_source', \Core\user()->exists() ? 'admin' : 'self');
		$user->set('registration_invitee', \Core\user()->get('id'));

		$user->save();

		// User created... make a log of this!
		\SystemLogModel::LogSecurityEvent('/user/register', 'User registration successful', null, $user->get('id'));

		// Send a thank you for registering email to the user.
		try{
			$email = new \Email();
			$email->assign('user', $user);
			$email->assign('sitename', SITENAME);
			$email->assign('rooturl', ROOT_URL);
			$email->assign('loginurl', \Core::ResolveLink('/user/login'));
			$email->setSubject('Welcome to ' . SITENAME);
			$email->templatename = 'emails/user/registration.tpl';
			$email->to($user->get('email'));

			// TESTING
			//error_log($email->renderBody());
			$email->send();
		}
		catch(\Exception $e){
			\Core\ErrorManagement\exception_handler($e);
			\Core::SetMessage('Unable to send welcome email', 'error');
		}



		// "login" this user if not already logged in.
		if(!\Core\user()->exists()){

			// If the user came from the registration page, get the page before that.
			if(REL_REQUEST_PATH == '/user/register2') $url = \Core::GetHistory(2);
			// else the registration link is now on the same page as the 403 handler.
			else $url = REL_REQUEST_PATH;

			//$url = Core::GetHistory(2);
			if($user->get('active')){
				$user->set('last_login', \CoreDateTime::Now('U', \Time::TIMEZONE_GMT));
				$user->save();
				\Session::SetUser($user);
			}
			//var_dump($url); echo '<pre>'; debug_print_backtrace();
			\Core::SetMessage('Registered account successfully!', 'success');

			// Allow an external script to override the redirecting URL.
			$overrideurl = \HookHandler::DispatchHook('/user/postlogin/getredirecturl');
			if($overrideurl){
				$url = $overrideurl;
			}
			elseif(strpos(\Core::ResolveLink('/user/register2'), $url) === 0){
				$url = '/';
			}

			return $url;
		}
		// It was created administratively; redirect there instead.
		else{
			\Core::SetMessage('Created user successfully', 'success');
			return '/user/admin';
		}

	}

	public static function UpdateHandler(\Form $form){

		$userid      = $form->getElement('id')->get('value');
		$usermanager = \Core\user()->checkAccess('p:/user/users/manage');

		// Only allow this if the user is either the same user or has the user manage permission.
		if(!($userid == \Core\user()->get('id') || $usermanager)){
			\Core::SetMessage('Insufficient Permissions', 'error');
			return false;
		}

		/** @var \UserModel $user */
		$user = \UserModel::Construct($userid);

		if(!$user->exists()){
			\Core::SetMessage('User not found', 'error');
			return false;
		}

		$userisactive = $user->get('active');

		try{
			$user->setFromForm($form);
		}
		catch(\ModelValidationException $e){
			\Core::SetMessage($e->getMessage(), 'error');
			return false;
		}
		catch(\Exception $e){
			if(DEVELOPMENT_MODE) \Core::SetMessage($e->getMessage(), 'error');
			else \Core::SetMessage('An unknown error occured', 'error');

			return false;
		}

		$user->save();


		if(!$userisactive && $user->get('active')){
			// If the user wasn't active before, but is now....
			// Send an activation notice email to the user.
			try{
				$email = new \Email();
				$email->assign('user', $user);
				$email->assign('sitename', SITENAME);
				$email->assign('rooturl', ROOT_URL);
				$email->assign('loginurl', \Core::ResolveLink('/user/login'));
				$email->setSubject('Welcome to ' . SITENAME);
				$email->templatename = 'emails/user/activation.tpl';
				$email->to($user->get('email'));

				// TESTING
				//error_log($email->renderBody());
				$email->send();
			}
			catch(\Exception $e){
				\Core\ErrorManagement\exception_handler($e);
			}
		}


		// If this was the current user, update the session data too!
		if($user->get('id') == \core\user()->get('id')){
			\Session::SetUser($user);

			if(\ConfigHandler::Get('/user/profileedits/requireapproval') && \Core::IsComponentAvailable('model-audit')){
				\Core::SetMessage('Updated your account successfully, but an administrator will need to approve all changes.', 'success');
			}
			else{
				\Core::SetMessage('Updated your account successfully', 'success');
			}
		}
		else{
			\Core::SetMessage('Updated user successfully', 'success');
		}


		return true;
	}

	/**
	 * Get the control links for a given user based on the current user's access permissions.
	 *
	 * @param \UserModel|int $user
	 * @return array
	 */
	public static function GetControlLinks($user){
		$a = array();

		if(is_scalar($user)){
			// Transpose the ID to a user backend object.
			$user = \UserModel::Construct($user);
		}
		elseif($user instanceof \UserModel){
			// NO change needed :)
		}
		else{
			// Umm, wtf was it?
			return array();
		}

		// still nothing?
		if(!$user) return array();


		$usersudo    = \Core\user()->checkAccess('p:/user/users/sudo');
		$usermanager = \Core\user()->checkAccess('p:/user/users/manage');
		$selfaccount = \Core\user()->get('id') == $user->get('id');

		if($usersudo && !$selfaccount){
			$a[] = array(
				'title' => 'Switch To User',
				'icon' => 'bullseye',
				'link' => '/user/sudo/' . $user->get('id'),
				'confirm' => 'By switching, (or SUDOing), to a user, you inherit that user permissions.',
			);
		}

		if($usermanager || $selfaccount){
			$a[] = array(
				'title' => 'Edit',
				'icon' => 'edit',
				'link' => '/user/edit/' . $user->get('id'),
			);

			// Even though this user has admin access, he/she cannot remove his/her own account!
			if(!$selfaccount){
				$a[] = array(
					'title' => 'Delete',
					'icon' => 'remove',
					'link' => '/user/delete/' . $user->get('id'),
					'confirm' => 'Are you sure you want to delete user ' . $user->getDisplayName() . '?',
				);
			}
		}

		return $a;
	}

	/**
	 * Method to purge the user activity cron.
	 *
	 * This is useful because on an extremely busy site, this table can grow to several gigs within not much time.
	 */
	public static function PurgeUserActivityCron() {
		$opt = \ConfigHandler::Get('/user/activity/keephistory');

		if($opt == 'all' || !$opt){
			echo 'Not purging any user activity.' . "\n";
			return true;
		}

		// Convert the key to a datestring value.
		$date = new \CoreDateTime();
		switch($opt){
			case '1-week':
				$date->modify('-1 week');
				break;
			case '1-month':
				$date->modify('-1 month');
				break;
			case '2-months':
				$date->modify('-2 month');
				break;
			case '3-months':
				$date->modify('-3 month');
				break;
			case '6-months':
				$date->modify('-6 month');
				break;
			case '12-months':
				$date->modify('-12 month');
				break;
			case '24-months':
				$date->modify('-24 month');
				break;
			case '36-months':
				$date->modify('-36 month');
				break;
			default:
				echo 'Invalid value for /user/activity/keephistory: [' . $opt . ']';
				return false;
		}

		// And delete any activity older than this date.
		echo 'Purging user activity older than ' . $date->getFormatted('r') . "...\n";
		$ds = new \Core\Datamodel\Dataset();
		$ds->delete()->table('user_activity')->where('datetime < ' . $date->getFormatted('U', \TIME::TIMEZONE_GMT))->execute();
		echo 'Removed ' . $ds->num_rows . ' record(s).' . "\n";
		return true;
	}

	/**
	 * Get the form object for registrations.
	 *
	 * @return \Form
	 */
	public static function GetRegistrationForm(){
		return self::GetForm(null);
	}

	/**
	 * Get the form object for editing users.
	 *
	 * @param \UserModel
	 *
	 * @return \Form
	 */
	public static function GetEditForm(\UserModel $user){
		return self::GetForm($user);
	}

	/**
	 * @param \UserModel|null $user
	 *
	 * @return \Form
	 */
	public static function GetForm($user = null){
		$form = new \Form();
		if($user === null) $user = new \UserModel();

		$type               = ($user->exists()) ? 'edit' : 'registration';
		$usermanager        = \Core\user()->checkAccess('p:/user/users/manage');
		$groupmanager       = \Core\user()->checkAccess('p:/user/groups/manage');
		$allowemailchanging = \ConfigHandler::Get('/user/email/allowchanging');

		if($type == 'registration'){
			$form->set('callsmethod', 'User\\Helper::RegisterHandler');
		}
		else{
			$form->set('callsmethod', 'User\\Helper::UpdateHandler');
		}

		$form->addElement('system', ['name' => 'user', 'value' => $user]);

		// Because the user system may not use a traditional Model for the backend, (think LDAP),
		// I cannot simply do a setModel() call here.

		// Only enable email changes if the current user is an admin or it's new.
		// (Unless the admin allows it via the site config)
		if($type != 'registration' && ( $usermanager || $allowemailchanging)){
			$form->addElement('text', array('name' => 'email', 'title' => 'Email', 'required' => true, 'value' => $user->get('email')));
		}

		// Tack on the active option if the current user is an admin.
		if($usermanager){
			$form->addElement(
				'checkbox',
				array(
					'name' => 'active',
					'title' => 'Active',
					'checked' => $user->get('active'),
				)
			);

		}

		// Avatars can be updated on editing the profile, if enabled.
		if(\ConfigHandler::Get('/user/enableavatar')){
			// Avatar is for existing accounts or admins.
			$form->addElement(
				'file',
				array(
					'name' => 'avatar',
					'title' => 'Avatar Image',
					'basedir' => 'public/user/avatar',
					'accept' => 'image/*',
					'value' => $user->get('avatar')
				)
			);
		}

		// For non-admins, the factory depends on the registration type as well.
		if($usermanager){
			$fac = \UserConfigModel::Find(array('hidden = 0'), null, 'weight ASC');
		}
		elseif($type == 'registration'){
			$fac = \UserConfigModel::Find(array('onregistration' => 1), null, 'weight ASC');
		}
		else{
			$fac = \UserConfigModel::Find(array('onedit' => 1), null, 'weight ASC');
		}

		foreach($fac as $f){
			/** @var \UserConfigModel $f */
			$key = $f->get('key');

			try{
				$val = ($user->get($key) === null) ? $f->get('default_value') : $user->get($key);
				$el = \FormElement::Factory($f->get('formtype'));
				$el->set('name', 'option[' . $key . ']');
				$el->set('title', $f->get('name'));
				$el->set('value', $val);
				if($f->get('required')) $el->set('required', true);

				switch($f->get('formtype')){
					case 'file':
						$el->set('basedir', 'public/user/config');
						break;
					case 'checkboxes':
					case 'select':
					case 'radio':
						$opts = array_map('trim', explode('|', $f->get('options')));
						$el->set('options', $opts);
						break;
					case 'checkbox':
						$el->set('value', 1);
						$el->set('checked', ($val ? true : false));
						break;
				}

				$form->addElement($el);
			}
			catch(\Exception $e){
				// Well, damn... Don't really care.
				\SystemLogModel::LogErrorEvent('/userconfig/exception/' . $key, $e->getMessage());
			}

			//var_dump($f);
		}

		if(\Core\user()->checkAccess('g:admin')){
			$form->addElement(
				'checkbox',
				array(
					'name' => 'admin',
					'title' => 'System Admin',
					'checked' => $user->get('admin'),
					'description' => 'The system admin, (or root user), has complete control over the site and all systems.',
				)
			);
		}

		// Tack on the group registration if the current user is an admin.
		if($groupmanager){
			// Find all the groups currently on the site.

			$where = ['context = '];
			if(\Core::IsComponentAvailable('enterprise') && \MultiSiteHelper::IsEnabled()){
				$where['site'] = \MultiSiteHelper::GetCurrentSiteID();
			}

			$groups = \UserGroupModel::Find($where, null, 'name');

			if(sizeof($groups)){
				$groupopts = array();
				foreach($groups as $g){
					$groupopts[$g->get('id')] = $g->get('name');
				}

				$form->addElement(
					'checkboxes',
					array(
						'name' => 'groups[]',
						'title' => 'Group Membership',
						'options' => $groupopts,
						'value' => $user->getGroups()
					)
				);
			}




			$where = new DatasetWhereClause();
			$where->addWhere('context != ');
			if(\Core::IsComponentAvailable('enterprise') && \MultiSiteHelper::IsEnabled()){
				$w = new DatasetWhereClause();
				$w->setSeparator('or');
				$w->addWhere('site = ' . \MultiSiteHelper::GetCurrentSiteID());
				$w->addWhere('site = -1');
				$where->addWhere($w);
			}
			$contextgroups = \UserGroupModel::Count($where);

			if($contextgroups > 0){
				// If this is a non-global context.
				// Good enough to stop here!
				$form->addElement(
					new \FormGroup(
						[
							'name' => 'context-groups',
							'id'   => 'context-groups',
							'title' => 'Context Group Membership',
						]
					)
				);

				// So that these elements will be registered on the form object...
				$form->addElement('hidden', ['name' => 'contextgroup[]', 'persistent' => false]);
				$form->addElement('hidden', ['name' => 'contextgroupcontext[]', 'persistent' => false]);
			}

		}

		// If the config is enabled and the current user is guest...
		if($type == 'registration' && \ConfigHandler::Get('/user/register/requirecaptcha') && !\Core\user()->exists()){
			$form->addElement('captcha');
		}

		$form->addElement(
			'submit',
			[
				'value' => (($type == 'registration') ? 'Register' : 'Update'),
				'name' => 'submit',
			]
		);

		return $form;
	}

	/**
	 * Called from the /user/postsave hook with the one argument of the UserModel.
	 *
	 * @param \UserModel $user
	 * @return bool
	 */
	public static function ForceSessionSync(\UserModel $user){

		// BEFORE I do this, cleanup any old sessions!
		\Session::CleanupExpired();

		$me = (\Core\user() && \Core\user()->get('id') == $user->get('id'));

		foreach(\SessionModel::Find(['user_id = ' . $user->get('id')]) as $sess){
			/** @var \SessionModel $sess */

			if($me && $sess->get('session_id') == session_id()){
				// It's this current session!
				// Reload this user object :)
				// Remember, the external data cannot be set from within the same session!
				\Session::SetUser($user);
				continue;
			}

			$dat = $sess->getExternalData();
			$dat['user_forcesync'] = true;
			$sess->setExternalData($dat);
			$sess->save();
		}

		return true;
	}

	public static function GetEnabledAuthDrivers(){
		static $auths = null;

		if($auths === null){
			// Get the available user auth systems available.
			$allauths = \Core\User\Helper::$AuthDrivers;
			// Which ones are currently enabled by the admin.
			$enabled = array_map('trim', explode('|', \ConfigHandler::Get('/user/authdrivers')));
			// The classes of the actual driver backend.
			$auths    = [];

			foreach($enabled as $name){
				// Skip blank entries.
				if(!$name) continue;

				if(!isset($allauths[$name])){
					// Skip non-present auth drivers.  Means it's currently not installed.
					trigger_error('Bad Auth Driver [' . $name . '], it is not provided by any enabled component!', E_USER_NOTICE);
					continue;
				}

				try{
					$ref = new \ReflectionClass( $allauths[$name] );
					$auths[ $name ] = $ref->newInstance();
				}
				catch(\Exception $e){
					// I don't care, it just won't be enabled.
					// Do however notify the admin of this.
					trigger_error('Bad Auth Driver [' . $name . ']: ' . $e->getMessage(), E_USER_NOTICE);
				}
			}

			// There needs to be at least one!
			if(!sizeof($auths)){
				$auths['datastore'] = new \Core\User\AuthDrivers\datastore();
			}
		}

		return $auths;
	}
}