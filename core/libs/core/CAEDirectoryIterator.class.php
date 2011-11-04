<?php
/**
 * // enter a good description here
 * 
 * @package Core
 * @since 2011.06
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl.html>
 * This system is licensed under the GNU LGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/lgpl-3.0.html>, 
 * and please contribute back to the community :)
 */

/**
 * Class that closely mimics PHP's DirectoryIterator object, only with CAE specifics,
 * ie: a File object is returned for each file instead of just the filename.
 */
class CAEDirectoryIterator implements Iterator{
	
	/**
	 * The directory to iterate through.
	 * @var string
	 */
	private $_path;
	/**
	 * The internal listing of files assembled in the constructor.
	 */
	private $_files = array();

	/**
	 * A list of files/directories to ignore in the sift procedure.
	 * @var array
	 */
	private $_ignores = array();
	
	
	public function __construct($path = null){
		if($path){
			$this->scan($path);
		}
	}

	/**
	 * Add a file or directory to ignore when retrieving results.
	 * If this is a directory, everything in that directory will be ignored.
	 *
	 * @param string $path
	 */
	public function addIgnore($path){
		// If the path is already precursed with the ROOT_PDIR, no need to prepend it.
		if(strpos($path, ROOT_PDIR) === false) $path = ROOT_PDIR . $path;
		
		$this->_ignores[] = $path;
	}

	/**
	 * Add a list of files or directories to ignore when retrieving results.
	 * The list can either be a singular array or a set of parameters.
	 *
	 * @param array|string $list
	 * @param ...
	 */
	public function addIgnores($list){
		$args = func_get_args();
		if(sizeof($args)){
			foreach($args as $a){
				if(is_array($a)) foreach($a as $innera) $this->addIgnore($innera);
				else $this->addIgnore($a);
			}
		}
		else{
			if(is_array($list)) foreach($list as $innera) $this->addIgnore($innera);
			else $this->addIgnore($list);
		}
	}

	public function setPath($path){
		// Clear the list if it's present.
		$this->_files = array();
		// If the path is already precursed with the ROOT_PDIR, no need to prepend it.
		if(strpos($path, ROOT_PDIR) === false) $path = ROOT_PDIR . $path;
		// And remember it.
		$this->_path = $path;
	}

	/**
	 * Manually run a scan.  This is called automatically if a filename is given in the constructor.
	 * @param string $path
	 */
	public function scan($path = null){
		if($path){
			$this->setPath($path);
		}
		// Open the directory and "sift" through it.
		$this->sift($this->_path);
	}
	
	/**
	 * Sift through a directory and get the files in it.
	 * @param unknown_type $dir
	 * @return unknown_type
	 */
	protected function sift($dir){
		if(!(is_dir($dir) && is_readable($dir))){
			// @todo probably should do an error or something here.
			return;
		}
		// Make sure the directory ends in a slash.
		if(strrpos($dir, '/') + 1 != strlen($dir)) $dir .= '/';
		//echo "Checking directory or file [[ $dir ]]\n"; // DEBUG //
		// Skip the file if it's in the array of ignore files.
		if(sizeof($this->_ignores) && in_array($dir, $this->_ignores)) return;

		$dh = opendir($dir);

		//echo "Reading directory $dir<br/>\n"; // DEBUG //
		while($sub = readdir($dh)){
			// Skip hidden files/directories.
			if($sub{0} == '.') continue;

			// Skip the file if it's in the array of ignore files.
			if(sizeof($this->_ignores) && in_array($dir . $sub, $this->_ignores)) continue;

			//echo "GOGO $dir$sub<br/>\n"; // DEBUG //

			if(is_dir($dir . $sub)){
				// Recurse.
				$this->sift($dir . $sub);
			}
			else{
				// @todo have intelligent file matching to return the appropriate object.
				$this->_files[] = new File_local_backend($dir . $sub);
			}
		}
		
		closedir($dh);
	}
	
	public function rewind(){
		reset($this->_files);
	}

	public function current(){
		$var = current($this->_files);
		return $var;
	}

	public function key(){
		$var = key($this->_files);
		return $var;
	}

	public function next(){
		$var = next($this->_files);
		return $var;
	}

	public function valid(){
		$var = $this->current() !== false;
		return $var;
	}
	
	/**
	 * @return int
	 */
	public function getATime(){
		// @todo Implement this method.
	}
	/**
	 * @return string
	 */
	//public function getBasename ([ string $suffix ] )
	/**
	 * @return int
	 */
	//public function getCTime ( void )
	/**
	 * @return string
	 */
	public function getFilename($prefix = ROOT_PDIR){
		return $this->current()->getFilename($prefix);
	}
	/**
	 * @return int
	 */
	//public function getGroup ( void )
	/**
	 * @return int
	 */
	//public function getInode ( void )
	/**
	 * @return int
	 */
	//public function getMTime ( void )
	/**
	 * @return int
	 */
	//public function getOwner ( void )
	/**
	 * @return string
	 */
	//public function getPath ( void )
	/**
	 * @return string
	 */
	//public function getPathname ( void )
	/**
	 * @return int
	 */
	//public function getPerms ( void )
	/**
	 * @return int
	 */
	//public function getSize ( void )
	/**
	 * @return string
	 */
	//public function getType ( void )
	/**
	 * @return boolean
	 */
	//public function isDir ( void )
	/**
	 * @return boolean
	 */
	//public function isDot ( void )
	/**
	 * @return boolean
	 */
	//public function isExecutable ( void )
	/**
	 * @return boolean
	 */
	//public function isFile ( void )
	/**
	 * @return boolean
	 */
	//public function isLink ( void )
	/**
	 * @return boolean
	 */
	//public function isReadable ( void )
	/**
	 * @return boolean
	 */
	//public function isWritable ( void )
	/**
	 * @return string
	 */
	//public function __toString ( void )
}
