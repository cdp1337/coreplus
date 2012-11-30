<?php
/**
 * DESCRIPTION
 *
 * @package
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

class Directory_local_backend implements Directory_Backend {

	private $_path;

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
	 * The fully resolved path of assets.
	 * Used as a cache.
	 *
	 * @var string
	 */
	private static $_Root_pdir_assets = null;
	private static $_Root_pdir_public = null;
	private static $_Root_pdir_private = null;
	private static $_Root_pdir_tmp = null;

	public function __construct($directory) {
		if (!is_null($directory)) {

			// Ensure that the root_pdir directories are cached and ready.
			if (self::$_Root_pdir_assets === null) {
				$dir = ConfigHandler::Get('/core/filestore/assetdir');
				if ($dir{0} != '/') $dir = ROOT_PDIR . $dir; // Needs to be fully resolved
				self::$_Root_pdir_assets = $dir;
			}
			if (self::$_Root_pdir_public === null) {
				$dir = ConfigHandler::Get('/core/filestore/publicdir');
				if ($dir{0} != '/') $dir = ROOT_PDIR . $dir; // Needs to be fully resolved
				self::$_Root_pdir_public = $dir;
			}
			if (self::$_Root_pdir_private === null) {
				$dir = ConfigHandler::Get('/core/filestore/privatedir');
				if ($dir{0} != '/') $dir = ROOT_PDIR . $dir; // Needs to be fully resolved
				self::$_Root_pdir_private = $dir;
			}
			if (self::$_Root_pdir_tmp === null) {
				$dir = TMP_DIR;
				if ($dir{0} != '/') $dir = ROOT_PDIR . $dir; // Needs to be fully resolved
				self::$_Root_pdir_tmp = $dir;
			}

			// Directories should always end in a trailing slash
			$directory = $directory . '/';

			// Do some cleaning on the filename, ie: // should be just /.
			$directory = preg_replace(':/+:', '/', $directory);

			// Also lookup this filename and resolve it.
			// ie, if it starts with "asset/", it should be an asset.
			// public/, public.
			// private/, private.

			if (strpos($directory, 'assets/') === 0) {
				$theme     = ConfigHandler::Get('/theme/selected');
				$directory = substr($directory, 7); // Trim off the 'asset/' prefix.
				if (file_exists(self::$_Root_pdir_assets . $theme . '/' . $directory)) $directory = self::$_Root_pdir_assets . $theme . '/' . $directory;
				else $directory = self::$_Root_pdir_assets . 'default/' . $directory;
			}
			elseif (strpos($directory, 'public/') === 0) {
				$directory = substr($directory, 7); // Trim off the 'public/' prefix.
				$directory = self::$_Root_pdir_public . $directory;
			}
			elseif (strpos($directory, 'private/') === 0) {
				$directory = substr($directory, 8); // Trim off the 'private/' prefix.
				$directory = self::$_Root_pdir_private . $directory;
			}
			elseif (strpos($directory, 'tmp/') === 0) {
				$directory = substr($directory, 4); // Trim off the 'tmp/' prefix.
				$directory = self::$_Root_pdir_tmp . $directory;
			}
			else {
				// Nothing to do on the else, just use this filename as-is.
			}

			$this->_path = $directory;
		}
	}

	/**
	 * List the files and directories in this directory and return the
	 * respective file identifier for each file/directory
	 *
	 * @return array
	 */
	public function ls() {
		// Not readable, then it can't be read...
		if (!$this->isReadable()) return array();

		// Get the initial set of files if it hasn't been done already.
		if ($this->_files === null) $this->_sift();

		$ret = array();
		foreach ($this->_files as $file => $obj) {
			// Skip the file if it's in the array of ignore files.
			if (sizeof($this->_ignores) && in_array($file, $this->_ignores)) continue;

			$ret[] = $obj;
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
		return is_readable($this->_path);
	}

	public function isWritable() {
		$ftp    = \Core\FTP();
		$tmpdir = TMP_DIR;
		if ($tmpdir{0} != '/') $tmpdir = ROOT_PDIR . $tmpdir; // Needs to be fully resolved


		if (!$ftp) {
			// If the directory doesn't exist, maybe the parent is still writable...
			$testpath = $this->_path;
			while($testpath && !is_dir($testpath)){
				$testpath = substr($testpath, 0, strrpos($testpath, '/'));
			}
			return is_writable($testpath);
		}
		elseif (strpos($this->_path, $tmpdir) === 0) {
			// Tmp files should be written directly.
			return is_writable($this->_path);
		}
		else {
			// There is no easy way to know if an FTP directory is writable....
			return true;
		}
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
		if (is_dir($this->getPath())) return null;
		else return File_local_backend::_Mkdir($this->getPath(), null, true);
	}

	public function rename($newname) {
		// If the new name is not fully resolved, translate it to the same as the current directory.
		if($newname{0} != '/'){
			$newname = substr($this->getPath(), 0, -1 - strlen($this->getBasename())) . $newname;
		}

		if(File_local_backend::_Rename($this->getPath(), $newname)){
			$this->_path = $newname;
			$this->_files = null;
			return true;
		}
		else{
			return false;
		}
	}

	/**
	 * Get this directory's fully resolved path
	 *
	 * @return string
	 */
	public function getPath() {
		return $this->_path;
	}


	/**
	 * Get just the basename of this directory
	 *
	 * @return string
	 */
	public function getBasename() {
		$p = trim($this->_path, '/');
		return substr($p, strrpos($p, '/') + 1);
	}

	/**
	 * Remove a directory and recursively any file inside it.
	 */
	public function remove() {
		$ftp    = \Core\FTP();

		if(!$ftp){
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
				if($sub instanceof File_local_backend) $sub->delete();
				else $sub->remove();
			}
			$path = $this->getPath();

			// Trim off the ROOT_PDIR since it'll be relative to the ftp root set in the config.
			if (strpos($path, ROOT_PDIR) === 0) $path = substr($path, strlen(ROOT_PDIR));

			// Prepend the ftp directory
			//$path = \ConfigHandler::Get('/core/ftp/path') . $path;

			return ftp_rmdir($ftp, $path);
		}
	}

	/**
	 * Find and get a directory or file that matches the name provided.
	 *
	 * Will search run down subdirectories if a tree'd path is provided.
	 *
	 * @param string $name
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

		$dh = opendir($this->_path);

		// If for some reason opendir cannot open the directory, do nothing else.
		if (!$dh) return;

		//echo "Reading directory $dir<br/>\n"; // DEBUG //
		while ($sub = readdir($dh)) {
			// Skip hidden files/directories.  
			// This is kind of like a built-in autoignore.
			if ($sub{0} == '.') continue;

			if (is_dir($this->_path . $sub)) {
				$this->_files[$sub] = new Directory_local_backend($this->_path . $sub);
			}
			else {
				$this->_files[$sub] = new File_local_backend($this->_path . $sub);
			}
		}

		closedir($dh);
	} // private function _sift()
}
