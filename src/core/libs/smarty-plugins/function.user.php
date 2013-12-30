<?php
/**
 * Convert a user id to a username and display that to the template.
 *
 * @package Core\User
 *
 * @param $params
 * @param $template
 *
 * @return string
 * @throws SmartyException
 *
 * <h3>Usage Example</h3>
 *
 * <p>Typical usage for the Smarty user function</p>
 *
 * <code lang="text/html">
 * User Name: {user $userid}&lt;br/&gt;
 * </code>
 */
function smarty_function_user($params, $template){
	if(isset($params['user'])){
		$userid = $params['user'];
	}
	elseif(isset($params[0])){
		$userid = $params[0];
	}
	else{
		throw new SmartyException('Required parameter [user] not provided for user!');
	}

	/** @var UserModel $user */
	$user = UserModel::Construct($userid);

	if(!$user) return '';

	$username = $user->getDisplayName();
	return $username;
}