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
	 * The \Core\UserAgent object for this log.
	 *
	 * @var null|\Core\UserAgent
	 */
	private $_ua = null;

	public static $Schema = array(
		'datetime' => array(
			'type' => Model::ATT_TYPE_FLOAT,
			'precision' => '16,4',
		),
		'session_id' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
		),
		'user_id'    => array(
			'type'    => Model::ATT_TYPE_UUID_FK,
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
		),
		'xhprof_run' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 16,
		),
		'xhprof_source' => array(
			'type' => Model::ATT_TYPE_STRING,
		),
	);

	public static $Indexes = array(
		'primary' => array('datetime', 'session_id', 'ip_addr'),
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
	 * @return \Core\UserAgent
	 */
	public function getUserAgent(){
		if($this->_ua === null){
			$this->_ua = new \Core\UserAgent($this->get('useragent'));
		}
		return $this->_ua;
	}

	public function getDisplayName(){
		if($this->get('user_id')){
			$user = UserModel::Construct($this->get('user_id'));
			$uname = $user->getDisplayName();
		}
		else{
			$uname = ConfigHandler::Get('/user/displayname/anonymous');
		}

		return $uname;
	}

	/**
	 * Get the processing time of this request formatted with either "ms" or "s.
	 *
	 * @return string
	 */
	public function getTimeFormatted() {
		$t = $this->get('processing_time');
		if($t < 1100) return $t . 'ms';
		else return round(($t/1000), 1) . 's';
	}
}
