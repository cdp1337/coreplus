<?php
/**
 * Model for ForumPostEditsModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class ForumPostEditsModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'post_id' => array(
			'type' => Model::ATT_TYPE_INT,
			'null' => false,
		),
		'date_edited' => array(
			'type' => Model::ATT_TYPE_INT,
			'null' => false,
		),
		'edited_by' => array(
			'type' => Model::ATT_TYPE_INT,
			'default' => null,
			'null' => true,
		),
		'reason' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 512,
			'null' => false,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 256,
			'default' => null,
			'null' => true,
		),
		'body' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'default' => null,
			'null' => true,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
		'post_id' => array('post_id'),
		'edited_by' => array('edited_by'),
	);

	// @todo Put your code here.

} // END class ForumPostEditsModel extends Model
