<?php
/**
 * Model for ForumPostFlagsModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class ForumPostFlagsModel extends Model {
	public static $Schema = array(
		'post_id' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'owner_id' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'description' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
		'date_posted' => array(
			'type' => Model::ATT_TYPE_INT,
		),
	);
	
	public static $Indexes = array(
		'unique:post_id' => array('post_id', 'owner_id'),
	);

	// @todo Put your code here.

} // END class ForumPostFlagsModel extends Model
