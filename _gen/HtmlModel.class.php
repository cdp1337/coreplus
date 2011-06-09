<?php
/**
 * Model for HtmlModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class HtmlModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'null' => false,
		),
		'body' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'null' => false,
		),
		'revised_date' => array(
			'type' => Model::ATT_TYPE_INT,
			'null' => false,
		),
		'meta_author' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'default' => null,
			'null' => true,
		),
		'meta_keywords' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 256,
			'default' => null,
			'null' => true,
		),
		'meta_description' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 511,
			'default' => null,
			'null' => true,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
	);

	// @todo Put your code here.

} // END class HtmlModel extends Model
