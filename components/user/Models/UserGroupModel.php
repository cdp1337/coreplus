<?php
/**
 * Model for UserGroupModel
 * 
 * @author Charlie Powell <charlie@eval.bz>
 */
class UserGroupModel extends Model {
	
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'name' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 48,
			'null' => false,
			'required' => true,
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
		'primary' => array('id'),
		'unique:name' => array('name'),
	);
	
} // END class UserGroupModel extends Model
