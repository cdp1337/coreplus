<?php
/**
 * Remote file backend.
 *
 * Provides standard API access to remote HTTP files.
 *
 * @package Core\Filestore\Backends
 * @since 2.5.6
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2017  Charlie Powell
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

use Core\Cache;
use Core\Filestore;


class FileRemote implements Filestore\File {

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
	 * Set to false to require the remote file to be downloaded every request.
	 * @var bool
	 */
	public $cacheable = true;

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
	 * @var FileLocal
	 */
	private $_tmplocal = null;
	
	private $_requestHeaders = null;
	
	private $_method = 'GET';
	
	private $_payload = null;

	/**
	 * If the file was a 302, this is the temporary redirect placeholder.
	 *
	 * This is a separate file because according to the RFC2616 spec,
	 * requests that fall under a 302 are independent of each other and should be cached independently.
	 *
	 * @var null|FileRemote
	 */
	protected $_redirectFile = null;

	/**
	 * Level of redirect counts this file request is under.
	 *
	 * Used to prevent infinite redirect loops.
	 *
	 * @var int
	 */
	protected $_redirectCount = 0;

	public function __construct($filename = null) {
		if ($filename) $this->setFilename($filename);
		
		// Set the initial  headers when requesting this file over HTTP.
		$this->_requestHeaders = \Core::GetStandardHTTPHeaders(true);
	}

	/**
	 * Get the title of this file, either generated from the filename or pulled from the meta data as appropriate.
	 *
	 * @return string
	 */
	public function getTitle(){
		$metas = new Filestore\FileMetaHelper($this);

		// If no title was set, I need to pick one by default.
		if(($t = $metas->getMetaTitle('title'))){
			return $t;
		}
		else{
			// Generate a moderately meaningful title from the filename.
			$title = $this->getBasename(true);
			$title = preg_replace('/[^a-zA-Z0-9 ]/', ' ', $title);
			$title = trim(preg_replace('/[ ]+/', ' ', $title));
			$title = ucwords($title);
			return $title;
		}
	}

	public function getFilesize($formatted = false) {
		$h = $this->_getHeaders();

		if(isset($h['Content-Length'])){
			// yay!
			$size = $h['Content-Length'];
		}
		else{
			// Well damn, download the file then...
			$tmp = $this->_getTmpLocal();
			$size = $tmp->getFilesize(false);
		}

		return ($formatted) ? \Core::FormatSize($size, 2) : $size;
	}

	public function getMimetype() {
		if (!$this->exists()) return null;

		$h    = $this->_getHeaders();
		$type = (isset($h['Content-Type'])) ? $h['Content-Type'] : null;

		return $type;
	}

	public function getExtension() {
		return \Core::GetExtensionFromString($this->getBasename());
	}

	/**
	 * Get the URL of this file
	 *
	 * @return string | null
	 */
	public function getURL() {

		// If basic authentication is required, this must be done in the URL.
		if ($this->username && $this->password) {
			$url = str_replace('://', '://' . $this->username . ':' . $this->password . '@', $this->_url);
		}
		elseif ($this->username) {
			$url = str_replace('://', '://' . $this->username . '@', $this->_url);
		}
		else {
			$url = $this->_url;
		}

		return $url;
	}

	/**
	 * Get the filename of this remote file.
	 */
	public function getFilename($prefix = ROOT_PDIR) {
		return $this->_url;
	}

	/**
	 * Get the base filename of this file.
	 */
	public function getBaseFilename($withoutext = false) {
		return $this->getBasename($withoutext);
	}

	/**
	 * Get the directory name of this file
	 *
	 * Will return the parent directory name, ending with a trailing slash.
	 *
	 * @return string
	 */
	public function getDirectoryName(){
		return dirname($this->getFilename()) . '/';
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
	 * Get an ascii hash of the filename.
	 * useful for transposing this file to another page call.
	 *
	 * @return string The encoded string
	 */
	public function getFilenameHash() {
		return 'base64:' . base64_encode($this->_url);
	}

	/**
	 * Get the hash for this file.
	 */
	public function getHash() {
		// In order for this to work, I need to apply the operation on a local copy.
		$local = $this->_getTmpLocal();
		return $local->getHash();
	}

	public function delete() {
		throw new \Exception('Cannot delete a remote file!');
	}

	/**
	 * Copies the file to the requested destination.
	 * If the destination is a directory (ends with a '/'), the same filename is used, (if possible).
	 * If the destination is relative, ('.' or 'subdir/'), it is assumed relative to the current file.
	 *
	 * @param string|Filestore\File $dest
	 * @param boolean     $overwrite
	 *
	 * @return Filestore\File
	 */
	public function copyTo($dest, $overwrite = false) {
		//echo "Copying " . $this->_filename . " to " . $dest . "\n"; // DEBUG //

		if (!(is_a($dest, 'File') || $dest instanceof Filestore\File)) {
			// Well it should be damnit!....
			$file = $dest;

			// Get the location of the destination, be it relative or absolute.
			// If the file does not start with a "/", assume it's relative to this current file.
			//if($file{0} != '/'){
			//	$file = dirname($this->_filename) . '/' . $file;
			//}

			// Is the destination a directory or filename?
			// If it's a directory just tack on this current file's basename.
			if (substr($file, -1) == '/') {
				$file .= $this->getBaseFilename();
			}

			// Now dest can be instantiated as a valid file object!
			$dest = Filestore\Factory::File($file);
		}

		if ($this->identicalTo($dest)) return $dest;

		// GO!
		// The receiving function's logic will handle the rest.
		$dest->copyFrom($this, $overwrite);

		return $dest;
	}

	public function copyFrom(Filestore\File $src, $overwrite = false) {
		throw new \Exception('Unable to write to remote files!');
	}

	public function getContents() {
		return $this->_getTmpLocal()->getContents();
	}

	public function putContents($data) {
		throw new \Exception('Unable to write to remote files!');
	}

	public function getContentsObject() {
		return Filestore\resolve_contents_object($this);
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
		return ($this->isImage());
	}

	/**
	 * Display a preview of this file to the browser.  Must be an image.
	 *
	 * @param string|int $dimensions A string of the dimensions to create the image at, widthxheight.
	 *                               Also supports the previous version of simply "dimension", as an int.
	 * @param boolean    $includeHeader Include the correct mimetype header or no.
	 */
	public function displayPreview($dimensions = "300x300", $includeHeader = true) {
		if (!$this->exists()) {
			// Log it so the admin knows that the file is missing, otherwise nothing is shown.
			error_log('File not found [ ' . $this->_url . ' ]', E_USER_NOTICE);

			// Return a 404 image.
			$file = Filestore\Factory::File('asset/images/mimetypes/notfound.png');
			$file->displayPreview($dimensions, $includeHeader);
		}
		else{
			// Yay, just use the local copy to get the preview url :p
			$file = $this->_getTmpLocal();
			$file->displayPreview($dimensions, $includeHeader);
		}
	}

	public function getPreviewURL($dimensions = "300x300") {
		if (!$this->exists()) {
			// Log it so the admin knows that the file is missing, otherwise nothing is shown.
			error_log('File not found [ ' . $this->_url . ' ]', E_USER_NOTICE);

			// Return a 404 image.
			$file = Filestore\Factory::File('asset/images/mimetypes/notfound.png');
			return $file->getPreviewURL($dimensions);
		}
		else{
			// Yay, just use the local copy to get the preview url :p
			$file = $this->_getTmpLocal();
			return $file->getPreviewURL($dimensions);
		}
	}

	/**
	 * See if this file is in the requested directory.
	 *
	 * @param $path string
	 *
	 * @return boolean
	 */
	public function inDirectory($path) {
		// Just a simple strpos shortcut...
		return (strpos($this->_url, $path) !== false);
	}

	/**
	 * @param Filestore\File|string $otherfile
	 *
	 * @return bool
	 */
	public function identicalTo($otherfile) {

		if (is_a($otherfile, 'File') || $otherfile instanceof Filestore\File) {
			// Just compare the hashes.
			//var_dump($this->getHash(), $this, $otherfile->getHash(), $otherfile); die();
			return ($this->_getTmpLocal()->getHash() == $otherfile->getHash());
		}
		else {
			// Can't be the same if it doesn't exist!
			if (!file_exists($otherfile)){
				return false;
			}

			$result = exec('diff -q "' . $this->_getTmpLocal()->getFilename() . '" "' . $otherfile . '"', $array, $return);
			return ($return == 0);
		}
	}

	public function exists() {
		$this->_getHeaders();

		return ($this->_response != 404);
	}

	public function isReadable() {
		$this->_getHeaders();

		return ($this->_response != 404);
	}

	public function isOK(){
		$this->_getHeaders();

		return ($this->_response == 200 || $this->_response == 301 || $this->_response == 302);
	}

	public function requiresAuthentication(){
		$this->_getHeaders();

		return ($this->_response == 401 || $this->_response == 403);
	}

	/**
	 * Get the HTTP status code for this file.
	 *
	 * @return int
	 */
	public function getStatus(){
		$this->_getHeaders();
		return $this->_response;
	}

	public function isLocal() {
		// Simple function that indicates if the file is on a local filesystem
		// Please note, even mounted filesystems are considered local for this matter.
		// Amazon S3 and other CDN services.... are not.

		return false;
	}

	public function getMTime() {
		// Remote files don't support modified time.
		return false;
	}

	/**
	 * Get the base filename of this file.
	 *
	 * This makes use of the Content-Disposition header as per RFC2616 Section 19.5 Item 1
	 * @url http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html
	 *
	 * For example:
	 * <pre>
	 *  Content-Type: image/jpeg
	 *  Content-Disposition: attachment; filename=genome.jpeg;
	 *      modification-date="Wed, 12 Feb 1997 16:29:51 -0500";
	 *  Content-Description: a complete map of the human genome
	 *
	 *  &lt;jpeg data&gt;
	 * </pre>
	 *
	 * @param boolean $withoutext Set to true to drop the extension.
	 *
	 * @return string
	 */
	public function getBasename($withoutext = false) {
		$basename = null;

		// First, check the Content-Disposition Header.
		$d = $this->_getHeader('Content-Disposition');
		if($d !== null) {
			// Content-Disposition WAS provided by the server!
			// This means that we can read that filename and know what the server wanted the file to be named.
			$dParts = explode(';', $d);
			foreach($dParts as $p) {
				if(strpos($p, 'filename=') !== false) {
					$value = trim(substr($p, strpos($p, '=') + 1), " '\"");

					// Any directories requested? (Directories here are NOT supported by the spec)
					$value = str_replace('/', '-', $value);

					$basename = $value;
				}
			}
		}

		if($basename === null && ($l = $this->_getHeader('Location'))){
			// This will also pull from the Location header if it's set.
			$basename = $l;
		}

		// Still no?  Just use the URL.
		if($basename === null){
			$basename = $this->getFilename();
		}

		if (strpos($basename, '?') !== false) {
			// Take off everything after the '?'.
			$basename = substr($basename, 0, strpos($basename, '?'));
		}

		// Drop off the directory and everything else.
		// (yes, basename somehow works on URLs too!)
		$basename = basename($basename);

		if ($withoutext) {
			$ext = $this->getExtension();
			if($ext != '') {
				return substr($basename, 0, (-1 - strlen($ext)));
			}
		}

		return $basename;
	}

	/**
	 * Rename this file to a new name
	 *
	 * @param $newname
	 *
	 * @return boolean
	 */
	public function rename($newname) {
		return false;
	}

	/**
	 * Get the mimetype icon for this file.
	 *
	 * @param string $dimensions
	 *
	 * @return string
	 */
	public function getMimetypeIconURL($dimensions = '32x32'){
		$filemime = str_replace('/', '-', $this->getMimetype());

		$file = Filestore\Factory::File('assets/images/mimetypes/' . $filemime . '.png');
		if(!$file->exists()){
			if(DEVELOPMENT_MODE){
				// Inform the developer, otherwise it's not a huge issue.
				error_log('Unable to locate mimetype icon [' . $filemime . '], resorting to "unknown" (filename: ' . $this->getFilename('') . ')');
			}
			$file = Filestore\Factory::File('assets/images/mimetypes/unknown.png');
		}
		return $file->getPreviewURL($dimensions);
	}

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
	public function getQuickPreviewFile($dimensions = '300x300') {
		return $this->_getTmpLocal()->getQuickPreviewFile($dimensions);
	}

	/**
	 * Get the preview file with the contents copied over resized/previewed.
	 *
	 * @param string $dimensions
	 *
	 * @return File
	 */
	public function getPreviewFile($dimensions = '300x300') {
		return $this->_getTmpLocal()->getPreviewFile($dimensions);
	}

	/**
	 * Check if this file is writable.
	 *
	 * @return boolean
	 */
	public function isWritable() {
		return false;
	}
	
	public function setFilename($filename) {
		$this->_url = $filename;
	}

	/**
	 * Set the request method for this remote file
	 * 
	 * @param string $method
	 *
	 * @throws \Exception
	 */
	public function setMethod($method){
		$method = strtoupper($method);
		switch($method){
			case 'GET':
			case 'POST':
				$this->_method = $method;
				break;
			default:
				throw new \Exception('Unsupported method: ' . $method);
		}
	}
	
	public function setPayload($data){
		if($this->_method == 'GET'){
			$this->_method = 'POST';
		}
		
		if(!is_array($data)){
			throw new \Exception('POST payloads MUST be an associative array.');
		}
		
		$this->_payload = $data;
	}

	/**
	 * Set a particular REQUEST header to this file.
	 *
	 * @param string $value The header key to set before the ':')
	 * @param string $key   The header value to set (after the ':')
	 */
	public function setRequestHeader($value, $key){
		$this->_requestHeaders[] = $value . ': ' . $key;
	}

	/**
	 * Get the headers for this given file.
	 * This will go out and query the server with a HEAD request if no headers set otherwise.
	 * 
	 * ONLY applicable with GET based requests!
	 */
	protected function _getHeaders() {

		if($this->_method == 'POST' && $this->_headers === null){
			return [];
		}
		
		if ($this->_headers === null) {
			$this->_headers = array();

			// I like curl better because it doesn't make a GET request when 
			// all I want to do is a HEAD request, JUST THE TIP!

			$curl = curl_init();
			curl_setopt_array(
				$curl, array(
					CURLOPT_HEADER         => true,
					CURLOPT_NOBODY         => true,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_URL            => $this->getURL(),
					CURLOPT_HTTPHEADER     => $this->_requestHeaders,
				)
			);

			$result = curl_exec($curl);
			if($result === false){
				switch(curl_errno($curl)){
					case CURLE_COULDNT_CONNECT:
					case CURLE_COULDNT_RESOLVE_HOST:
					case CURLE_COULDNT_RESOLVE_PROXY:
						$this->_response = 404;
						break;
					default:
						$this->_response = 500;
						break;
				}
			}

			$h = explode("\n", $result);
			curl_close($curl);

			// Will read all the headers
			foreach ($h as $line) {
				if (strpos($line, 'HTTP/1.') !== false) {
					$this->_response = substr($line, 9, 3);
				}
				elseif (strpos($line, ':') !== false) {
					$k                  = substr($line, 0, strpos($line, ':'));
					$v                  = trim(substr($line, strpos($line, ':') + 1));
					// Content-Type can have an embedded charset request.
					if($k == 'Content-Type' && strpos($v, 'charset=') !== false){
						$this->_headers['Charset'] = substr($v, strpos($v, 'charset=') + 8);
						$v = substr($v, 0, strpos($v, 'charset=') - 2);
					}
					$this->_headers[$k] = $v;
				}
			}

			if(($this->_response == '302' || $this->_response == '301') && isset($this->_headers['Location'])){
				/*
				From: http://www.ietf.org/rfc/rfc2616.txt and http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html

				10.3.3 302 Found

				The requested resource resides temporarily under a different URI.
				Since the redirection might be altered on occasion, the client SHOULD continue to use the Request-URI for future requests.
				This response is only cacheable if indicated by a Cache-Control or Expires header field.

				The temporary URI SHOULD be given by the Location field in the response.
				Unless the request method was HEAD,
				the entity of the response SHOULD contain a short hypertext note with a hyperlink to the new URI(s).

				If the 302 status code is received in response to a request other than GET or HEAD,
				the user agent MUST NOT automatically redirect the request unless it can be confirmed by the user,
				since this might change the conditions under which the request was issued.
				*/
				$newcount = $this->_redirectCount + 1;
				if($newcount <= 5){
					$this->_redirectFile = new FileRemote();
					$this->_redirectFile->_redirectCount = ($this->_redirectCount + 1);
					$this->_redirectFile->setFilename($this->_headers['Location']);
					$this->_redirectFile->_getHeaders();
				}
				else{
					trigger_error('Too many redirects when requesting ' . $this->getURL(), E_USER_WARNING);
				}
			}
		}

		if(($this->_response == '302' || $this->_response == '301') && $this->_redirectFile !== null){
			return $this->_redirectFile->_headers;
		}
		else{
			return $this->_headers;
		}

	}

	protected function _getHeader($header) {
		$h = $this->_getHeaders();
		return (isset($h[$header])) ? $h[$header] : null;
	}

	/**
	 * Get the temporary local version of the file.
	 * This is useful for doing operations such as hash and identicalto.
	 *
	 * @return FileLocal
	 */
	protected function _getTmpLocal() {
		if ($this->_tmplocal === null) {
			$f = md5($this->getFilename());

			// Gotta love obviously-named flags.
			$needtodownload = true;

			$this->_tmplocal = Filestore\Factory::File('tmp/remotefile-cache/' . $f);

			// File exists already!  Check and see if it needs to be redownloaded.
			if ($this->cacheable && $this->_tmplocal->exists()) {
				// Lookup this file in the system cache.
				$systemcachedata = Cache::Get('remotefile-cache-header-' . $f);
				if ($systemcachedata && isset($systemcachedata['headers'])) {
					// I can only look them up if the cache is available.

					// First check will be the expires header.
					if(isset($systemcachedata['headers']['Expires']) && strtotime($systemcachedata['headers']['Expires']) > time()){
						$needtodownload = false;
						// And set the headers!
						// This is required
						$this->_headers = $systemcachedata['headers'];
						$this->_response = $systemcachedata['response'];
					}
					// Next, try ETag.
					elseif ($this->_getHeader('ETag') && isset($systemcachedata['headers']['ETag'])) {
						$needtodownload = ($this->_getHeader('ETag') != $systemcachedata['headers']['ETag']);
					}
					// No?  How 'bout 
					elseif ($this->_getHeader('Last-Modified') && isset($systemcachedata['headers']['Last-Modified'])) {
						$needtodownload = ($this->_getHeader('Last-Modified') != $systemcachedata['headers']['Last-Modified']);
					}
					// Still no?  The default is to download it anyway.
				}
			}

			if ($needtodownload || !$this->cacheable) {
				// Make sure that the headers are updated, this is a requirement to use the 302 tag.
				$this->_getHeaders();
				if(($this->_response == '302' || $this->_response == '301') && $this->_redirectFile !== null){
					$this->_tmplocal = $this->_redirectFile->_getTmpLocal();
				}
				else{
					// BTW, use cURL.
					$curl = curl_init();
					curl_setopt_array(
						$curl, array(
							CURLOPT_HEADER         => false,
							CURLOPT_NOBODY         => false,
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_URL            => $this->getURL(),
							CURLOPT_HTTPHEADER     => \Core::GetStandardHTTPHeaders(true),
						)
					);
					
					if($this->_method == 'POST'){
						curl_setopt($curl, CURLOPT_POSTFIELDS, $this->_payload);
					}

					$result = curl_exec($curl);
					if($result === false){
						switch(curl_errno($curl)){
							case CURLE_COULDNT_CONNECT:
							case CURLE_COULDNT_RESOLVE_HOST:
							case CURLE_COULDNT_RESOLVE_PROXY:
								$this->_response = 404;
								return $this->_tmplocal;
								break;
							default:
								$this->_response = 500;
								return $this->_tmplocal;
								break;
						}
					}

					curl_close($curl);

					// Copy the data down to the local file.
					$this->_tmplocal->putContents($result);
				}

				// And remember this header data for nexttime.
				Cache::Set(
					'remotefile-cache-header-' . $f,
					[
						'headers'  => $this->_getHeaders(),
						'response' => $this->_response,
					]
				);
			}
		}

		return $this->_tmplocal;
	}

	/**
	 * Send a file to the user agent
	 *
	 * @param bool $forcedownload Set to true to force download instead of just sending the file.
	 *
	 * @throws \Exception
	 *
	 * @return void
	 */
	public function sendToUserAgent($forcedownload = false) {
		$view = \Core\view();
		$request = \Core\page_request();

		$view->mode = \View::MODE_NOOUTPUT;
		$view->contenttype = $this->getMimetype();
		$view->updated = $this->getMTime();
		if($forcedownload){
			$view->headers['Content-Disposition'] = 'attachment; filename="' . $this->getBasename() . '"';
			$view->headers['Cache-Control'] = 'no-cache, must-revalidate';
			$view->headers['Content-Transfer-Encoding'] = 'binary';
		}
		$view->headers['Content-Length'] = $this->getFilesize();

		// Send all the view headers
		$view->render();

		// And now the actual content if it's not a HEAD request.
		if($request->method != \PageRequest::METHOD_HEAD){
			echo $this->getContents();
		}
	}
}

