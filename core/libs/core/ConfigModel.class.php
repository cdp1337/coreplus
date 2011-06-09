<?php
/**
 * Model for ConfigModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class ConfigModel extends Model {
	public static $Schema = array(
		'key' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 256,
			'required' => true,
			'null' => false,
		),
		'type' => array(
			'type' => Model::ATT_TYPE_ENUM,
			'options' => array('string','int','boolean','enum'),
			'default' => 'string',
			'null' => false,
		),
		'default_value' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'default' => null,
			'null' => true,
		),
		'value' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'default' => null,
			'null' => true,
		),
		'options' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 511,
			'default' => null,
			'null' => true,
		),
		'description' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'default' => null,
			'null' => true,
		),
		'mapto' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 32,
			'default' => null,
			'comment' => 'The define constant to map the value to on system load.',
			'null' => true,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('key'),
	);

	// @todo Put your code here.

} // END class ConfigModel extends Model
