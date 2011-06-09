<?php
/**
 * Model for SubscriptionsModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class SubscriptionsModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'class' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'null' => false,
		),
		'event' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'null' => false,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'null' => false,
		),
		'href' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 256,
			'null' => false,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
		'unique:class' => array('class', 'event'),
	);

	// @todo Put your code here.

} // END class SubscriptionsModel extends Model
