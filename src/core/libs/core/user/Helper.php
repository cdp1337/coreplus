<?php
/**
 * Provides common user functionality, such as registration form generation and any logic required by
 * both widget and controller.
 *
 * @package User
 * @since 2.0
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2016  Charlie Powell
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
use Core\Session;
use Core\Utilities\Profiler\DatamodelProfiler;
use Core\Utilities\Profiler\Profiler;

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

		if(!$view->record) return true;

		try{

			$processingtime = (round(Profiler::GetDefaultProfiler()->getTime(), 3) * 1000);

			$log = new \UserActivityModel();
			$log->setFromArray(
				[
					'datetime' => microtime(true),
					'session_id' => session_id(),
					'user_id' => \Core\user()->get('id'),
					'ip_addr' => REMOTE_IP,
					'useragent' => $request->useragent,
					'referrer' => $request->referrer,
					'type' => $_SERVER['REQUEST_METHOD'],
					'request' => $_SERVER['REQUEST_URI'],
					'baseurl' => $request->getBaseURL(),
					'status' => $view->error,
					'db_reads' => DatamodelProfiler::GetDefaultProfiler()->readCount(),
					'db_writes' => (DatamodelProfiler::GetDefaultProfiler()->writeCount() + 1),
					'processing_time' => $processingtime,
				]
			);

			if(defined('XHPROF_RUN') && defined('XHPROF_SOURCE')){
				$log->set('xhprof_run', XHPROF_RUN);
				$log->set('xhprof_source', XHPROF_SOURCE);
			}

			$log->save();
		}
		catch(\Exception $e){
			// I don't actually care if it couldn't save.
			// This could happen if the user refreshes the page twice with in a second.
			// (and with a system that responds in about 100ms, it's very possible).
			\Core\ErrorManagement\exception_handler($e);
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

			\Core\set_message($e->getMessage(), 'error');
			return false;
		}
		catch(\Exception $e){
			// Make a note of this!
			\SystemLogModel::LogSecurityEvent('/user/register', $e->getMessage());

			if(DEVELOPMENT_MODE){
				\Core\set_message($e->getMessage(), 'error');
			}
			else{
				\Core\set_message('t:MESSAGE_ERROR_FORM_SUBMISSION_UNHANDLED_EXCEPTION');
			}

			return false;
		}

		if( \Core\user()->checkAccess('g:admin') ) {
			$active = ($form->getElementValue('active') === "on" ? 1 : 0);
			$user->set('active', $active);
		}
		else {
			$user->setDefaultActiveStatuses();
		}
		
		$user->setDefaultGroups();
		$user->setDefaultMetaFields();
		$user->generateNewApiKey();
		$user->save();

		// User created... make a log of this!
		\SystemLogModel::LogSecurityEvent('/user/register', 'User registration successful', null, $user->get('id'));

		// Send a thank you for registering email to the user.
		try{
			$user->sendWelcomeEmail();
		}
		catch(\Exception $e){
			\Core\ErrorManagement\exception_handler($e);
			\Core\set_message('t:MESSAGE_ERROR_CANNOT_SEND_WELCOME_EMAIL');
		}

		// "login" this user if not already logged in.
		if(!\Core\user()->exists()){
			if($user->get('active')){
				$user->set('last_login', \CoreDateTime::Now('U', \Time::TIMEZONE_GMT));
				$user->save();
				Session::SetUser($user);
			}
			\Core\set_message('t:MESSAGE_SUCCESS_CREATED_USER_ACCOUNT');

			if(($overrideurl = \HookHandler::DispatchHook('/user/postlogin/getredirecturl'))){
				// Allow an external script to override the redirecting URL.
				$url = $overrideurl;
			}
			elseif($form->getElementValue('redirect')){
				// The preferred default redirect method.
				// This is set from /user/register2, which is in turn passed in, (hopefully), by the original callee registration page.
				$url = $form->getElementValue('redirect');
			}
			elseif(strpos(REL_REQUEST_PATH, '/user/register') === 0){
				// If the user came from the registration page, get the page before that.
				$url = '/';
			}
			else{
				// else the registration link is now on the same page as the 403 handler.
				$url = REL_REQUEST_PATH;
			}

			return $url;
		}
		// It was created administratively; redirect there instead.
		else{
			\Core\set_message('t:MESSAGE_SUCCESS_CREATED_USER_ACCOUNT');
			return '/user/admin';
		}
	}

	public static function UpdateHandler(\Form $form){

		/** @var \UserModel $user */
		$user        = $form->getElement('user')->get('value');
		$userid      = $user->get('id');
		$usermanager = \Core\user()->checkAccess('p:/user/users/manage');

		// Only allow this if the user is either the same user or has the user manage permission.
		if(!($userid == \Core\user()->get('id') || $usermanager)){
			\Core\set_message('t:MESSAGE_ERROR_INSUFFICIENT_ACCESS_PERMISSIONS');
			return false;
		}

		if(!$user->exists()){
			\Core\set_message('t:MESSAGE_ERROR_REQUESTED_RESOURCE_NOT_FOUND');
			return false;
		}

		$userisactive = $user->get('active');

		$user->setFromForm($form);

		if($userisactive == 1 && $user->get('active') == 0){
			// User was set from active to inactive.
			// Instead of setting to a new account, set to deactivated.
			$user->set('active', '-1');
		}
		elseif($userisactive == -1 && $user->get('active') == 0){
			// User was deactivated before, reset back to that.
			// This is because the active form element is simply an on/off checkbox.
			$user->set('active', '-1');
		}

		$user->save();


		if($userisactive == 0 && $user->get('active') == 1){
			// If the user wasn't active before, but is now....
			// Send an activation notice email to the user.
			try{
				$email = new \Email();
				$email->templatename = 'emails/user/activation.tpl';
				$email->assign('user', $user);
				$email->assign('sitename', SITENAME);
				$email->assign('rooturl', ROOT_URL);
				$email->assign('loginurl', \Core\resolve_link('/user/login'));
				$email->setSubject('Welcome to ' . SITENAME);
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
			Session::SetUser($user);

			if(\ConfigHandler::Get('/user/profileedits/requireapproval') && \Core::IsComponentAvailable('model-audit')){
				\Core\set_message('t:MESSAGE_SUCCESS_UPDATED_OWN_USER_ACCOUNT_PENDING_APPROVAL');
			}
			else{
				\Core\set_message('t:MESSAGE_SUCCESS_UPDATED_OWN_USER_ACCOUNT');
			}
		}
		else{
			\Core\set_message('t:MESSAGE_SUCCESS_UPDATED_USER_ACCOUNT');
		}
		
		return 'back';
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
			$form->set('callsmethod', 'Core\\User\\Helper::RegisterHandler');
		}
		else{
			$form->set('callsmethod', 'Core\\User\\Helper::UpdateHandler');
		}

		$form->addElement('system', ['name' => 'user', 'value' => $user]);

		// Because the user system may not use a traditional Model for the backend, (think LDAP),
		// I cannot simply do a setModel() call here.

		// Tack on the active option if the current user is an admin.
		if($usermanager){
			$form->addElement(
				'checkbox',
				array(
					'name' => 'active',
					'title' => 'Active',
					'checked' => ($user->get('active') == 1),
				)
			);

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
		
		if($usermanager){
			$elements = array_keys($user->getKeySchemas());
		}
		elseif($type == 'registration'){
			$elements = explode('|', \ConfigHandler::Get('/user/register/form_elements'));
		}
		else{
			$elements = explode('|', \ConfigHandler::Get('/user/edit/form_elements'));
		}
		
		foreach($elements as $k){
			if($k && ($c = $user->getColumn($k))){
				// Skip blank elements that can be caused by string|param|foo| or empty strings.
				$el = $c->getAsFormElement();
				if($el){
					$form->addElement($el);	
				}
			}
		}

		// Tack on the group registration if the current user is an admin.
		if($groupmanager){
			// Find all the groups currently on the site.

			$where = new DatasetWhereClause();
			$where->addWhere('context = ');
			if(\Core::IsComponentAvailable('multisite') && \MultiSiteHelper::IsEnabled()){
				$where->addWhereSub('OR', ['site = ' . \MultiSiteHelper::GetCurrentSiteID(), 'site = -1']);
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
			if(\Core::IsComponentAvailable('multisite') && \MultiSiteHelper::IsEnabled()){
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
		Session::CleanupExpired();

		$me = (\Core\user() && \Core\user()->get('id') == $user->get('id'));

		foreach(\SessionModel::Find(['user_id = ' . $user->get('id')]) as $sess){
			/** @var \SessionModel $sess */

			if($me && $sess->get('session_id') == session_id()){
				// It's this current session!
				// Reload this user object :)
				// Remember, the external data cannot be set from within the same session!
				Session::SetUser($user);
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
			$allauths = Helper::$AuthDrivers;
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
				$auths['datastore'] = new AuthDrivers\datastore();
			}
		}

		return $auths;
	}
}