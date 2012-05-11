<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of UserController
 *
 * @author powellc
 */
class UserController extends Controller_2_1{
	
	public function index(){
		$view = $this->getView();
		
		if(!$view->setAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}
		
		$this->setTemplate('/pages/user/index.tpl');
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
					if(REL_REQUEST_PATH == '/User/Login') Core::Redirect('/');
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
		$view = $this->getView();
		
		// Set the access permissions for this page as anonymous-only.
		if(!$view->setAccess('g:anonymous;g:!admin')){
			return View::ERROR_ACCESSDENIED;
		}
		
		// Also disallow access to this page if the configuration option is disabled.
		if(!ConfigHandler::Get('/user/register/allowpublic')){
			return View::ERROR_BADREQUEST;
		}
		
		$form = new Form();
		$form->set('callsMethod', 'UserController::_RegisterHandler');
		// Because the user system may not use a traditional Model for the backend, (think LDAP),
		// I cannot simply do a setModel() call here.
		$form->addElement('text', array('name' => 'email', 'title' => 'Email', 'required' => true));
		$form->addElement('password', array('name' => 'pass', 'title' => 'Password', 'required' => true));
		$form->addElement('password', array('name' => 'pass2', 'title' => 'Confirm', 'required' => true));
		
		$fac = UserConfigModel::Find(array('onregistration' => 1));
		foreach($fac as $f){
			$el = FormElement::Factory($f->get('formtype'));
			$el->set('name', 'option[' . $f->get('key') . ']');
			$el->set('title', $f->get('name'));
			$el->set('value', $f->get('default_value'));
			
			switch($f->get('formtype')){
				case 'file':
					$el->set('basedir', 'public/user/');
					break;
				case 'select':
					$opts = array_map('trim', explode('|', $f->get('options')));
					$el->set('options', $opts);
					break;
			}
			
			$form->addElement($el);
			//var_dump($f);
		}
		
		$form->addElement('submit', array('value' => 'Register'));
		
		
		
		// Do something with /user/register/requirecaptcha
		
		// @todo Implement a hook handler here for UserPreRegisterForm
		
		$view->assign('form', $form);
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
		if(REL_REQUEST_PATH == '/User/Login') return '/';
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
		
		$u = User_datamodel_Backend::Find(array('email' => $e->get('value')));
		
		// All other validation can be done from the model.
		// All set calls will throw a ModelValidationException if the validation fails.
		try{
			$lastel = $e;
			$u->set('email', $e->get('value'));
			
			$lastel = $p1;
			$u->set('password', $p1->get('value'));
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
			// Is this element a config option?
			if(strpos($el->get('name'), 'option[') === 0){
				$k = substr($el->get('name'), 7, -1);
				$v = $el->get('value');
				
				// Some attributes require some modifications.
				if($el instanceof FormFileInput){
					$v = 'public/user/' . $v;
				}
				
				$u->set($k, $v);
			}
		}
		
		// Check if there are no users already registered on the system.  If 
		// none, register this user as an admin automatically.
		if(UserModel::Count() == 0){
			$u->set('admin', true);
		}
		
		$u->save();
		
		// "login" this user.
		Session::SetUser($u);
		
		return '/';
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
