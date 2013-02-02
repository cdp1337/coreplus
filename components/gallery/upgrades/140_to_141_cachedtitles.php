<?php

// Load the necessary files to do this
require_once(ROOT_PDIR . 'components/gallery/models/GalleryAlbumModel.php');

$albums = GalleryAlbumModel::Find(null, null, null);
foreach($albums as $album){
	/** @var $album GalleryAlbumModel */
	// Set this album's cached title from the page.
	$album->set('title', $album->getLink('Page')->get('title'));
	$album->save();
}