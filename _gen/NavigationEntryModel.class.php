<?php
/**
 * Model for NavigationEntryModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class NavigationEntryModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
		),
		'navigationid' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'parentid' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'type' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 6,
		),
		'baseurl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 256,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
		),
		'target' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 16,
		),
		'weight' => array(
			'type' => Model::ATT_TYPE_INT,
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
	);

	// @todo Put your code here.

} // END class NavigationEntryModel extends Model
