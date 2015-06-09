<?php
/**
 * Main loading file for the Caching system.
 * 
 * @package Core
 * @since 1.9
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

namespace Core;


define('__CACHE_PDIR', ROOT_PDIR . 'core/libs/core/cache/');

/**
 * Class Cache
 * This is the main public interface that will be used by external scripts.
 *
 * @package Core
 */
class Cache {
	/**
	 * Cache of keyed data used for get/set.
	 *
	 * Instead of calling a new cache object every time and loading its data.... every time
	 * this will contain a cache of the key's data.
	 *
	 * @var array
	 */
	private static $_KeyCache = array();

	/**
	 * @var null|string The backend type
	 */
	private static $_Backend = null;

	/**
	 * Get a cached value based on its key
	 *
	 * @param string $key
	 * @param int    $expires
	 *
	 * @return mixed
	 */
	public static function Get($key, $expires = 7200){
		$obj = self::_Factory($key, $expires);
		return $obj->read();
	}

	/**
	 * Set a cached value on a given key
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @param int    $expires
	 *
	 * @return bool
	 */
	public static function Set($key, $value, $expires = 7200){
		$obj = self::_Factory($key, $expires);

		// Try to create and if that fails try an update.
		if($obj->create($value)){
			return true;
		}
		elseif($obj->update($value)){
			return true;
		}
		else{
			return false;
		}
	}

	/**
	 * Delete a given key from the system cache
	 *
	 * @param $key
	 *
	 * @return bool
	 */
	public static function Delete($key){
		return self::_Factory($key)->delete();
	}

	/**
	 * Flush the entire system cache
	 *
	 * @return bool
	 */
	public static function Flush(){
		$s = self::_Factory('FLUSH')->flush();
		self::$_KeyCache = array();
		return $s;
	}

	/**
	 * @param     $key
	 * @param int $expires
	 *
	 * @return Cache\CacheInterface
	 */
	private static function _Factory($key, $expires = 7200){

		if(self::$_Backend === null){
			// Load the backend from the site configuration.xml
			$cs = \ConfigHandler::LoadConfigFile("configuration");
			self::$_Backend = $cs['cache_type'];
		}

		if(isset(self::$_KeyCache[$key])){
			return self::$_KeyCache[$key];
		}
		
		switch(self::$_Backend){
			case 'apc':
				if(!class_exists('CacheAPC')){
					require_once(__CACHE_PDIR . 'backends/cacheapc.class.php'); ##SKIPCOMPILER
				}
				$obj = new CacheAPC($key, null, $expires);
				break;
			case 'memcache':
			case 'memcached':
				if(!class_exists('Core\Cache\Memcache')){
					require_once(__CACHE_PDIR . 'Memcache.php'); ##SKIPCOMPILER
				}
				$obj = new Cache\Memcache($key, $expires);
				break;
			case 'file':
			default:
				if(!class_exists('Core\Cache\File')){
					require_once(__CACHE_PDIR . 'File.php'); ##SKIPCOMPILER
				}
				if(!is_dir(TMP_DIR . 'cache')){
					mkdir(TMP_DIR . 'cache');
				}

				$obj = new Cache\File($key, $expires);
				break;
		}

		self::$_KeyCache[$key] = $obj;
		return $obj;
	}
}
