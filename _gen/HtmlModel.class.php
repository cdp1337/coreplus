<?php
/**
 * Model for HtmlModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class HtmlModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
		),
		'body' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
		'revised_date' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'meta_author' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
		),
		'meta_keywords' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 256,
		),
		'meta_description' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 511,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
	);

	// @todo Put your code here.

} // END class HtmlModel extends Model
