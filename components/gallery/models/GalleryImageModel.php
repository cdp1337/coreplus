<?php
/**
 * Model for the gallery images themselves.
 *
 * @package Gallery
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2012  Charlie Powell
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
class GalleryImageModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'albumid' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
			'null' => false,
			'formtype' => 'system'
		),
		'weight' => array(
			'type' => Model::ATT_TYPE_INT,
			'formtype' => 'hidden',
		),
		'file' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'required' => true,
			'form' => array(
				'type' => 'file',
				'basedir' => 'public/galleryalbum',
				'accept' => 'image/*',
			),
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128
		),
		'keywords' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
		'description' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
		'created' => array(
			'type' => Model::ATT_TYPE_CREATED,
			'null' => false,
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_UPDATED,
			'null' => false,
		),
	);

	public static $Indexes = array(
		'primary' => array('id'),
		'unique:albumid_file' => array('albumid', 'file'), // Each image should be in each album at most once.
	);

	public function __construct($key = null) {
		$this->_linked = array(
			'Page' => array(
				'link' => Model::LINK_HASONE,
				'on' => 'baseurl',
			),
		);

		parent::__construct($key);
	}

	/**
	 * Get the file associated to this image.
	 *
	 * @return File_Backend
	 */
	public function getFile(){
		return Core::File('public/galleryalbum/' . $this->get('file'));
	}
}
