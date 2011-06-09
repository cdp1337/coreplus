<?php
/**
 * Model for WidgetModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class WidgetModel extends Model {
	public static $Schema = array(
		'baseurl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'required' => true,
		),
		'wrapper_template' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
		),
		'widget_template' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'comment' => '[cached]',
		),
		'access' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 512,
			'comment' => '[cached]',
		),
		'created' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_INT,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('baseurl'),
	);

	// @todo Put your code here.

} // END class WidgetModel extends Model
