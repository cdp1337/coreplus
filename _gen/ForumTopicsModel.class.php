<?php
/**
 * Model for ForumTopicsModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class ForumTopicsModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
		),
		'category_id' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'parent_id' => array(
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
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
		'category_id' => array('category_id'),
		'parent_id' => array('parent_id'),
	);

	// @todo Put your code here.

} // END class ForumTopicsModel extends Model
