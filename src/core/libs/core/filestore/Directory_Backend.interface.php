<?php
/**
 * DESCRIPTION
 *
 * @package Core
 * @since 0.1
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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

interface Directory_Backend {
	public function __construct($directory);

	/**
	 * List the files and directories in this directory and return the
	 * respective file identifier for each file/directory
	 *
	 * @return array
	 */
	public function ls();

	/**
	 * Tells whether a directory exists and is readable
	 *
	 * @link http://php.net/manual/en/function.is-readable.php
	 * @return bool true if the directory exists and is readable, false otherwise.
	 */
	public function isReadable();

	public function isWritable();

	/**
	 * Create this directory, (has no effect if already exists)
	 * Returns true if successful, null if exists, and false if failure
	 *
	 * @return boolean | null
	 */
	public function mkdir();

	/**
	 * Rename this directory
	 *
	 * @param $newname
	 *
	 * @return boolean
	 */
	public function rename($newname);

	/**
	 * Get this directory's fully resolved path
	 *
	 * @return string
	 */
	public function getPath();

	/**
	 * Get just the basename of this directory
	 *
	 * @return string
	 */
	public function getBasename();

	/**
	 * Remove a directory and recursively any file inside it.
	 */
	public function remove();

	/**
	 * Find and get a directory or file that matches the name provided.
	 *
	 * Will search run down subdirectories if a tree'd path is provided.
	 *
	 * @param string $name
	 */
	public function get($name);
}
