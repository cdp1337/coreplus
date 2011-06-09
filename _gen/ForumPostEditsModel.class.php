<?php
/**
 * Model for ForumPostEditsModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class ForumPostEditsModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
		),
		'post_id' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'date_edited' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'edited_by' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'reason' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 512,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 256,
		),
		'body' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
		'post_id' => array('post_id'),
		'edited_by' => array('edited_by'),
	);

	// @todo Put your code here.

} // END class ForumPostEditsModel extends Model
