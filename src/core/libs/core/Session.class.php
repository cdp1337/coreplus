<?php
/**
 * Session system, responsible for saving and retrieving all data to and from the database.
 *
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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
//register_shutdown_function("session_write_close");

namespace Core;

class Session implements \SessionHandlerInterface {

	/**
	 * @var Session
	 */
	public static $Instance;

	/** @var array Any externally set information on this session model. */
	public static $Externals = [];

	private static $_IsReady = false;

	public function __construct(){
		/*if(self::$Instance === null){
			self::$Instance = $this;

			//$session = new Session();
			session_set_save_handler(self::$Instance, true);
			session_start();
		}*/
	}

	/**
	 * PHP >= 5.4.0<br/>
	 * Close the session
	 * @link http://php.net/manual/en/sessionhandlerinterafce.close.php
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 */
	public function close() {
		// Sessions are persistent!
		return true;
	}

	/**
	 * PHP >= 5.4.0<br/>
	 * Initialize session
	 * @link http://php.net/manual/en/sessionhandlerinterafce.open.php
	 *
	 * @param string $save_path The path where to store/retrieve the session.
	 * @param string $session_id The session id.
	 *
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 */
	public function open($save_path, $session_id) {
		// Anything waiting for the session?
		\HookHandler::DispatchHook('/core/session/ready');

		self::$_IsReady = true;

		// Open really doesn't need to do anything, everything that will be controlling the session will be built into Core Plus.
		return true;
	}

	/**
	 * PHP >= 5.4.0<br/>
	 * Destroy a session
	 * @link http://php.net/manual/en/sessionhandlerinterafce.destroy.php
	 *
	 * @param int $session_id The session ID being destroyed.
	 *
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 */
	public function destroy($session_id) {
		// Low-level datasets are used here because they have less overhead than
		// the full-blown model system.
		$dataset = new Datamodel\Dataset();
		$dataset->table('session');
		$dataset->where('session_id = ' . $session_id);
		$dataset->where('ip_addr = ' . REMOTE_IP);

		$dataset->delete();
		$dataset->execute();

		// Blow away the current session data too!
		$_SESSION = null;
		self::$_IsReady = false;

		return true;
	}

	/**
	 * PHP >= 5.4.0<br/>
	 * Read session data
	 * @link http://php.net/manual/en/sessionhandlerinterafce.read.php
	 * @param string $session_id The session id to read data for.
	 * @return string <p>
	 * Returns an encoded string of the read data.
	 * If nothing was read, it must return an empty string.
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 */
	public function read($session_id) {
		$model = self::_GetModel($session_id);

		self::$Externals = $model->getExternalData();
		return $model->getData();
	}

	/**
	 * PHP >= 5.4.0<br/>
	 * Write session data
	 * @link http://php.net/manual/en/sessionhandlerinterafce.write.php
	 * @param string $session_id The session id.
	 * @param string $session_data <p>
	 * The encoded session data. This data is the
	 * result of the PHP internally encoding
	 * the $_SESSION superglobal to a serialized
	 * string and passing it as this parameter.
	 * Please note sessions use an alternative serialization method.
	 * </p>
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 */
	public function write($session_id, $session_data) {
		$model = self::_GetModel($session_id);
		$model->setData($session_data);
		$model->setExternalData(self::$Externals);
		return $model->save();
	}

	/**
	 * PHP >= 5.4.0<br/>
	 * Cleanup old sessions
	 * @link http://php.net/manual/en/sessionhandlerinterafce.gc.php
	 *
	 * @param int $maxlifetime <p>
	 * Sessions that have not updated for
	 * the last maxlifetime seconds will be removed.
	 * </p>
	 *
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 */
	public function gc($maxlifetime) {
		return self::CleanupExpired();
	}

	/**
	 * Set the current session to be owned by the given user,
	 * effectively logging the user in.
	 *
	 * Drop support for the User class in favour of UserModel after pre-2.8.x is no longer supported.
	 *
	 * @param \UserModel $u
	 */
	public static function SetUser($u) {
		$model = self::_GetModel(session_id());

		$model->set('user_id', $u->get('id'));
		$model->save();

		// If this user is currently SUDO, then set the SUDO'd user and NOT the actual user.
		if(isset($_SESSION['user_sudo'])){
			Session::Set('user_sudo', $u);
		}
		else{
			Session::Set('user', $u);
		}
	}

	/**
	 * Shortcut static function to call that will destroy the current session and logout the user.
	 *
	 */
	public static function DestroySession(){
		if(self::$Instance !== null){
			self::$Instance->destroy(session_id());
		}
	}

	/**
	 * Force the saving of the contents of $_SESSION back to the database.
	 */
	public static function ForceSave(){
		/*$session = self::$Instance;
		if($session){
			$session->write(session_id(), serialize($_SESSION));
		}*/
	}

	/**
	 * Cleanup any expired sessions from the database.
	 *
	 * @return bool Always returns true :)
	 */
	public static function CleanupExpired(){
		static $lastexecuted = 0;

		/**
		 * Delete ANY session that has expired.
		 */
		$ttl = \ConfigHandler::Get('/core/session/ttl');
		$datetime = (\Time::GetCurrentGMT() - $ttl);

		if($lastexecuted == $datetime){
			// This operation was already called this second.  No need to do it again.
			// This is used because if a LOT of user operations occur on a given page,
			// this method may be called many many many times.
			return true;
		}

		// Low-level datasets are used here because they have less overhead than
		// the full-blown model system.
		$dataset = new Datamodel\Dataset();
		$dataset->table('session');
		$dataset->where('updated < ' . $datetime);
		$dataset->delete()->execute();

		$lastexecuted = $datetime;

		// Always return TRUE
		return true;
	}

	/**
	 * Get the value of a key from the session or $default if that key is explicitly not set.
	 *
	 * If the key "example/*" is provided, then all subkeys under the example array are returned.
	 * If no keys exist, an empty array is returned.
	 *
	 * @param string     $key
	 * @param null|mixed $default
	 *
	 * @return mixed
	 */
	public static function Get($key, $default = null){
		if(strpos($key, '/*') !== false){
			// If a group of keys are selected, the default is forced to be an empty array.
			// This ensures that an array is returned even if there are no COOKIEs.
			$default = [];
		}

		if(sizeof($_COOKIE) == 0){
			// If no cookie is set, then don't try to look anything up!
			// The act of looking up a variable will create the session, which sets the cookie value!
			// This doesn't help caching servers very much.
			return $default;
		}

		self::_GetInstance();

		if(strpos($key, '/*') !== false){
			// User requested to retrieve a set of keys, retrieve anything contained in this subarray
			$sub = substr($key, 0, strpos($key, '/*'));
			foreach($_SESSION as $k => $v){
				if($k == $sub){
					return $_SESSION[$k];
				}
			}
		}

		return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
	}

	/**
	 * Set the value of a key to some value.
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public static function Set($key, $value){
		self::_GetInstance();

		if(strpos($key, '/') !== false){
			// User requested to retrieve a set of keys, retrieve anything contained in this subarray
			$sub = substr($key, 0, strpos($key, '/'));
			$spr = substr($key, strlen($sub) + 1);
			if(!isset($_SESSION[$sub])){
				$_SESSION[$sub] = [];
			}
			$_SESSION[$sub][$spr] = $value;
		}
		else{
			$_SESSION[$key] = $value;
		}
	}

	/**
	 * Explictly unset a key from the session.
	 *
	 * @param $key string
	 */
	public static function UnsetKey($key){
		if(sizeof($_COOKIE) == 0){
			// If no cookie is set, then don't try to look anything up!
			// The act of looking up a variable will create the session, which sets the cookie value!
			// This doesn't help caching servers very much.
			return;
		}

		self::_GetInstance();

		if($key === '*'){
			// If the user requested to purge all the session data.
			$_SESSION = [];
		}
		elseif(strpos($key, '/*') !== false){
			// User requested to remove a set of keys, remove anything starting with this.
			$sub = substr($key, 0, strpos($key, '/*'));
			foreach($_SESSION as $k => $v){
				if($k == $sub){
					unset($_SESSION[$k]);
				}
			}
		}
		elseif(isset($_SESSION[$key])){
			unset($_SESSION[$key]);
		}
	}

	/**
	 * @return Session
	 */
	private static function _GetInstance(){
		if(self::$Instance === null){
			ini_set('session.hash_bits_per_character', 5);
			ini_set('session.hash_function', 1);
			if(!defined('SESSION_COOKIE_NAME')){
				define('SESSION_COOKIE_NAME', 'CorePlusSession');
			}
			session_name(SESSION_COOKIE_NAME);

			// Allow a config-set cookie domain.  This is required for xsite sessions in multimode.
			if(defined('SESSION_COOKIE_DOMAIN') && SESSION_COOKIE_DOMAIN){
				// A valid session name is required for xsite sessions to work. (not sure why)
				session_set_cookie_params(0, '/', SESSION_COOKIE_DOMAIN);
			}

			self::$Instance = new Session();

			//$session = new Session();
			session_set_save_handler(self::$Instance, true);
			session_start();
		}

		return self::$Instance;
	}


	/**
	 * Get the Model for this current session.
	 * This method will NOT cache the results of the model.  This is due to race conditions at some point...
	 *
	 * @param string $session_id The session id to read the model for.
	 * @return \SessionModel
	 */
	private static function _GetModel($session_id) {
		$model = new \SessionModel($session_id);

		// Ensure the data is matched up.
		$model->set('ip_addr', REMOTE_IP);

		return $model;
	}
}

