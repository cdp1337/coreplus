<?php
/**
 * Model for UserSubscriptionsModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class UserSubscriptionsModel extends Model {
	public static $Schema = array(
		'uid' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'sid' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'date_added' => array(
			'type' => Model::ATT_TYPE_INT,
			'null' => false,
		),
		'queue' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'null' => false,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('uid', 'sid'),
		'sid' => array('sid'),
	);

	// @todo Put your code here.

} // END class UserSubscriptionsModel extends Model
