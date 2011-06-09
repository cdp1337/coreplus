<?php
/**
 * Model for ComponentModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class ComponentModel extends Model {
	public static $Schema = array(
		'name' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 48,
			'required' => true,
		),
		'version' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 24,
		),
		'enabled' => array(
			'type' => Model::ATT_TYPE_BOOL,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('name'),
	);

	// @todo Put your code here.

} // END class ComponentModel extends Model
