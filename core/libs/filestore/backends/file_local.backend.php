<?php
/**
 * // enter a good description here
 * 
 * @package Core
 * @since 2011.06
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Lesser General Public License v3 <http://www.gnu.org/licenses/lgpl-3.0.html>
 * This system is licensed under the GNU LGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/lgpl-3.0.html>, 
 * and please contribute back to the community :)
 */


class File_local_backend implements File_Backend{
	
	private $_filename = null;
	
	private $_type = null;
	
	private static $_Root_pdir_assets = null;
	private static $_Root_pdir_public = null;
	private static $_Root_pdir_private = null;
	
	public function __construct($filename){
		
		// Ensure that the root_pdir directories are cached and ready.
		if(self::$_Root_pdir_assets == null){
			$dir = ConfigHandler::GetValue('/core/filestore/assetdir');
			if($dir{0} != '/') $dir = ROOT_PDIR . $dir; // Needs to be fully resolved
			self::$_Root_pdir_assets = $dir;
		}
		if(self::$_Root_pdir_public == null){
			$dir = ConfigHandler::GetValue('/core/filestore/publicdir');
			if($dir{0} != '/') $dir = ROOT_PDIR . $dir; // Needs to be fully resolved
			self::$_Root_pdir_public = $dir;
		}
		if(self::$_Root_pdir_private == null){
			$dir = ConfigHandler::GetValue('/core/filestore/privatedir');
			if($dir{0} != '/') $dir = ROOT_PDIR . $dir; // Needs to be fully resolved
			self::$_Root_pdir_private = $dir;
		}
		
		// base64 comes first.  If the filename is encoded in that, decode it first.
		if(strpos($filename, 'base64:') === 0) $filename = base64_decode(substr($filename, 7));
		
		// Do some cleaning on the filename, ie: // should be just /.
		$filename = preg_replace(':/+:', '/', $filename);

		// Also lookup this filename and resolve it.
		// ie, if it starts with "asset/", it should be an asset.
		// public/, public.
		// private/, private.

		
		// Allow "asset/blah" to be passed in
		if(strpos($filename, 'assets/') === 0){
			$theme = ConfigHandler::GetValue('/core/theme');
			$filename = substr($filename, 7); // Trim off the 'asset/' prefix.
			if(file_exists(self::$_Root_pdir_assets . $theme . '/' . $filename)) $filename = self::$_Root_pdir_assets . $theme . '/' . $filename;
			else $filename = self::$_Root_pdir_assets . 'default/' . $filename;

			$this->_type = 'asset';
		}
		// Allow the fully resolved filename to be passed in
		elseif(strpos($filename, self::$_Root_pdir_assets) === 0){
			// No filename resolution needed, already in full form.
			
			$this->_type = 'asset';
		}
		elseif(strpos($filename, 'public/') === 0){
			$filename = substr($filename, 7); // Trim off the 'public/' prefix.
			$filename = self::$_Root_pdir_public . $filename;

			$this->_type = 'public';
		}
		// Allow the fully resolved filename to be passed in
		elseif(strpos($filename, self::$_Root_pdir_public) === 0){
			// No filename resolution needed, already in full form.
			
			$this->_type = 'public';
		}
		elseif(strpos($filename, 'private/') === 0){
			$filename = substr($filename, 8); // Trim off the 'private/' prefix.
			$filename = self::$_Root_pdir_private . $filename;

			$this->_type = 'private';
		}
		// Allow the fully resolved filename to be passed in
		elseif(strpos($filename, self::$_Root_pdir_private) === 0){
			// No filename resolution needed, already in full form.
			
			$this->_type = 'private';
		}
		else{
			// Nothing to do on the else, just use this filename as-is.
		}

		$this->_filename = $filename;
	}
	
	public function getFilesize($formatted = false){
		$f = filesize($this->_filename);
		return ($formatted)? Core::FormatSize($f, 2) : $f;
	}
	
	public function getMimetype(){
		// PEAR, you have failed me for the last time... :'(
		//return MIME_Type::autoDetect($this->_filename);
		
		$finfo = finfo_open(FILEINFO_MIME);
		$type  = finfo_file($finfo, $this->_filename);
		finfo_close($finfo);
		
		// $type may have some extra crap after a semicolon.
		if(($pos = strpos($type, ';')) !== false) $type = substr($type, 0, $pos);
		$type = trim($type);
		
		// There are a few exceptions to the rule.... namely with plain text.
		$ext = strtolower($this->getExtension());
		if($ext == 'js' && $type == 'text/plain')                    $type = 'text/javascript';
		elseif($ext == 'js' && $type == 'text/x-c++')                $type = 'text/javascript';
		elseif($ext == 'css' && $type == 'text/plain')               $type = 'text/css';
		elseif($ext == 'css' && $type == 'text/x-c')                 $type = 'text/css';
		elseif($ext == 'html' && $type == 'text/plain')              $type = 'text/html';
		elseif($ext == 'ttf' && $type == 'application/octet-stream') $type = 'font/ttf';
		elseif($ext == 'otf' && $type == 'application/octet-stream') $type = 'font/otf';
		
		return $type;
	}
	
	public function getExtension(){
		return Core::GetExtensionFromString($this->_filename);
		//return substr($this->_filename, strrpos($this->_filename, '.'));
	}
	
	/**
	 * Get a filename that can be retrieved from the web.
	 * Resolves with the ROOT_DIR prefix already attached.
	 * 
	 * @return string | false
	 */
	public function getURL(){
		if(!preg_match('/^' . str_replace('/', '\\/', ROOT_PDIR) . '/', $this->_filename)) return false;
		
		return preg_replace('/^' . str_replace('/', '\\/', ROOT_PDIR) . '(.*)/', ROOT_WDIR . '$1', $this->_filename);
	}
	
	/**
	 * Get the filename of this file resolved to a specific directory, usually ROOT_PDIR or ROOT_WDIR.
	 */
	public function getFilename($prefix = ROOT_PDIR){
		if($prefix == ROOT_PDIR) return $this->_filename;
		
		return preg_replace('/^' . str_replace('/', '\\/', ROOT_PDIR) . '(.*)/', $prefix . '$1', $this->_filename);
	}
	
	/**
	 * Get the base filename of this file.
	 */
	public function getBaseFilename($withoutext = false){
		$b = basename($this->_filename);
		if($withoutext){
			return substr($b, 0, (-1 - strlen($this->getExtension() ) ) );
		}
		else{
			return $b;
		}
	}
	
	/**
	 * Get an ascii hash of the filename.
	 * useful for transposing this file to another page call.
	 * 
	 * @return string The encoded string
	 */
	public function getFilenameHash(){
		if($this->_type == 'asset') $filename = 'asset/' . substr($this->_filename, strlen(self::$_Root_pdir_asset));
		elseif($this->_type == 'public') $filename = 'public/' . substr($this->_filename, strlen(self::$_Root_pdir_public));
		elseif($this->_type == 'private') $filename = 'private/' . substr($this->_filename, strlen(self::$_Root_pdir_private));
		else $filename = $this->_filename;
		
		return 'base64:' . base64_encode($filename);
	}
	
	/**
	 * Get the hash for this file.
	 */
	public function getHash(){
		if(!file_exists($this->_filename)) return null;
		
		return md5_file($this->_filename);
	}
	
	public function delete(){
		if(!@unlink($this->getFilename())) return false;
		$this->_filename = null;
		return true;
	}

	/**
	 * Copies the file to the requested destination.
	 * If the destination is a directory (ends with a '/'), the same filename is used, (if possible).
	 * If the destination is relative, ('.' or 'subdir/'), it is assumed relative to the current file.
	 *
	 * @param string|File $dest
	 * @param boolean $overwrite
	 * @return File
	 */
	public function copyTo($dest, $overwrite = false){
		//echo "Copying " . $this->_filename . " to " . $dest . "\n"; // DEBUG //
		
		if(is_a($dest, 'File') || $dest instanceof File_Backend){
			// Don't need to do anything! The object either is a File
			// Or is an implmentation of the File_Backend interface.
		}
		else{
			// Well it should be damnit!....
			$file = $dest;
			
			// Get the location of the destination, be it relative or absolute.
			// If the file does not start with a "/", assume it's relative to this current file.
			if($file{0} != '/'){
				$file = dirname($this->_filename) . '/' . $file;
			}
			
			// Is the destination a directory or filename?
			// If it's a directory just tack on this current file's basename.
			if(substr($file, -1) == '/'){
				$file .= $this->getBaseFilename();
			}
			
			// Now dest can be instantiated as a valid file object!
			$dest = new File_local_backend($file);
		}
		
		if($this->identicalTo($dest)) return $this;
		
		// GO!
		// The receiving function's logic will handle the rest.
		$dest->copyFrom($this, $overwrite);
		
		return $dest;
		

		// Now I can ensure that $dest is an absolutely positioned filename of an actual file.
		if(!is_dir(dirname($dest))){
			// PHP doesn't support the '-p' argument... :(
			exec('mkdir -p "' . dirname($dest) . '"');
		}
	}
	
	public function copyFrom($src, $overwrite = false){
		// Don't overwrite existing files unless told otherwise...
		if(!$overwrite){
			$c = 0;
			$ext = $this->getExtension();
			$base = $this->getBaseFilename(true);
			$dir = dirname($this->_filename);
			
			$f = $dir . '/' . $base . '.' . $ext;
			while(file_exists($f)){
				$f = $dir . '/' . $base . ' (' . ++$c . ')' . '.' . $ext;
			}
			
			$this->_filename = $f;
		}
		
		
		// Ensure the directory exists.
		// This is essentially a recursive mkdir.
		$ds = explode('/', dirname($this->_filename));
		$d = '';
		foreach($ds as $dir){
			if($dir == '') continue;
			$d .= '/' . $dir;
			if(!is_dir($d)){
				if(mkdir($d) === false) throw new Exception("Unable to make directory $d, please check permissions.");
			}
		}

		// @todo Should this incorporate permissions, to prevent files being wrote as "www-data"?

		// And do the actual copy!
		$this->putContents($src->getContents());
	}
	
	public function getContents(){
		return file_get_contents($this->_filename);
	}
	
	public function putContents($data){
		// Ensure the directory exists.
		// This is essentially a recursive mkdir.
		if(!is_dir(dirname($this->_filename))){
			$ds = explode('/', dirname($this->_filename));
			$d = '';
			foreach($ds as $dir){
				if($dir == '') continue;
				$d .= '/' . $dir;
				if(!is_dir($d)){
					if(mkdir($d) === false) throw new Exception("Unable to make directory $d, please check permissions.");
				}
			}
		}
		
		return file_put_contents($this->_filename, $data);
	}
	
	public function isImage(){
		$m = $this->getMimetype();
		return (preg_match('/image\/jpeg|image\/png|image\/gif/', $m)? true : false);
	}
	
	public function isText(){
		$m = $this->getMimetype();
		return (preg_match('/text\/.*|application\/x-shellscript/', $m)? true : false);
	}
	
	/**
	 * Get if this file can be previewed in the web browser.
	 * 
	 * @return boolean
	 */
	public function isPreviewable(){
		return ($this->isImage() || $this->isText());
	}
	
	/**
	 * Display a preview of this file to the browser.  Must be an image.
	 * 
	 * @param string|int $dimensions A string of the dimensions to create the image at, widthxheight.
	 *                               Also supports the previous version of simply "dimension", as an int.
	 * @param boolean $includeHeader Include the correct mimetype header or no.
	 */
	public function displayPreview($dimensions = "300x300", $includeHeader = true){
		
		// The legacy support for simply a number.
		if(is_numeric($dimensions)){
			$width = $dimensions;
			$height = $dimensions;
		}
		elseif($dimensions === null){
			$width = 300;
			$height = 300;
		}
		else{
			// New method. Split on the "x" and that should give me the width/height.
			$vals = explode('x', strtolower($dimensions));
			$width = (int)$vals[0];
			$height = (int)$vals[1];
		}
		
		// Enable caching.
		$key = 'filepreview-' . $this->getHash() . '-' . $width . 'x' . $height . '.png';
		
		if(file_exists(TMP_DIR . $key)){
			header('Content-Type: image/png');
			echo file_get_contents(TMP_DIR . $key); // (should be binary)
			return; // whee, nothing else to do!
		}
		
		$img2 = $this->_getResizedImage($width, $height);
		
		// Save this image to cache.
		imagepng($img2, TMP_DIR . $key);

		if($includeHeader) header('Content-Type: image/png');
		imagepng($img2);
		return;
		
		
		// __TODO__ Support text for previewing.	Use maxWidth as maxLines instead.
	}
	
	public function getPreviewURL($dimensions = "300x300"){
		// The legacy support for simply a number.
		if(is_numeric($dimensions)){
			$width = $dimensions;
			$height = $dimensions;
		}
		elseif($dimensions === null){
			$width = 300;
			$height = 300;
		}
		else{
			// New method. Split on the "x" and that should give me the width/height.
			$vals = explode('x', strtolower($dimensions));
			$width = (int)$vals[0];
			$height = (int)$vals[1];
		}
		
		if($this->isPreviewable()){
			$key = 'filepreview-' . $this->getHash() . '-' . $width . 'x' . $height . '.png';
			
			$file = Core::File('public/tmp/' . $key);
			if(!$file->exists()){
				$img2 = $this->_getResizedImage($width, $height);
				// Save this image to cache.
				imagepng($img2, TMP_DIR . $key);
				$file->putContents(file_get_contents(TMP_DIR . $key));
			}
			
			return $file->getURL();
		}
		else{
			return false;
		}
	}

	/**
	 * See if this file is in the requested directory.
	 * 
	 * @param $path string
	 * @return boolean
	 */
	public function inDirectory($path){
		// The path should be fully resolved, (the file is).
		if(strpos($path, ROOT_PDIR) === false) $path = ROOT_PDIR . $path;
		
		// Just a simple strpos shortcut...
		return (strpos($this->_filename, $path) !== false);
	}

	public function identicalTo($otherfile){
	
		if(is_a($otherfile, 'File') || $otherfile instanceof File_Backend){
			// Just compare the hashes.
			return ($this->getHash() == $otherfile->getHash());
		}
		else{
			// Can't be the same if it doesn't exist!
			if(!file_exists($otherfile)) return false;
			if(!file_exists($this->_filename)) return false;
			$result = exec('diff -q "' . $this->_filename . '" "' . $otherfile . '"', $array, $return);
			return ($return == 0);
		}		
	}
	
	public function exists(){
		return file_exists($this->_filename);
	}
	
	public function isReadable(){
		return is_readable($this->_filename);
	}
	
	public function isLocal(){
		// Simple function that indicates if the file is on a local filesystem
		// Please note, even mounted filesystems are considered local for this matter.
		// Amazon S3 and other CDN services.... are not.
		
		return true;
	}
	
	
	private function _getResizedImage($width, $height){
		$m = $this->getMimetype();
		
		if($this->isImage()){
			switch($m){
				case 'image/jpeg':
					$img = imagecreatefromjpeg($this->getFilename());
					break;
				case 'image/png':
					$img = imagecreatefrompng($this->getFilename());
					break;
				case 'image/gif':
					$img = imagecreatefromgif($this->getFilename());
					break;
			}
			if($img){
				$sW = imagesx($img);
				$sH = imagesy($img);
				
				$nW = $sW;
				$nH = $sH;
				
				if($nW > $width){
					$nH = $width * $sH / $sW;
					$nW = $width;
				}
				
				if($nH > $height){
					$nW = $height * $sW / $sH;
					$nH = $height;
				}
				
				$img2 = imagecreatetruecolor($nW, $nH);
				imagealphablending($img2, false);
				imagesavealpha($img2, true);
				imagealphablending($img, true);
				// Assign a transparency color.
				//$trans = imagecolorallocatealpha($img2, 0, 0, 0, 0);
				//imagefill($img2, 0, 0, $trans);
				imagecopyresampled($img2, $img, 0, 0, 0, 0, $nW, $nH, $sW, $sH);
				imagedestroy($img);
				
				return $img2;
			}
		}
	}

	
	
	/**
	 * Guess the mimetype for a given extension.
	 * 
	 * Some extensions may have multiple mimetypes based on the detection software,
	 *	so an array is returned with all possibilities for that extension.
	 * 
	 * @return array
	 */
	/*public static function GetMimetypeFromExtension($ext){
		// Remove the beginning '.' if there is one.
		if($ext{0} == '.') $ext = substr($ext, 1);
		
		switch(strtolower($ext)){
			case 'gif':
				return array('image/gif');
			case 'jpg':
			case 'jpeg':
				return array('image/jpeg');
			case 'pdf':
				return array('application/pdf');
			case 'png':
				return array('image/png');
			case 'sh':
				return array('application/x-shellscript', 'text/plain');
			case 'txt':
				return array('text/plain');
			case 'zip':
				return array('application/x-zip', 'application/zip');
			default:
				return array();
		}
	}
	*/
	/*
	public static function GetMimetypesFromExtensions($exts = array()){
		if(!is_array($exts)) return array();
		$ret = array();
		foreach($exts as $ext){
			$ret = array_merge($ret, File::GetMimetypeFromExtension($ext));
		}
		return $ret;
	}
	*/
	
}
?>
