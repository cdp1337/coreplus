<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * This is the upgrade file from 2.7.0 (technically 2.6.0, but...), to 2.8.0.
 *
 * Required because with the file input change in 2.6.0 returning core resolved paths,
 * the data in the database will contain that path now.
 */

$images = UserModel::Find();

foreach($images as $i){
	/** @var $i UserModel */
	// Just in case
	if(strpos($i->get('avatar'), 'public/') !== 0){
	    // User avatars prior to 2.8.0 were saved in public/user.
	    // After they are relocated to public/user/avatar, but with the Core relative path saved in the database, it'll be fine.
	    // Saves me from having to copy all the files over to public/user/avatar!
		$i->set('avatar', 'public/user/' . $i->get('file'));
		$i->save();
	}
}
