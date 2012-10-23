<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 10/18/12
 * Time: 10:06 PM
 * To change this template use File | Settings | File Templates.
 */
class UserProfileWidget extends Widget_2_1{
	public function badge(){
		$view = $this->getView();
		$request = $this->getRequest();


		$uid = $request->getParameter('user');
		if($uid instanceof User){
			$user = $uid;
			$uid = $user->get('id');
		}
		elseif(is_numeric($uid)){
			$user = User::Find(array('id' => $uid), 1);
		}
		else{
			throw new WidgetException('/userprofile/badge widget requires a user parameter.');
		}

		$direction = $request->getParameter('direction') ? $request->getParameter('direction') : 'horizontal';
		$orientation = $request->getParameter('orientation') ? $request->getParameter('orientation') : 'left';

		$view->assign('link', UserSocialHelper::ResolveProfileLink($user));
		$view->assign('user', $user);
		$view->assign('profiles', json_decode($user->get('json:profiles'), true));
		$view->assign('direction', $direction);
		$view->assign('orientation', $orientation);
		$view->assign('title', $request->getParameter('title'));
	}
}
