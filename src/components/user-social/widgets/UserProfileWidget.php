<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 10/18/12
 * Time: 10:06 PM
 * To change this template use File | Settings | File Templates.
 */
class UserProfileWidget extends \Core\Widget{
	public function badge(){
		$view = $this->getView();
		$request = $this->getRequest();


		$uid = $request->getParameter('user');
		if($uid instanceof UserModel){
			$user = $uid;
			$uid = $user->get('id');
		}
		else{
			$user = UserModel::Construct($uid);
		}

		$direction = $request->getParameter('direction') ? $request->getParameter('direction') : 'horizontal';
		$orientation = $request->getParameter('orientation') ? $request->getParameter('orientation') : 'left';

		$view->assign('enableavatar', (\ConfigHandler::Get('/user/enableavatar')));
		$view->assign('link', UserSocialHelper::ResolveProfileLink($user));
		$view->assign('user', $user);
		$view->assign('profiles', $user->get('external_profiles'));
		$view->assign('direction', $direction);
		$view->assign('orientation', $orientation);
		$view->assign('title', $request->getParameter('title'));
	}
}
