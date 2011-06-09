<?php
/**
 * Model for ContentModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class ContentModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
		),
		'description' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
		'keywords' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
		'access' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 512,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
	);

	// @todo Put your code here.

} // END class ContentModel extends Model
