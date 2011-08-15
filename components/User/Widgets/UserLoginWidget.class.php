<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of UserLoginWidget
 *
 * @author powellc
 */
class UserLoginWidget extends Widget{
	
	public function __construct() {
		//$this->_getView();
		
	}
	
	public function execute() {
		$v = $this->_getView();
		
		$u = Core::User();
		
		$v->assign('user', $u);
		$v->assign('loggedin', $u->exists());
		
		return $v;
	}
	
}
