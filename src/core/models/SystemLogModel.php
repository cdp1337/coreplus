<?php
/**
 * File for class SystemLogModel definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20131015.2105
 * @copyright Copyright (C) 2009-2013  Author
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/agpl-3.0.txt.
 */


/**
 * A short teaser of what SystemLogModel does.
 *
 * More lengthy description of what SystemLogModel does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for SystemLogModel
 * <h4>Example 1</h4>
 * <p>Description 1</p>
 * <code>
 * // Some code for example 1
 * $a = $b;
 * </code>
 *
 *
 * <h4>Example 2</h4>
 * <p>Description 2</p>
 * <code>
 * // Some code for example 2
 * $b = $a;
 * </code>
 *
 * 
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class SystemLogModel extends Model {
	const TYPE_SECURITY = 'security';
	const TYPE_ERROR    = 'error';
	const TYPE_INFO     = 'info';

	/** @var null|Core\UserAgent */
	public $_ua = null;

	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_UUID,
		),
		'datetime' => array(
			'type' => Model::ATT_TYPE_CREATED
		),
		'type' => array(
			'type' => Model::ATT_TYPE_ENUM,
			'options' => array('security', 'error', 'info'),
			'default' => 'info',
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
		'affected_user_id'    => array(
			'type'    => Model::ATT_TYPE_INT,
			'default' => null,
			'null'    => true,
			'comment' => 'If this action potentially affects a user, list the ID here.'
		),
		'code' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'comment' => 'A short phrase or code for this log event, used by sorting'
		),
		'message' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 512,
			'comment' => 'Any primary message that goes with this log event',
		),
		'details' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'comment' => 'Any details or backtrace that needs to accompany this log event',
		),
	);

	public static $Indexes = array(
		'primary'       => ['id'],
		'datetime'      => ['datetime'],
		'user'          => ['user_id'],
		'affected_user' => ['affected_user_id'],
		'code'          => ['code']
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

	public function save(){

		$isnew = !$this->exists();

		$ret = parent::save();

		// No change happened, nothing extra to do.
		if(!$ret) return $ret;

		// Wasn't a previously new model?  Also nothing to do beyond.
		if(!$isnew) return $ret;

		// @todo email message function

		// log message (to file).
		if(
			($this->get('type') == 'error' || $this->get('type') == 'security') &&
			$this->get('details')
		){
			Core\Utilities\Logger\append_to($this->get('type'), $this->get('message') . "\n" . $this->get('details'), $this->get('code'));
		}
		else{
			Core\Utilities\Logger\append_to($this->get('type'), $this->get('message'), $this->get('code'));
		}

	}


	public static function LogSecurityEvent($code, $message = '', $details = '', $affected_user = null) {
		$log = self::Factory();
		$log->set('type', 'security');
		$log->set('code', $code);
		$log->set('message', $message);
		$log->set('details', $details);
		$log->set('affected_user_id', $affected_user);
		$log->save();
	}

	public static function LogErrorEvent($code, $message, $details = '') {
		$log = self::Factory();
		$log->set('type', 'error');
		$log->set('code', $code);
		$log->set('message', $message);
		$log->set('details', $details);
		$log->save();
	}

	public static function LogInfoEvent($code, $message, $details = '') {
		$log = self::Factory();
		$log->set('type', 'info');
		$log->set('code', $code);
		$log->set('message', $message);
		$log->set('details', $details);
		$log->save();
	}

	/**
	 * Create a new log entry populated with the standard information.
	 *
	 * @return SystemLogModel
	 */
	public static function Factory() {
		$log = new self();
		$log->setFromArray(
			array(
				'session_id' => session_id(),
				'user_id' => (\Core\user() ? \Core\user()->get('id') : 0),
				'ip_addr' => REMOTE_IP,
				'useragent' => $_SERVER['HTTP_USER_AGENT'],
			)
		);

		return $log;
	}

}