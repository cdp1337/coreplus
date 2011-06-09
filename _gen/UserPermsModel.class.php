<?php
/**
 * Model for UserPermsModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class UserPermsModel extends Model {
	public static $Schema = array(
		'uid' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'permission' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 32,
			'required' => true,
			'null' => false,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('uid', 'permission'),
		'pid' => array('permission'),
	);

	// @todo Put your code here.

} // END class UserPermsModel extends Model
