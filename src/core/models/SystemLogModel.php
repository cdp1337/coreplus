<?php
/**
 * File for class SystemLogModel definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20131015.2105
 * @copyright Copyright (C) 2009-2016  Charlie Powell
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
 * @author Charlie Powell <charlie@evalagency.com>
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
			'type' => Model::ATT_TYPE_CREATED,
			'formatter' => '\\Core\\Formatter\\GeneralFormatter::DateStringSDT',
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
			'type'    => Model::ATT_TYPE_UUID_FK,
			'default' => 0,
			'formatter' => '\\Core\\Formatter\\GeneralFormatter::User',
		),
		'ip_addr'    => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 39,
			'formatter' => '\\Core\\Formatter\\GeneralFormatter::IPAddress',
		),
		'useragent' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'formatter' => '\\Core\\Formatter\\GeneralFormatter::UserAgent',
		),
		'affected_user_id'    => array(
			'type'    => Model::ATT_TYPE_UUID_FK,
			'default' => null,
			'null'    => true,
			'comment' => 'If this action potentially affects a user, list the ID here.',
			'formatter' => '\\Core\\Formatter\\GeneralFormatter::User',
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
		'icon' => [
			'type' => Model::ATT_TYPE_STRING,
			'comment' => 'An optional icon to display along with this log message.',
		],
		'source' => [
			'type' => Model::ATT_TYPE_STRING,
			'comment' => 'An optional source to keep track of what type of message this comes from.',
		],
	);

	public static $Indexes = array(
		'primary'       => ['id'],
		'datetime'      => ['datetime'],
		'user'          => ['user_id'],
		'affected_user' => ['affected_user_id'],
		'code'          => ['code'],
		'source'        => ['source'],
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
	
	public function render($key){
		if($key == 'type'){
			switch($this->get('type')){
				case 'info':
					if($this->get('icon')){
						$i = '<i class="icon icon-' . $this->get('icon') . '"></i>';
					}
					else{
						$i = '';
					}
					break;
				case 'error':
					$i = '<i class="icon icon-exclamation" title="Error Entry"></i>';
					break;
				case 'security':
					$i = '<i class="icon icon-exclamation-triangle" title="Security Entry"></i>';
					break;
				default:
					$i = '[ ' . $this->get('type') . ' ]';
			}
			
			return $i . ' ' . $this->get('code');
		}
		elseif($key == 'datetime'){
			return \Core\Date\DateTime::FormatString($this->get('datetime'), 'SDT');
		}
		elseif($key == 'ip_addr'){
			$ua = new \Core\UserAgent($this->get('useragent'));
			$ip = new geocode\IPLookup($this->get('ip_addr'));
			return $ua->getAsHTML() . ' ' . $ip->getAsHTML(true) . ' ' . $this->get('ip_addr');
		}
		else{
			return parent::render($key);
		}
	}

	public function save($defer = false){

		if(Core::IsComponentAvailable('core')){
			$isnew = !$this->exists();
	
			$ret = parent::save($defer);
	
			// No change happened, nothing extra to do.
			if(!$ret){
				return $ret;
			}
	
			// Wasn't a previously new model?  Also nothing to do beyond.
			if(!$isnew){
				return $ret;
			}
		}

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
	
	public function getControlLinks() {
		$r = [];
		$id = $this->get('id');
		$ip = $this->get('ip_addr');
		
		if(\Core\user()->checkAccess('g:admin')){
			$r[] = [
				'title' => 'View Details',
				'link' => '/admin/log/details/' . $id,
				'class' => 'ajax-link',
				'icon' => 'view',
			];
			
			$r[] = [
				'title' => 'Ban IP',
				'link' => '/security/blacklistip/add?ip_addr=' . $ip . '/32',
				'icon' => 'thumbs-down',
			];
		}
		
		if(\Core\user()->checkAccess('p:/user/activity/view')){
			$r[] = [
				'title' => 'View Activity by IP',
				'link' => '/useractivity/details?filter[ip_addr]=' . $ip,
				'icon' => 'list-alt',
			];
			
			if($this->get('user_id')){
				$r[] = [
					'title' => '/useractivity/details?filter[userid]=' . $this->get('user_id'),
					'title' => 'View Activity by User',
					'icon' => 'user',
				];
			}
		}
		
		$r = array_merge($r, parent::getControlLinks());
		
		return $r;
	}


	/**
	 * Shortcut method available to log a security-based event on the system
	 *
	 * @param string   $code          Any code that may be associated to this event.
	 * @param string   $message       The subject of the security event.
	 * @param string   $details       Any additional details of the security event beyond the subject line.
	 * @param null|int $affected_user If this security event affected a given user, the affected user's id.
	 */
	public static function LogSecurityEvent($code, $message = '', $details = '', $affected_user = null) {
		try{
			$log = self::Factory();
			$log->set('type', 'security');
			$log->set('code', $code);
			$log->set('message', $message);
			$log->set('details', $details);
			$log->set('affected_user_id', $affected_user);
			$log->save();
		}
		catch(Exception $e){
			// If the model isn't available for some reason, (Core isn't set up, etc),
			// just fall back to the traditional error log mechanism.
			error_log($code . ': ' . $message);
			error_log('ADDITIONALLY, ' . $e->getMessage());
		}
	}

	/**
	 * Shortcut method available to log a error-based event on the system
	 *
	 * @param string $code    Any code that may be associated to this event.
	 * @param string $message The subject of the error event.
	 * @param string $details Any additional details of the error event beyond the subject line.
	 */
	public static function LogErrorEvent($code, $message, $details = '') {
		try{
			$log = self::Factory();
			$log->set('type', 'error');
			$log->set('code', $code);
			$log->set('message', $message);
			$log->set('details', $details);
			$log->save();
		}
		catch(Exception $e){
			// If the model isn't available for some reason, (Core isn't set up, etc),
			// just fall back to the traditional error log mechanism.
			error_log($code . ': ' . $message);
			error_log('ADDITIONALLY, ' . $e->getMessage());
		}
	}

	/**
	 * Shortcut method available to log a informative-based event on the system
	 *
	 * @param string $code    Any code that may be associated to this event.
	 * @param string $message The subject of the info event.
	 * @param string $details Any additional details of the info event beyond the subject line.
	 */
	public static function LogInfoEvent($code, $message, $details = '') {
		try{
			$log = self::Factory();
			$log->set('type', 'info');
			$log->set('code', $code);
			$log->set('message', $message);
			$log->set('details', $details);
			$log->save();
		}
		catch(Exception $e){
			// If the model isn't available for some reason, (Core isn't set up, etc),
			// just fall back to the traditional error log mechanism.
			error_log($code . ': ' . $message);
			error_log('ADDITIONALLY, ' . $e->getMessage());
		}
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
				'useragent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
			)
		);

		return $log;
	}

	/**
	 * Simple method to get the valid options for DB Keep logs options.
	 * 
	 * @return array
	 */
	public static function GetKeepDBAsOptions(){
		return [
			'7'   => t('STRING_KEEP_DB_LOGS_7_DAYS'),
			'30'  => t('STRING_KEEP_DB_LOGS_N_MONTH', 1),
			'60'  => t('STRING_KEEP_DB_LOGS_N_MONTH', 2),
			'90'  => t('STRING_KEEP_DB_LOGS_N_MONTH', 3),
			'180' => t('STRING_KEEP_DB_LOGS_N_MONTH', 6),
			'365' => t('STRING_KEEP_DB_LOGS_N_MONTH', 12),
			'558' => t('STRING_KEEP_DB_LOGS_N_MONTH', 18),
			'744' => t('STRING_KEEP_DB_LOGS_N_MONTH', 24),
			'1095' => t('STRING_KEEP_DB_LOGS_N_MONTH', 36),
			'0'   => t('STRING_KEEP_DB_LOGS_NEVER'),
		];
	}

	/**
	 * Purge old system logs from the database, as per configuration options.
	 * 
	 * @return bool
	 */
	public static function PurgeHook(){
		$len = ConfigHandler::Get('/core/logs/db/keep');
		if(!$len){
			echo "Not purging any logs, as per configuration option.\n";
			return true;
		}
		
		// Otherwise, the length will be the number of days to keep.
		$d = new \Core\Date\DateTime();
		$d->modify('-' . $len . ' days');
		
		echo "Deleting system logs older than " . $d->format(\Core\Date\DateTime::FULLDATE) . "\n";
		
		$count = \Core\Datamodel\Dataset::Init()
			->count()
			->table('system_log')
			->where('datetime < ' . $d->format('U'))
			->executeAndGet();
		
		echo "Found " . $count . " log entries, deleting!\n";
		\Core\Datamodel\Dataset::Init()
			->table('system_log')
			->where('datetime < ' . $d->format('U'))
			->delete()
			->execute();
		
		return true;
	}
}