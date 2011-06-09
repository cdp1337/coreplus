<?php
/**
 * Model for WidgetInstanceModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class WidgetInstanceModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'baseurl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'null' => false,
		),
		'pages' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'default' => '*',
			'null' => false,
		),
		'theme' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'null' => false,
		),
		'template' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'null' => false,
		),
		'area' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'null' => false,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'default' => null,
			'null' => true,
		),
		'container' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'default' => null,
			'null' => true,
		),
		'weight' => array(
			'type' => Model::ATT_TYPE_INT,
			'default' => '10',
			'null' => false,
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_UPDATED,
			'null' => false,
		),
		'created' => array(
			'type' => Model::ATT_TYPE_CREATED,
			'null' => false,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
		'instance' => array('baseurl'),
	);

	// @todo Put your code here.

} // END class WidgetInstanceModel extends Model
