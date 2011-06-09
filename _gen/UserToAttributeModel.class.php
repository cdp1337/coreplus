<?php
/**
 * Model for UserToAttributeModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class UserToAttributeModel extends Model {
	public static $Schema = array(
		'uid' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
		),
		'attid' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
		),
		'value' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('uid', 'attid'),
		'name' => array('attid'),
	);

	// @todo Put your code here.

} // END class UserToAttributeModel extends Model
