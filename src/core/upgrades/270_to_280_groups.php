<?php
/**
 * Upgrade script to convert all the JSON-encoded user groups and transpose them to the new UserUserGroup object.
 *
 * @package Core
 */

$fac = new ModelFactory('UserModel');
$fac->where('groups != ');

while(($user = $fac->getNext())){
	/** @var UserModel $user */
	// datamodel backed users have the groups listed in their column "groups".
	$g = json_decode($user->get('groups'), true);
	if(!$g) $g = array();

	$gids = [];

	foreach($g as $gid){
		if(is_array($gid)){
			$gids = array_merge($gids, $gid);
		}
		else{
			$gids[] = $gid;
		}
	}
	$gids = array_unique($gids);

	$user->setGroups($gids);
	$user->save();
}

// Now wipe out this temporary column data.
\Core\Datamodel\Dataset::Init()
	->update(['groups' => ''])
	->table('user')
	->where('groups != ')
	->execute();
