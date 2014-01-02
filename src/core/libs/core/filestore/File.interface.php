<?php
/**
 * File interface defining the structure of all files.
 *
 * @package   Core\Filestore
 * @since     2.5.6
 * @author    Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2014  Charlie Powell
 * @license   GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
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

namespace Core\Filestore;

interface File {

	/**
	 * Images, css, js, and other asset resources that get installed.
	 *
	 * @var string
	 */
	const TYPE_ASSET = 'asset';

	/**
	 * User-submitted content that is publicly available.
	 *
	 * @var string
	 */
	const TYPE_PUBLIC = 'public';

	/**
	 * User-submitted content that is protected against direct public access.
	 *
	 * @var string
	 */
	const TYPE_PRIVATE = 'private';

	/**
	 * Temporary files and cache files meant for system use.
	 *
	 * @var string
	 */
	const TYPE_TMP = 'tmp';

	/**
	 * Any standard file or remote resource.
	 *
	 * @var string
	 */
	const TYPE_OTHER = 'other';

	/**
	 * Get the filesize of this file object, as either raw bytes or a formatted string.
	 *
	 * @param bool $formatted
	 *
	 * @return string|int
	 */
	public function getFilesize($formatted = false);

	/**
	 * Get the mimetype of this file.
	 *
	 * @return string
	 */
	public function getMimetype();

	/**
	 * Get the extension of this file, (without the ".")
	 *
	 * @return string
	 */
	public function getExtension();

	/**
	 * Get a filename that can be retrieved from the web.
	 * Resolves with the ROOT_DIR prefix already attached.
	 *
	 * @return string|boolean
	 */
	public function getURL();

	/**
	 * Get a serverside-resized thumbnail url for this file.
	 *
	 * @param string $dimensions
	 *
	 * @return string
	 */
	public function getPreviewURL($dimensions = "300x300");


	/**
	 * Get the filename of this file resolved to a specific directory, usually ROOT_PDIR or ROOT_WDIR.
	 */
	public function getFilename($prefix = \ROOT_PDIR);

	/**
	 * Set the filename of this file manually.
	 * Useful for operating on a file after construction.
	 *
	 * @param $filename string
	 */
	public function setFilename($filename);

	/**
	 * Get the base filename of this file.
	 *
	 * @param boolean $withoutext Set to true to drop the extension.
	 *
	 * @return string
	 */
	public function getBasename($withoutext = false);

	/**
	 * Get the base filename of this file.
	 *
	 * Alias of getBasename
	 *
	 * @param boolean $withoutext Set to true to drop the extension.
	 *
	 * @return string
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
	 * This is generally an MD5 sum of the file contents.
	 *
	 * @return string
	 */
	public function getHash();

	/**
	 * Get an ascii hash of the filename.
	 * useful for transposing this file to another page call.
	 *
	 * @return string The encoded string
	 */
	public function getFilenameHash();

	/**
	 * Delete this file from the filesystem.
	 *
	 * @return boolean
	 */
	public function delete();

	/**
	 * Rename this file to a new name
	 *
	 * @param $newname
	 *
	 * @return boolean
	 */
	public function rename($newname);

	/**
	 * Shortcut function to see if this file's mimetype is image/*
	 *
	 * @return boolean
	 */
	public function isImage();

	/**
	 * Shortcut function to see if this file's mimetype is text/*
	 *
	 * @return boolean
	 */
	public function isText();

	/**
	 * Get if this file can be previewed in the web browser.
	 *
	 * @return boolean
	 */
	public function isPreviewable();

	/**
	 * Display a preview of this file to the browser.  Must be an image.
	 *
	 * @param string|int $dimensions    A string of the dimensions to create the image at, widthxheight.
	 *                                  Also supports the previous version of simply "dimension", as an int.
	 * @param boolean    $includeHeader Include the correct mimetype header or no.
	 */
	public function displayPreview($dimensions = "300x300", $includeHeader = true);

	/**
	 * Get the mimetype icon for this file.
	 *
	 * @param string $dimensions
	 *
	 * @return string
	 */
	public function getMimetypeIconURL($dimensions = '32x32');

	/**
	 * Get the preview file object without actually populating the sources.
	 * This is useful for checking to see if the file exists before resizing it over.
	 *
	 * WARNING, this will NOT check if the file exists and/or copy data over!
	 *
	 * @param string $dimensions
	 *
	 * @return File
	 */
	public function getQuickPreviewFile($dimensions = '300x300');

	/**
	 * Get the preview file with the contents copied over resized/previewed.
	 *
	 * @param string $dimensions
	 *
	 * @return File
	 */
	public function getPreviewFile($dimensions = '300x300');

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
	 * Make a copy of a source File into this File.
	 *
	 * (Generally only useful internally)
	 *
	 * @param File $src       Source file backend
	 * @param bool $overwrite true to overwrite existing file
	 *
	 * @throws \Exception
	 * @return bool True or False if succeeded.
	 */
	public function copyFrom(File $src, $overwrite = false);

	/**
	 * Get the raw contents of this file
	 *
	 * Essentially file_get_contents()
	 *
	 * @return mixed
	 */
	public function getContents();

	/**
	 * Write the raw contents of this file
	 *
	 * Essentially file_put_contents()
	 *
	 * @param mixed $data
	 *
	 * @return boolean
	 */
	public function putContents($data);

	/**
	 * Get the contents object that can then be manipulated in more detail,
	 * ie: an image can be displayed, compressed files can be uncompressed, etc.
	 *
	 * @return Contents
	 */
	public function getContentsObject();

	/**
	 * Check if this file exists on the filesystem currently.
	 *
	 * @return boolean
	 */
	public function exists();

	/**
	 * Check if this file is readable.
	 *
	 * @return boolean
	 */
	public function isReadable();

	/**
	 * Check if this file is writable.
	 *
	 * @return boolean
	 */
	public function isWritable();

	/**
	 * Get the modified time for this file as a unix timestamp.
	 *
	 * @return int
	 */
	public function getMTime();
}

