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

// This file depends on the PEAR MIME_Type class.
//require_once('MIME/Type.php');

class File implements IFile{
	
	public $filename = null;
	
	public function __construct($filename = null){
		if(!is_null($filename)){
			// Do some cleaning on the filename, ie: // should be just /.
			$filename = preg_replace(':/+:', '/', $filename);
			$this->filename = $filename;
		}
	}
	
	public function getFilesize($formatted = false){
		$f = filesize($this->filename);
		return ($formatted)? File::FormatSize($f, 2) : $f;
	}
	
	public function getMimetype(){
		// PEAR, you have failed me for the last time... :'(
		//return MIME_Type::autoDetect($this->filename);
		
		$finfo = finfo_open(FILEINFO_MIME);
		$type  = finfo_file($finfo, $this->filename);
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
		return File::GetExtensionFromString($this->filename);
		//return substr($this->filename, strrpos($this->filename, '.'));
	}

	public static function GetExtensionFromString($str){
		// I *could* use php's pathinfo function... but that doesn't handle "tar.gz" files too well...
		$exts = explode('.', $str);
		$s = sizeof($exts);
		// File doesn't have any extension... easy enough!
		if($s == 1) return '';

		$ext = strtolower($exts[--$s]);
		if($s == 1) return $ext;

		// Some extensions have some 'extra' logic required...
		if($ext == 'php' && strtolower($exts[$s-1]) == 'inc'){
			// PHP files may have .inc.php for them...
			return 'inc.php';
		}
		if($ext == 'gz' || $ext == 'asc'){
			// gz can compress ANYTHING.... sadly, but gladly too.. 0.o
			// GPG can also encrypt anything...
			if(strlen($exts[$s-1]) > 1 && strlen($exts[$s-1]) < 5) $ext = strtolower($exts[--$s]) . '.' . $ext;
			if($s == 1) return $ext;
			// This second one will allow for a file such as: something.tar.gz.asc or something.tar.asc.gz
			if(strlen($exts[$s-1]) > 1 && strlen($exts[$s-1]) < 5) $ext = strtolower($exts[--$s]) . '.' . $ext;
		}

		return $ext;
	}
	
	/**
	 * Get a filename that can be retrieved from the web.
	 * Resolves with the ROOT_DIR prefix already attached.
	 * 
	 * @return string | false
	 */
	public function getURL(){
		if(!preg_match('/^' . str_replace('/', '\\/', ROOT_PDIR) . '/', $this->filename)) return false;
		
		return preg_replace('/^' . str_replace('/', '\\/', ROOT_PDIR) . '(.*)/', ROOT_WDIR . '$1', $this->filename);
	}
	
	/**
	 * Get the filename of this file resolved to a specific directory, usually ROOT_PDIR or ROOT_WDIR.
	 */
	public function getFilename($prefix = ROOT_PDIR){
		if($prefix == ROOT_PDIR) return $this->filename;
		
		return preg_replace('/^' . str_replace('/', '\\/', ROOT_PDIR) . '(.*)/', $prefix . '$1', $this->filename);
	}
	
	/**
	 * Get the base filename of this file.
	 */
	public function getBaseFilename($withoutext = false){
		$b = basename($this->filename);
		if($withoutext){
			return substr($b, 0, (-1 - strlen($this->getExtension() ) ) );
		}
		else{
			return $b;
		}
	}
	
	/**
	 * Get the hash for this file.
	 */
	public function getHash(){
		if(!file_exists($this->filename)) return null;
		
		return md5_file($this->filename);
	}
	
	public function delete(){
		if(!@unlink($this->getFilename())) return false;
		$this->filename = null;
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
		//echo "Copying " . $this->filename . " to " . $dest . "\n"; // DEBUG //
		
		if(is_a($dest, 'File') || $dest instanceof IFile){
			// Don't need to do anything! The object either is a File
			// Or is an implmentation of the IFile interface.
		}
		else{
			// Well it should be damnit!....
			$file = $dest;
			
			// Get the location of the destination, be it relative or absolute.
			// If the file does not start with a "/", assume it's relative to this current file.
			if($file{0} != '/'){
				$file = dirname($this->filename) . '/' . $file;
			}
			
			// Is the destination a directory or filename?
			// If it's a directory just tack on this current file's basename.
			if(substr($file, -1) == '/'){
				$file .= $this->getBaseFilename();
			}
			
			// Now dest can be instantiated as a valid file object!
			$dest = new File($file);
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
			$dir = dirname($this->filename);
			
			$f = $dir . '/' . $base . '.' . $ext;
			while(file_exists($f)){
				$f = $dir . '/' . $base . ' (' . ++$c . ')' . '.' . $ext;
			}
			
			$this->filename = $f;
		}
		
		
		// Ensure the directory exists.
		// This is essentially a recursive mkdir.
		$ds = explode('/', dirname($this->filename));
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
		return file_get_contents($this->filename);
	}
	
	public function putContents($data){
		return file_put_contents($this->filename, $data);
	}

	
	public static function FormatSize($filesize, $round = 2){
		$suf = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
		$c = 0;
		while($filesize >= 1024){
			$c++;
			$filesize = $filesize / 1024;
		}
		return (round($filesize, $round) . ' ' . $suf[$c]);
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
	 * Guess the mimetype for a given extension.
	 * 
	 * Some extensions may have multiple mimetypes based on the detection software,
	 *	so an array is returned with all possibilities for that extension.
	 * 
	 * @return array
	 */
	public static function GetMimetypeFromExtension($ext){
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
	
	public static function GetMimetypesFromExtensions($exts = array()){
		if(!is_array($exts)) return array();
		$ret = array();
		foreach($exts as $ext){
			$ret = array_merge($ret, File::GetMimetypeFromExtension($ext));
		}
		return $ret;
	}
	
	/**
	 * Display a preview of this file to the browser.  Must be an image.
	 * 
	 * @param string|int $dimensions A string of the dimensions to create the image at, widthxheight.
	 *                               Also supports the previous version of simply "dimension", as an int.
	 * @param boolean $includeHeader Include the correct mimetype header or no.
	 */
	public function displayPreview($dimensions = "300x300", $includeHeader = true){
		$m = $this->getMimetype();
		
		// The legacy support for simply a number.
		if(is_numeric($dimensions)){
			$width = $dimensions;
			$height = $dimensions;
		}
		else{
			// New method. Split on the "x" and that should give me the width/height.
			$vals = explode('x', strtolower($dimensions));
			$width = (int)$vals[0];
			$height = (int)$vals[1];
		}
		
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
				
				if($includeHeader) header('Content-Type: image/png');
				imagepng($img2);
				return;
			}
		}
		// __TODO__ Support text for previewing.	Use maxWidth as maxLines instead.
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
		return (strpos($this->filename, $path) !== false);
	}

	public function identicalTo($otherfile){
	
		if(is_a($otherfile, 'File') || $otherfile instanceof IFile){
			// Just compare the hashes.
			return ($this->getHash() == $otherfile->getHash());
		}
		else{
			// Can't be the same if it doesn't exist!
			if(!file_exists($otherfile)) return false;
			if(!file_exists($this->filename)) return false;
			$result = exec('diff -q "' . $this->filename . '" "' . $otherfile . '"', $array, $return);
			return ($return == 0);
		}		
	}
	
	public function exists(){
		return file_exists($this->filename);
	}
	
}
?>
