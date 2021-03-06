<?php
/**
 * File for class DatasetAuthController definition in the coreplus project
 *
 * @package Core\User
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140320.1636
 * @copyright Copyright (C) 2009-2017  Charlie Powell
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
 * A short teaser of what DatasetAuthController does.
 *
 * More lengthy description of what DatasetAuthController does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for DatasetAuthController
 * <h4>Example 1</h4>
 * <p>Description 1</p>
 * <code>
 * // Some code for example 1
 * $a = $b;
 * </code>
 *
 *
 * <h4>Example 2</h4>
 * <p>Description 2</p>
 * <code>
 * // Some code for example 2
 * $b = $a;
 * </code>
 *
 * @package Core\User
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class DatastoreAuthController extends Controller_2_1 {

	/**
	 * View to set the user's password, both administratively and from the user's profile.
	 *
	 * @return int
	 */
	public function password(){

		$view    = $this->getView();
		$req     = $this->getPageRequest();
		$userid  = $req->getParameter(0);
		$manager = \Core\user()->checkAccess('p:/user/users/manage'); // Current user an admin?

		// Default to current user.
		if($userid === null){
			$userid = \Core\user()->get('id');
		}

		$ownpassword = ($userid == \Core\user()->get('id'));

		// Only allow this if the user is either the same user or has the user manage permission.
		if(!($userid == \Core\user()->get('id') || $manager)){
			return View::ERROR_ACCESSDENIED;
		}

		/** @var UserModel $user */
		$user = UserModel::Construct($userid);

		if(!$user->exists()){
			\Core\set_message('Unable to locate requested user', 'error');
			\Core\go_back();
		}

		try{
			/** @var \Core\User\AuthDrivers\datastore $auth */
			$auth = $user->getAuthDriver('datastore');	
		}
		catch(Exception $e){
			// This will fail if the datastore auth is not enabled yet.
			$auth = new \Core\User\AuthDrivers\datastore($user);
		}

		$form = new \Core\Forms\Form();
		$form->set('callsmethod', 'DatastoreAuthController::_PasswordHandler');
		$form->addElement('system', ['name' => 'user', 'value' => $user->get('id')]);

		if($manager){
			$form->addElement(
				'checkbox', [
					'name' => 'pwgen',
					'value' => '1',
					'title' => t('STRING_GENERATE_SECURE_PASSWORD'),
					'description' => t('MESSAGE_GENERATE_SECURE_PASSWORD'),
				]
			);
		}
		
		$form->addElement(
			'password', 
			[
				'name' => 'pass',
				'title' => t('STRING_PASSWORD'),
				'required' => (!$manager)
			]
		);
		$form->addElement(
			'password', 
			[
				'name' => 'pass2',
				'title' => t('STRING_CONFIRM_PASSWORD'),
				'required' => (!$manager)
			]
		);

		$form->addElement('submit', array('value' => t('STRING_SAVE')));

		$complexity = $auth->getPasswordComplexityAsHTML();
		if($complexity){
			\Core\set_message($complexity, 'tutorial');
		}

		$view->mastertemplate = ConfigHandler::Get('/theme/siteskin/user');
		$view->assign('form', $form);
		$view->assign('is_manager', $manager);
		$view->title = 't:STRING_PASSWORD_MANAGEMENT';
		
		// Breadcrumbs! (based on access permissions)
		if(!$ownpassword){
			$view->addBreadcrumb('t:STRING_USER_ADMIN', '/user/admin');
			$view->addBreadcrumb($user->getDisplayName(), '/user/edit/' . $user->get('id'));
		}
		else{
			$view->addBreadcrumb('t:STRING_MY_PROFILE', '/user/me');
		}
	}

	/**
	 * Front-end view to allow users to reset their password.
	 */
	public function forgotPassword(){
		$request = $this->getPageRequest();

		// If e and k are set as parameters... it's on step 2.
		if($request->getParameter(0)){
			return $this->_forgotPassword2();
		}
		// Else, just step 1.
		else{
			return $this->_forgotPassword1();
		}
	}

	/**
	 * POST-only view to disable a user's password login ability.
	 * @return int
	 */
	public function disable(){
		$view    = $this->getView();
		$request = $this->getPageRequest();
		$userid  = $request->getParameter(0);
		$isadmin = \Core\user()->checkAccess('p:/user/users/manage'); // Current user an admin?
		$isself  = (\Core\user()->get('id') == $userid);

		if(!($isadmin || $isself)){
			return View::ERROR_ACCESSDENIED;
		}

		if(!$request->isPost()){
			return View::ERROR_BADREQUEST;
		}

		/** @var UserModel $user */
		$user = UserModel::Construct($userid);

		if(!$user->exists()){
			return View::ERROR_NOTFOUND;
		}

		if(sizeof($user->getEnabledAuthDrivers()) == 1){
			return View::ERROR_OTHER;
		}

		$user->disableAuthDriver('datastore');
		$user->save();

		\Core\set_message('t:MESSAGE_SUCCESS_DISABLED_PASSWORD_AUTH');
		\Core\go_back();
	}

	private function _forgotPassword1(){
		$view = $this->getView();
		$request = $this->getPageRequest();


		// Create a simple form to render.  This is better than doing it in the template.
		$form = new \Core\Forms\Form();
		$form->set('method', 'POST');

		if(\Core\user()->exists()){
			// This may happen with the enable-password feature for facebook accounts.
			// They shouldn't have the option to change the email, but it should display where the information will go to.
			$form->addElement('system', ['name' => 'email', 'value' => \Core\user()->get('email')]);
			$current = \Core\user()->get('email');
		}
		else{
			$form->addElement('text', ['name' => 'email', 'title' => 'Email', 'required' => true]);
			$current = false;
		}

		$form->addElement('submit', ['name' => 'submit', 'value' => 'Send Reset Instructions']);

		$view->title = 'Forgot Password';
		// This is step 1
		$view->assign('step', 1);
		$view->assign('form', $form);
		$view->assign('current', $current);
		$view->assign('can_change_email', \ConfigHandler::Get('/user/email/allowchanging'));
		// Google has no business indexing user-action pages.
		$view->addMetaName('robots', 'noindex');

		// There's really nothing to do here except for check the email and send it.

		if($request->isPost()){

			if(\Core\user()->exists()){
				/** @var UserModel $u */
				$u = \Core\user();
			}
			else{
				/** @var UserModel $u */
				$u = UserModel::Find(array('email' => $_POST['email']), 1);
			}

			if(!$u){
				\Core\set_message('t:MESSAGE_ERROR_USER_LOGIN_EMAIL_NOT_FOUND');
				SystemLogModel::LogSecurityEvent('/user/forgotpassword/send', 'Failed Forgot Password. Invalid email requested for reset: [' . $_POST['email'] . ']');
				return;
			}

			// Use the Nonce system to generate a one-time key with this user's data.
			$nonce = NonceModel::Generate(
				'20 minutes',
				null,
				[
					'type' => 'password-reset',
					'user' => $u->get('id'),
				]
			);

			$link = '/datastoreauth/forgotpassword/' . $nonce;

			$e = new \Core\Email();
			$e->setSubject('Forgot Password Request');
			$e->setTo($u->get('email'));
			$e->templatename = 'emails/user/datastoreauth_forgotpassword.tpl';
			$e->assign('link', \Core\resolve_link($link));
			$e->assign('ip', REMOTE_IP);
			try{
				$e->send();
				SystemLogModel::LogSecurityEvent('/user/forgotpassword/send', 'Forgot password request sent successfully', null, $u->get('id'));
			}
			catch(Exception $e){
				\Core\set_message('Error sending the email, ' . $e->getMessage(), 'error');
				SystemLogModel::LogErrorEvent('/user/forgotpassword/send', $e->getMessage());
				return;
			}

			// Otherwise, it must have sent, (hopefully)...
			\Core\set_message('t:MESSAGE_SUCCESS_PLEASE_CHECK_EMAIL_FOR_PASSWORD_RESET_INSTRUCTIONS');
			\core\redirect('/');
		}
	}

	private function _forgotPassword2(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$genericauth = new \Core\User\AuthDrivers\datastore();

		// Create a simple form to render.  This is better than doing it in the template.
		$form = new \Core\Forms\Form();
		$form->set('method', 'POST');
		$form->addElement('password', ['name' => 'p1', 'title' => 'Password', 'required' => true]);
		$form->addElement('password', ['name' => 'p2', 'title' => 'Confirm', 'required' => true]);
		$form->addElement('submit', ['name' => 'submit', 'value' => 'Set New Password']);

		$view->title = 'Forgot Password';
		$view->assign('step', 2);
		$view->assign('form', $form);
		$view->assign('requirements', $genericauth->getPasswordComplexityAsHTML());

		$n = $request->getParameter(0);

		/** @var $nonce NonceModel */
		$nonce = NonceModel::Construct($n);

		if(!$nonce->isValid()){
			SystemLogModel::LogSecurityEvent('/user/forgotpassword/confirm', 'Failed Forgot Password. Invalid nonce requested: [' . $n . ']');
			\Core\set_message('t:MESSAGE_ERROR_USER_LOGIN_EMAIL_NOT_FOUND');
			\core\redirect('/');
			return;
		}

		$nonce->decryptData();
		$data = $nonce->get('data');

		/** @var UserModel $u */
		$u = UserModel::Construct($data['user']);
		if(!$u){
			SystemLogModel::LogSecurityEvent('/user/forgotpassword/confirm', 'Failed Forgot Password. Invalid user account requested: [' . $data['user'] . ']');
			\Core\set_message('t:MESSAGE_ERROR_USER_LOGIN_EMAIL_NOT_FOUND');
			\core\redirect('/');
			return;
		}


		if($request->isPost()){
			// Validate the password.
			if($_POST['p1'] != $_POST['p2']){
				\Core\set_message('t:MESSAGE_ERROR_USER_REGISTER_PASSWORD_MISMATCH');
				return;
			}

			// Else, try to set it... the user model will complain if it's invalid.
			try{
				$u->enableAuthDriver('datastore');
				/** @var \Core\User\AuthDrivers\datastore $auth */
				$auth = $u->getAuthDriver('datastore');

				$auth->setPassword($_POST['p1']);
				$u->save();
				// NOW I can invalidate that nonce!
				$nonce->markUsed();
				SystemLogModel::LogSecurityEvent('/user/forgotpassword/confirm', 'Reset password successfully!', null, $u->get('id'));
				\Core\set_message('Reset password successfully', 'success');
				if($u->get('active')){
					\Core\Session::SetUser($u);
				}
				\core\redirect('/');
			}
			catch(ModelValidationException $e){
				SystemLogModel::LogSecurityEvent('/user/forgotpassword/confirm', 'Failed Forgot Password. ' . $e->getMessage(), null, $u->get('id'));
				\Core\set_message($e->getMessage(), 'error');
				return;
			}
			catch(Exception $e){
				SystemLogModel::LogSecurityEvent('/user/forgotpassword/confirm', 'Failed Forgot Password. ' . $e->getMessage(), null, $u->get('id'));
				\Core\set_message((DEVELOPMENT_MODE ? $e->getMessage() : 'An unknown error occured'), 'error');
				return;
			}
		}
	}

	/**
	 * This is the handler the standard registration login form.
	 *
	 * @param Form $form
	 *
	 * @return bool|string
	 */
	public static function RegisterHandler(\Core\Forms\Form $form) {
		$p1 = $form->getElement('pass');
		$p2 = $form->getElement('pass2');

		///////       VALIDATION     \\\\\\\\

		// All other validation can be done from the model.
		// All set calls will throw a ModelValidationException if the validation fails.
		try{
			$user = new \UserModel();
			$password = null;

			$user->set('email', $form->getElement('email')->get('value'));
			$user->enableAuthDriver('datastore');
			/** @var \Core\User\AuthDrivers\datastore $auth */
			$auth = $user->getAuthDriver('datastore');

			if($form->getElement('pwgen') && $form->getElementValue('pwgen')){
				$password = $auth->pwgen();
				$auth->setPassword($password);
				$user->set('password_raw', $password);
			}

			// Users can be created with no password.  They will be prompted to set it on first login.
			if($p1->get('value') || $p2->get('value')){

				if($p1->get('value') != $p2->get('value')){
					$p1->setError('t:MESSAGE_ERROR_USER_REGISTER_PASSWORD_MISMATCH');
					$p2->set('value', '');
					return false;
				}

				$passresult = $auth->setPassword($p1->get('value'));

				if($passresult !== true){
					$p1->setError($passresult === false ? 'Invalid password' : $passresult);
					$p2->set('value', '');
					return false;
				}
				
				// Do not set the password_raw value here as we do not wish for it to be sent to the user via email.
			}
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

			\Core\set_message(DEVELOPMENT_MODE ? $e->getMessage() : 'An unknown error occurred', 'error');

			return false;
		}

		// Otherwise, w00t!  Record this user into a nonce and forward to step 2 of registration.
		$nonce = NonceModel::Generate(
			'20 minutes',
			null,
			[
				'user' => $user,
				'redirect' => $form->getElementValue('redirect'),
			]
		);
		return '/user/register2/' . $nonce;
	}

	/**
	 * Form Handler for logging in.
	 *
	 * @static
	 *
	 * @param \Form $form
	 *
	 * @return bool|null|string
	 */
	public static function LoginHandler(\Core\Forms\Form $form){
		/** @var \FormElement $e */
		$e = $form->getElement('email');
		/** @var \FormElement $p */
		$p = $form->getElement('pass');


		/** @var \UserModel $u */
		$u = \UserModel::Find(array('email' => $e->get('value')), 1);

		if(!$u){
			// Log this as a login attempt!
			$logmsg = 'Failed Login. Email not registered' . "\n" . 'Email: ' . $e->get('value') . "\n";
			\SystemLogModel::LogSecurityEvent('/user/login', $logmsg);
			$e->setError('t:MESSAGE_ERROR_USER_LOGIN_EMAIL_NOT_FOUND');
			return false;
		}

		if($u->get('active') == 0){
			// The model provides a quick cut-off for active/inactive users.
			// This is the control managed with in the admin.
			$logmsg = 'Failed Login. User tried to login before account activation' . "\n" . 'User: ' . $u->get('email') . "\n";
			\SystemLogModel::LogSecurityEvent('/user/login', $logmsg, null, $u->get('id'));
			$e->setError('t:MESSAGE_ERROR_USER_LOGIN_ACCOUNT_NOT_ACTIVE');
			return false;
		}
		elseif($u->get('active') == -1){
			// The model provides a quick cut-off for active/inactive users.
			// This is the control managed with in the admin.
			$logmsg = 'Failed Login. User tried to login after account deactivation.' . "\n" . 'User: ' . $u->get('email') . "\n";
			\SystemLogModel::LogSecurityEvent('/user/login', $logmsg, null, $u->get('id'));
			$e->setError('t:MESSAGE_ERROR_USER_LOGIN_ACCOUNT_DEACTIVATED');
			return false;
		}

		try{
			/** @var \Core\User\AuthDrivers\datastore $auth */
			$auth = $u->getAuthDriver('datastore');
		}
		catch(Exception $err){
			$e->setError('t:MESSAGE_ERROR_USER_LOGIN_PASSWORD_AUTH_DISABLED');
			return false;
		}


		// This is a special case if the password isn't set yet.
		// It can happen with imported users or if a password is invalidated.
		if($u->get('password') == ''){
			// Use the Nonce system to generate a one-time key with this user's data.
			$nonce = \NonceModel::Generate(
				'20 minutes',
				['type' => 'password-reset', 'user' => $u->get('id')]
			);

			$link = '/datastoreauth/forgotpassword?e=' . urlencode($u->get('email')) . '&n=' . $nonce;

			$email = new \Core\Email();
			$email->setSubject('Initial Password Request');
			$email->setTo($u->get('email'));
			$email->assign('link', \Core\resolve_link($link));
			$email->assign('ip', REMOTE_IP);
			$email->templatename = 'emails/user/initialpassword.tpl';
			try{
				$email->send();
				\SystemLogModel::LogSecurityEvent('/user/initialpassword/send', 'Initial password request sent successfully', null, $u->get('id'));

				\Core\set_message('t:MESSAGE_INFO_USER_LOGIN_MUST_SET_NEW_PASSWORD_INSTRUCTIONS_HAVE_BEEN_EMAILED');
				return true;
			}
			catch(\Exception $e){
				\Core\ErrorManagement\exception_handler($e);
				\Core\set_message('t:MESSAGE_ERROR_USER_LOGIN_MUST_SET_NEW_PASSWORD_UNABLE_TO_SEND_EMAIL');
				return false;
			}
		}


		if(!$auth->checkPassword($p->get('value'))){

			// Log this as a login attempt!
			$logmsg = 'Failed Login. Invalid password' . "\n" . 'Email: ' . $e->get('value') . "\n";
			\SystemLogModel::LogSecurityEvent('/user/login/failed_password', $logmsg, null, $u->get('id'));

			// Also, I want to look up and see how many login attempts there have been in the past couple minutes.
			// If there are too many, I need to start slowing the attempts.
			$time = new \CoreDateTime();
			$time->modify('-5 minutes');

			$securityfactory = new \ModelFactory('SystemLogModel');
			$securityfactory->where('code = /user/login/failed_password');
			$securityfactory->where('datetime > ' . $time->getFormatted(\Time::FORMAT_EPOCH, \Time::TIMEZONE_GMT));
			$securityfactory->where('ip_addr = ' . REMOTE_IP);

			$attempts = $securityfactory->count();
			if($attempts > 4){
				// Start slowing down the response.  This should help deter brute force attempts.
				// (x+((x-7)/4)^3)-4
				sleep( ($attempts+(($attempts-7)/4)^3)-4 );
				// This makes a nice little curve with the following delays:
				// 5th  attempt: 0.85
				// 6th  attempt: 2.05
				// 7th  attempt: 3.02
				// 8th  attempt: 4.05
				// 9th  attempt: 5.15
				// 10th attempt: 6.52
				// 11th attempt: 8.10
				// 12th attempt: 10.05
			}

			$e->setError('t:MESSAGE_ERROR_USER_LOGIN_INCORRECT_PASSWORD');
			$p->set('value', '');
			return false;
		}


		if($form->getElementValue('redirect')){
			// The page was set via client-side javascript on the login page.
			// This is the most reliable option.
			$url = $form->getElementValue('redirect');
		}
		elseif(REL_REQUEST_PATH == '/user/login'){
			// If the user came from the registration page, get the page before that.
			$url = $form->referrer;
		}
		else{
			// else the registration link is now on the same page as the 403 handler.
			$url = REL_REQUEST_PATH;
		}

		// Well, record this too!
		\SystemLogModel::LogSecurityEvent('/user/login', 'Login successful (via password)', null, $u->get('id'));

		// yay...
		$u->set('last_login', \CoreDateTime::Now('U', \Time::TIMEZONE_GMT));
		$u->save();
		\Core\Session::SetUser($u);

		// Allow an external script to override the redirecting URL.
		$overrideurl = \HookHandler::DispatchHook('/user/postlogin/getredirecturl');
		if($overrideurl){
			$url = $overrideurl;
		}

		return $url;
	}

	/**
	 * Hook receiver for /core/controllinks/user/view
	 *
	 * @param int $userid
	 *
	 * @return array
	 */
	public static function GetUserControlLinks($userid){

		$globalEnabled = \Core\User\Helper::GetEnabledAuthDrivers();
		if(!isset($globalEnabled['datastore'])){
			// Datastore isn't enabled at all, disable any control links from the system.
			return [];
		}

		if($userid instanceof UserModel){
			$user = $userid;
		}
		else{
			/** @var UserModel $user */
			$user = UserModel::Construct($userid);
		}

		if(!$user->exists()){
			// Invalid user.
			return [];
		}

		$isself = (\Core\user()->get('id') == $user->get('id'));
		$isadmin = \Core\user()->checkAccess('p:/user/users/manage');

		if(!($isself || $isadmin)){
			// Current user does not have access to manage the provided user's data.
			return [];
		}

		$ret = [];
		$enabled = $user->isAuthDriverEnabled('datastore');
		
		if(!$enabled){
			$ret[] = [
				'link' => '/datastoreauth/password/' . $user->get('id'),
				'title' => t('STRING_ENABLE_PASSWORD_LOGIN'),
				'icon' => 'key',
			];
		}
		else{
			$ret[] = [
				'link' => '/datastoreauth/password/' . $user->get('id'),
				'title' => t('STRING_CHANGE_PASSWORD'),
				'icon' => 'key',
			];

			if(sizeof($user->getEnabledAuthDrivers()) > 1){
				// Only give the option to disable the password logins if there is another type available.
				$ret[] = [
					'link' => '/datastoreauth/disable/' . $user->get('id'),
					'title' => t('STRING_DISABLE_PASSWORD_LOGIN'),
					'icon' => 'ban',
					'confirm' => 'Are you sure you want to disable password-based logins?  (They can be re-enabled if requested.)',
				];
			}
		}
		
		return $ret;

	}

	public static function _PasswordHandler(\Core\Forms\Form $form) {
		$p1 = $form->getElement('pass');
		$p2 = $form->getElement('pass2');
		
		$p1val = $p1->get('value');
		$p2val = $p2->get('value');

		$manager = \Core\user()->checkAccess('p:/user/users/manage'); // Current user an admin?
		
		/** @var UserModel $user */
		$user = UserModel::Construct($form->getElementValue('user'));
		if(!$user->exists()){
			throw new ModelValidationException('User does not exist');
		}

		$ownpassword = ($user->get('id') == \Core\user()->get('id'));

		// Only allow this if the user is either the same user or has the user manage permission.
		if(!($ownpassword || $manager)){
			return View::ERROR_ACCESSDENIED;
		}
		
		$user->enableAuthDriver('datastore');
		/** @var \Core\User\AuthDrivers\datastore $auth */
		$auth = $user->getAuthDriver('datastore');

		if($manager && $form->getElement('pwgen') && $form->getElementValue('pwgen')){
			$password = $auth->pwgen();
			$auth->setPassword($password);
			
			$user->save();
			\SystemLogModel::LogSecurityEvent('/user/password', 'Password changed (administratively)', null, $user->get('id'));
			
			// Send an email to this user notifying them of the new password.
			$email = new \Core\Email();
			$email->setTo($user->get('email'));
			$email->setSubject('New Password Set');
			$email->templatename = 'emails/user/admin_password.tpl';
			$email->assign('user', $user);
			$email->assign('new_password', $password);
			$email->assign('sitename', SITENAME);
			try{
				$email->send();
				\Core\set_message('Updated user password!', 'success');
			}
			catch(Exception $e){
				\Core\set_message($e->getMessage(), 'error');
				\Core\set_message('Set password as "' . $password . '" but could not send the notification email.', 'warning');
			}
		}
		else{
			// Check the passwords, (that they match).
			if($p1val != $p2val){
				throw new ModelValidationException('Passwords do not match');
			}
			$status = $auth->setPassword($p1val);

			if($status === false){
				// No change
				\Core\set_message('t:MESSAGE_INFO_NO_CHANGES_PERFORMED');
			}
			elseif($status === true){
				$user->save();
				\SystemLogModel::LogSecurityEvent('/user/password', 'Password changed', null, $user->get('id'));
				\Core\set_message('t:MESSAGE_SUCCESS_UPDATED_PASSWORD');
			}
			else{
				throw new ModelValidationException($status);
			}
		}

		if($ownpassword){
			return '/user/me';
		}
		else{
			return '/user/admin';
		}
	}
}