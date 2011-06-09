<?php
/**
 * Model for ForumCategoriesModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class ForumCategoriesModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
		),
		'forum_id' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'name' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 127,
		),
		'description' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
		'weight' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'view_access' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 255,
		),
		'post_access' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 255,
		),
		'mod_access' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 255,
		),
		'upload_access' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 255,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
		'unique:forum_id' => array('forum_id', 'name'),
	);

	// @todo Put your code here.

} // END class ForumCategoriesModel extends Model
