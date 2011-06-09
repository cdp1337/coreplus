<?php
/**
 * Model for UserGroupsModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class UserGroupsModel extends Model {
	public static $Schema = array(
		'uid' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
		),
		'gid' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('gid', 'uid'),
	);

	// @todo Put your code here.

} // END class UserGroupsModel extends Model
