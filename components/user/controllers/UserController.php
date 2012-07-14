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
		$form->set('callsMethod', 'UserController::_LoginHandler');

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
		$newcontroller->login();
	}



	public static function _LoginHandler(Form $form){
		$e = $form->getElement('email');
		$p = $form->getElement('pass');


		$u = User::Find(array('email' => $e->get('value')));
		if(!$u){
			$e->setError('Requested email is not registered.');
			return false;
		}

		// A few exceptions for backends.
		if($u instanceof User_facebook_Backend){
			$e->setError('That is a Facebook account, please use the Facebook connect button to login.');
			return false;
		}

		if(!$u->checkPassword($p->get('value'))){

			if(!isset($_SESSION['invalidpasswordattempts'])) $_SESSION['invalidpasswordattempts'] = 1;
			else $_SESSION['invalidpasswordattempts']++;

			if($_SESSION['invalidpasswordattempts'] > 4){
				// Start slowing down the response.  This should help deter brute force attempts.
				sleep( ($_SESSION['invalidpasswordattempts'] - 4) ^ 1.5 );
			}

			$p->setError('Invalid password');
			$p->set('value', '');
			return false;
		}

		// yay...
		Session::SetUser($u);

		// Where shall I return to?
		if(REL_REQUEST_PATH == '/user/login') return '/';
		else return REL_REQUEST_PATH;
	}

	public static function _RegisterHandler(Form $form){
		$e = $form->getElement('email');
		$p1 = $form->getElement('pass');
		$p1val = $p1->get('value');
		$p2 = $form->getElement('pass2');
		$p2val = $p2->get('value');

		///////       VALIDATION     \\\\\\\\

		// Check the passwords, (that they match).
		if($p1val != $p2val){
			$p1->setError('Passwords do not match.');
			return false;
		}

		// Try to retrieve the user data from the database based on the email.
		// Email is a unique key, so there can only be 1 in the system.
		if(UserModel::Find(array('email' => $e->get('value')), 1)){
			$e->setError('Requested email is already registered.');
			return false;
		}

		$user = User_datamodel_Backend::Find(array('email' => $e->get('value')));

		// All other validation can be done from the model.
		// All set calls will throw a ModelValidationException if the validation fails.
		try{
			$lastel = $e;
			$user->set('email', $e->get('value'));

			$lastel = $p1;
			$user->set('password', $p1->get('value'));
		}
		catch(ModelValidationException $e){
			$lastel->setError($e->getMessage());
			return false;
		}
		catch(Exception $e){
			if(DEVELOPMENT_MODE) Core::SetMessage($e->getMessage(), 'error');
			else Core::SetMessage('An unknown error occured', 'error');

			return false;
		}


		///////   USER CREATION   \\\\\\\\

		// Sanity checks and validation passed, (right?...), now create the actual account.
		// For that, I need to assemble clean data to send to the appropriate backend, (in this case datamodel).
		$attributes = array();
		foreach($form->getElements() as $el){
			$name = $el->get('name');
			// Is this element a config option?
			if(strpos($name, 'option[') === 0){
				$k = substr($el->get('name'), 7, -1);
				$v = $el->get('value');

				// Some attributes require some modifications.
				if($el instanceof FormFileInput){
					$v = 'public/user/' . $v;
				}

				$user->set($k, $v);
			}

			elseif($name == 'active'){
				$user->set('active', $el->get('value'));
			}

			elseif($name == 'admin'){
				$user->set('admin', $el->get('value'));
			}
		}

		// Check if there are no users already registered on the system.  If
		// none, register this user as an admin automatically.
		if(UserModel::Count() == 0){
			$user->set('admin', true);
		}
		else{
			if(\ConfigHandler::Get('/user/register/requireapproval')){
				$user->set('active', false);
			}
		}

		$user->save();

		// "login" this user if not already logged in.
		if(!\Core\user()->exists()){
			Session::SetUser($user);
			return '/';
		}
		// It was created administratively; redirect there instead.
		else{
			Core::SetMessage('Created user successfully', 'success');
			return '/useradmin';
		}

	}

	public static function _UpdateHandler(Form $form){

		$userid = $form->getElement('id')->get('value');

		// Only allow this if the user is either the same user or has the user manage permission.
		if(!($userid == \Core\user()->get('id') || \Core\user()->checkAccess('p:user_manage'))){
			Core::SetMessage('Insufficient Permissions', 'error');
			return false;
		}

		$user = User::Find(array('id' => $userid));

		if(!$user->exists()){
			Core::SetMessage('User not found', 'error');
			return false;
		}


		try{
			foreach($form->getElements() as $el){
				$name = $el->get('name');

				// Email?
				if($name == 'email'){
					$v = $el->get('value');

					if($v != $user->get('email')){
						// Try to retrieve the user data from the database based on the email.
						// Email is a unique key, so there can only be 1 in the system.
						if(UserModel::Find(array('email' => $v), 1)){
							$el->setError('Requested email is already registered.');
							return false;
						}

						$user->set('email', $v);
					}
				}

				// Is this element a config option?
				elseif(strpos($name, 'option[') === 0){
					$k = substr($el->get('name'), 7, -1);
					$v = $el->get('value');

					// Some attributes require some modifications.
					if($el instanceof FormFileInput){
						$v = 'public/user/' . $v;
					}

					$user->set($k, $v);
				}

				// Is this element the group definition?
				elseif($name == 'groups[]'){
					$v = $el->get('value');

					$user->setGroups($v);
				}

				elseif($name == 'active'){
					$user->set('active', $el->get('value'));
				}

				elseif($name == 'admin'){
					$user->set('admin', $el->get('value'));
				}

				else{
					// I don't care.
				}
			}
		}
		catch(ModelValidationException $e){
			$el->setError($e->getMessage());
			return false;
		}
		catch(Exception $e){
			if(DEVELOPMENT_MODE) Core::SetMessage($e->getMessage(), 'error');
			else Core::SetMessage('An unknown error occured', 'error');

			return false;
		}

		$user->save();

		Core::SetMessage('Updated user successfully', 'success');
		return '/useradmin';
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
