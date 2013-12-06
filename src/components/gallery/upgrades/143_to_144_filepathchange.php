<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 1/17/13
 * Time: 10:20 PM
 * This is the upgrade file from 1.4.3 to 1.4.4
 *
 * Required because with the file input change in 2.6.0 returning core resolved paths,
 * the data in the database will contain that path now.
 */

$images = GalleryImageModel::Find();

foreach($images as $i){
	/** @var $i GalleryImageModel */
	// Just in case
	if(strpos($i->get('file'), 'public/') !== 0){
		$i->set('file', 'public/galleryalbum/' . $i->get('file'));
		$i->save();
	}


	// Don't forget to copy over the meta data too!
	// This is because the gallery system will use the new version of metadata.
	$u = UserModel::Construct($i->get('uploaderid'));

	$helper = new \Core\Filestore\FileMetaHelper($i->getOriginalFile());
	$helper->setMeta('title', $i->get('title'));
	$helper->setMeta('keywords', explode(',', $i->get('keywords')));
	$helper->setMeta('description', $i->get('description'));
	$helper->setMeta('authorid', $i->get('uploaderid'));
	if($u && $u instanceof User_Backend){
		$helper->setMeta('author', $u->getDisplayName());
	}
}
