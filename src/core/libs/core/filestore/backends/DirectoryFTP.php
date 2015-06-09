<?php
/**
 * DESCRIPTION
 *
 * @package
 * @since 0.1
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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

class DirectoryFTP implements Filestore\Directory {

	protected $_prefix;
	protected $_path;

	private $_type;

	/**
	 * The internal listing of files
	 *
	 * @var array
	 */
	private $_files = null;

	/**
	 * A list of files/directories to ignore when listing files
	 *
	 * @var array
	 */
	private $_ignores = array();

	/**
	 * The backend FTP resource.
	 * This is a native PHP object.
	 *
	 * @var
	 */
	protected $_ftp;

	/**
	 * Set to true if this FTP connection is the proxy for local files.
	 * @var bool
	 */
	protected $_islocal = false;

	public function __construct($directory, $ftpobject = null) {
		if($ftpobject !== null){
			$this->_ftp = $ftpobject;
		}
		else{
			$this->_ftp = \Core\FTP();
		}

		if($this->_ftp == \Core\FTP()){
			$this->_islocal = true;
		}

		if (!is_null($directory)) {
			$this->setPath($directory);
		}
	}

	/**
	 * List the files and directories in this directory and return the
	 * respective file identifier for each file/directory
	 *
	 * @param null|string $extension The extension to look for, (optional)
	 * @param bool        $recursive Set to true to recurse into sub directories and perform the same search.
	 *
	 * @return array
	 */
	public function ls($extension = null, $recursive = false) {
		// Not readable, then it can't be read...
		if (!$this->isReadable()) return array();

		// Get the initial set of files if it hasn't been done already.
		if ($this->_files === null) $this->_sift();

		$ret = array();
		foreach ($this->_files as $file => $obj) {
			// Skip the file if it's in the array of ignore files.
			if (sizeof($this->_ignores) && in_array($file, $this->_ignores)) continue;

			// Is there an extension requested?
			if($extension){
				if($obj instanceof Filestore\Directory && $recursive){
					$ret = array_merge($ret, $obj->ls($extension, $recursive));
				}
				elseif($obj instanceof Filestore\File){
					//echo $obj->getExtension() . ' vs ' . $extension . '<br/>';
					// Is it a match?
					if($obj->getExtension() == $extension){
						$ret[] = $obj;
					}
				}
			}
			elseif($recursive){
				// Tack on the parent itself regardless.
				$ret[] = $obj;

				// And recurse into directories for its children.
				if($obj instanceof Filestore\Directory && $recursive){
					$ret = array_merge($ret, $obj->ls($extension, $recursive));
				}
			}
			else{
				// Just default old behaviour.
				$ret[] = $obj;
			}
		}
		return $ret;
	}


	/**
	 * Tells whether a directory exists and is readable
	 *
	 * @link http://php.net/manual/en/function.is-readable.php
	 * @return bool true if the directory exists and is readable, false otherwise.
	 */
	public function isReadable() {
		return is_readable($this->getPath());
	}

	public function isWritable() {
		// There is no easy way to know if an FTP directory is writable....
		return true;
	}

	/**
	 * Check and see if this exists and is in-fact a directory.
	 *
	 * @return bool
	 */
	public function exists(){
		return (is_dir($this->getPath()));
	}

	/**
	 * Create this directory, (has no effect if already exists)
	 * Returns true if successful, null if exists, and false if failure
	 *
	 * @return boolean | null
	 */
	public function mkdir() {
		if($this->exists()) return null;

		// Resolve it from its default.
		// This is provided from a config define, (probably).
		$mode = (defined('DEFAULT_DIRECTORY_PERMS') ? DEFAULT_DIRECTORY_PERMS : 0777);

		// Because ftp_mkdir doesn't like to create parent directories...
		$paths = explode('/', $this->_path);

		foreach ($paths as $p) {
			if(trim($p) == '') continue;

			if (!@ftp_chdir($this->_ftp, $p)) {
				if (!ftp_mkdir($this->_ftp, $p)) return false;
				if (!ftp_chmod($this->_ftp, $mode, $p)) return false;
				ftp_chdir($this->_ftp, $p);
			}
		}

		// woot...
		return true;
	}

	public function rename($newname) {

		$cwd = ftp_pwd($this->_ftp);

		if(strpos($newname, ROOT_PDIR) === 0){
			// If the file starts with the PDIR... trim that off!
			$newname = substr($newname, strlen(ROOT_PDIR));
		}
		elseif(strpos($newname, $cwd) === 0){
			// If the file already starts with the CWD... trim that off!
			$newname = substr($newname, strlen($cwd));
		}
		else{
			$newname = dirname($this->_path) . '/' . $newname;
		}

		$status = ftp_rename($this->_ftp, $this->_path, $newname);

		if($status){
			$this->_path = $newname;
			$this->_files = null;
		}

		return $status;
	}

	/**
	 * Get this directory's fully resolved path
	 *
	 * @return string
	 */
	public function getPath() {
		return $this->_prefix . $this->_path;
	}

	/**
	 * Set the path for this directory.
	 *
	 * @param $path
	 *
	 * @return void
	 */
	public function setPath($path){
		if(substr($path, -1) != '/'){
			// Directories should always end in a trailing slash
			$path = $path . '/';
		}

		$cwd = ftp_pwd($this->_ftp);

		if(strpos($path, ROOT_PDIR) === 0){
			// If the file starts with the PDIR... trim that off!
			$path = substr($path, strlen(ROOT_PDIR));
			$prefix = ROOT_PDIR;
		}
		elseif(strpos($path, $cwd) === 0){
			// If the file already starts with the CWD... trim that off!
			$path = substr($path, strlen($cwd));
			$prefix = $cwd;
		}
		else{
			$prefix = $cwd;
		}

		// Make sure that prefix ends with a '/'.
		if(substr($prefix, -1) != '/') $prefix .= '/';

		// Do some cleaning on the filename, ie: // should be just /.
		$path = preg_replace(':/+:', '/', $path);

		$this->_prefix = $prefix;
		$this->_path = $path;

		// Resolve if this is an asset, public, etc.
		// This is to speed up the other functions so they don't have to perform this operation.
		if(strpos($this->_path, Filestore\get_asset_path()) === 0){
			$this->_type = 'asset';
		}
		elseif(strpos($this->_path, Filestore\get_public_path()) === 0){
			$this->_type = 'public';
		}
		elseif(strpos($this->_path, Filestore\get_tmp_path()) === 0){
			$this->_type = 'tmp';
		}
	}


	/**
	 * Get just the basename of this directory
	 *
	 * @return string
	 */
	public function getBasename() {
		return basename($this->_path);

		//$p = trim($this->_path, '/');
		//return substr($p, strrpos($p, '/') + 1);
	}

	/**
	 * Delete a directory and recursively any file inside it.
	 */
	public function delete() {
		$ftp    = \Core\FTP();
		$tmpdir = TMP_DIR;
		if ($tmpdir{0} != '/') $tmpdir = ROOT_PDIR . $tmpdir; // Needs to be fully resolved

		if(
			!$ftp || // FTP not enabled or
			(strpos($this->getPath(), $tmpdir) === 0) // Destination is a temporary directory.
		){
			$dirqueue = array($this->getPath());
			$x        = 0;
			do {
				$x++;
				foreach ($dirqueue as $k => $d) {
					$isempty = true;
					$dh      = opendir($d);
					if (!$dh) return false;
					while (($file = readdir($dh)) !== false) {
						if ($file == '.') continue;
						if ($file == '..') continue;
						$isempty = false;
						if (is_dir($d . $file)) $dirqueue[] = $d . $file . '/';
						else unlink($d . $file);
					}
					closedir($dh);
					if ($isempty) {
						rmdir($d);
						unset($dirqueue[$k]);
					}
				}

				$dirqueue = array_unique($dirqueue);
				krsort($dirqueue);
			}
			while (sizeof($dirqueue) && $x <= 10);
			return true;
		}
		else{
			// If there are children, drop into them and remove those too.
			// This is because directories need to be empty.
			foreach($this->ls() as $sub){
				if($sub instanceof Filestore\File) $sub->delete();
				else $sub->delete();
			}
			$path = $this->getPath();

			// Trim off the ROOT_PDIR since it'll be relative to the ftp root set in the config.
			if (strpos($path, ROOT_PDIR) === 0) $path = substr($path, strlen(ROOT_PDIR));

			// Prepend the ftp directory
			//$path = \ConfigHandler::Get('/core/ftp/path') . $path;

			return ftp_rmdir($ftp, $path);
		}
	}

	public function remove(){
		return $this->delete();
	}

	/**
	 * Find and get a directory or file that matches the name provided.
	 *
	 * Will search run down subdirectories if a tree'd path is provided.
	 *
	 * @param string $name
	 * @return null|Filestore\File|Filestore\Directory
	 */
	public function get($name) {
		// Trim beginning and trailing slashes.
		$name    = trim($name, '/');
		$parts   = explode('/', $name);
		$lastkey = sizeof($parts) - 1; // -1 because 0-indexed arrays.

		$obj = $this;
		foreach ($parts as $k => $step) {
			$listing = $obj->ls();
			foreach ($listing as $l) {
				if ($l->getBasename() == $step) {
					// Found! (and the last key)
					if ($k == $lastkey) return $l;
					// Not the last key, it was still found!
					$obj = $l;
					continue 2;
				}
			}

			// If it got here, one of the paths failed to resolve!
			return null;
		}

		// Did every path fail to resolve?
		return null;
	}

	/**
	 * To ensure compatibility with the File system.
	 *
	 * @return null
	 */
	public function getExtension(){
		return null;
	}


	/**
	 * Sift through a directory and get the files in it.
	 * This is an internal function to populate the contents of $this->_files.
	 */
	private function _sift() {

		// Clear out the files array, (should be null anyways)
		$this->_files = array();

		$dh = opendir($this->getPath());

		// If for some reason opendir cannot open the directory, do nothing else.
		if (!$dh) return;

		//echo "Reading directory $dir<br/>\n"; // DEBUG //
		while ($sub = readdir($dh)) {
			// Skip hidden files/directories.  
			// This is kind of like a built-in autoignore.
			if ($sub{0} == '.') continue;

			if (is_dir($this->getPath() . $sub)) {
				$this->_files[$sub] = new DirectoryFTP($this->getPath() . $sub);
			}
			else {
				$this->_files[$sub] = new FileFTP($this->getPath() . $sub);
			}
		}

		closedir($dh);
	} // private function _sift()
}
