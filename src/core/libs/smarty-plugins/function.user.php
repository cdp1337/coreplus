<?php
/**
 * @package Core\Templates\Smarty
 * @since 2.6.0
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2016  Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/agpl-3.0.txt.
 */

/**
 * Convert a user id to a username and display that to the template.
 *
 * @param array  $params  Associative (and/or indexed) array of smarty parameters passed in from the template
 * @param Smarty $smarty  Parent Smarty template object
 *
 * @return string
 * @throws SmartyException
 * 
 * ### Attributes
 * 
 * #### Indexed Attribute 0 / "user"
 * 
 * The first parameter, or the user="" parameter should be the user ID
 * or the user object of the user to render.  This is a required field.
 * 
 * #### link
 * 
 * Optionally set link=1 to display a link to the user profile.
 * 
 * ### Examples
 * 
 * ``` {.syntax-html}
 * 
 * <!-- Display User Name: foobar -->
 * User Name: {user $userid}
 * 
 * <!-- Display a link to it along with the output. -->
 * User: {user $userid link=1}
 * 
 * ```
 */
function smarty_function_user($params, $smarty){
	if(isset($params['user'])){
		$userid = $params['user'];
	}
	elseif(isset($params[0])){
		$userid = $params[0];
	}
	else{
		throw new SmartyException('Required parameter [user] not provided for user!');
	}
	
	$link = isset($params['link']) ? $params['link'] : false;
	
	if($userid instanceof \UserModel){
		$user = $userid;
	}
	else{
		/** @var UserModel $user */
		$user = UserModel::Construct($userid);
	}

	if(!$user) return '';

	$username = $user->getDisplayName();
	
	if($link){
		// Wrap the username with an A to the user page.
		$username = '<a href="' . \Core\resolve_link('/user/view/' . $user->get('id')) . '">' . $username . '</a>';
	}
	return $username;
}