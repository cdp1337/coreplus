<?php
/**
 * Model for SessionModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class SessionModel extends Model {
	public static $Schema = array(
		'sid' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 32,
			'required' => true,
			'null' => false,
		),
		'ip_addr' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 39,
			'required' => true,
			'null' => false,
		),
		'uid' => array(
			'type' => Model::ATT_TYPE_INT,
			'default' => null,
			'null' => true,
		),
		'expires' => array(
			'type' => Model::ATT_TYPE_INT,
			'null' => false,
		),
		'session_data' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'null' => false,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('sid', 'ip_addr'),
		'uid' => array('uid'),
	);

	// @todo Put your code here.

} // END class SessionModel extends Model
