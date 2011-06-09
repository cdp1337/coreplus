<?php
/**
 * Model for UserAttributeModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class UserAttributeModel extends Model {
	public static $Schema = array(
		'attid' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
		),
		'key' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
		),
		'group' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
		),
		'type' => array(
			'type' => Model::ATT_TYPE_ENUM,
			'options' => array('string','int','boolean','float','enum','text'),
		),
		'options' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
		'default' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
		'description' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
		'public' => array(
			'type' => Model::ATT_TYPE_BOOL,
		),
		'required' => array(
			'type' => Model::ATT_TYPE_BOOL,
		),
		'style' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 511,
		),
		'weight' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'display_on_registration' => array(
			'type' => Model::ATT_TYPE_BOOL,
		),
		'display_on_edit' => array(
			'type' => Model::ATT_TYPE_BOOL,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('attid'),
		'unique:key' => array('key'),
	);

	// @todo Put your code here.

} // END class UserAttributeModel extends Model
