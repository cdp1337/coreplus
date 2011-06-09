<?php
/**
 * Model for ForumThreadsModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class ForumThreadsModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'topic_id' => array(
			'type' => Model::ATT_TYPE_INT,
			'null' => false,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 127,
			'null' => false,
		),
		'emblem' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'default' => null,
			'null' => true,
		),
		'owner_id' => array(
			'type' => Model::ATT_TYPE_INT,
			'default' => null,
			'null' => true,
		),
		'locked' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'null' => false,
		),
		'type' => array(
			'type' => Model::ATT_TYPE_ENUM,
			'options' => array('normal','sticky','z_pinned'),
			'default' => 'normal',
			'null' => false,
		),
		'views' => array(
			'type' => Model::ATT_TYPE_INT,
			'null' => false,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
		'topic_id' => array('topic_id'),
		'owner_id' => array('owner_id'),
	);

	// @todo Put your code here.

} // END class ForumThreadsModel extends Model
