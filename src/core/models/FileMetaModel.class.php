<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 1/23/13
 * Time: 1:49 PM
 * To change this template use File | Settings | File Templates.
 */
class FileMetaModel extends Model {
	public static $Schema = array(
		'file' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 192,
			'required' => true,
			'null' => false,
			'form' => array('type' => 'system'),
		),
		'meta_key' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 36,
			'required' => true,
			'comment' => 'The key of this meta tag',
		),
		'meta_value' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'required' => true,
			'comment' => 'Machine version of the value of this meta tag',
		),
		'meta_value_title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 256,
			'required' => true,
			'comment' => 'Human readable version of the value of this meta tag',
		),
	);

	public static $Indexes = array(
		'primary' => array('file', 'meta_key', 'meta_value'),
	);
}
