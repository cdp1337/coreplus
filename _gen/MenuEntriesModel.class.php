<?php
/**
 * Model for MenuEntriesModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class MenuEntriesModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'menu_id' => array(
			'type' => Model::ATT_TYPE_INT,
			'null' => false,
		),
		'parent_id' => array(
			'type' => Model::ATT_TYPE_INT,
			'null' => false,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'default' => null,
			'null' => true,
		),
		'page_id' => array(
			'type' => Model::ATT_TYPE_INT,
			'null' => false,
		),
		'weight' => array(
			'type' => Model::ATT_TYPE_INT,
			'default' => '10',
			'null' => false,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
		'parent_id' => array('parent_id'),
	);

	// @todo Put your code here.

} // END class MenuEntriesModel extends Model
