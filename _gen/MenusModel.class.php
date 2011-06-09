<?php
/**
 * Model for MenusModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class MenusModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
		),
		'name' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
	);

	// @todo Put your code here.

} // END class MenusModel extends Model
