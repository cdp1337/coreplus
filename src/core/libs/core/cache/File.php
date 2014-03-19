<?php
/**
 * File: CacheFile
 *
 * @package Core\Cache
 *
 */

namespace Core\Cache;

/**
 * Class: CacheFile
 */
class File implements CacheInterface {

	private $_key;
	private $_expires;
	private $_dir;
	private $_file;
	private $_gzip;

	/**
	 * Construct a new cache object with the given key and the given expiration.
	 *
	 * @param string $key
	 * @param int    $expires
	 */
	public function __construct($key, $expires) {
		$this->_key = $key;
		$this->_expires = $expires;

		$this->_dir = TMP_DIR . 'cache/';
		// @todo Sanitize the incoming key name!
		$this->_file = TMP_DIR . 'cache/' . $key . '.cache';

		$this->_gzip = (extension_loaded('zlib'));
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
		if (file_exists($this->_file)) {
			return false;
		}
		elseif (file_exists($this->_dir) && is_writeable($this->_dir)) {
			$data = serialize($data);
			$data = $this->_gzip ? gzcompress($data) : $data;

			return (bool) file_put_contents($this->_file, $data);
		}

		return false;
	}

	/**
	 * Method: read()
	 * 	Reads a cache.
	 *
	 * @access public
	 * @returns mixed Either the content of the cache object, or _boolean_ false.
	 */
	public function read() {
		if(!file_exists($this->_file)){
			return false;
		}
		elseif(!is_readable($this->_file)){
			return false;
		}
		elseif($this->is_expired()){
			return false;
		}
		else{
			$data = file_get_contents($this->_file);
			$data = $this->_gzip ? gzuncompress($data) : $data;
			$data = unserialize($data);

			if ($data === false) {
				/*
					This should only happen when someone changes the gzip settings and there is
					existing data or someone has been mucking about in the cache folder manually.
					Delete the bad entry since the file cache doesn't clean up after itself and
					then return false so fresh data will be retrieved.
				 */
				$this->delete();
				return false;
			}

			return $data;
		}
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
		if (file_exists($this->_file) && is_writeable($this->_file)) {
			$data = serialize($data);
			$data = $this->_gzip ? gzcompress($data) : $data;

			return (bool) file_put_contents($this->_file, $data);
		}

		return false;
	}

	/**
	 * Method: delete()
	 * 	Deletes a cache.
	 *
	 * @access public
	 * @returns boolean Whether the operation was successful.
	 */
	public function delete() {
		if (file_exists($this->_file)) {
			return unlink($this->_file);
		}

		return false;
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
		$dir = opendir($this->_dir);
		if(!$dir){
			return true;
		}
		while(($file = readdir($dir)) !== false){
			if($file == '.' || $file == '..') continue;

			unlink($this->_dir . $file);
		}
		closedir($dir);

		return true;
	}

	/**
	 * Method: is_expired()
	 * 	Checks whether the cache object is expired or not.
	 *
	 * @returns boolean Whether the cache is expired or not.
	 */
	private function is_expired() {
		clearstatcache();

		if(filemtime($this->_file) + $this->_expires < time()){
			return true;
		}
		else{
			return false;
		}
	}
}
