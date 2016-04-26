<?php

/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 4/25/16
 * Time: 8:56 AM
 */
class UserSocial_UserModelSupplemental implements ModelSupplemental {
	
	public static $Schema = [
		'username' => [
			'type' => Model::ATT_TYPE_STRING,
			'validation' => 'UserSocial_UserModelSupplemental::ValidateUsername',
		],
		'first_name' => [
			'type' => Model::ATT_TYPE_STRING,
		],
		'last_name' => [
			'type' => Model::ATT_TYPE_STRING,
		],
		'phone' => [
			'type' => Model::ATT_TYPE_STRING,
		],
		'bio' => [
			'type' => Model::ATT_TYPE_TEXT,
			'form' => [
				'type' => 'markdown',
			],
		],
		/*
		<userconfig weight="2" key="first_name" name="First Name" onedit="1" onregistration="1" searchable="1"/>
		<userconfig weight="3" key="last_name" name="Last Name" onedit="1" onregistration="1" searchable="1"/>
		<userconfig weight="4" key="phone" name="Phone Number" onedit="1" onregistration="0"/>
		<userconfig weight="50" key="bio" name="Biography" formtype="markdown" onedit="1" onregistration="0"/>
		<userconfig weight="1" key="username" name="Username" required="1" onedit="1" onregistration="1" validation="UserSocialHelper::ValidateUsername" searchable="1"/>
		*/
	];

	/**
	 * Called prior to save completion.
	 *
	 * @param Model $model The base model that is being saved
	 *
	 * @return void
	 */
	public static function PreSaveHook($model) {
		// TODO: Implement PreSaveHook() method.
	}

	/**
	 * Called immediately after the model has been saved to the database.
	 *
	 * @param Model $model
	 *
	 * @return void
	 */
	public static function PostSaveHook($model) {
		// TODO: Implement PostSaveHook() method.
	}

	/**
	 * Called before the model is deleted from the database.
	 *
	 * @param Model $model
	 *
	 * @return void
	 */
	public static function PreDeleteHook($model) {
		// TODO: Implement PreDeleteHook() method.
	}

	/**
	 * Validate a username
	 * 
	 * @param                     $username
	 * @param UserUserConfigModel $userconfig
	 *
	 * @return bool|string
	 */
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
	 * Called during getControlLinks to return additional links in the controls.
	 *
	 * @param Model $model
	 *
	 * @return array
	 */
	public static function GetControlLinks($model) {
		$a = array();

		//$usermanager = \Core\user()->checkAccess('p:/user/users/manage');
		//$selfaccount = \Core\user()->get('id') == $model->get('id');
		
		if($model->get('username')){
			$a[] = [
				'title' => 'View Profile',
				'icon'  => 'user',
				'link'  => '/userprofile/view/' . $model->get('username'),
			];	
		}
		else{
			$a[] = [
				'title' => 'View Profile',
				'icon'  => 'user',
				'link'  => '/userprofile/view/' . $model->get('id'),
			];
		}

		return $a;
	}
}