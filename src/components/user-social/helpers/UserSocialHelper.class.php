<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 10/18/12
 * Time: 2:01 AM
 * To change this template use File | Settings | File Templates.
 */
class UserSocialHelper {
	/**
	 * Get the resolved profile link for a given user
	 *
	 * @param UserModel $user
	 * @return string
	 */
	public static function ResolveProfileLink(UserModel $user){
		if($user->get('username')){
			return \Core\resolve_link('/userprofile/view/' . $user->get('username'));
		}
		else{
			return \Core\resolve_link('/userprofile/view/' . $user->get('id'));
		}
	}

	public static function ResolveUsernameById($userid){
		$user = UserModel::Construct($userid);
		return $user->get('username') ? $user->get('username') : $user->get('id');
	}

	public static function ResolveProfileLinkById($userid){
		$user = UserModel::Construct($userid);
		if(!$user) return false;

		return self::ResolveProfileLink($user);
	}
}
