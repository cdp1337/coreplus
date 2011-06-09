<?php
/**
 * Model for GroupsModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class GroupsModel extends Model {
	public static $Schema = array(
		'gid' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
		),
		'gname' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 32,
		),
		'locked' => array(
			'type' => Model::ATT_TYPE_BOOL,
		),
		'public' => array(
			'type' => Model::ATT_TYPE_BOOL,
		),
		'selectable' => array(
			'type' => Model::ATT_TYPE_BOOL,
		),
		'description' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('gid'),
		'unique:gname' => array('gname'),
	);

	// @todo Put your code here.

} // END class GroupsModel extends Model
