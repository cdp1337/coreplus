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
		$form = new Form();
		$form->set('callsMethod', 'UserController::_LoginHandler');
		
		$form->addElement('text', array('name' => 'email', 'title' => 'Email', 'required' => true));
		$form->addElement('password', array('name' => 'pass', 'title' => 'Password', 'required' => true));
		$form->addElement('submit', array('value' => 'Login'));
		
		// @todo Implement a hook handler here for UserPreLoginForm
		
		$page->assign('form', $form);
	}
	
	public static function _LoginHandler(Form $form){
		$e = $form->getElement('email');
		$p = $form->getElement('pass');
		
		$res = User::GetSystemBackend()->Login($e->get('value'), $p->get('value'));
		die();
	}
	
	public static function Register(View $page){
		
		$form = new Form();
		$form->set('callsMethod', 'UserController::_RegisterHandler');
		// Because the user system may not use a traditional Model for the backend, (think LDAP),
		// I cannot simply do a setModel() call here.
		$form->addElement('text', array('name' => 'email', 'title' => 'Email', 'required' => true));
		$form->addElement('password', array('name' => 'pass', 'title' => 'Password', 'required' => true));
		$form->addElement('password', array('name' => 'pass2', 'title' => 'Confirm', 'required' => true));
		$form->addElement('submit', array('value' => 'Register'));
		
		// Do something with /user/register/requirecaptcha
		
		// @todo Implement a hook handler here for UserPreRegisterForm
		
		$page->assign('form', $form);
	}
	
	public static function _RegisterHandler(Form $form){
		$e = $form->getElement('email');
		$p1 = $form->getElement('pass');
		$p2 = $form->getElement('pass2');
		
		//if($p1->get('value') != $p2->get('value'))
		
		// @todo Check email
		
		$u = User::GetSystemBackend()->Register($e->get('value'), $p1->get('value'));
		var_dump($u);
	}
}

?>
