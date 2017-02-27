<?php
/**
 * Main controller for the user system
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

/**
 * Main controller for the user system
 */
class UserController extends Controller_2_1{
	
	/*public function index(){
		$view = $this->getView();

		// @todo This should probably be enabled in the future, at least toggleable.
		if(!$view->setAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}
	}*/

	/**
	 * Show the current user's profile.
	 */
	public function me(){
		// I could put this in the component xml, but it's not needed everywhere
		require_once(ROOT_PDIR . 'components/user/helpers/UserFunctions.php');

		$view    = $this->getView();
		$req     = $this->getPageRequest();
		$user    = \Core\user();

		if(!$user->exists()){
			return View::ERROR_ACCESSDENIED;
		}

		$form = \User\get_edit_form($user);


		$view->controls = ViewControls::Dispatch('/user/view', $user->get('id'));
		$view->controls->hovercontext = true;

		$view->assign('user', $user);
		$view->assign('form', $form);
		$view->title = 'My Profile';
	}

	public function password(){
		// I could put this in the component xml, but it's not needed everywhere
		require_once(ROOT_PDIR . 'components/user/helpers/UserFunctions.php');

		$view    = $this->getView();
		$req     = $this->getPageRequest();
		$userid  = $req->getParameter(0);
		$manager = \Core\user()->checkAccess('p:/user/users/manage'); // Current user an admin?

		// Default to current user.
		if($userid === null){
			$ownpassword = true;
			$userid = \Core\user()->get('id');
		}
		else{
			$ownpassword = false;
		}

		// Only allow this if the user is either the same user or has the user manage permission.
		if(!($userid == \Core\user()->get('id') || $manager)){
			return View::ERROR_ACCESSDENIED;
		}

		$user = User::Construct($userid);

		if(!$user->exists()){
			Core::SetMessage('Unable to locate requested user', 'error');
			Core::GoBack(1);
		}

		if($req->isPost()){
			try{
				$p1val = $_POST['pass'];
				$p2val = $_POST['pass2'];
				// Check the passwords, (that they match).
				if($p1val != $p2val){
					throw new ModelValidationException('Passwords do not match');
				}

				$user->set('password', $p1val);
				$user->set('last_password', CoreDateTime::Now('U', Time::TIMEZONE_GMT));
				$user->save();
				Core::SetMessage('Updated Password Successfully', 'success');
				if($ownpassword){
					\core\redirect('/user/me');
				}
				else{
					\core\redirect('/useradmin');
				}
			}
			catch(ModelValidationException $e){
				Core::SetMessage($e->getMessage(), 'error');
			}
			catch(Exception $e){
				if(DEVELOPMENT_MODE) Core::SetMessage($e->getMessage(), 'error');
				else Core::SetMessage('An unknown error occured', 'error');
				error_log($e->getMessage());
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

		$view->assign('complexity', $complexity);
		$view->assign('form', $form);
		$view->title = 'Password Management ';

		// Breadcrumbs! (based on access permissions)
		if(!$ownpassword){
			$view->addBreadcrumb('User Administration', '/useradmin');
			$view->addBreadcrumb($user->getDisplayName(), '/user/edit/' . $user->get('id'));
		}
		else{
			$view->addBreadcrumb('My Profile', '/user/me');
		}
	}

	public function edit(){
		// I could put this in the component xml, but it's not needed everywhere
		require_once(ROOT_PDIR . 'components/user/helpers/UserFunctions.php');

		$view    = $this->getView();
		$req     = $this->getPageRequest();
		$userid  = $req->getParameter(0);
		$manager = \Core\user()->checkAccess('p:/user/users/manage'); // Current user an admin?

		if($userid === null) $userid = \Core\user()->get('id'); // Default to current user.

		// Only allow this if the user is either the same user or has the user manage permission.
		if(!($userid == \Core\user()->get('id') || $manager)){
			Core::SetMessage('Insufficient Permissions', 'error');
			\core\redirect('/');
		}

		$user = User::Find(array('id' => $userid));
		if($user) {
			$form = \User\get_edit_form($user);
		} else {
			Core::SetMessage('A user with this ID does not exist');
			\Core\redirect( '/admin' );
		}

		$view->controls = ViewControls::Dispatch('/user/view', $user->get('id'));
		$view->assign('form', $form);
		$view->title = 'Editing ' . $user->getDisplayName();

		// Breadcrumbs! (based on access permissions)
		if($manager){
			$view->addBreadcrumb('User Administration', '/useradmin');
		}
	}

	public function login(){
		$view = $this->getView();

		$this->setTemplate('/pages/user/login.tpl');

		// Is the user already logged in?
		if(\Core\user()->exists()){
			\core\redirect('/user/me');
		}

		// Set the access permissions for this page as anonymous-only.
		if(!$this->setAccess('g:anonymous;g:!admin')){
			return View::ERROR_ACCESSDENIED;
		}

		$form = new Form();
		$form->set('callsMethod', 'UserHelper::LoginHandler');

		$form->addElement('text', array('name' => 'email', 'title' => 'Email', 'required' => true));
		$form->addElement('password', array('name' => 'pass', 'title' => 'Password', 'required' => false));
		$form->addElement('submit', array('name' => 'submit', 'value' => 'Login'));

		$error = false;

		// @todo Implement a hook handler here for UserPreLoginForm


		$view->ssl = true;
		$view->assign('error', $error);
		$view->assign('backends', ConfigHandler::Get('/user/backends'));
		$view->assign('form', $form);
		$view->assign('allowregister', ConfigHandler::Get('/user/register/allowpublic'));
		// Google has no business indexing user-action pages.
		$view->addMetaName('robots', 'noindex');


		return $view;
	}

	/**
	 * Ajax page to allow for quickly linking the current user to a facebook account from a strictly javascript interface.
	 *
	 */
	public function linkfacebook(){
		$request = $this->getPageRequest();
		$view    = $this->getView();

		$view->mode = View::MODE_AJAX;
		$view->contenttype = View::CTYPE_JSON;

		if(!$request->isAjax()){
			return View::ERROR_BADREQUEST;
		}
		if(!$request->isPost()){
			return View::ERROR_BADREQUEST;
		}

		$user = \Core\user();
		if(!$user->exists()){
			return View::ERROR_BADREQUEST;
		}

		// Necessary fields are id and token.
		$user->set('facebook_access_token', $_POST['token']);
		$user->set('facebook_id', $_POST['id']);
		$user->save();

		$view->jsondata = array(
			'id' => $user->get('id'),
			'access_token' => $user->get('facebook_access_token'),
			'facebook_id' => $user->get('facebook_id'),
		);
	}

	public function register(){
		// I could put this in the component xml, but it's not needed everywhere
		require_once(ROOT_PDIR . 'components/user/helpers/UserFunctions.php');

		$view = $this->getView();
		$manager = \Core\user()->checkAccess('p:/user/users/manage'); // Current user an admin?

		// Anonymous users should have access to this if it's allow public.
		if(!\Core\user()->exists() && !ConfigHandler::Get('/user/register/allowpublic')){
			return View::ERROR_BADREQUEST;
		}

		// Authenticated users must check the permission to manage users.
		if(\Core\user()->exists() && !$manager){
			return View::ERROR_ACCESSDENIED;
		}

		$form = \User\get_registration_form();

		$view->ssl = true;
		$view->assign('form', $form);
		// Google has no business indexing user-action pages.
		$view->addMetaName('robots', 'noindex');

		// Breadcrumbs! (based on access permissions)
		if($manager){
			$view->addBreadcrumb('User Administration', '/useradmin');
		}
	}

	public function logout(){
		$view = $this->getView();

		// Set the access permissions for this page as authenticated-only.
		if(!$view->setAccess('g:authenticated;g:!admin')){
			return View::ERROR_ACCESSDENIED;
		}

		Session::DestroySession();
		\core\redirect('/');
	}


	public function forgotPassword(){
		$request = $this->getPageRequest();

		// If e and k are set as parameters... it's on step 2.
		if($request->getParameter('e') && $request->getParameter('n')){
			return $this->_forgotPassword2();
		}
		// Else, just step 1.
		else{
			return $this->_forgotPassword1();
		}
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

			$u = User::Find(array('email' => $_POST['email']), 1);
			if(!$u){
				Core::SetMessage('Invalid user account requested', 'error');
				SecurityLogModel::Log('/user/forgotpassword/send', 'fail', null, 'Invalid email requested for reset: [' . $_POST['email'] . ']');
				return;
			}

			if(($str = $u->canResetPassword()) !== true){
				Core::SetMessage($str, 'error');
				SecurityLogModel::Log('/user/forgotpassword/send', 'fail', $u->get('id'), $str . ': [' . $_POST['email'] . ']');
				return;
			}

			// Use the Nonce system to generate a one-time key with this user's data.
			$nonce = NonceModel::Generate(
				'20 minutes',
				['type' => 'password-reset', 'user' => $u->get('id')]
			);

			$link = '/user/forgotpassword?e=' . urlencode($u->get('email')) . '&n=' . $nonce;

			$e = new Email();
			$e->setSubject('Forgot Password Request');
			$e->to($u->get('email'));
			$e->assign('link', Core::ResolveLink($link));
			$e->assign('ip', REMOTE_IP);
			$e->templatename = 'emails/user/forgotpassword.tpl';
			try{
				$e->send();
				SecurityLogModel::Log('/user/forgotpassword/send', 'success', $u->get('id'), 'Forgot password request sent successfully');
			}
			catch(Exception $e){
				Core::SetMessage('Error sending the email, ' . $e->getMessage(), 'error');
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

		// Create a simple form to render.  This is better than doing it in the template.
		$form = new Form();
		$form->set('method', 'POST');
		$form->addElement('password', ['name' => 'p1', 'title' => 'Password', 'required' => true]);
		$form->addElement('password', ['name' => 'p2', 'title' => 'Confirm', 'required' => true]);
		$form->addElement('submit', ['name' => 'submit', 'value' => 'Set New Password']);

		$view->title = 'Forgot Password';
		$view->assign('step', 2);
		$view->assign('form', $form);

		// Lookup and validate this information first.
		$e = urldecode($request->getParameter('e'));

		$u = User::Find(array('email' => $e), 1);
		if(!$u){
			SecurityLogModel::Log('/user/forgotpassword/confirm', 'fail', null, 'Invalid user account requested: [' . $e . ']');
			Core::SetMessage('Invalid user account requested', 'error');
			\core\redirect('/');
			return;
		}

		// Make sure that nonce hasn't expired yet and is still valid.
		$n = $request->getParameter('n');

		/** @var $nonce NonceModel */
		$nonce = NonceModel::Construct($n);
		
		// There are two data types.... (Yeah yeah, I know.....)
		$data1 = [
			'type' => 'password-reset',
			'user' => (int)$u->get('id')
		];
		$data2 = [
			'type' => 'password-reset',
			'user' => $u->get('id')
		];
		// I can't invalidate it quite yet... the user still needs to set the new password.
		if(!$nonce->isValid($data1) && !$nonce->isValid($data2)){
			SecurityLogModel::Log('/user/forgotpassword/confirm', 'fail', $u->get('id'), 'Invalid key requested: [' . $n . ']');
			Core::SetMessage('Invalid key provided!', 'error');
			\core\redirect('/');
			return;
		}

		if(($str = $u->canResetPassword()) !== true){
			Core::SetMessage($str, 'error');
			SecurityLogModel::Log('/user/forgotpassword/confirm', 'fail', $u->get('id'), $str);
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
				$u->set('password', $_POST['p1']);
				$u->set('last_password', CoreDateTime::Now('U', Time::TIMEZONE_GMT));
				$u->save();
				// NOW I can invalidate that nonce!
				$nonce->markUsed();
				SecurityLogModel::Log('/user/forgotpassword/confirm', 'success', $u->get('id'), 'Reset password successfully!');
				Core::SetMessage('Reset password successfully', 'success');
				if($u->get('active')){
					Session::SetUser($u);
				}
				\core\redirect('/');
			}
			catch(ModelValidationException $e){
				SecurityLogModel::Log('/user/forgotpassword/confirm', 'fail', $u->get('id'), $e->getMessage());
				Core::SetMessage($e->getMessage(), 'error');
				return;
			}
			catch(Exception $e){
				SecurityLogModel::Log('/user/forgotpassword/confirm', 'fail', $u->get('id'), $e->getMessage());
				if(DEVELOPMENT_MODE) Core::SetMessage($e->getMessage(), 'error');
				else Core::SetMessage('An unknown error occured', 'error');

				return;
			}
		}
	}

	/**
	 * This is a helper controller to expose server-side data to javascript.
	 *
	 * It's useful for currently logged in user and what not.
	 * Obviously nothing critical is exposed here, since it'll be sent to the useragent.
	 */
	public function jshelper(){
		$request = $this->getPageRequest();

		// This is a json-only page.
		if($request->ctype != View::CTYPE_JSON){
			\core\redirect('/');
		}

		// The data that will be returned.
		$data = array();

		$cu = Core::User();

		if(!$cu->exists()){
			$data['user'] = array(
				'id' => null,
				'displayname' => ConfigHandler::Get('/user/displayname/anonymous'),
				//'email' => null,
			);
			$data['accessstringtemplate'] = null;
		}
		else{
			$data['user'] = array(
				'id' => $cu->get('id'),
				'displayname' => $cu->getDisplayName(),
				//'email' => $cu->get('email'),
			);

			// Templated version of the access string form system, useful for dynamic permissions on the page.
			$templateel = new FormAccessStringInput(array(
				'title' => '##TITLE##',
				'name' => '##NAME##',
				'description' => '##DESCRIPTION##',
				'class' => '##CLASS##',
                'value' => 'none'
			));
			$data['accessstringtemplate'] = $templateel->render();
		}

		$this->getView()->jsondata = $data;
		$this->getView()->contenttype = View::CTYPE_JSON;
	}


	public static function _HookHandler403(View $view){
		require_once(ROOT_PDIR . 'components/user/helpers/UserFunctions.php');

		if(\Core\user()->exists()){
		//if(Core::User()->exists()){
			// User is already logged in... I can't do anything.
			return true;
		}

		$newcontroller = new self();
		$newcontroller->overwriteView($view);
		//$view->baseurl = '/user/login';
		$view->ssl = true;
		$view->error = View::ERROR_ACCESSDENIED;
		$view->allowerrors = true;
		$view->templatename = 'pages/user/guest403.tpl';


		$loginform = new Form();
		$loginform->set('callsMethod', 'UserHelper::LoginHandler');

		$loginform->addElement('text', array('name' => 'email', 'title' => 'Email', 'required' => true));
		$loginform->addElement('password', array('name' => 'pass', 'title' => 'Password', 'required' => true));
		$loginform->addElement('submit', array('value' => 'Login'));

		if(ConfigHandler::Get('/user/register/allowpublic')){
			$registerform = \User\get_registration_form();
		}
		else{
			$registerform = null;
		}


		$error = false;



		$view->assign('error', $error);
		$view->assign('backends', ConfigHandler::Get('/user/backends'));
		$view->assign('loginform', $loginform);
		$view->assign('registerform', $registerform);
		$view->assign('allowregister', ConfigHandler::Get('/user/register/allowpublic'));


		return $view;
	}

}
