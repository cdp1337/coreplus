<?php
/**
 * Model for ForumPostsModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class ForumPostsModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'thread_id' => array(
			'type' => Model::ATT_TYPE_INT,
			'null' => false,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 127,
			'null' => false,
		),
		'body' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'null' => false,
		),
		'owner_id' => array(
			'type' => Model::ATT_TYPE_INT,
			'default' => null,
			'null' => true,
		),
		'date_posted' => array(
			'type' => Model::ATT_TYPE_INT,
			'null' => false,
		),
		'attachment' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 127,
			'default' => null,
			'null' => true,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
		'thread_id' => array('thread_id'),
		'owner_id' => array('owner_id'),
	);

	// @todo Put your code here.

} // END class ForumPostsModel extends Model
