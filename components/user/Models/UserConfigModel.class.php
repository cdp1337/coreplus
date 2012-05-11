<?php

/**
 * Description of UserConfigModel
 *
 * @author powellc
 */
class UserConfigModel extends Model{
	public static $Schema = array(
		'key' => array(
			'type' => Model::ATT_TYPE_STRING,
			'required' => true,
			'null' => false,
		),
		'name' => array(
			'type' => Model::ATT_TYPE_STRING,
			'required' => false
		),
		'formtype' => array(
			'type' => Model::ATT_TYPE_STRING,
			'required' => false,
			'default' => 'text'
		),
		'default_value' => array(
			'type' => Model::ATT_TYPE_TEXT
		),
		'options' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'required' => false,
			'null' => true
		),
		'onregistration' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => true
		),
		/*'system' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => false
		),*/
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
		'primary' => array('key'),
	);
}
