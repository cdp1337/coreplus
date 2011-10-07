<?php

/**
 * Description of UpdateSiteModel
 *
 * @author powellc
 */
class UpdateSiteModel extends Model{
	
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'url' => array(
			'type' => Model::ATT_TYPE_STRING,
			'required' => true,
			'null' => false
		),
		'enabled' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'null' => false,
			'default' => true
		),
		'username' => array(
			'type' => Model::ATT_TYPE_STRING,
			'required' => false,
			'null' => true
		),
		'password' => array(
			'type' => Model::ATT_TYPE_STRING,
			'required' => false,
			'null' => true
		),
		'created' => array(
			'type' => Model::ATT_TYPE_CREATED
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_UPDATED
		)
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
		'unique:url' => array('url'),
	);
	
}
