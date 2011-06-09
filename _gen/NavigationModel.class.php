<?php
/**
 * Model for NavigationModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class NavigationModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
		),
		'name' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
		),
		'access' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 512,
		),
		'created' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_INT,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
		'unique:name' => array('name'),
	);

	// @todo Put your code here.

} // END class NavigationModel extends Model
