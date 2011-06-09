<?php
/**
 * Model for ForumTopicsModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class ForumTopicsModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'category_id' => array(
			'type' => Model::ATT_TYPE_INT,
			'null' => false,
		),
		'parent_id' => array(
			'type' => Model::ATT_TYPE_INT,
			'default' => null,
			'null' => true,
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
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
		'category_id' => array('category_id'),
		'parent_id' => array('parent_id'),
	);

	// @todo Put your code here.

} // END class ForumTopicsModel extends Model
