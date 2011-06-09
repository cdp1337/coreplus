<?php
/**
 * Model for ViewClassesModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class ViewClassesModel extends Model {
	public static $Schema = array(
		'class' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'required' => true,
			'null' => false,
		),
		'class_alias' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'null' => false,
		),
		'widgetable' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'null' => false,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('class'),
		'unique:class_alias' => array('class_alias'),
	);

	// @todo Put your code here.

} // END class ViewClassesModel extends Model
