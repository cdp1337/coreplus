<?php
/**
 * // enter a good description here
 * 
 * @package Core
 * @since 2011.07.09
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Lesser General Public License v3 <http://www.gnu.org/licenses/lgpl-3.0.html>
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
	
	public function __construct($directory) {
		if(!is_null($directory)){
			// Directories should always end in a trailing slash
			$directory = $directory . '/';
			
			// Do some cleaning on the filename, ie: // should be just /.
			$directory = preg_replace(':/+:', '/', $directory);
			
			// Also lookup this filename and resolve it.
			// ie, if it starts with "asset/", it should be an asset.
			// public/, public.
			// private/, private.
			
			if(strpos($directory, 'assets/') === 0){
				$base = ConfigHandler::GetValue('/core/filestore/assetdir');
				if($base{0} != '/') $base = ROOT_PDIR . $base; // Needs to be fully resolved
				$theme = ConfigHandler::GetValue('/core/theme');
				$directory = substr($directory, 7); // Trim off the 'asset/' prefix.
				if(file_exists($base . $theme . '/' . $directory)) $directory = $base . $theme . '/' . $directory;
				else $directory = $base . 'default/' . $directory;
			}
			elseif(strpos($directory, 'public/') === 0){
				$directory = substr($directory, 7); // Trim off the 'public/' prefix.
				$base = ConfigHandler::GetValue('/core/filestore/publicdir');
				if($base{0} != '/') $base = ROOT_PDIR . $base; // Needs to be fully resolved
				$directory = $base . $directory;
			}
			elseif(strpos($directory, 'private/') === 0){
				$directory = substr($directory, 8); // Trim off the 'private/' prefix.
				$base = ConfigHandler::GetValue('/core/filestore/privatedir');
				if($base{0} != '/') $base = ROOT_PDIR . $base; // Needs to be fully resolved
				$directory = $base . $directory;
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
