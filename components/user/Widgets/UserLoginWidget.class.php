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
class UserLoginWidget extends Widget_2_1{
	
	public function execute() {
		$v = $this->getView();
		
		$u = Core::User();
		
		$v->assign('user', $u);
		$v->assign('loggedin', $u->exists());
		$v->assign('allowregister', ConfigHandler::Get('/user/register/allowpublic'));
	}
	
}
