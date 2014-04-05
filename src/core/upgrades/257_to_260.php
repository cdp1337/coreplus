<?php
/**
 * Upgrade file for 2.5.7 to 2.6.0.
 *
 * This is meant to run through every registered user group on the site and
 * migrate their permissions to the new expanded version.
 *
 * @package Core
 */


$groups = UserGroupModel::Find();
foreach($groups as $g){
	/** @var $g UserGroupModel */
	$perms = $g->getPermissions();

	$key = array_search('user_manage', $perms);

	if($key !== false){
		// It has the legacy key, update it!
		unset($perms[$key]);
		$perms = array_merge($perms, ['/user/users/manage', '/user/groups/manage', '/user/permissions/manage']);
		$g->setPermissions($perms);
		$g->save();
	}

	$key = array_search('user_activity_list', $perms);

	if($key !== false){
		// It has the legacy key, update it!
		unset($perms[$key]);
		$perms = array_merge($perms, ['/user/activity/view']);
		$g->setPermissions($perms);
		$g->save();
	}
}
