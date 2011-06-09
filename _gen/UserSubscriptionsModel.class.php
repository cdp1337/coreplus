<?php
/**
 * Model for UserSubscriptionsModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class UserSubscriptionsModel extends Model {
	public static $Schema = array(
		'uid' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
		),
		'sid' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
		),
		'date_added' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'queue' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('uid', 'sid'),
		'sid' => array('sid'),
	);

	// @todo Put your code here.

} // END class UserSubscriptionsModel extends Model
