<?php
/**
 * [PAGE DESCRIPTION HERE]
 *
 * @package Core Plus\Core
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
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

//This is a hack to allow classes to still be available after the page has been rendered.
register_shutdown_function("session_write_close");


class Session implements ISingleton{
	
	/**
	 * The amodel for this given session.
	 * 
	 * @var SessionModel
	 */
	private $_model = null;
  
	private static $_Instance = null;
  
	/**
	 * Get the global instance for the session.
	 * 
	 * @return Session
	 */
	public static function Singleton(){
		if(self::$_Instance === null){
			self::$_Instance = new Session();
			
			// Now I can session_start everything.
			session_start();
			
			// And start a new session.
			$m = self::$_Instance->_getModel();
			// The "updated" flag is explictly required to be set because it also
			// indicates when the last page activity for the user was.
			// This is the main tracking variable for inactivity.
			$m->set('updated', Time::GetCurrentGMT());
			
			// From this point on, the $_SESSION variable is available.
			
			// Anything waiting for the session?
			HookHandler::DispatchHook('session_ready');
		}

		return Session::$_Instance;
	}

	public static function GetInstance(){
		return Session::Singleton();
	}

	public static function Start($save_path, $session_name) {
		// Singleton will handle starting a new session!
		self::Singleton();
	}

	public static function End() {
		// Nothing needs to be done in this function
		// since we used persistent connection.
	}

	public static function Read( $id ) {
		return self::Singleton()->_getModel()->get('data');
	}

	public static function Write( $id, $data ) {
		
		$m = self::Singleton()->_getModel();
		$m->set('data', $data);
		$m->save();
		
		return TRUE;
	}

	public static function Destroy( $id = null) {

		// If "null" is requested, just destroy the system session.
		if($id === null){
			self::Singleton()->_destroy();
			// Purge the session data
			session_destroy();
			// And invalidate the session id.
			session_regenerate_id(true);
		}
		else{
			// Low-level datasets are used here because they have less overhead than
			// the full-blown model system.
			$dataset = new Dataset();
			$dataset->table('session');
			$dataset->where('session_id = ' . $id );

			$dataset->delete();
			$dataset->execute();
		}
		
		return TRUE;
	}

	public static function GC() {
		/**
		 * Delete ANY session that has expired.
		 */
		$ttl = ConfigHandler::Get('/core/session/ttl');
		
		// Low-level datasets are used here because they have less overhead than
		// the full-blown model system.
		$dataset = new Dataset();
		$dataset->table('session');
		$dataset->where('updated < ' . (Time::GetCurrentGMT() - $ttl) );
		
		$dataset->delete();
		
		// Always return TRUE
		return true;
	}
	
	public static function SetUser(User $u){
		$m = self::Singleton()->_getModel();
		
		$m->set('user_id', $u->get('id'));
		$_SESSION['user'] = $u;
	}
  
	private function __construct(){

		// Set the save handlers
		session_set_save_handler(
			array('Session', "Start"),
			array('Session', "End"),
			array('Session', "Read"),
			array('Session', "Write"),
			array('Session', "Destroy"),
			array('Session', "GC")
		);
		
		// Ensure garbage collection is done at some point.
		Session::GC();
	}
	
	private function _getModel(){
		if($this->_model === null){
			$this->_model = new SessionModel(session_id());
			
			// Ensure the data is matched up.
			$this->_model->set('ip_addr', REMOTE_IP);
		}
		
		return $this->_model;
	}
	
	private function _destroy(){
		// Delete the information from the database and purge the cached information.
		if($this->_model){
			$this->_model->delete();
			$this->_model = null;
		}
	}
}
