<?php
/**
 * @license GNU Lesser General Public License v3 <http://www.gnu.org/licenses/lgpl-3.0.txt>
 * 
 * Copyright (C) 2009  Charlie Powell <powellc@powelltechs.com>
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
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/lgpl-3.0.txt.
 */

//This is a hack to allow classes to still be available after the page has been rendered.
register_shutdown_function("session_write_close");

/**
 * Kudos to Rich Smith <http://www.jamsoft.biz/> for the original concept I found
 *  a while back.
 * He submitted a script onto Devshed <http://www.devshed.com>
 * labled "Storing PHP Sessions in a Database" 
 * <http://www.devshed.com/c/a/PHP/Storing-PHP-Sessions-in-a-Database/>.
 *
 */
class Session implements ISingleton{
  
	private $_sid;
	private $_uid = 0;
	private $_ttl;
  
	private static $instance = null;
  
	private function __construct(){

		$this->_ttl = ConfigHandler::GetValue('session', 'ttl');

		// Set the save handlers
		session_set_save_handler(
			array('Session', "Start"),
			array('Session', "End"),
			array('Session', "Read"),
			array('Session', "Write"),
			array('Session', "Destroy"),
			array('Session', "GC")
		);

		// Possibly give a notice or something if it's started previously... or maybe not, shrug.
    
	}
  
	public static function Singleton(){
		if(is_null(Session::$instance)){
			Session::$instance = new Session();

			// Ensure garbage collection is done at some point.
			Session::GC();
		}

		return Session::$instance;
	}

	public static function GetInstance(){
		return Session::Singleton();
	}

	public static function Start($save_path, $session_name) {
		Session::Singleton()->_sid = session_id();

		//This is a core PHP function.  Save that a session has been started in the default server log.
		//error_log('Starting Session ' . $session_name . " ". $this->sid);//DEBUG//


		/*
		* Get the userID of the saved session (if exists).
		* If the query returns no valid rows (ie: this is a NEW session),
		* $data will be a blank array, thus never tripping the foreach and preserving $this->uid as FALSE.
		*/
		$rs = DB::Execute(
			"SELECT `uid`
			FROM ".DB_PREFIX."session
			WHERE `sid` = ? AND `ip_addr` = ? LIMIT 1",
			array(Session::Singleton()->_sid, REMOTE_IP)
		);

		$data = $rs->fields;
		if(!$data) echo DB::Error ();
		var_dump($rs, $data);
		/**
		* The session is NEW.  Create it.
		*/
		if($rs->NumRows() == 0){
			echo "NEW";
			DB::Execute(
			"INSERT INTO ".DB_PREFIX."session
			(`sid`, `ip_addr`, `uid`, `expires`)
			VALUES
			(?, ?, ?, ?)",
			array(Session::Singleton()->_sid, REMOTE_IP, 0, Session::Singleton()->_ttl + Time::GetCurrent()));
		}
		/**
		* The session already exists, just update the timestamp.
		*/
		else{
			Session::Singleton()->_uid = $rs->fields['uid'];

			DB::Execute(
			"UPDATE ".DB_PREFIX."session
			SET `expires` = ?
			WHERE `sid` = ? AND `ip_addr` = ?",
			array(Session::Singleton()->_ttl + Time::GetCurrent(), Session::Singleton()->_sid, REMOTE_IP)
			);
		}
	}

	public static function End() {
		// Nothing needs to be done in this function
		// since we used persistent connection.
	}

	public static function Read( $id ) {

		// Fetch session data from the selected database    
		$data = DB::Execute("SELECT `session_data` FROM ".DB_PREFIX."session WHERE `sid` = '$id' AND `ip_addr` = '" . REMOTE_IP . "'");
		$data = $data->fields['session_data'];

		if(!$data) $data = '';

		return $data;
	}

	public static function Write( $id, $data ) {
		//CAELogger::write('Writing to the session...' . $data, 'debug', 'debug');

		DB::Execute(
		"UPDATE ".DB_PREFIX."session SET session_data = ? WHERE `sid` = ? AND `ip_addr` = ?",
		array($data, $id, REMOTE_IP)
		);

		return TRUE;
	}

	public static function Destroy( $id = null) {

		if(is_null($id)) $id = Session::$sid;
		// Build query
		DB::Execute("DELETE FROM `".DB_PREFIX."session` WHERE `sid` = '$id'");

		return TRUE;
	}

	public static function GC() {
		/**
		* Delete ANY session that has expired.
		*/
		DB::Execute("DELETE FROM " . DB_PREFIX . "session WHERE `expires` < ?", array(Time::GetCurrent()));

		// Always return TRUE
		return true;
	}

	/**
	* Saves the userID in the session database for logging in (or out) the user.
	*
	* @param int $uid
	*/
	public static function SetUID($uid){
		DB::Execute(
		"UPDATE `".DB_PREFIX."session`
		SET `uid` = ?
		WHERE `sid` = ? AND `ip_addr` = ?
		LIMIT 1 ;",
		array($uid, Session::Singleton()->_sid, REMOTE_IP)
		);
	}
}

//HookHandler::RegisterHook('libraries_loaded', 'Session::singleton');
?>
