<?php
/**
 * Remote file backend.
 * 
 * Provides standard API access to remote HTTP files.
 * Could also in theory be used with FTP files, but untested.
 * 
 * @package Core
 * @since 2011.07
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl.html>
 * This system is licensed under the GNU LGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/lgpl-3.0.html>, 
 * and please contribute back to the community :)
 */


class File_remote_backend implements File_Backend{
	
	/**
	 * The username to use if basic authentication is required.
	 * 
	 * @var string
	 */
	public $username = null;
	
	/**
	 * The password to use if basic authentication is required.
	 * 
	 * @var string
	 */
	public $password = null;
	
	/**
	 * The fully resolved filename of this file.
	 * 
	 * @var string
	 */
	private $_url = null;
	
	/**
	 * A key/value paired array of headers for this given URL.
	 * Useful for determining if a file exists without downloading it.
	 * 
	 * @var array 
	 */
	private $_headers = null;
	
	/**
	 * The response code for this file.
	 * Generally 200, 302, or 404.
	 * 
	 * @var int
	 */
	private $_response = null;
	
	/**
	 * Temporary local version of the file.
	 * This is necessary for some operations such as "copyFrom" and "identicalTo"
	 * 
	 * @var File_local_backend
	 */
	private $_tmplocal = null;
	
	public function __construct($filename = null){
		if($filename) $this->setFilename($filename);
	}
	
	public function getFilesize($formatted = false){
		$h = $this->_getHeaders();
		
		$size = (isset($h['Content-Length']))? $h['Content-Length'] : 0;
		
		return ($formatted)? Core::FormatSize($size, 2) : $size;
	}
	
	public function getMimetype(){
		if(!$this->exists()) return null;
		
		$h = $this->_getHeaders();
		$type = (isset($h['Content-Type']))? $h['Content-Type'] : null;
		
		return $type;
	}
	
	public function getExtension(){
		return Core::GetExtensionFromString($this->getBaseFilename());
	}
	
	/**
	 * Get the URL of this file
	 * 
	 * @return string | null
	 */
	public function getURL(){
		
		// If basic authentication is required, this must be done in the URL.
		if($this->username && $this->password){
			$url = str_replace('://', '://' . $this->username . ':' . $this->password . '@', $this->_url);
		}
		elseif($this->username){
			$url = str_replace('://', '://' . $this->username . '@', $this->_url);
		}
		else{
			$url = $this->_url;
		}

		return $url;
	}
	
	/**
	 * Get the filename of this remote file.
	 */
	public function getFilename($prefix = ROOT_PDIR){
		return $this->_url;
	}
	
	public function setFilename($filename){
		$this->_url = $filename;
	}
	
	/**
	 * Get the base filename of this file.
	 */
	public function getBaseFilename($withoutext = false){
		// This will also intelligently pull from the Location header if it's set.
		$h = $this->_getHeaders();
		
		if(isset($h['Location'])) $f = $h['Location'];
		else $f = $this->_url;
		
		// Drop off the directory and everything else.
		// (yes, basename somehow works on URLs too!)
		$f = basename($f);
		if(strpos($f, '?') !== false){
			// Take off everything after the '?'.
			$f = substr($f, 0, strpos($f, '?'));
		}
		
		return $withoutext ? substr($f, 0, strrpos($f, '.')) : $f;
	}
	
	/**
	 * Get the filename for a local clone of this file.
	 * For local files, it's the same thing, but remote files will be copied to a temporary local location first.
	 * 
	 * @return string
	 */
	public function getLocalFilename(){
		return $this->_getTmpLocal()->getFilename();
	}
	
	/**
	 * Get an ascii hash of the filename.
	 * useful for transposing this file to another page call.
	 * 
	 * @return string The encoded string
	 */
	public function getFilenameHash(){
		return 'base64:' . base64_encode($this->_url);
	}
	
	/**
	 * Get the hash for this file.
	 */
	public function getHash(){
		die('Implement this method...');
	}
	
	public function delete(){
		throw new FileException('Cannot delete a remote file!');
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
			die('Implement this method...');
			// Well it should be damnit!....
			$file = $dest;
			
			// Get the location of the destination, be it relative or absolute.
			// If the file does not start with a "/", assume it's relative to this current file.
			//if($file{0} != '/'){
			//	$file = dirname($this->_filename) . '/' . $file;
			//}
			
			// Is the destination a directory or filename?
			// If it's a directory just tack on this current file's basename.
			if(substr($file, -1) == '/'){
				$file .= $this->getBaseFilename();
			}
			
			// Now dest can be instantiated as a valid file object!
			$dest = new File_local_backend($file);
		}
		
		if($this->identicalTo($dest)) return $dest;
		
		// GO!
		// The receiving function's logic will handle the rest.
		$dest->copyFrom($this, $overwrite);
		
		return $dest;
	}
	
	public function copyFrom($src, $overwrite = false){
		throw new FileException('Unable to write to remote files!');
	}
	
	public function getContents(){
		return $this->_getTmpLocal()->getContents();
	}
	
	public function putContents($data){
		throw new FileException('Unable to write to remote files!');
	}
	
	public function getContentsObject(){
		return FileContentFactory::GetFromFile($this);
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
		return ($this->isImage());
	}
	
	/**
	 * Display a preview of this file to the browser.  Must be an image.
	 * 
	 * @param string|int $dimensions A string of the dimensions to create the image at, widthxheight.
	 *                               Also supports the previous version of simply "dimension", as an int.
	 * @param boolean $includeHeader Include the correct mimetype header or no.
	 */
	public function displayPreview($dimensions = "300x300", $includeHeader = true){
		// @todo Revise this method!
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
		// @todo Revise this method!
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
		
		if(!$this->exists()){
			// Log it so the admin knows that the file is missing, otherwise nothing is shown.
			error_log('File not found [ ' . $this->_filename . ' ]', E_USER_NOTICE);
			
			// Return a 404 image.
			$size = Core::TranslateDimensionToPreviewSize($width, $height);
			return Core::ResolveAsset('mimetype_icons/notfound-' . $size . '.png');
		}
		elseif($this->isPreviewable()){
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
		// @todo Revise this method!
		// The path should be fully resolved, (the file is).
		if(strpos($path, ROOT_PDIR) === false) $path = ROOT_PDIR . $path;
		
		// Just a simple strpos shortcut...
		return (strpos($this->_filename, $path) !== false);
	}

	public function identicalTo($otherfile){
	
		if(is_a($otherfile, 'File') || $otherfile instanceof File_Backend){
			// Just compare the hashes.
			//var_dump($this->getHash(), $this, $otherfile->getHash(), $otherfile); die();
			return ($this->_getTmpLocal()->getHash() == $otherfile->getHash());
		}
		else{
			// Can't be the same if it doesn't exist!
			if(!file_exists($otherfile)) return false;
			
			$result = exec('diff -q "' . $this->_getTmpLocal()->getFilename() . '" "' . $otherfile . '"', $array, $return);
			return ($return == 0);
		}
	}
	
	public function exists(){
		$this->_getHeaders();
		
		return ($this->_response != 404);
	}
	
	public function isReadable(){
		$this->_getHeaders();
		
		return ($this->_response != 404);
	}
	
	public function isLocal(){
		// Simple function that indicates if the file is on a local filesystem
		// Please note, even mounted filesystems are considered local for this matter.
		// Amazon S3 and other CDN services.... are not.
		
		return false;
	}
	
	public function getMTime(){
		// Remote files don't support modified time.
		return false;
	}
	
	/**
	 * Get the headers for this given file. 
	 */
	private function _getHeaders(){
		if($this->_headers === null){
			$this->_headers = array();
			
			// I like curl better because it doesn't make a GET request when 
			// all I want to do is a HEAD request.
			// Just give me HEAD damnit!..... :p
			
			$curl = curl_init();
			curl_setopt_array( $curl, array(
				CURLOPT_HEADER => true,
				CURLOPT_NOBODY => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_URL => $this->getURL(),
				CURLOPT_HTTPHEADER => Core::GetStandardHTTPHeaders(true),
			));
			$h = explode( "\n", curl_exec( $curl ) );
			curl_close( $curl );
			
			foreach($h as $line){
				if(strpos($line, 'HTTP/1.') !== false){
					$this->_response = substr($line, 9, 3);
					continue;
				}
				if(strpos($line, ':') !== false){
					$k = substr($line, 0, strpos($line, ':'));
					$v = trim(substr($line, strpos($line, ':') + 1));
					$this->_headers[$k] = $v;
				}
			}
		}
		
		return $this->_headers;
	}
	
	private function _getHeader($header){
		$h = $this->_getHeaders();
		return (isset($h[$header])) ? $h[$header] : null;
	}
	
	/**
	 * Get the temporary local version of the file.
	 * This is useful for doing operations such as hash and identicalto.
	 * 
	 * @return File_local_backend 
	 */
	private function _getTmpLocal(){
		if($this->_tmplocal === null){
			$f = md5($this->getFilename());
			// Gotta love obviously-named flags.
			$needtodownload = true;
			
			$this->_tmplocal = new File_local_backend('tmp/remotefile-cache/' . $f);
			
			// File exists already!  Check and see if it needs to be redownloaded.
			if($this->_tmplocal->exists()){
				// Lookup this file in the system cache.
				$systemcachedata = Core::Cache()->get('remotefile-cache-header-' . $f);
				if($systemcachedata){
					// I can only look them up if the cache is available.
					
					// First attempt, try ETag.
					if($this->_getHeader('ETag')){
						$needtodownload = ($this->_getHeader('ETag') != $systemcachedata['etag']);
					}
					// No?  How 'bout 
					elseif($this->_getHeader('Last-Modified')){
						$needtodownload = ($this->_getHeader('Last-Modified') != $systemcachedata['last-modified']);
					}
					// Still no?  The default is to download it anway.
				}
			}
			
			if($needtodownload){
				// Create a stream
				$opts = array(
					'http' => array(
						'protocol_version' => '1.1',
						'method' => "GET",
						'header' => Core::GetStandardHTTPHeaders(false, true)
					)
				);

				$context = stream_context_create($opts);
				// Copy the data down to the local file.
				$this->_tmplocal->putContents(file_get_contents( $this->getURL(), false, $context));
				
				// And remember this header data for nexttime.
				Core::Cache()->set('remotefile-cache-header-' . $f, array(
					'etag' => $this->_getHeader('ETag'),
					'last-modified' => $this->_getHeader('Last-Modified'),
				));
			}
		}
		
		return $this->_tmplocal;
	}
}
