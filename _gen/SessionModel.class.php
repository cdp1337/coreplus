<?php
/**
 * Model for SessionModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class SessionModel extends Model {
	public static $Schema = array(
		'sid' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 32,
			'required' => true,
		),
		'ip_addr' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 39,
			'required' => true,
		),
		'uid' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'expires' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'session_data' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('sid', 'ip_addr'),
		'uid' => array('uid'),
	);

	// @todo Put your code here.

} // END class SessionModel extends Model
