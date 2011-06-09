<?php
/**
 * Model for ConfigModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class ConfigModel extends Model {
	public static $Schema = array(
		'key' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 256,
			'required' => true,
		),
		'type' => array(
			'type' => Model::ATT_TYPE_ENUM,
			'options' => array('string','int','boolean','enum'),
		),
		'default_value' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
		'value' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
		'options' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 511,
		),
		'description' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
		'mapto' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 32,
			'comment' => '
					The define constant to map the value to on system load.
				',
		),
	);
	
	public static $Indexes = array(
		'primary' => array('key'),
	);

	// @todo Put your code here.

} // END class ConfigModel extends Model
