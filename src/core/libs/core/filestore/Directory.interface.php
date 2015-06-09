<?php
/**
 * Enter a meaningful file description here!
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130604.1113
 * @package Core\Filestore
 */

namespace Core\Filestore;

/**
 * Interface Directory
 *
 * @package Core\Filestore
 */
interface Directory {
	/**
	 * List the files and directories in this directory and return the
	 * respective file identifier for each file/directory
	 *
	 * @param null|string $extension The extension to look for, (optional)
	 * @param bool        $recursive Set to true to recurse into sub directories and perform the same search.
	 *
	 * @return array
	 */
	public function ls($extension = null, $recursive = false);


	/**
	 * Tells whether a directory exists and is readable
	 *
	 * @link http://php.net/manual/en/function.is-readable.php
	 * @return bool true if the directory exists and is readable, false otherwise.
	 */
	public function isReadable();

	public function isWritable();

	/**
	 * Check and see if this exists and is in-fact a directory.
	 *
	 * @return bool
	 */
	public function exists();

	/**
	 * Create this directory, (has no effect if already exists)
	 * Returns true if successful, null if exists, and false if failure
	 *
	 * @return boolean | null
	 */
	public function mkdir();

	public function rename($newname);

	/**
	 * Get this directory's fully resolved path
	 *
	 * @return string
	 */
	public function getPath();

	/**
	 * Set the path for this directory.
	 *
	 * @param $path
	 *
	 * @return void
	 */
	public function setPath($path);


	/**
	 * Get just the basename of this directory
	 *
	 * @return string
	 */
	public function getBasename();

	/**
	 * Delete a directory and recursively any file inside it.
	 */
	public function delete();

	/**
	 * Delete a directory and recursively any file inside it.
	 *
	 * Alias of delete
	 *
	 * @return mixed
	 */
	public function remove();

	/**
	 * Find and get a directory or file that matches the name provided.
	 *
	 * Will search run down subdirectories if a tree'd path is provided.
	 *
	 * @param string $name
	 * @return null|File|Directory
	 */
	public function get($name);
}