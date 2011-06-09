<?php

class ConfigModel extends Model {
	// key 	type 	default_value 	value 	options 	description 	mapto
	public static $Schema = array(
		'key' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 256,
			'required' => true,
		),
		'type' => array(
			'type' => Model::ATT_TYPE_ENUM,
			'options' => array('string','int','boolean','enum'),
			'required' => true,
		),
		'default_value' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
		'value' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
		'options' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
		'description' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
		'mapto' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 32,
			'comment' => 'The define constant to map the value to on system load.'
		),
	);
	
	public static $Indexes = array(
		'primary' => 'key'
	);
}
