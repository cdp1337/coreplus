<?php
/**
 * Model for WidgetModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class WidgetModel extends Model {
	public static $Schema = array(
		'baseurl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'required' => true,
			'null' => false,
		),
		'wrapper_template' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'default' => null,
			'null' => true,
		),
		'widget_template' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'default' => null,
			'null' => true,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'comment' => '[cached]',
			'null' => false,
		),
		'access' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 512,
			'default' => '*',
			'comment' => '[cached]',
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
		'primary' => array('baseurl'),
	);

	// @todo Put your code here.

} // END class WidgetModel extends Model
