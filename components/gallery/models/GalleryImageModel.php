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
		'rotation' => array(
			'type' => Model::ATT_TYPE_INT,
			'formtype' => 'hidden',
			'default' => 0
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
	 * This has an interesting function; it will return the original filename or the rotated version.
	 * This rotated version is stored in a different directory to prevent name conflicts.
	 *
	 * @return File_Backend
	 */
	public function getFile(){
		if($this->get('rotation') == 0){
			// Simple enough :p
			return $this->getOriginalFile();
		}
		else{
			$filename = $this->get('file');
			$ext = substr($filename, strrpos($filename, '.'));
			$base = substr($filename, 0, 0 -strlen($ext));
			$rotatedfilename = $base . '-deg' . $this->get('rotation') . $ext;

			// Since rotated files are all temporary...
			$tmpfile = Core::File('tmp/galleryalbum/' . $rotatedfilename);

			if(!$tmpfile->exists()){
				$tmpfile->putContents('');
				// Rotate it!
				$originallocal = $this->getOriginalFile()->getLocalFilename();

				switch(strtolower($ext)){
					case '.jpg':
					case '.jpeg':
						$imagedat   = imagecreatefromjpeg($originallocal);
						$rotateddat = imagerotate($imagedat, $this->get('rotation'), 0);
						imagejpeg($rotateddat, $tmpfile->getFilename());
						break;
				}
			}

			return $tmpfile;
			var_dump($tmpfile, $tmpfile->exists());

			var_dump($ext, $base); die();
		}
		//$dir = ( ? 'galleryalbum'
		return Core::File('public/galleryalbum/' . $this->get('file'));
	}

	/**
	 * Get the original file associated to this image.
	 *
	 * This is critical because the getFile may return the *rotated* image!
	 *
	 * @return File_Backend
	 */
	public function getOriginalFile(){
		return Core::File('public/galleryalbum/' . $this->get('file'));
	}

	public function rotate($degrees){

	}
}
