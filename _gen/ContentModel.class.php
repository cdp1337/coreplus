<?php
/**
 * Model for ContentModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class ContentModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'null' => false,
		),
		'description' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'null' => false,
		),
		'keywords' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'null' => false,
		),
		'access' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 512,
			'default' => '*',
			'null' => false,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
	);

	// @todo Put your code here.

} // END class ContentModel extends Model
