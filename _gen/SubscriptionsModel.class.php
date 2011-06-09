<?php
/**
 * Model for SubscriptionsModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class SubscriptionsModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
		),
		'class' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
		),
		'event' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
		),
		'href' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 256,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
		'unique:class' => array('class', 'event'),
	);

	// @todo Put your code here.

} // END class SubscriptionsModel extends Model
