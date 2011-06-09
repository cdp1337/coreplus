<?php
/**
 * Model for PermissionsModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class PermissionsModel extends Model {
	public static $Schema = array(
		'permission' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 32,
			'required' => true,
		),
		'mid' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'description' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('permission'),
		'mid' => array('mid'),
	);

	// @todo Put your code here.

} // END class PermissionsModel extends Model
