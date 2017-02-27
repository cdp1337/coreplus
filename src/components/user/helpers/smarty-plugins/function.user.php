<?php
/**
 * Convert a user id to a username and display that to the template.
 *
 * @param $params
 * @param $template
 *
 * @return string
 * @throws SmartyException
 */
function smarty_function_user($params, $template){
	if(!isset($params['user'])){
		throw new SmartyException('Required parameter [user] not provided for user!');
	}

	// Defaults
	$userid = $params['user'];

	$user = User::Construct($userid);

	if(!$user) return '[Non-existent User]';

	$username = $user->getDisplayName();
	return $username;
}