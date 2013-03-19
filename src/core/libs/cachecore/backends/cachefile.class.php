<?php
/**
 * File: CacheFile
 * 	File-based caching class.
 *
 * @version 2011.07.28
 * @copyright 2006-2010 Ryan Parman, Foleeo Inc., and contributors.
 * @license Simplified BSD License - http://opensource.org/licenses/bsd-license.php
 * @package CacheCore
 * 
 * @see AWS SDK for PHP - http://aws.amazon.com/sdkforphp/
 */


/*%**************************************************************************%*/
// CLASS

/**
 * Class: CacheFile
 * Container for all file-based cache methods. Inherits additional methods
 * from CacheCore. Adheres to the ICacheCore interface.
 */
class CacheFile extends CacheCore implements ICacheCore
{

	/*%**********************************************************************%*/
	// CONSTRUCTOR

	/**
	 * Method: __construct()
	 * 	The constructor
	 *
	 * @access public
	 *
	 * @param $name - _string_ (Required) A name to uniquely identify the cache object.
	 * @param $location - _string_ (Required) The location to store the cache object in. This may vary by cache method.
	 * @param $expires - _integer_ (Required) The number of seconds until a cache object is considered stale.
	 * @param $gzip - _boolean_ (Optional) Whether data should be gzipped before being stored. Defaults to true.
	 *
	 * @returns _object_ Reference to the cache object.
	 */
	public function __construct($name, $location, $expires, $gzip = true)
	{
		parent::__construct($name, $location, $expires, $gzip);
		$this->id = $this->location . '/' . $this->name . '.cache';
	}

	/**
	 * Method: create()
	 * 	Creates a new cache.
	 *
	 * @access public
	 * @param $data mixed (Required) The data to cache.
	 * @returns _boolean_ Whether the operation was successful.
	 */
	public function create($data)
	{
		if (file_exists($this->id))
		{
			return false;
		}
		elseif (file_exists($this->location) && is_writeable($this->location))
		{
			$data = serialize($data);
			$data = $this->gzip ? gzcompress($data) : $data;

			return (bool) file_put_contents($this->id, $data);
		}

		return false;
	}

	/**
	 * Method: read()
	 * 	Reads a cache.
	 *
	 * @access public
	 * @returns _mixed_ Either the content of the cache object, or _boolean_ false.
	 */
	public function read()
	{
		if (file_exists($this->id) && is_readable($this->id))
		{
			$data = file_get_contents($this->id);
			$data = $this->gzip ? gzuncompress($data) : $data;
			$data = unserialize($data);

			if ($data === false)
			{
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

		return false;
	}

	/**
	 * Method: update()
	 * 	Updates an existing cache.
	 *
	 * @access public
	 * @param $data mixed (Required) The data to cache.
	 * @returns _boolean_ Whether the operation was successful.
	 */
	public function update($data)
	{
		if (file_exists($this->id) && is_writeable($this->id))
		{
			$data = serialize($data);
			$data = $this->gzip ? gzcompress($data) : $data;

			return (bool) file_put_contents($this->id, $data);
		}

		return false;
	}

	/**
	 * Method: delete()
	 * 	Deletes a cache.
	 *
	 * @access public
	 * @returns _boolean_ Whether the operation was successful.
	 */
	public function delete()
	{
		if (file_exists($this->id))
		{
			return unlink($this->id);
		}

		return false;
	}

	/**
	 * Method: timestamp()
	 * 	Retrieves the timestamp of the cache.
	 *
	 * @access public
	 * @returns _mixed_ Either the Unix timestamp of the cache creation, or _boolean_ false.
	 */
	public function timestamp()
	{
		clearstatcache();

		if (file_exists($this->id))
		{
			$this->timestamp = filemtime($this->id);
			return $this->timestamp;
		}

		return false;
	}

	/**
	 * Method: reset()
	 * 	Resets the freshness of the cache.
	 *
	 * @access public
	 * @returns _boolean_ Whether the operation was successful.
	 */
	public function reset()
	{
		if (file_exists($this->id))
		{
			return touch($this->id);
		}

		return false;
	}

	/**
	 * Method: is_expired()
	 * 	Checks whether the cache object is expired or not.
	 *
	 * @access public
	 * @returns _boolean_ Whether the cache is expired or not.
	 */
	public function is_expired()
	{
		if ($this->timestamp() + $this->expires < time())
		{
			return true;
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
	public function flush()
	{
		// flush is not supported on filesystem-based caching.
		return false;
	}
}
