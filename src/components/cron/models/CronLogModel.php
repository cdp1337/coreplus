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
			'options' => array('1-minute', '5-minute', '10-minute', '15-minute', '20-minute', '30-minute', '45-minute', 'hourly', '2-hour', '3-hour','6-hour', '12-hour', 'daily', 'weekly', 'monthly'),
			'default' => 'hourly',
			'required' => true,
			'null'     => false,
			'comment' => 'The type of cron executed'
		),
		'status' => array(
			'type' => Model::ATT_TYPE_ENUM,
			'options' => array('pass', 'fail', 'running'),
			'default' => 'running',
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

	/**
	 * Method to cleanup the Cron database from old entries.
	 */
	public static function _CleanupDatabase(){
		$date = new \Core\Date\DateTime();
		$date->modify('-1 week');
		$week = $date->format('U');
		$date->modify('-3 weeks');
		$month = $date->format('U');
		$date->modify('-5 months');
		$half = $date->format('U');

		\Core\Datamodel\Dataset::Init()
			->delete()
			->table('cron_log')
			->where('cron = 1-minute')
			->where('created <= ' . $week)
			->execute();

		\Core\Datamodel\Dataset::Init()
			->delete()
			->table('cron_log')
			->where('cron = 5-minute')
			->where('created <= ' . $week)
			->execute();

		\Core\Datamodel\Dataset::Init()
			->delete()
			->table('cron_log')
			->where('cron = 10-minute')
			->where('created <= ' . $week)
			->execute();

		\Core\Datamodel\Dataset::Init()
			->delete()
			->table('cron_log')
			->where('cron = 15-minute')
			->where('created <= ' . $week)
			->execute();

		\Core\Datamodel\Dataset::Init()
			->delete()
			->table('cron_log')
			->where('cron = 20-minute')
			->where('created <= ' . $week)
			->execute();

		\Core\Datamodel\Dataset::Init()
			->delete()
			->table('cron_log')
			->where('cron = 30-minute')
			->where('created <= ' . $week)
			->execute();

		\Core\Datamodel\Dataset::Init()
			->delete()
			->table('cron_log')
			->where('cron = 45-minute')
			->where('created <= ' . $week)
			->execute();
		
		\Core\Datamodel\Dataset::Init()
			->delete()
			->table('cron_log')
			->where('cron = hourly')
			->where('created <= ' . $week)
			->execute();

		\Core\Datamodel\Dataset::Init()
			->delete()
			->table('cron_log')
			->where('cron = 2-hour')
			->where('created <= ' . $week)
			->execute();

		\Core\Datamodel\Dataset::Init()
			->delete()
			->table('cron_log')
			->where('cron = 3-hour')
			->where('created <= ' . $week)
			->execute();

		\Core\Datamodel\Dataset::Init()
			->delete()
			->table('cron_log')
			->where('cron = 6-hour')
			->where('created <= ' . $week)
			->execute();

		\Core\Datamodel\Dataset::Init()
			->delete()
			->table('cron_log')
			->where('cron = 12-hour')
			->where('created <= ' . $week)
			->execute();

		\Core\Datamodel\Dataset::Init()
			->delete()
			->table('cron_log')
			->where('cron = daily')
			->where('created <= ' . $month)
			->execute();

		\Core\Datamodel\Dataset::Init()
			->delete()
			->table('cron_log')
			->where('cron = weekly')
			->where('created <= ' . $half)
			->execute();

		\Core\Datamodel\Dataset::Init()
			->delete()
			->table('cron_log')
			->where('cron = monthly')
			->where('created <= ' . $half)
			->execute();
		
		return true;
	}
}