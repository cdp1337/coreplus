<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 12/15/12
 * Time: 11:34 AM
 * To change this template use File | Settings | File Templates.
 */
class SecurityLogModel extends Model{
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_UUID,
		),
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
		'action' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128
		),
		'affected_user_id'    => array(
			'type'    => Model::ATT_TYPE_INT,
			'default' => null,
			'null'    => true,
			'comment' => 'If this action potentially affects a user, list the ID here.'
		),
		'status' => array(
			'type' => Model::ATT_TYPE_ENUM,
			'options' => array('fail', 'success'),
			'null' => true,
			'default' => null,
		),
		'details' => array(
			'type' => Model::ATT_TYPE_TEXT
		),
	);

	public static $Indexes = array(
		'primary' => array('id'),
		'datetime' => array('datetime'),
		'user' => array('user_id'),
		'affected_user' => array('affected_user_id'),
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

	/**
	 * Shortcut function to record a new security log entry.
	 *
	 * Return the log entry that's created.
	 *
	 * @param string      $action
	 * @param null|string $status
	 * @param null|int    $affecteduser
	 * @param null|string $details
	 *
	 * @return SecurityLogModel
	 */
	public static function Log($action, $status = null, $affecteduser = null, $details = null){
		$log = new SecurityLogModel();
		$log->setFromArray(
			array(
				'session_id' => session_id(),
				'user_id' => \Core\user()->get('id'),
				'ip_addr' => REMOTE_IP,
				'useragent' => $_SERVER['HTTP_USER_AGENT'],
				'action' => $action,
				'status' => $status,
				'affected_user_id' => $affecteduser,
				'details' => $details,
			)
		);
		$log->save();

		return $log;
	}
}
