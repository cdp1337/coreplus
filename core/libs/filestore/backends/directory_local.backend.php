<?php
/**
 * // enter a good description here
 * 
 * @package Core
 * @since 2011.07.09
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl.html>
 * This system is licensed under the GNU LGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/lgpl-3.0.html>, 
 * and please contribute back to the community :)
 */

/**
 * Description of Directory_local_backend
 *
 * @author powellc
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
		if(!is_null($directory)){
			
			// Ensure that the root_pdir directories are cached and ready.
			if(self::$_Root_pdir_assets === null){
				$dir = ConfigHandler::Get('/core/filestore/assetdir');
				if($dir{0} != '/') $dir = ROOT_PDIR . $dir; // Needs to be fully resolved
				self::$_Root_pdir_assets = $dir;
			}
			if(self::$_Root_pdir_public === null){
				$dir = ConfigHandler::Get('/core/filestore/publicdir');
				if($dir{0} != '/') $dir = ROOT_PDIR . $dir; // Needs to be fully resolved
				self::$_Root_pdir_public = $dir;
			}
			if(self::$_Root_pdir_private === null){
				$dir = ConfigHandler::Get('/core/filestore/privatedir');
				if($dir{0} != '/') $dir = ROOT_PDIR . $dir; // Needs to be fully resolved
				self::$_Root_pdir_private = $dir;
			}
			if(self::$_Root_pdir_tmp === null){
				$dir = TMP_DIR;
				if($dir{0} != '/') $dir = ROOT_PDIR . $dir; // Needs to be fully resolved
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
			
			if(strpos($directory, 'assets/') === 0){
				$theme = ConfigHandler::Get('/theme/selected');
				$directory = substr($directory, 7); // Trim off the 'asset/' prefix.
				if(file_exists(self::$_Root_pdir_assets . $theme . '/' . $directory)) $directory = self::$_Root_pdir_assets . $theme . '/' . $directory;
				else $directory = self::$_Root_pdir_assets . 'default/' . $directory;
			}
			elseif(strpos($directory, 'public/') === 0){
				$directory = substr($directory, 7); // Trim off the 'public/' prefix.
				$directory = self::$_Root_pdir_public . $directory;
			}
			elseif(strpos($directory, 'private/') === 0){
				$directory = substr($directory, 8); // Trim off the 'private/' prefix.
				$directory = self::$_Root_pdir_private . $directory;
			}
			elseif(strpos($directory, 'tmp/') === 0){
				$directory = substr($directory, 4); // Trim off the 'tmp/' prefix.
				$directory = self::$_Root_pdir_tmp . $directory;
			}
			else{
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
	public function ls(){
		// Not readable, then it can't be read...
		if(!$this->isReadable()) return array();
		
		// Get the initial set of files if it hasn't been done already.
		if($this->_files === null) $this->_sift();
		
		$ret = array();
		foreach($this->_files as $file => $obj){
			// Skip the file if it's in the array of ignore files.
			if(sizeof($this->_ignores) && in_array($file, $this->_ignores)) continue;
			
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
	public function isReadable(){
		return is_readable($this->_path);
	}
	
	/**
	 * Create this directory, (has no effect if already exists)
	 *  
	 */
	public function mkdir(){
		mkdir($this->getPath());
	}
	
	/**
	 * Get this directory's fully resolved path
	 * 
	 * @return string
	 */
	public function getPath(){
		return $this->_path;
	}
	
	public function getBasename(){
		$p = trim($this->_path, '/');
		return substr($p, strrpos($p, '/')+1);
	}
	
	/**
	 * Remove a directory and recursively any file inside it. 
	 */
	public function remove(){
		$dirqueue = array($this->getPath());
		$x = 0;
		do{
			$x++;
			foreach($dirqueue as $k => $d){
				$isempty = true;
				$dh = opendir($d);
				if(!$dh) return false;
				while(($file = readdir($dh)) !== false){
					if($file == '.') continue;
					if($file == '..') continue;
					$isempty = false;
					if(is_dir($d . $file)) $dirqueue[] = $d . $file . '/';
					else unlink($d . $file);
				}
				closedir($dh);
				if($isempty){
					rmdir($d);
					unset($dirqueue[$k]);
				}
			}
			
			$dirqueue = array_unique($dirqueue);
			krsort($dirqueue);
		}
		while(sizeof($dirqueue) && $x <= 10);
	}
	
	/**
	 * Find and get a directory or file that matches the name provided.
	 * 
	 * Will search run down subdirectories if a tree'd path is provided.
	 * 
	 * @param string $name 
	 */
	public function get($name){
		// Trim beginning and trailing slashes.
		$name = trim($name, '/');
		$parts = explode('/', $name);
		$lastkey = sizeof($parts) - 1; // -1 because 0-indexed arrays.
		
		$obj = $this;
		foreach($parts as $k => $step){
			$listing = $obj->ls();
			foreach($listing as $l){
				if($l->getBasename() == $step){
					// Found! (and the last key)
					if($k == $lastkey) return $l;
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
	 * Sift through a directory and get the files in it.
	 * This is an internal function to populate the contents of $this->_files.
	 */
	private function _sift(){
		
		// Clear out the files array, (should be null anyways)
		$this->_files = array();
		
		$dh = opendir($this->_path);
		
		// If for some reason opendir cannot open the directory, do nothing else.
		if(!$dh) return;

		//echo "Reading directory $dir<br/>\n"; // DEBUG //
		while($sub = readdir($dh)){
			// Skip hidden files/directories.  
			// This is kind of like a built-in autoignore.
			if($sub{0} == '.') continue;

			if(is_dir($this->_path . $sub)){
				$this->_files[$sub] = new Directory_local_backend($this->_path . $sub);
			}
			else{
				$this->_files[$sub] = new File_local_backend($this->_path . $sub);
			}
		}
		
		closedir($dh);
	} // private function _sift()
}
