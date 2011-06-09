<?php
/**
 * Model for UserAttributeModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class UserAttributeModel extends Model {
	public static $Schema = array(
		'attid' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'key' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'null' => false,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'null' => false,
		),
		'group' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'null' => false,
		),
		'type' => array(
			'type' => Model::ATT_TYPE_ENUM,
			'options' => array('string','int','boolean','float','enum','text'),
			'default' => 'string',
			'null' => false,
		),
		'options' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'default' => null,
			'null' => true,
		),
		'default' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'null' => false,
		),
		'description' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'null' => false,
		),
		'public' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => '1',
			'null' => false,
		),
		'required' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'null' => false,
		),
		'style' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 511,
			'default' => null,
			'null' => true,
		),
		'weight' => array(
			'type' => Model::ATT_TYPE_INT,
			'default' => '10',
			'null' => false,
		),
		'display_on_registration' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => '1',
			'null' => false,
		),
		'display_on_edit' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => '1',
			'null' => true,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('attid'),
		'unique:key' => array('key'),
	);

	// @todo Put your code here.

} // END class UserAttributeModel extends Model
