<?php
/**
 * Model for GroupsModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class GroupsModel extends Model {
	public static $Schema = array(
		'gid' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'gname' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 32,
			'null' => false,
		),
		'locked' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'null' => false,
		),
		'public' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'null' => false,
		),
		'selectable' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'null' => false,
		),
		'description' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'null' => false,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('gid'),
		'unique:gname' => array('gname'),
	);

	// @todo Put your code here.

} // END class GroupsModel extends Model
