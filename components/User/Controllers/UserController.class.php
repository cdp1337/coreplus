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
class UserController extends Controller{
	public static function Login(View $page){
		
		// Set the access permissions for this page as anonymous-only.
		if(!$page->setAccess('g:anonymous;g:!admin')){
			return;
		}
		
		$form = new Form();
		$form->set('callsMethod', 'UserController::_LoginHandler');
		
		$form->addElement('text', array('name' => 'email', 'title' => 'Email', 'required' => true));
		$form->addElement('password', array('name' => 'pass', 'title' => 'Password', 'required' => true));
		$form->addElement('submit', array('value' => 'Login'));
		
		$error = false;
		
		// @todo Implement a hook handler here for UserPreLoginForm
		
		// Provide some facebook logic if that backend is enabled.
		if(in_array('facebook', ConfigHandler::GetValue('/user/backends'))){
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
					// Hmm, what do I do now?
					Core::Redirect('/');
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
		
		

		
		$page->assign('error', $error);
		$page->assign('facebooklink', $facebooklink);
		$page->assign('backends', ConfigHandler::GetValue('/user/backends'));
		$page->assign('form', $form);
	}
	
	public static function Register(View $page){
		
		// Set the access permissions for this page as anonymous-only.
		if(!$page->setAccess('g:anonymous;g:!admin')){
			return;
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
		
		$page->assign('form', $form);
	}
	
	public static function Logout(View $page){
		// Set the access permissions for this page as authenticated-only.
		if(!$page->setAccess('g:authenticated;g:!admin')){
			return;
		}
		
		Session::Destroy();
		Core::Redirect('/');
	}
	
	
	public static function ForgotPassword(View $view){
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			
			$u = User::Find(array('email' => $_POST['email']), 1);
			if(!$u){
				$view->assign('error', 'Invalid user account requested');
				return;
			}
			
			if(($str = $u->canResetPassword()) !== true){
				$view->assign('error', $str);
				return;
			}
			
			$e = new Email();
			$e->setSubject('Forgot Password Request');
			$e->to($u->get('email'));
			$e->assign('link', Core::ResolveLink('/User/ForgotPassword/Execute'));
			$e->assign('ipaddr', IP_ADDRESS);
			
		}
	}
	
	
	public static function _HookHandler403(View $page){
		if(Core::User()->exists()){
			// User is already logged in... I can't do anything.
			return true;
		}
		
		$p = new PageModel('/User/Login');
		$p->hijackView($page);
		UserController::Login($page);
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
		return '/';
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
		
		// Check the passwords (complexity check).
		if(strlen($p1val) < ConfigHandler::GetValue('/user/password/minlength')){
			$p1->setError('Please ensure that the password is at least ' . ConfigHandler::GetValue('/user/password/minlength') . ' characters long.');
			return false;
		}
		
		// Check the passwords (complexity check).
		if(ConfigHandler::GetValue('/user/password/requiresymbols') > 0){
			preg_match_all('/[^a-zA-Z]/', $p1val, $matches); // Count a number as a symbol.  Close enough :/
			if(sizeof($matches[0]) < ConfigHandler::GetValue('/user/password/requiresymbols')){
				$p1->setError('Please ensure that the password has at least ' . ConfigHandler::GetValue('/user/password/requiresymbols') . ' symbol(s) or number(s).');
				return false;
			}
		}
		
		// Check the passwords (complexity check).
		if(ConfigHandler::GetValue('/user/password/requirecapitals') > 0){
			preg_match_all('/[A-Z]/', $p1val, $matches);
			if(sizeof($matches[0]) < ConfigHandler::GetValue('/user/password/requirecapitals')){
				$p1->setError('Please ensure that the password has at least ' . ConfigHandler::GetValue('/user/password/requirecapitals') . ' capital letter(s).');
				return false;
			}
		}
		
		// Is this a valid email?
		if(!Core::CheckEmailValidity($e->get('value'))){
			$e->setError('Email does not appear to be valid.');
			return false;
		}
		
		// Do some validation on the email address (if it's taken).
		$u = User_datamodel_Backend::Find(array('email' => $e->get('value')));
		if($u->exists()){
			$e->setError('Requested email is already registered.');
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
				
				$attributes[$k] = $v;
			}
		}
		
		$u = User_datamodel_Backend::Register($e->get('value'), $p1val, $attributes);
		
		// "login" this user.
		Session::SetUser($u);
		
		return true;
	}
}

?>
