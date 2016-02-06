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
 * @todo Finish documentation of smarty_function_user
 *
 * @param array  $params  Associative (and/or indexed) array of smarty parameters passed in from the template
 * @param Smarty $smarty  Parent Smarty template object
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

	/** @var UserModel $user */
	$user = UserModel::Construct($userid);

	if(!$user) return '';

	$username = $user->getDisplayName();
	return $username;
}