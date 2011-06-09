<?php
/**
 * Model for ForumPostsModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class ForumPostsModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
		),
		'thread_id' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 127,
		),
		'body' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
		'owner_id' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'date_posted' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'attachment' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 127,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
		'thread_id' => array('thread_id'),
		'owner_id' => array('owner_id'),
	);

	// @todo Put your code here.

} // END class ForumPostsModel extends Model
