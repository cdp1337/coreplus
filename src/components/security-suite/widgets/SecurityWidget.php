<?php

/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 5/1/16
 * Time: 7:45 PM
 */
class SecurityWidget extends Widget_2_1 {
	
	public function Userlogins(){
		$view = $this->getView();
		
		/** @var UserModel $user */
		$user = $this->getParameter('user');

		// Grab the login attempts for this user
		$logins = SystemLogModel::Find(
			['affected_user_id = ' . $user->get('id'), 'code = /user/login'],
			//['affected_user_id = ' . $user->get('id')],
			20,
			'datetime DESC'
		);
		
		$canViewLogs = \Core\user()->checkAccess('p:/core/systemlog/view');
		
		$view->assign('logins', $logins);
		$view->assign('can_view_logs', $canViewLogs);
		$view->assign('user', $user);
	}
}