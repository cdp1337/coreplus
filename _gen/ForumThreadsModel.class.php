<?php
/**
 * Model for ForumThreadsModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class ForumThreadsModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
		),
		'topic_id' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 127,
		),
		'emblem' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
		),
		'owner_id' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'locked' => array(
			'type' => Model::ATT_TYPE_BOOL,
		),
		'type' => array(
			'type' => Model::ATT_TYPE_ENUM,
			'options' => array('normal','sticky','z_pinned'),
		),
		'views' => array(
			'type' => Model::ATT_TYPE_INT,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
		'topic_id' => array('topic_id'),
		'owner_id' => array('owner_id'),
	);

	// @todo Put your code here.

} // END class ForumThreadsModel extends Model
