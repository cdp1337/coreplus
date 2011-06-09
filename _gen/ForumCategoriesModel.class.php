<?php
/**
 * Model for ForumCategoriesModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class ForumCategoriesModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'forum_id' => array(
			'type' => Model::ATT_TYPE_INT,
			'null' => false,
		),
		'name' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 127,
			'null' => false,
		),
		'description' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'null' => false,
		),
		'weight' => array(
			'type' => Model::ATT_TYPE_INT,
			'null' => false,
		),
		'view_access' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 255,
			'null' => false,
		),
		'post_access' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 255,
			'null' => false,
		),
		'mod_access' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 255,
			'null' => false,
		),
		'upload_access' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 255,
			'null' => false,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
		'unique:forum_id' => array('forum_id', 'name'),
	);

	// @todo Put your code here.

} // END class ForumCategoriesModel extends Model
