<?php
/**
 * Model for NavigationModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class NavigationModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'name' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'null' => false,
		),
		'access' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 512,
			'null' => false,
		),
		'created' => array(
			'type' => Model::ATT_TYPE_CREATED,
			'null' => false,
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_UPDATED,
			'null' => false,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
		'unique:name' => array('name'),
	);

	// @todo Put your code here.

} // END class NavigationModel extends Model
