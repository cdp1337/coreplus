<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 10/18/12
 * Time: 2:01 AM
 * To change this template use File | Settings | File Templates.
 */
class UserSocialHelper {
	public static function ValidateUsername($username, UserUserConfigModel $userconfig){
		// Usernames need to be unique across the system, since they will be used as URL identifiers.

		// However, blank usernames are acceptable.
		if($username == '') return true;

		// Usernames should start with a number at least.
		if(!preg_match('/^[a-zA-Z].*/', $username)) return 'Please start nicknames with a letter.';

		// and should not contain spaces
		if(strpos($username, ' ') !== false) return 'Please do not include spaces in your nickname.';

		// Usernames should be only a-z, 0-9, and a few standard characters.
		if(!preg_match('/^[a-zA-Z0-9\-\.@_+]*$/', $username)){
			return 'Please ensure that your nickname only contains letters, numbers, and dashes.';
		}

		// Search the database for the same username.  Remember, THERE CAN ONLY BE ONE!
		$match = UserUserConfigModel::Find(array('key' => 'username', 'value' => $username), 1);
		if($match && $match->get('user_id') != $userconfig->get('user_id')){
			return 'Requested username is already taken!';
		}

		// yay!
		return true;
	}

	/**
	 * Get the resolved profile link for a given user
	 *
	 * @param User $user
	 * @return string
	 */
	public static function ResolveProfileLink(User $user){
		if($user->get('username')){
			return Core::ResolveLink('/userprofile/view/' . $user->get('username'));
		}
		else{
			return Core::ResolveLink('/userprofile/view/' . $user->get('id'));
		}
	}

	public static function ResolveUsernameById($userid){
		$user = User::Construct($userid);
		return $user->get('username') ? $user->get('username') : $user->get('id');
	}

	public static function ResolveProfileLinkById($userid){
		$user = User::Find(array('id' => $userid), 1);
		if(!$user) return false;

		return self::ResolveProfileLink($user);
	}

	public static function GetUserLinks($user){
		$a = array();

		if(is_numeric($user)){
			// Transpose the ID to a user backend object.
			$user = User::Construct($user);
		}
		elseif($user instanceof UserModel){
			// Transpose the model to a user backend object.
			$user = User::Construct($user->get('id'));
		}
		elseif(is_subclass_of($user, 'UserBackend')){
			// NO change needed :)
		}
		else{
			// Umm, wtf was it?
			return array();
		}

		// still nothing?
		if(!$user) return array();

		$usermanager = \Core\user()->checkAccess('p:/user/users/manage');
		$selfaccount = \Core\user()->get('id') == $user->get('id');

		$a[] = array(
			'title' => 'View Profile',
			'icon' => 'user',
			'link' => self::ResolveProfileLink($user),
		);

		if($usermanager || $selfaccount){
			$a[] = array(
				'title' => 'Public Profiles',
				'icon' => 'link',
				'link' => '/userprofile/connectedprofiles/' . $user->get('id'),
			);
		}

		return $a;
	}
}
