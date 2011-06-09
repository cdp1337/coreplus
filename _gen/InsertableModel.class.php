<?php
/**
 * Model for InsertableModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class InsertableModel extends Model {
	public static $Schema = array(
		'baseurl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'required' => true,
			'null' => false,
		),
		'name' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'required' => true,
			'null' => false,
		),
		'value' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'null' => false,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('baseurl', 'name'),
	);

	// @todo Put your code here.

} // END class InsertableModel extends Model
