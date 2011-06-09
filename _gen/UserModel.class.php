<?php
/**
 * Model for UserModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class UserModel extends Model {
	public static $Schema = array(
		'uid' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
		),
		'username' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 32,
		),
		'password' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 60,
		),
		'apikey' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
		),
		'email' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
		),
		'active' => array(
			'type' => Model::ATT_TYPE_BOOL,
		),
		'admin' => array(
			'type' => Model::ATT_TYPE_BOOL,
		),
		'created' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_INT,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('uid'),
		'unique:email' => array('email'),
	);

	// @todo Put your code here.

} // END class UserModel extends Model
