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
	
	public function index(){
		$view = $this->getView();

		// @todo This should probably be enabled in the future, at least toggleable.
		if(!$view->setAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}
	}

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
		$manager = \Core\user()->checkAccess('p:user_manage'); // Current user an admin?

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

		$user = User::Find(array('id' => $userid));

		if($req->isPost()){
			try{
				$p1val = $_POST['pass'];
				$p2val = $_POST['pass2'];
				// Check the passwords, (that they match).
				if($p1val != $p2val){
					throw new ModelValidationException('Passwords do not match');
				}

				$user->set('password', $p1val);
				$user->save();
				Core::SetMessage('Updated Password Successfully', 'success');
				if($ownpassword){
					Core::Redirect('/user/me');
				}
				else{
					Core::Redirect('/useradmin');
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
		$manager = \Core\user()->checkAccess('p:user_manage'); // Current user an admin?

		if($userid === null) $userid = \Core\user()->get('id'); // Default to current user.

		// Only allow this if the user is either the same user or has the user manage permission.
		if(!($userid == \Core\user()->get('id') || $manager)){
			Core::SetMessage('Insufficient Permissions', 'error');
			Core::Redirect('/');
		}

		$user = User::Find(array('id' => $userid));
		$form = \User\get_edit_form($user);

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

		// Set the access permissions for this page as anonymous-only.
		if(!$this->setAccess('g:anonymous;g:!admin')){
			return View::ERROR_ACCESSDENIED;
		}

		$form = new Form();
		$form->set('callsMethod', 'UserHelper::LoginHandler');

		$form->addElement('text', array('name' => 'email', 'title' => 'Email', 'required' => true));
		$form->addElement('password', array('name' => 'pass', 'title' => 'Password', 'required' => true));
		$form->addElement('submit', array('value' => 'Login'));

		$error = false;

		// @todo Implement a hook handler here for UserPreLoginForm

		// Provide some facebook logic if that backend is enabled.
		if(in_array('facebook', ConfigHandler::Get('/user/backends'))){
			$facebook = new Facebook(array(
				'appId'  => FACEBOOK_APP_ID,
				'secret' => FACEBOOK_APP_SECRET,
			));

			// Did the user submit the facebook login request?
			if(
				$_SERVER['REQUEST_METHOD'] == 'POST' &&
				isset($_POST['login-method']) &&
				$_POST['login-method'] == 'facebook' &&
				$_POST['access-token']
			){
				try{
					$facebook->setAccessToken($_POST['access-token']);
					User_facebook_Backend::Login($facebook);
					// Redirect to the home page or the page originally requested.
					if(REL_REQUEST_PATH == '/user/login') Core::Redirect('/');
					else Core::Reload();
				}
				catch(Exception $e){
					$error = $e->getMessage();
				}
			}

			$user = $facebook->getUser();
			if($user){
				// User was already logged in.
				try{
					$user_profile = $facebook->api('/me');
					$facebooklink = false;
				}
				catch(Exception $c){
					$facebooklink = $facebook->getLoginUrl();
				}

				// $logoutUrl = $facebook->getLogoutUrl();
			}
			else{
				$facebooklink = $facebook->getLoginUrl();
			}
		}
		else{
			$facebooklink = false;
		}




		$view->assign('error', $error);
		$view->assign('facebooklink', $facebooklink);
		$view->assign('backends', ConfigHandler::Get('/user/backends'));
		$view->assign('form', $form);
		$view->assign('allowregister', ConfigHandler::Get('/user/register/allowpublic'));


		return $view;
	}

	public function register(){
		// I could put this in the component xml, but it's not needed everywhere
		require_once(ROOT_PDIR . 'components/user/helpers/UserFunctions.php');

		$view = $this->getView();
		$manager = \Core\user()->checkAccess('p:user_manage'); // Current user an admin?

		// Anonymous users should have access to this if it's allow public.
		if(!\Core\user()->exists() && !ConfigHandler::Get('/user/register/allowpublic')){
			return View::ERROR_BADREQUEST;
		}

		// Authenticated users must check the permission to manage users.
		if(\Core\user()->exists() && !$manager){
			return View::ERROR_ACCESSDENIED;
		}

		$form = \User\get_registration_form();

		$view->assign('form', $form);

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

		Session::Destroy();
		Core::Redirect('/');
	}


	public function forgotPassword(){
		$view = $this->getView();

		// If e and k are set as parameters... it's on step 2.
		if($view->getParameter('e') && $view->getParameter('k')){
			self::_ForgotPassword2($view);
		}
		// Else, just step 1.
		else{
			self::_ForgotPassword1($view);
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
			Core::Redirect('/');
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
		if(\Core\user()->exists()){
		//if(Core::User()->exists()){
			// User is already logged in... I can't do anything.
			return true;
		}

		$newcontroller = new self();
		$newcontroller->overwriteView($view);
		$view->baseurl = '/User/Login';
		$view->error = View::ERROR_ACCESSDENIED;
		$view->allowerrors = true;
		$newcontroller->login();
	}





	private static function _ForgotPassword1($view){
		$view->title = 'Forgot Password';

		// This is step 1
		$view->assign('step', 1);

		// There's really nothing to do here except for check the email and send it.

		if($_SERVER['REQUEST_METHOD'] == 'POST'){

			$u = User::Find(array('email' => $_POST['email']), 1);
			if(!$u){
				Core::SetMessage('Invalid user account requested', 'error');
				return;
			}

			if(($str = $u->canResetPassword()) !== true){
				Core::SetMessage($str, 'error');
				return;
			}

			// Generate the key based on the apikey and the current password.
			$key = md5(substr($u->get('apikey'), 0, 15) . substr($u->get('password'), -10));
			$link = '/User/ForgotPassword?e=' . urlencode(base64_encode($u->get('email'))) . '&k=' . $key;

			$e = new Email();
			$e->setSubject('Forgot Password Request');
			$e->to($u->get('email'));
			$e->assign('link', Core::ResolveLink($link));
			$e->assign('ip', REMOTE_IP);
			$e->templatename = 'emails/user/forgotpassword.tpl';
			try{
				$e->send();
			}
			catch(Exception $e){
				Core::SetMessage('Error sending the email, ' . $e->getMessage(), 'error');
				return;
			}

			// Otherwise, it must have sent, (hopefully)...
			Core::SetMessage('Sent reset instructions via email.', 'success');
			Core::Redirect('/');
		}
	}

	private static function _ForgotPassword2($view){
		$view->title = 'Forgot Password';

		$view->assign('step', 2);

		// Lookup and validate this information first.
		$e = base64_decode($view->getParameter('e'));

		$u = User::Find(array('email' => $e), 1);
		if(!$u){
			Core::SetMessage('Invalid user account requested', 'error');
			return;
		}

		$key = md5(substr($u->get('apikey'), 0, 15) . substr($u->get('password'), -10));
		if($key != $view->getParameter('k')){
			Core::SetMessage('Invalid user account requested', 'error');
			return;
		}

		if(($str = $u->canResetPassword()) !== true){
			Core::SetMessage($str, 'error');
			return;
		}

		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			// Validate the password.
			if($_POST['p1'] != $_POST['p2']){
				Core::SetMessage('Passwords do not match.', 'error');
				return;
			}

			// Else, try to set it... the user model will complain if it's invalid.
			try{
				$u->set('password', $_POST['p1']);
				$u->save();
				Core::SetMessage('Reset password successfully', 'success');
				Session::SetUser($u);
				Core::Redirect('/');
			}
			catch(ModelValidationException $e){
				Core::SetMessage($e->getMessage(), 'error');
				return;
			}
			catch(Exception $e){
				if(DEVELOPMENT_MODE) Core::SetMessage($e->getMessage(), 'error');
				else Core::SetMessage('An unknown error occured', 'error');

				return;
			}
		}
	}
}
