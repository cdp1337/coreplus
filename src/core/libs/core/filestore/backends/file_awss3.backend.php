<?php
/**
 * DESCRIPTION
 *
 * @package Core\Filestore
 * @since 2.5.6
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

namespace Core\Filestore\Backends;

use Core\Filestore;

class File_awss3_backend implements File {
	/**
	 * @var AmazonS3
	 */
	private $_backend;

	public $filename;

	public $bucket;

	public $acl = AmazonS3::ACL_PUBLIC;

	public $storage = AmazonS3::STORAGE_STANDARD;

	private $_metadata = null;

	public function __construct($filename = null, $bucket = null) {
		$this->_backend = new AmazonS3();
		$this->filename = $filename;
		$this->bucket   = $bucket;
	}

	private function _getMetadata() {
		if ($this->_metadata === null) {
			// Will act as a minor buffer, since this contains pretty much all information about an object.
			$this->_metadata = $this->_backend->get_object_metadata($this->bucket, $this->filename);
		}
		return $this->_metadata;
	}

	public function getFilesize($formatted = false) {
		$d = $this->_getMetadata();
		if (!$d) return null;

		$f = $d['Size'];
		return ($formatted) ? Core::FormatSize($f, 2) : $f;
	}

	public function getMimetype() {
		$d = $this->_getMetadata();
		if (!$d) return null;

		return $d['ContentType'];
	}

	public function getExtension() {
		// Surprisingly this one is the same...
		return File::GetExtensionFromString($this->filename);
	}


	/**
	 * Get a filename that can be retrieved from the web.
	 * Resolves with the ROOT_DIR prefix already attached.
	 *
	 * @return string | false
	 */
	public function getURL() {

		return $this->_backend->get_object_url($this->bucket, $this->filename);
	}

	/**
	 * Get a serverside-resized thumbnail url for this file.
	 *
	 * @abstract
	 *
	 * @param string $dimensions
	 *
	 * @return mixed
	 */
	public function getPreviewURL($dimensions = "300x300"){
		// @todo Implement this...
		return $this->getURL();
	}

	/**
	 * Get the filename of this file resolved to a specific directory, usually ROOT_PDIR or ROOT_WDIR.
	 */
	public function getFilename($prefix = ROOT_PDIR) {
		if ($prefix == ROOT_PDIR) return $this->filename;

		return preg_replace('/^' . str_replace('/', '\\/', ROOT_PDIR) . '(.*)/', $prefix . '$1', $this->filename);
	}

	/**
	 * Get the base filename of this file.
	 */
	public function getBaseFilename($withoutext = false) {
		$b = basename($this->filename);
		if ($withoutext) {
			return substr($b, 0, (-1 - strlen($this->getExtension())));
		}
		else {
			return $b;
		}
	}

	/**
	 * Get the filename for a local clone of this file.
	 * For local files, it's the same thing, but remote files will be copied to a temporary local location first.
	 *
	 * @return string
	 */
	public function getLocalFilename() {
		return $this->_getTmpLocal()->getFilename();
	}

	/**
	 * Get the hash for this file.
	 */
	public function getHash() {
		$d = $this->_getMetadata();
		if (!$d) return null;

		return str_replace('"', '', $d['ETag']);
	}

	public function delete() {
		// Delete the object in the bucket.
		$this->_backend->delete_object($this->bucket, $this->filename);

		// Blank out the data stored internally.
		$this->_metadata = false;
		$this->filename  = null;

		return true;
	}

	/**
	 * Copies the file to the requested destination.
	 * If the destination is a directory (ends with a '/'), the same filename is used, (if possible).
	 * If the destination is relative, ('.' or 'subdir/'), it is assumed relative to the current file.
	 *
	 * @param string|File $dest
	 * @param boolean     $overwrite
	 *
	 * @return File
	 */
	public function copyTo($dest, $overwrite = false) {
		//echo "Copying " . $this->filename . " to " . $dest . "\n"; // DEBUG //

		if (is_a($dest, 'File') || $dest instanceof File) {
			// Don't need to do anything! The object either is a File
			// Or is an implmentation of the File interface.
		}
		else {
			// Well it should be damnit!....
			$file = $dest;

			// Get the location of the destination, be it relative or absolute.
			// If the file does not start with a "/", assume it's relative to this current file.
			if ($file{0} != '/') {
				$file = dirname($this->filename) . '/' . $file;
			}

			// Is the destination a directory or filename?
			// If it's a directory just tack on this current file's basename.
			if (substr($file, -1) == '/') {
				$file .= $this->getBaseFilename();
			}

			// Now dest can be instantiated as a valid file object!
			$dest = new File_awss3_backend($file);
		}

		if ($this->identicalTo($dest)) return $this;

		// GO!
		// The receiving function's logic will handle the rest.
		$dest->copyFrom($this, $overwrite);

		return $dest;


		// Now I can ensure that $dest is an absolutely positioned filename of an actual file.
		if (!is_dir(dirname($dest))) {
			// PHP doesn't support the '-p' argument... :(
			exec('mkdir -p "' . dirname($dest) . '"');
		}
	}

	public function copyFrom($src, $overwrite = false) {

		// And do the actual copy!
		$this->putContents($src->getContents(), $src->getMimetype());
		return;
		var_dump($src, $this);
		die();
		// Don't overwrite existing files unless told otherwise...
		if (!$overwrite) {
			$c    = 0;
			$ext  = $this->getExtension();
			$base = $this->getBaseFilename(true);
			$dir  = dirname($this->filename);

			$f = $dir . '/' . $base . '.' . $ext;
			while (file_exists($f)) {
				$f = $dir . '/' . $base . ' (' . ++$c . ')' . '.' . $ext;
			}

			$this->filename = $f;
		}


		// Ensure the directory exists.
		// This is essentially a recursive mkdir.
		$ds = explode('/', dirname($this->filename));
		$d  = '';
		foreach ($ds as $dir) {
			if ($dir == '') continue;
			$d .= '/' . $dir;
			if (!is_dir($d)) {
				if (mkdir($d) === false) throw new Exception("Unable to make directory $d, please check permissions.");
			}
		}

		// @todo Should this incorporate permissions, to prevent files being wrote as "www-data"?


	}

	public function getContents() {
		return $this->_backend->get_object($this->bucket, $this->filename);
	}

	public function putContents($data, $mimetype = 'application/octet-stream') {
		$opt = array(
			'body'        => $data,
			'acl'         => $this->acl,
			'storage'     => $this->storage,
			'contentType' => $mimetype,
		);
		return $this->_backend->create_object($this->bucket, $this->filename, $opt);
		return file_put_contents($this->filename, $data);
	}

	public function getContentsObject() {
		return FileContentFactory::GetFromFile($this);
	}


	public function isImage() {
		$m = $this->getMimetype();
		return (preg_match('/image\/jpeg|image\/png|image\/gif/', $m) ? true : false);
	}

	public function isText() {
		$m = $this->getMimetype();
		return (preg_match('/text\/.*|application\/x-shellscript/', $m) ? true : false);
	}

	/**
	 * Get if this file can be previewed in the web browser.
	 *
	 * @return boolean
	 */
	public function isPreviewable() {
		return ($this->isImage() || $this->isText());
	}


	/**
	 * See if this file is in the requested directory.
	 *
	 * @param $path string
	 *
	 * @return boolean
	 */
	public function inDirectory($path) {
		// The path should be fully resolved, (the file is).
		if (strpos($path, ROOT_PDIR) === false) $path = ROOT_PDIR . $path;

		// Just a simple strpos shortcut...
		return (strpos($this->filename, $path) !== false);
	}

	public function identicalTo($otherfile) {

		if (is_a($otherfile, 'File') || $otherfile instanceof File) {
			// Just compare the hashes.
			return ($this->getHash() == $otherfile->getHash());
		}
		else {
			// Can't be the same if it doesn't exist!
			if (!file_exists($otherfile)) return false;
			if (!file_exists($this->filename)) return false;
			$result = exec('diff -q "' . $this->filename . '" "' . $otherfile . '"', $array, $return);
			return ($return == 0);
		}
	}

	public function exists() {
		// getMetadata will return false if it doesn't exist in the bucket.
		return ($this->_getMetadata());
	}
}
