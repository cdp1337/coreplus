<?php
/**
 * File: CacheInterface
 * Interface that all storage-specific adapters must adhere to.
 *
 * @version 2011.07.28
 * @copyright 2006-2010 Ryan Parman, Foleeo Inc., and contributors.
 * @license Simplified BSD License - http://opensource.org/licenses/bsd-license.php
 * @package Core\Cache
 * 
 * @see AWS SDK for PHP (aka CloudFusion) - http://aws.amazon.com/sdkforphp/
 * @see https://github.com/cdp1337/cachecore
 * @see https://github.com/skyzyx/cachecore
 */

namespace Core\Cache;


/**
 * Interface: CacheInterface
 * 	Defines the methods that all implementing classes MUST have. Covers CRUD 
 * (create, read, update, delete) methods, as well as others that are used in 
 * the base CacheCore class.
 */
interface CacheInterface {

	/**
	 * Construct a new cache object with the given key and the given expiration.
	 *
	 * @param string $key
	 * @param int    $expires
	 */
	public function __construct($key, $expires);

	/**
	 * Method: create()
	 * Creates a new cache. Placeholder method should be defined by the 
	 * implementing class.
	 * 
	 * @access public
	 * @param mixed $data The data to cache.
	 * @return boolean Whether the operation was successful.
	 */
	public function create($data);

	/**
	 * Method: read()
	 * Reads a cache. Placeholder method should be defined by the implementing class.
	 * 
	 * @access public
	 * @return mixed Either the content of the cache object, or _boolean_ false.
	 */
	public function read();

	/**
	 * Method: update()
	 * Updates an existing cache. Placeholder method should be defined by the implementing class.
	 * 
	 * @access public
	 * @param mixed $data The data to cache.
	 * @return boolean Whether the operation was successful.
	 */
	public function update($data);

	/**
	 * Method: delete()
	 * Deletes a cache. Placeholder method should be defined by the implementing class.
	 * 
	 * @access public
	 * @return boolean Whether the operation was successful.
	 */
	public function delete();
	
	/**
	 * Method: flush()
	 *  Invalidate all items in the cache
	 * 
	 * @since 2011.07.28
	 * @access public
	 * @return boolean Whether the operation was successful.
	 */
	public function flush();
	
	/**
	 * Return a list of all keys on this backend, not supported by all processors!
	 * 
	 * @since 2017.04
	 * @return array
	 */
	public function listKeys();
}
