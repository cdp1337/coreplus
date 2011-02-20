<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Cache
 * 
 * @author powellc
 */
class Cache implements ICacheCore{
	
	private $_backend;
	
	private static $_cachecache;
	
	private function __construct($key, $expires = 7200, $gzip = true){
		$this->_backend = self::_Factory($key, $expires, $gzip);
	}
	
	private static function _Factory($key, $expires, $gzip){
		$obj = false;
		
		switch(ConfigHandler::GetValue('/core/cache_backend')){
			case 'apc':
				$obj = new CacheAPC($key, null, $expires, $gzip);
				break;
			case 'file':
				if(!is_dir(TMP_DIR . 'cache')) mkdir(TMP_DIR . 'cache');
				$obj = new CacheFile($key, TMP_DIR . 'cache', $expires, $gzip);
				break;
		}
		
		return $obj;
	}
	
	/**
	 *
	 * @param string $key
	 * @param int $expires
	 * @param boolean $gzip
	 * @return Cache
	 */
	public static function Singleton($key, $expires = 7200, $gzip = true){
		if(!isset(self::$_cachecache[$key])){
			self::$_cachecache[$key] = new self($key, $expires, $gzip);
		}
		
		return self::$_cachecache[$key];
	}
	
	public static function Get($key, $expires = 7200){
		$c = self::Singleton($key, $expires);
		return $c->read();
	}
	
	public static function Set($key, $value, $expires = 7200){
		$c = self::Singleton($key, $expires);
		// Try to create and if that fails try an update.
		if($c->create($value)) return true;
		elseif($c->update($value)) return true;
		else return false;
	}
	
	/**
	 * Creates a new cache.
	 *
	 * @param mixed $data (Required) The data to cache.
	 * @return boolean Whether the operation was successful.
	 */
	public function create($data){
		return $this->_backend->create($data);
	}

	/**
	 * Reads a cache.
	 *
	 * @return mixed Either the content of the cache object, or boolean `false`.
	 */
	public function read(){
		return $this->_backend->read();
	}

	/**
	 * Updates an existing cache.
	 *
	 * @param mixed $data (Required) The data to cache.
	 * @return boolean Whether the operation was successful.
	 */
	public function update($data){
		return $this->_backend->update($data);
	}

	/**
	 * Deletes a cache.
	 *
	 * @return boolean Whether the operation was successful.
	 */
	public function delete(){
		return $this->_backend->delete();
	}

	/**
	 * Checks whether the cache object is expired or not.
	 *
	 * @return boolean Whether the cache is expired or not.
	 */
	public function is_expired(){
		return $this->_backend->is_expired();
	}

	/**
	 * Retrieves the timestamp of the cache.
	 *
	 * @return mixed Either the Unix time stamp of the cache creation, or boolean `false`.
	 */
	public function timestamp(){
		return $this->_backend->timestamp();
	}

	/**
	 * Resets the freshness of the cache.
	 *
	 * @return boolean Whether the operation was successful.
	 */
	public function reset(){
		return $this->_backend->reset();
	}
}

?>
