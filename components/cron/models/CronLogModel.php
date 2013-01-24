<?php
/**
 * Class file for the model CronLogModel
 *
 * @package Cron
 */
class CronLogModel extends Model {
	/**
	 * Schema definition for CronLogModel
	 *
	 * @static
	 * @var array
	 */
	public static $Schema = array(
		'id'       => array(
			'type'     => Model::ATT_TYPE_ID,
		),
		'cron'      => array(
			'type'     => Model::ATT_TYPE_ENUM,
			'options' => array('hourly', 'daily', 'weekly', 'monthly'),
			'required' => true,
			'null'     => false,
			'comment' => 'The type of cron executed'
		),
		'status' => array(
			'type' => Model::ATT_TYPE_ENUM,
			'options' => array('pass', 'fail', 'running'),
			'required' => true,
			'null' => false,
		),
		'log' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'comment' => 'The output from all operations performed',
		),
		'created'  => array(
			'type' => Model::ATT_TYPE_CREATED
		),
		'completed' => array(
			'type' => Model::ATT_TYPE_INT,
			'comment' => 'Unix timestamp of the completion time for this job',
		),
		'duration' => array(
			'type' => Model::ATT_TYPE_FLOAT,
			'precision' => '18,12',
			'comment' => 'Duration (in milliseconds) of the script execution'
		),
		'memory' => array(
			'type' => Model::ATT_TYPE_INT,
			'comment' => 'Amount of memory (in bytes) used by script',
		),
		'ip' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'comment' => 'IP address of calling host, (since they will be standard page requests)',
		),
	);

	/**
	 * Index definition for CronLogModel
	 *
	 * @static
	 * @var array
	 */
	public static $Indexes = array(
		'primary' => array('id'),
	);
}