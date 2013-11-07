<?php
/**
 * Main loading file for the Caching system.
 * 
 * @package Core Plus\Core
 * @since 1.9
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2013  Charlie Powell
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


// I has some dependencies...
//define('__CACHE_PDIR', dirname(__FILE__) . '/');
define('__CACHE_PDIR', ROOT_PDIR . 'core/libs/cachecore/');
if(!class_exists('CacheCore')){
	require_once(__CACHE_PDIR . 'backends/cachecore.class.php'); #SKIPCOMPILER
}


class Cache{
	
	private static $_cachecache;
	
	/**
	 * This points to the system/global Cache object.
	 * 
	 * @var Cache
	 */
	static protected $_Interface = null;
	
	private $_backend = null;

	/**
	 * Cache of keyed data used for get/set.
	 *
	 * Instead of calling a new cache object every time and loading its data.... every time
	 * this will contain a cache of the key's data.
	 *
	 * @var array
	 */
	private static $_KeyCache = array();
	
	public function __construct($backend = null){
		if(!$backend){
			$cs = ConfigHandler::LoadConfigFile("configuration");
			$backend = $cs['cache_type'];
		}
		
		$this->_backend = $backend;
	}

	/**
	 * Get a cached key
	 *
	 * @param     $key
	 * @param int $expires
	 *
	 * @return bool|mixed|string
	 * @throws Exception
	 */
	public function get($key, $expires = 7200){
		if(!isset($this)){
			throw new Exception('Cannot call Cache::get() statically, please use Core::Cache()->get() instead.');
		}

		if(!isset(self::$_KeyCache[$key])){
			self::$_KeyCache[$key] = $this->_factory($key, $expires)->read();
		}
		return self::$_KeyCache[$key];
	}
	
	public function set($key, $value, $expires = 7200){
		if(!isset($this)) throw new Exception('Cannot call Cache::set() statically, please use Core::Cache()->set() instead.');
		$c = $this->_factory($key, $expires);

		self::$_KeyCache[$key] = $value;

		// Try to create and if that fails try an update.
		if($c->create($value)) return true;
		elseif($c->update($value)) return true;
		else return false;
	}
	
	public function delete($key){
		return $this->_factory($key)->delete();
	}
	
	public function flush(){
		self::$_KeyCache = array();
		return $this->_factory(null)->flush();
	}
	
	public function _factory($key, $expires = 7200){
		$obj = false;
		
		switch($this->_backend){
			case 'apc':
				if(!class_exists('CacheAPC')) require_once(__CACHE_PDIR . 'backends/cacheapc.class.php');
				$obj = new CacheAPC($key, null, $expires);
				break;
			case 'file':
			default:
			if(!class_exists('CacheFile')) require_once(__CACHE_PDIR . 'backends/cachefile.class.php');
				if(!is_dir(TMP_DIR . 'cache')) mkdir(TMP_DIR . 'cache');
				$obj = new CacheFile($key, TMP_DIR . 'cache', $expires);
				break;
		}
		
		return $obj;
	}
	
	/**
	 * Get the current system Cache based on configuration values.
	 * @return Cache
	 */
	public static function GetSystemCache(){
		if(self::$_Interface !== null) return self::$_Interface;
		
		self::$_Interface = new Cache();
		
		return self::$_Interface;
	}
}

class Cache_Exception extends Exception{
	
}
