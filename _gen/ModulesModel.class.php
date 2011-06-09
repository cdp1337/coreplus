<?php
/**
 * Model for ModulesModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class ModulesModel extends Model {
	public static $Schema = array(
		'mid' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'mname' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'null' => false,
		),
		'version' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'null' => false,
		),
		'directory' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'null' => false,
		),
		'enabled' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'null' => false,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('mid'),
		'unique:mname' => array('mname', 'version'),
		'unique:directory' => array('directory'),
	);

	// @todo Put your code here.

} // END class ModulesModel extends Model
