<?php

/**
 * Description of UserConfigModel
 *
 * @author powellc
 */
class UserUserConfigModel extends Model{
	public static $Schema = array(
		'user_id' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
			'null' => false,
		),
		'key' => array(
			'type' => Model::ATT_TYPE_STRING,
			'required' => true,
			'null' => false
		),
		'value' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'required' => false,
			'null' => true
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
		'primary' => array('user_id', 'key'),
	);
}

?>
