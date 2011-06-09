<?php
/**
 * Model for WidgetsModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class WidgetsModel extends Model {
	public static $Schema = array(
		'instance' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
		),
		'class' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 32,
		),
		'data' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('instance'),
		'class' => array('class'),
	);

	// @todo Put your code here.

} // END class WidgetsModel extends Model
