<?php
/**
 * Upgrade file for user data from 2.6.1 to 2.6.2.
 *
 * Namely setting the last login for users that have a password set.
 * It's good indication that if they have a password set, that they've logged in already.
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20131030.2031
 */

$timenow = Time::GetCurrentGMT();

// Find and update all user accounts that have a last login of not recorded, but have a password set (legacy data)
$users = UserModel::Find(['password != ', 'last_login = 0']);
foreach($users as $u){
	/** @var $u UserModel */
	$u->set('last_login', $timenow);
	$u->save();
}

// Find and update all user accounts that have a last login of not recorded, but have a password set (legacy data)
$users = UserModel::Find(['password != ', 'last_password = 0']);
foreach($users as $u){
	/** @var $u UserModel */
	$u->set('last_password', $timenow);
	$u->save();
}
