<?php
/**
 * File for class DatasetAuthController definition in the coreplus project
 *
 * @package Core\User
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20140320.1636
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
 * @author Charlie Powell <charlie@eval.bz>
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
			Core::SetMessage('Unable to locate requested user', 'error');
			\Core\go_back(1);
		}

		/** @var \Core\User\AuthDrivers\datastore $auth */
		$auth = $user->getAuthDriver('datastore');

		if($req->isPost()){
			try{
				$p1val = $_POST['pass'];
				$p2val = $_POST['pass2'];
				// Check the passwords, (that they match).
				if($p1val != $p2val){
					throw new ModelValidationException('Passwords do not match');
				}

				$status = $auth->setPassword($p1val);
				if($status === false){
					// No change
					Core::SetMessage('No change detected');
				}
				elseif($status === true){
					$user->save();
					Core::SetMessage('Updated Password Successfully', 'success');
				}
				else{
					throw new ModelValidationException($status);
				}

				if($ownpassword){
					\core\redirect('/user/me');
				}
				else{
					\core\redirect('/user/admin');
				}
			}
			catch(ModelValidationException $e){
				Core::SetMessage($e->getMessage(), 'error');
			}
			catch(Exception $e){
				if(DEVELOPMENT_MODE) Core::SetMessage($e->getMessage(), 'error');
				else Core::SetMessage('An unknown error occured', 'error');
				\Core\ErrorManagement\exception_handler($e);
			}
		}

		$form = new Form();

		$form->addElement('password', array('name' => 'pass', 'title' => 'Password', 'required' => true));
		$form->addElement('password', array('name' => 'pass2', 'title' => 'Confirm', 'required' => true));

		$form->addElement('submit', array('value' => 'Update Password'));

		// Pull some info about the complexity requirements.
		$complexity = [
			'enabled'  => false,
			'length'   => 0,
			'symbols'  => 0,
			'capitals' => 0,
			'numbers'  => 0,
			'messages' => [],
		];
		if(ConfigHandler::Get('/user/password/minlength')){
			$complexity['enabled'] = true;
			$complexity['length'] = ConfigHandler::Get('/user/password/minlength');
			$complexity['messages'][] = 'The password is at least ' . $complexity['length'] . ' characters long.';
		}
		if(ConfigHandler::Get('/user/password/requiresymbols')){
			$complexity['enabled'] = true;
			$complexity['symbols'] = ConfigHandler::Get('/user/password/requiresymbols');
			$complexity['messages'][] = 'The password contains at least ' . $complexity['symbols'] . ' symbol(s).';
		}
		if(ConfigHandler::Get('/user/password/requirecapitals')){
			$complexity['enabled'] = true;
			$complexity['capitals'] = ConfigHandler::Get('/user/password/requirecapitals');
			$complexity['messages'][] = 'The password contains at least ' . $complexity['capitals'] . ' capital(s).';
		}
		if(ConfigHandler::Get('/user/password/requirenumbers')){
			$complexity['enabled'] = true;
			$complexity['numbers'] = ConfigHandler::Get('/user/password/requirenumbers');
			$complexity['messages'][] = 'The password contains at least ' . $complexity['numbers'] . ' number(s).';
		}

		$view->mastertemplate = ConfigHandler::Get('/theme/siteskin/user');
		$view->assign('complexity', $complexity);
		$view->assign('form', $form);
		$view->title = 'Password Management ';

		// Breadcrumbs! (based on access permissions)
		if(!$ownpassword){
			$view->addBreadcrumb('User Administration', '/user/admin');
			$view->addBreadcrumb($user->getDisplayName(), '/user/edit/' . $user->get('id'));
		}
		else{
			$view->addBreadcrumb('My Profile', '/user/me');
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

		Core::SetMessage('Disabled password logins!', 'success');
		\Core\go_back();
	}

	private function _forgotPassword1(){
		$view = $this->getView();
		$request = $this->getPageRequest();


		// Create a simple form to render.  This is better than doing it in the template.
		$form = new Form();
		$form->set('method', 'POST');
		$form->addElement('text', ['name' => 'email', 'title' => 'Email', 'required' => true]);
		$form->addElement('submit', ['name' => 'submit', 'value' => 'Send Reset Instructions']);

		$view->title = 'Forgot Password';
		// This is step 1
		$view->assign('step', 1);
		$view->assign('form', $form);
		// Google has no business indexing user-action pages.
		$view->addMetaName('robots', 'noindex');

		// There's really nothing to do here except for check the email and send it.

		if($request->isPost()){

			/** @var UserModel $u */
			$u = UserModel::Find(array('email' => $_POST['email']), 1);
			if(!$u){
				Core::SetMessage('Invalid user account requested', 'error');
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

			$e = new Email();
			$e->setSubject('Forgot Password Request');
			$e->to($u->get('email'));
			$e->assign('link', Core::ResolveLink($link));
			$e->assign('ip', REMOTE_IP);
			$e->templatename = 'emails/user/datastoreauth_forgotpassword.tpl';
			try{
				$e->send();
				SystemLogModel::LogSecurityEvent('/user/forgotpassword/send', 'Forgot password request sent successfully', null, $u->get('id'));
			}
			catch(Exception $e){
				Core::SetMessage('Error sending the email, ' . $e->getMessage(), 'error');
				SystemLogModel::LogErrorEvent('/user/forgotpassword/send', $e->getMessage());
				return;
			}

			// Otherwise, it must have sent, (hopefully)...
			Core::SetMessage('Sent reset instructions via email.', 'success');
			\core\redirect('/');
		}
	}

	private function _forgotPassword2(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$genericauth = new \Core\User\AuthDrivers\datastore();

		// Create a simple form to render.  This is better than doing it in the template.
		$form = new Form();
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
			Core::SetMessage('Invalid user account requested', 'error');
			\core\redirect('/');
			return;
		}

		$nonce->decryptData();
		$data = $nonce->get('data');

		/** @var UserModel $u */
		$u = UserModel::Construct($data['user']);
		if(!$u){
			SystemLogModel::LogSecurityEvent('/user/forgotpassword/confirm', 'Failed Forgot Password. Invalid user account requested: [' . $e . ']');
			Core::SetMessage('Invalid user account requested', 'error');
			\core\redirect('/');
			return;
		}


		if($request->isPost()){
			// Validate the password.
			if($_POST['p1'] != $_POST['p2']){
				Core::SetMessage('Passwords do not match.', 'error');
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
				Core::SetMessage('Reset password successfully', 'success');
				if($u->get('active')){
					Session::SetUser($u);
				}
				\core\redirect('/');
			}
			catch(ModelValidationException $e){
				SystemLogModel::LogSecurityEvent('/user/forgotpassword/confirm', 'Failed Forgot Password. ' . $e->getMessage(), null, $u->get('id'));
				Core::SetMessage($e->getMessage(), 'error');
				return;
			}
			catch(Exception $e){
				SystemLogModel::LogSecurityEvent('/user/forgotpassword/confirm', 'Failed Forgot Password. ' . $e->getMessage(), null, $u->get('id'));
				if(DEVELOPMENT_MODE) Core::SetMessage($e->getMessage(), 'error');
				else Core::SetMessage('An unknown error occured', 'error');

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
	public static function RegisterHandler(Form $form) {
		$p1 = $form->getElement('pass');
		$p2 = $form->getElement('pass2');

		///////       VALIDATION     \\\\\\\\

		// All other validation can be done from the model.
		// All set calls will throw a ModelValidationException if the validation fails.
		try{
			$user = new \UserModel();

			$user->set('email', $form->getElement('email')->get('value'));
			$user->enableAuthDriver('datastore');
			/** @var \Core\User\AuthDrivers\datastore $auth */
			$auth = $user->getAuthDriver('datastore');

			// Users can be created with no password.  They will be prompted to set it on first login.
			if($p1->get('value') || $p2->get('value')){

				if($p1->get('value') != $p2->get('value')){
					$p1->setError('Passwords do not match!');
					$p2->set('value', '');
					return false;
				}

				$passresult = $auth->setPassword($p1->get('value'));

				if($passresult !== true){
					$p1->setError($passresult === false ? 'Invalid password' : $passresult);
					$p2->set('value', '');
					return false;
				}
			}
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
			else \Core::SetMessage('An unknown error occurred', 'error');

			return false;
		}

		// Otherwise, w00t!  Record this user into a nonce and forward to step 2 of registration.
		$nonce = NonceModel::Generate('20 minutes', null, ['user' => $user]);
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
	public static function LoginHandler(\Form $form){
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
			$e->setError('Requested email is not registered.');
			return false;
		}

		if($u->get('active') == 0){
			// The model provides a quick cut-off for active/inactive users.
			// This is the control managed with in the admin.
			$logmsg = 'Failed Login. User tried to login before account activation' . "\n" . 'User: ' . $u->get('email') . "\n";
			\SystemLogModel::LogSecurityEvent('/user/login', $logmsg, null, $u->get('id'));
			$e->setError('Your account is not active yet.');
			return false;
		}
		elseif($u->get('active') == -1){
			// The model provides a quick cut-off for active/inactive users.
			// This is the control managed with in the admin.
			$logmsg = 'Failed Login. User tried to login after account deactivation.' . "\n" . 'User: ' . $u->get('email') . "\n";
			\SystemLogModel::LogSecurityEvent('/user/login', $logmsg, null, $u->get('id'));
			$e->setError('Your account has been deactivated.');
			return false;
		}

		try{
			/** @var \Core\User\AuthDrivers\datastore $auth */
			$auth = $u->getAuthDriver('datastore');
			if(!$auth){
				Core::SetMessage('This account does not have password logins enabled!', 'error');
				return false;
			}
		}
		catch(Exception $e){
			Core::SetMessage('Your account does not have password logins enabled!<br/>If you wish to enable them, please <a href="' . Core::ResolveLink('/datastoreauth/forgotpassword') . '">use the password reset tool</a> to create one.', 'error');
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

			$email = new \Email();
			$email->setSubject('Initial Password Request');
			$email->to($u->get('email'));
			$email->assign('link', \Core::ResolveLink($link));
			$email->assign('ip', REMOTE_IP);
			$email->templatename = 'emails/user/initialpassword.tpl';
			try{
				$email->send();
				\SystemLogModel::LogSecurityEvent('/user/initialpassword/send', 'Initial password request sent successfully', null, $u->get('id'));

				\Core::SetMessage('You must set a new password.  An email has been sent to your inbox containing a link and instructions on setting a new password.', 'info');
				return true;
			}
			catch(\Exception $ex){
				\Core\ErrorManagement\exception_handler($e);
				\Core::SetMessage('Unable to send new password link to your email, please contact the system administrator!', 'error');
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

			$p->setError('Invalid password');
			$p->set('value', '');
			return false;
		}

		// If the user came from the registration page, get the page before that.
		if(REL_REQUEST_PATH == '/user/login') $url = \Core::GetHistory(2);
		// else the registration link is now on the same page as the 403 handler.
		else $url = REL_REQUEST_PATH;

		// Well, record this too!
		\SystemLogModel::LogSecurityEvent('/user/login', 'Login successful (via password)', null, $u->get('id'));

		// yay...
		$u->set('last_login', \CoreDateTime::Now('U', \Time::TIMEZONE_GMT));
		$u->save();
		\Session::SetUser($u);

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

		$enabled = User\Helper::GetEnabledAuthDrivers();
		if(!isset($enabled['datastore'])){
			// GPG isn't enabled at all, disable any control links from the system.
			return [];
		}

		/** @var UserModel $user */
		$user = UserModel::Construct($userid);

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

		try{
			// If this throws an exception, then it's not enabled!
			$user->getAuthDriver('datastore');
		}
		catch(Exception $e){
			if($isself){
				return [
					[
						'link' => '/datastoreauth/forgotpassword',
						'title' => 'Enable Password Login',
						'icon' => 'key',
					]
				];
			}
		}

		$ret = [];
		$ret[] = [
			'link' => '/datastoreauth/password/' . $user->get('id'),
			'title' => 'Change Password',
			'icon' => 'key',
		];

		if(sizeof($user->getEnabledAuthDrivers()) > 1){
			$ret[] = [
				'link' => '/datastoreauth/disable/' . $user->get('id'),
				'title' => 'Disable Password Login',
				'icon' => 'ban',
				'confirm' => 'Are you sure you want to disable password-based logins?  (They can be re-enabled if requested.)',
			];
		}


		return $ret;

	}
} 