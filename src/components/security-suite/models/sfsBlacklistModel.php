<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 12/15/12
 * Time: 1:56 PM
 * To change this template use File | Settings | File Templates.
 */
class sfsBlacklistModel extends Model {
	public static $Schema = array(
		'ip_addr'    => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 39,
		),
		'submissions' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'lastseen' => array(
			'type' => Model::ATT_TYPE_ISO_8601_DATETIME,
		),
	);

	public static $Indexes = array(
		'primary' => array('ip_addr'),
	);
}
