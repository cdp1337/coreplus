<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 8/4/12
 * Time: 4:26 AM
 * To change this template use File | Settings | File Templates.
 */
class UserActivityModel extends Model{
	/**
	 * The UserAgent object for this log.
	 *
	 * @var null|UserAgent
	 */
	private $_ua = null;

	public static $Schema = array(
		'datetime' => array(
			'type' => Model::ATT_TYPE_CREATED
		),
		'session_id' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 160,
		),
		'user_id'    => array(
			'type'    => Model::ATT_TYPE_INT,
			'default' => 0,
		),
		'ip_addr'    => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 39,
		),
		'useragent' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128
		),
		'referrer' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128
		),
		'type' => array(
			'type' => Model::ATT_TYPE_ENUM,
			'options' => array('GET', 'POST', 'HEAD', 'PUSH', 'PUT', 'DELETE'),
			'default' => 'GET',
		),
		'request' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128
		),
		'baseurl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128
		),
		'status' => array(
			'type' => Model::ATT_TYPE_INT
		),
		'db_reads' => array(
			'type' => Model::ATT_TYPE_INT
		),
		'db_writes' => array(
			'type' => Model::ATT_TYPE_INT
		),
		'processing_time' => array(
			'type' => Model::ATT_TYPE_INT
		)
	);

	public static $Indexes = array(
		'primary' => array('datetime', 'session_id'),
		'datetime' => array('datetime'),
		'user' => array('user_id')
	);

	/**
	 * Function that guesses if this user request was a bot.
	 *
	 * @return boolean
	 */
	public function isBot(){
		switch($this->getUserAgent()->type){
			case 'Robot':
			case 'Offline Browser':
			case 'Other':
				return true;
			default:
				return false;
		}
	}

	/**
	 * Get the matching user agent for this model.
	 *
	 * @return UserAgent
	 */
	public function getUserAgent(){
		if($this->_ua === null){
			$this->_ua = new UserAgent($this->get('useragent'));
		}
		return $this->_ua;
	}
}
