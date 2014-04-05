<?php
/**
 * This is the upgrade file from 2.7.0 (technically 2.6.0, but...), to 2.8.0.
 *
 * Required because with the file input change in 2.6.0 returning core resolved paths,
 * the data in the database will contain that path now.
 *
 * @package Core
 */

// I'm using raw objects here because if there are a lot of user accounts in the system,
// creating a giant array of them all may take quite a bit of memory.
// FindRaw returns simply arrays, so less memory is required.
$userdat = UserModel::FindRaw();

foreach($userdat as $dat){
	/** @var array $dat */

	if($dat['avatar'] == ''){
		// Skip empty avatars, they don't get updated.
		continue;
	}

	if(strpos($dat['avatar'], 'public/') === 0){
		// Skip avatars that are already resolved.  They don't need to be updated.
		continue;
	}

	/** @var $u UserModel */
	$u = UserModel::Construct($dat['id']);
	// User avatars prior to 2.8.0 were saved in public/user.
	// After they are relocated to public/user/avatar, but with the Core relative path saved in the database, it'll be fine.
	// Saves me from having to copy all the files over to public/user/avatar!
	$u->set('avatar', 'public/user/' . $u->get('avatar'));
	$u->save();
}
