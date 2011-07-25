<?php
/**
 * // enter a good description here
 * 
 * @package Core
 * @since 2011.06
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Lesser General Public License v3 <http://www.gnu.org/licenses/lgpl-3.0.html>
 * This system is licensed under the GNU LGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/lgpl-3.0.html>, 
 * and please contribute back to the community :)
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
		}
		else{
			// Low-level datasets are used here because they have less overhead than
			// the full-blown model system.
			$dataset = new Dataset();
			$dataset->table('session');
			$dataset->where('session_id = ' . $id );

			$dataset->delete();
		}
		
		return TRUE;
	}

	public static function GC() {
		/**
		 * Delete ANY session that has expired.
		 */
		$ttl = ConfigHandler::GetValue('/core/session/ttl');
		
		// Low-level datasets are used here because they have less overhead than
		// the full-blown model system.
		$dataset = new Dataset();
		$dataset->table('session');
		$dataset->where('updated < ' . (Time::GetCurrentGMT() - $ttl) );
		
		$dataset->delete();
		
		// Always return TRUE
		return true;
	}
	
	public static function SetUser(UserModel $u){
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
