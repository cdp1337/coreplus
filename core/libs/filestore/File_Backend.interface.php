<?php
/**
 * DESCRIPTION
 *
 * @package Core Plus
 * @since 0.1
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
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

interface File_Backend {
	public function __construct($filename = null);

	public function getFilesize($formatted = false);

	public function getMimetype();

	public function getExtension();

	/**
	 * Get a filename that can be retrieved from the web.
	 * Resolves with the ROOT_DIR prefix already attached.
	 *
	 * @return string | false
	 */
	public function getURL();

	/**
	 * Get a serverside-resized thumbnail url for this file.
	 *
	 * @abstract
	 *
	 * @param string $dimensions
	 *
	 * @return mixed
	 */
	public function getPreviewURL($dimensions = "300x300");


	/**
	 * Get the filename of this file resolved to a specific directory, usually ROOT_PDIR or ROOT_WDIR.
	 */
	public function getFilename($prefix = ROOT_PDIR);

	/**
	 * Get the base filename of this file.
	 */
	public function getBaseFilename($withoutext = false);

	/**
	 * Get the filename for a local clone of this file.
	 * For local files, it's the same thing, but remote files will be copied to a temporary local location first.
	 *
	 * @return string
	 */
	public function getLocalFilename();


	/**
	 * Get the hash for this file.
	 */
	public function getHash();

	public function delete();

	public function isImage();

	public function isText();

	/**
	 * Get if this file can be previewed in the web browser.
	 *
	 * @return boolean
	 */
	public function isPreviewable();


	/**
	 * See if this file is in the requested directory.
	 *
	 * @param $path string
	 *
	 * @return boolean
	 */
	public function inDirectory($path);

	public function identicalTo($otherfile);

	/**
	 * Copies the file to the requested destination.
	 * If the destination is a directory (ends with a '/'), the same filename is used, (if possible).
	 * If the destination is relative, ('.' or 'subdir/'), it is assumed relative to the current file.
	 *
	 * @param string  $dest
	 * @param boolean $overwrite
	 *
	 * @return File
	 */
	public function copyTo($dest, $overwrite = false);

	/**
	 * The recipient of the copyTo command, should $dest be this object instead of a filename.
	 */
	public function copyFrom($src, $overwrite = false);

	public function getContents();

	public function putContents($data);

	/**
	 * Get the contents object that can then be manipulated in more detail,
	 * ie: an image can be displayed, compressed files can be uncompressed, etc.
	 *
	 * @return File_Contents
	 */
	public function getContentsObject();

	/**
	 * Check if this file exists on the filesystem currently.
	 *
	 * @return boolean
	 */
	public function exists();
}

