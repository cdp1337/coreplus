<?php
/**
 * Model for UserModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class UserModel extends Model {
	public static $Schema = array(
		'uid' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'username' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 32,
			'null' => false,
		),
		'password' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 60,
			'null' => false,
		),
		'apikey' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'null' => false,
		),
		'email' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'null' => false,
		),
		'active' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => '1',
			'null' => false,
		),
		'admin' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => '0',
			'null' => false,
		),
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
		'primary' => array('uid'),
		'unique:email' => array('email'),
	);

	// @todo Put your code here.

} // END class UserModel extends Model
