<?php
/**
 * Model for WidgetInstanceModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class WidgetInstanceModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
		),
		'baseurl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
		),
		'pages' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
		),
		'theme' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
		),
		'template' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
		),
		'area' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
		),
		'container' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
		),
		'weight' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'created' => array(
			'type' => Model::ATT_TYPE_INT,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
		'instance' => array('baseurl'),
	);

	// @todo Put your code here.

} // END class WidgetInstanceModel extends Model
