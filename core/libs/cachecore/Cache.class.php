<?php
/**
 * Main loading file for the Caching system.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @package Core
 * @subpackage Cachecore
 * @since 20110610
 */


// I has some dependencies...
define('__CACHE_PDIR', dirname(__FILE__) . '/');
require_once(__CACHE_PDIR . 'backends/cachecore.class.php');

class Cache{
	
	private static $_cachecache;
	
	/**
	 * This points to the system/global Cache object.
	 * 
	 * @var Cache
	 */
	static protected $_Interface = null;
	
	private $_backend = null;
	
	public function __construct($backend = null){
		if(!$backend){
			$cs = ConfigHandler::LoadConfigFile("cache");
			$backend = $cs['type'];
		}
		
		$this->_backend = $backend;
	}
	
	public function get($key, $expires = 7200){
		return $this->_factory($key, $expires)->read();
	}
	
	public function set($key, $value, $expires = 7200){
		$c = $this->_factory($key, $expires);
		
		// Try to create and if that fails try an update.
		if($c->create($value)) return true;
		elseif($c->update($value)) return true;
		else return false;
	}
	
	public function _factory($key, $expires = 7200){
		$obj = false;
		
		switch($this->_backend){
			case 'apc':
				require_once(__CACHE_PDIR . 'backends/cacheapc.class.php');
				$obj = new CacheAPC($key, null, $expires);
				break;
			case 'file':
			default:
				require_once(__CACHE_PDIR . 'backends/cachefile.class.php');
				if(!is_dir(TMP_DIR . 'cache')) mkdir(TMP_DIR . 'cache');
				$obj = new CacheFile($key, TMP_DIR . 'cache', $expires);
				break;
		}
		
		return $obj;
	}
	
	/**
	 * Get the current system DMI based on configuration values.
	 * @return DMI
	 */
	public static function GetSystemCache(){
		if(self::$_Interface !== null) return self::$_Interface;
		
		self::$_Interface = new Cache();
		
		return self::$_Interface;
	}
}

class Cache_Exception extends Exception{
	
}