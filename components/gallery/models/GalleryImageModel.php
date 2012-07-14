<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 7/9/12
 * Time: 4:51 PM
 * To change this template use File | Settings | File Templates.
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
