<?php
/**
 * File: Memcache
 *
 * @package Core\Cache
 *
 */

namespace Core\Cache;

/**
 * Class: Memcache
 */
class Memcache implements CacheInterface {

	private $_key;
	private $_expires;
	private $_gzip;

	private static $_Connection;
	private static $_IsMemcached;

	/**
	 * Construct a new cache object with the given key and the given expiration.
	 *
	 * @param string $key
	 * @param int    $expires
	 */
	public function __construct($key, $expires) {
		// Enable compression, if available
		$this->_gzip = (extension_loaded('zlib'));

		$this->_key = $key;
		$this->_expires = $expires;

		// Check and see if the connection has been initialized.
		// Only do this once to save time.
		if(self::$_Connection === null){
			// Prefer Memcached over Memcache.
			if (class_exists('Memcached')) {
				self::$_Connection = new \Memcached();
				self::$_IsMemcached = true;
			}
			elseif (class_exists('Memcache')) {
				self::$_Connection = new \Memcache();
				self::$_IsMemcached = false;
			}
			else {
				return false;
			}

			if ($this->_gzip && self::$_IsMemcached) {
				self::$_Connection->setOption(\Memcached::OPT_COMPRESSION, true);
			}

			// Process Memcached servers.
			// @todo Make this configurable!
			self::$_Connection->addServer('localhost', 11211);
		}
	}

	/**
	 * Method: create()
	 * 	Creates a new cache.
	 *
	 * @access public
	 * @param mixed $data (Required) The data to cache.
	 * @returns boolean Whether the operation was successful.
	 */
	public function create($data) {

		return self::$_IsMemcached ?
			self::$_Connection->set($this->_key, $data, $this->_expires) :
			self::$_Connection->set($this->_key, $data, $this->_gzip, $this->_expires);
	}

	/**
	 * Method: read()
	 * 	Reads a cache.
	 *
	 * @access public
	 * @returns mixed Either the content of the cache object, or _boolean_ false.
	 */
	public function read() {

		return self::$_IsMemcached ?
			self::$_Connection->get($this->_key) :
			self::$_Connection->get($this->_key, $this->_gzip);
	}

	/**
	 * Method: update()
	 * 	Updates an existing cache.
	 *
	 * @access public
	 * @param $data mixed (Required) The data to cache.
	 * @returns boolean Whether the operation was successful.
	 */
	public function update($data) {
		return self::$_IsMemcached ?
			self::$_Connection->replace($this->_key, $data, $this->_expires) :
			self::$_Connection->replace($this->_key, $data, $this->_gzip, $this->_expires);
	}

	/**
	 * Method: delete()
	 * 	Deletes a cache.
	 *
	 * @access public
	 * @returns boolean Whether the operation was successful.
	 */
	public function delete() {
		return self::$_Connection->delete($this->_key);
	}

	/**
	 * Method: flush()
	 *  Invalidate all items in the cache
	 *
	 * @since 2011.07.28
	 * @access public
	 * @return boolean Whether the operation was successful.
	 */
	public function flush() {
		return self::$_Connection->flush();
	}
	
	public function listKeys(){
		if(self::$_IsMemcached){
			return self::$_Connection->getAllKeys();
		}
		else{
			// @todo Not supported yet.
		
			return [];
		}
	}
}
