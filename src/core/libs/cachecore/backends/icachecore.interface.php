<?php
/**
 * File: ICacheCore
 * Interface that all storage-specific adapters must adhere to.
 *
 * @version 2011.07.28
 * @copyright 2006-2010 Ryan Parman, Foleeo Inc., and contributors.
 * @license Simplified BSD License - http://opensource.org/licenses/bsd-license.php
 * @package CacheCore
 * 
 * @see AWS SDK for PHP (aka CloudFusion) - http://aws.amazon.com/sdkforphp/
 * @see https://github.com/cdp1337/cachecore
 * @see https://github.com/skyzyx/cachecore
 */


/*%**************************************************************************%*/
// INTERFACE

/**
 * Interface: ICacheCore
 * 	Defines the methods that all implementing classes MUST have. Covers CRUD 
 * (create, read, update, delete) methods, as well as others that are used in 
 * the base CacheCore class.
 */
interface ICacheCore
{

	/**
	 * Method: create()
	 * Creates a new cache. Placeholder method should be defined by the 
	 * implementing class.
	 * 
	 * @access public
	 * @param mixed The data to cache.
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
	 * @param mixed The data to cache.
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
	 * Method: is_expired()
	 * Determines whether a cache has expired or not. Placeholder method should be defined by the implementing class.
	 * 
	 * @access public
	 * @return boolean Whether the cache is expired or not.
	 */
	public function is_expired();

	/**
	 * Method: timestamp()
	 * Retrieves the time stamp of the cache. Placeholder method should be defined by the implementing class.
	 * 
	 * @access public
	 * @return mixed Either the Unix time stamp of the cache creation, or _boolean_ false.
	 */
	public function timestamp();

	/**
	 * Method: reset()
	 * Resets the freshness of the cache. Placeholder method should be defined by the implementing class.
	 * 
	 * @access public
	 * @return boolean Whether the operation was successful.
	 */
	public function reset();
	
	/**
	 * Method: flush()
	 *  Invalidate all items in the cache
	 * 
	 * @since 2011.07.28
	 * @access public
	 * @return boolean Whether the operation was successful.
	 */
	public function flush();
}
