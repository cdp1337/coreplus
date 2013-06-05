<?php
/**
 * The local file object
 *
 * @package Core\Filestore
 * @since 2.5.6
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

namespace Core\Filestore\Backends;

use Core\Filestore;

class FileLocal implements Filestore\File {

	public $_type = Filestore\File::TYPE_OTHER;

	/**
	 * The fully resolved filename of this file.
	 *
	 * @var string
	 */
	protected $_filename = null;

	public function __construct($filename = null) {
		if ($filename) $this->setFilename($filename);
	}

	public function getFilesize($formatted = false) {
		$f = filesize($this->_filename);
		return ($formatted) ? Filestore\format_size($f, 2) : $f;
	}

	public function getMimetype() {
		if (!$this->exists()) return null;
		// PEAR, you have failed me for the last time... :'(
		//return MIME_Type::autoDetect($this->_filename);

		if (!function_exists('finfo_open')) {
			// This is a backwards-compatability fix for when PHP doesn't have a certain PECL extension installed.
			$cli = exec('file -ib "' . $this->_filename . '"');
			list($type,) = explode(';', $cli);
			$type = trim($type);
		}
		else {
			$finfo = finfo_open(FILEINFO_MIME);
			$type  = finfo_file($finfo, $this->_filename);
			finfo_close($finfo);
		}

		// $type may have some extra crap after a semicolon.
		if (($pos = strpos($type, ';')) !== false) $type = substr($type, 0, $pos);
		$type = trim($type);

		// There are a few exceptions to the rule.... namely with plain text.
		$ext = strtolower($this->getExtension());
		if     ($ext == 'js'   && $type == 'text/plain')               $type = 'text/javascript';
		elseif ($ext == 'js'   && $type == 'text/x-c++')               $type = 'text/javascript';
		elseif ($ext == 'js'   && $type == 'text/x-asm')               $type = 'text/javascript';
		elseif ($ext == 'css'  && $type == 'text/plain')               $type = 'text/css';
		elseif ($ext == 'css'  && $type == 'text/x-c')                 $type = 'text/css';
		elseif ($ext == 'css'  && $type == 'text/x-asm')               $type = 'text/css';
		elseif ($ext == 'css'  && $type == 'text/troff')               $type = 'text/css';
		elseif ($ext == 'html' && $type == 'text/plain')               $type = 'text/html';
		elseif ($ext == 'html' && $type == 'text/x-c++')               $type = 'text/html';
		elseif ($ext == 'html' && $type == 'text/troff')               $type = 'text/html';
		elseif ($ext == 'ttf'  && $type == 'application/octet-stream') $type = 'font/ttf';
		elseif ($ext == 'otf'  && $type == 'application/octet-stream') $type = 'font/otf';
		elseif ($ext == 'csv'  && $type == 'text/plain')               $type = 'text/csv';

		return $type;
	}

	public function getExtension() {
		return \Core\get_extension_from_string($this->_filename);
		//return substr($this->_filename, strrpos($this->_filename, '.'));
	}

	/**
	 * Get a filename that can be retrieved from the web.
	 * Resolves with the ROOT_DIR prefix already attached.
	 *
	 * @return string | false
	 */
	public function getURL() {
		if (!preg_match('/^' . str_replace('/', '\\/', ROOT_PDIR) . '/', $this->_filename)) return false;

		return preg_replace('/^' . str_replace('/', '\\/', ROOT_PDIR) . '(.*)/', ROOT_URL . '$1', $this->_filename);
	}

	/**
	 * Get the filename of this file resolved to a specific directory, usually ROOT_PDIR or ROOT_WDIR.
	 */
	public function getFilename($prefix = ROOT_PDIR) {
		// Since the filename is stored fully resolved...
		if ($prefix == ROOT_PDIR) return $this->_filename;

		if ($prefix === false) {
			// Trim off all the prefacing components from the filename.
			if ($this->_type == 'asset')
				return 'asset/' . substr($this->_filename, strlen(self::$_Root_pdir_assets));
			elseif ($this->_type == 'public')
				return 'public/' . substr($this->_filename, strlen(self::$_Root_pdir_public));
			elseif ($this->_type == 'private')
				return 'private/' . substr($this->_filename, strlen(self::$_Root_pdir_private));
			elseif ($this->_type == 'tmp')
				return 'tmp/' . substr($this->_filename, strlen(self::$_Root_pdir_tmp));
			else
				return $this->_filename;
		}

		return preg_replace('/^' . str_replace('/', '\\/', ROOT_PDIR) . '(.*)/', $prefix . '$1', $this->_filename);
	}

	/**
	 * Set the filename of this file manually.
	 * Useful for operating on a file after construction.
	 *
	 * @param $filename string
	 */
	public function setFilename($filename) {
		// If this file already has a filename, ensure that it's deleted from cache!
		if($this->_filename){
			Filestore\Factory::RemoveFromCache($this);
		}

		if ($filename{0} != '/') $filename = ROOT_PDIR . $filename; // Needs to be fully resolved

		// Do some cleaning on the filename, ie: // should be just /.
		$filename = preg_replace(':/+:', '/', $filename);

		$this->_filename = $filename;

		// Resolve if this is an asset, public, etc.
		// This is to speed up the other functions so they don't have to perform this operation.
		if(strpos($this->_filename, Filestore\get_asset_path()) === 0){
			$this->_type = 'asset';
		}
		elseif(strpos($this->_filename, Filestore\get_public_path()) === 0){
			$this->_type = 'public';
		}
		elseif(strpos($this->_filename, Filestore\get_tmp_path()) === 0){
			$this->_type = 'tmp';
		}
	}

	/**
	 * Get the base filename of this file.
	 *
	 * @param boolean $withoutext Set to true to drop the extension.
	 *
	 * @return string
	 */
	public function getBasename($withoutext = false) {
		$b = basename($this->_filename);

		if ($withoutext) {
			$ext = $this->getExtension();
			if($ext != '') {
				return substr($b, 0, (-1 - strlen($ext)));
			}
		}

		return $b;
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
		return dirname($this->_filename) . '/';
	}

	/**
	 * Get the filename for a local clone of this file.
	 * For local files, it's the same thing, but remote files will be copied to a temporary local location first.
	 *
	 * @return string
	 */
	public function getLocalFilename() {
		return $this->getFilename();
	}

	/**
	 * Get an ascii hash of the filename.
	 * useful for transposing this file to another page call.
	 *
	 * @return string The encoded string
	 */
	public function getFilenameHash() {
		if ($this->_type == 'asset'){
			$base = 'assets/';
			$filename = substr($this->_filename, strlen(Filestore\get_asset_path()));
			// If the filename starts with the current theme, (which it very well may),
			// trim that off too.
			// this script is meant to be a generic resource handle that gets resolved by the receiving script.
			if(strpos($filename, \ConfigHandler::Get('/theme/selected') . '/') === 0){
				$filename = substr($filename, strlen(\ConfigHandler::Get('/theme/selected')) + 1);
			}
			elseif(strpos($filename, 'default/') === 0){
				$filename = substr($filename, 8);
			}
			// And now I can add the base onto it.
			$filename = $base . $filename;
		}
		elseif ($this->_type == 'public'){
			$filename = 'public/' . substr($this->_filename, strlen(Filestore\get_public_path() ));
		}
		elseif ($this->_type == 'private'){
			$filename = 'private/' . substr($this->_filename, strlen(Filestore\get_private_path() ));
		}
		elseif ($this->_type == 'tmp'){
			$filename = 'tmp/' . substr($this->_filename, strlen(Filestore\get_tmp_path() ));
		}
		else{
			$filename = $this->_filename;
		}

		return 'base64:' . base64_encode($filename);
	}

	/**
	 * Get the hash for this file.
	 */
	public function getHash() {
		if (!file_exists($this->_filename)) return null;

		return md5_file($this->_filename);
	}

	public function delete() {
		$ftp    = \Core\FTP();
		$tmpdir = TMP_DIR;
		if ($tmpdir{0} != '/') $tmpdir = ROOT_PDIR . $tmpdir; // Needs to be fully resolved

		if(
			!$ftp || // FTP not enabled or
			(strpos($this->_filename, $tmpdir) === 0) // Destination is a temporary file.
		){
			if (!@unlink($this->getFilename())) return false;
			$this->_filename = null;
			return true;
		}
		else{
			if(!ftp_delete($ftp, $this->getFilename())) return false;
			$this->_filename = null;
			return true;
		}
	}

	public function rename($newname){
		// If the new name is not fully resolved, translate it to the same as the current directory.
		if($newname{0} != '/'){
			$newname = substr($this->getFilename(), 0, 0 - strlen($this->getBaseFilename())) . $newname;
		}

		if(self::_Rename($this->getFilename(), $newname)){
			$this->_filename = $newname;
			return true;
		}

		return false;
	}

	/**
	 * Copies the file to the requested destination.
	 * If the destination is a directory (ends with a '/'), the same filename is used, (if possible).
	 * If the destination is relative, ('.' or 'subdir/'), it is assumed relative to the current file.
	 *
	 * @param string|File $dest
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
			if ($file{0} != '/') {
				$file = dirname($this->_filename) . '/' . $file;
			}

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

	/**
	 * Make a copy of a source Filestore\File into this File.
	 *
	 * (Generally only useful internally)
	 *
	 * @param      $src Source file backend
	 * @param bool $overwrite true to overwrite existing file
	 *
	 * @throws Exception
	 * @return bool True or False if succeeded.
	 */
	public function copyFrom($src, $overwrite = false) {
		// Don't overwrite existing files unless told otherwise...
		if (!$overwrite) {
			$c    = 0;
			$ext  = $this->getExtension();
			$base = $this->getBaseFilename(true);
			$dir  = dirname($this->_filename);
			$prefix = $dir . '/' . $base;
			$suffix = (($ext == '') ? '' : '.' . $ext);
			$thathash = $src->getHash();

			$f = $prefix . $suffix;
			while(file_exists($f) && md5_file($f) != $thathash){
				$f = $prefix . ' (' . ++$c . ')' . $suffix;
			}

			$this->_filename = $f;
		}

		// And do the actual copy!
		// To save memory, try to use as low-level functions as possible.
		$localfilename = $src->getLocalFilename();

		$ftp    = \Core\FTP();
		$tmpdir = TMP_DIR;
		if ($tmpdir{0} != '/') $tmpdir = ROOT_PDIR . $tmpdir; // Needs to be fully resolved

		// Resolve it from its default.
		// This is provided from a config define, (probably).
		$mode = (defined('DEFAULT_FILE_PERMS') ? DEFAULT_FILE_PERMS : 0644);

		// Make sure the directory exists first!
		// This has to be done regardless of FTP mode or not.
		self::_Mkdir(dirname($this->_filename), null, true);

		if (
			!$ftp || // FTP not enabled or
			(strpos($this->_filename, $tmpdir) === 0) // Destination is a temporary file.
		) {
			// Read in only so much data at a time.  This is to prevent
			// PHP from trying to read a full 2GB file into memory at once :S
			$maxbuffer = (1024 * 1024 * 10);
			$handlein  = fopen($localfilename, 'r');
			$handleout = fopen($this->_filename, 'w');

			// Couldn't get a lock on both input and output files.
			if(!$handlein){
				throw new Exception('Unable to open file ' . $localfilename . ' for reading.');
			}
			if(!$handleout){
				throw new Exception('Unable to open file ' . $this->_filename . ' for writing.');
			}

			while(!feof($handlein)){
				fwrite($handleout, fread($handlein, $maxbuffer));
			}

			// yayz
			fclose($handlein);
			fclose($handleout);
			chmod($this->_filename, $mode);
			return true;
		}
		else {
			// Trim off the ROOT_PDIR since it'll be relative to the ftp root set in the config.
			if (strpos($this->_filename, ROOT_PDIR) === 0){
				$filename = substr($this->_filename, strlen(ROOT_PDIR));
			}
			else{
				$filename = $this->_filename;
			}

			// Re-acquire the FTP connection.  Core will reset the cwd back to root upon doing this.
			// This is required because mkdir may change directories.
			$ftp = \Core\FTP();
			// FTP requires a filename, not data...
			// WELL how bout that!  I happen to have a local filename ;)
			if (!ftp_put($ftp, $filename, $localfilename, FTP_BINARY)) {
				throw new Exception(error_get_last()['message']);
				return false;
			}

			if (!ftp_chmod($ftp, $mode, $filename)){
				throw new Exception(error_get_last()['message']);
				return false;
			}

			// woot...
			return true;
		}
	}

	public function getContents() {
		return file_get_contents($this->_filename);
	}

	public function putContents($data) {

		// Ensure the directory exists.  The internal logic will handle if it already exists.
		self::_Mkdir(dirname($this->_filename), null, true);

		$dmode = (defined('DEFAULT_DIRECTORY_PERMS') ? DEFAULT_DIRECTORY_PERMS : 0777);

		if(!is_dir( dirname($this->_filename) )){
			mkdir(dirname($this->_filename), $dmode, true);
		}

		if(!is_dir(dirname($this->_filename))){
			throw new Exception("Unable to make directory " . dirname($this->_filename) . ", please check permissions.");
		}

		$mode = (defined('DEFAULT_FILE_PERMS') ? DEFAULT_FILE_PERMS : 0644);

		$ret = file_put_contents($this->_filename, $data);
		if ($ret === false) return $ret;
		chmod($this->_filename, $mode);
		return true;
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

		$preview = $this->getPreviewFile($dimensions);

		//var_dump($preview, $this); die(';)');
		if ($includeHeader){
			header('Content-Type: image/png');
			header('Content-Length: ' . $preview->getFilesize());
			header('X-Alternate-Location: ' . $preview->getURL());
			header('X-Content-Encoded-By: Core Plus ' . (DEVELOPMENT_MODE ? \Core::GetComponent()->getVersion() : ''));
		}
		echo $preview->getContents();
		return;
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
	 * @return Filestore\File_backend
	 */
	public function getQuickPreviewFile($dimensions = '300x300'){
		//var_dump('Requesting quick file preview for ' . __CLASS__);

		$bits   = $this->_getReizedKeyComponents($dimensions);
		$width  = $bits['width'];
		$height = $bits['height'];
		$mode   = $bits['mode'];
		$key    = $bits['key'];


		if (!$this->exists()) {
			// Log it so the admin knows that the file is missing, otherwise nothing is shown.
			error_log('File not found [ ' . $this->_filename . ' ]', E_USER_NOTICE);

			// Return a 404 image.
			$file = \Core\Filestore\Factory::File('assets/images/mimetypes/notfound.png');
			$preview = $file->getPreviewFile($dimensions);
		}
		elseif ($this->isPreviewable()) {
			// If no resize was requested, simply return the full size image.
			if($width === false) return $this;

			// Yes, this must be within public because it's meant to be publicly visible.
			$preview = \Core\Filestore\Factory::File('public/tmp/' . $key);
		}
		else {
			// Try and get the mime icon for this file.
			$filemime = str_replace('/', '-', $this->getMimetype());

			$file = \Core\Filestore\Factory::File('assets/images/mimetypes/' . $filemime . '.png');
			if(!$file->exists()){
				if(DEVELOPMENT_MODE){
					// Inform the developer, otherwise it's not a huge issue.
					error_log('Unable to locate mimetype icon [' . $filemime . '], resorting to "unknown"');
				}
				$file = \Core\Filestore\Factory::File('assets/images/mimetypes/unknown.png');
			}

			$preview = $file->getPreviewFile($dimensions);
		}

		//var_dump('From this: ', $this);
		//var_dump('Returning this: ', $preview, $preview->exists());
		return $preview;
	}

	public function getPreviewFile($dimensions = '300x300'){
		// If the system is getting too close to the max_execution_time variable, just return the mimetype!
		// One note though, this is only available when running php from the web.
		// CLI scripts don't have it!
		if(ini_get('max_execution_time') && \Core\Utilities\Profiler\Profiler::GetDefaultProfiler()->getTime() + 5 >= ini_get('max_execution_time')){
			// Try and get the mime icon for this file.
			$filemime = str_replace('/', '-', $this->getMimetype());

			$file = \Core\Filestore\Factory::File('assets/images/mimetypes/' . $filemime . '.png');
			if(!$file->exists()){
				$file = \Core\Filestore\Factory::File('assets/images/mimetypes/unknown.png');
			}
			return $file;
		}

		// This will get me the file, but none of the data or anything.
		$file = $this->getQuickPreviewFile($dimensions);

		$bits   = $this->_getReizedKeyComponents($dimensions);
		$width  = $bits['width'];
		$height = $bits['height'];
		$mode   = $bits['mode'];
		$key    = $bits['key'];

		// dunno how this may work... but prevent the possible infinite loop scenario.
		if($file == $this){
			return $this;
		}

		if (!$this->exists()) {
			// This will be a 404 image.
			//$file = \Core\Filestore\Factory::File('assets/images/mimetypes/notfound.png');
			return $file->getPreviewFile($dimensions);
		}
		elseif ($this->isPreviewable()) {
			// If no resize was requested, simply return the full size image.
			if($width === false) return $file;

			if (!$file->exists()) {
				$img2 = $this->_getResizedImage($width, $height, $mode);
				// Save this image to a temporary fs location.
				imagepng($img2, TMP_DIR . $key);
				// So I can put the contents in, (know of an easier method?  patch it!)
				$file->putContents(file_get_contents(TMP_DIR . $key));
				// And cleanup
				unlink(TMP_DIR . $key);
			}

			return $file;
		}
		else {
			// This will be a mimetype image.
			return $file->getPreviewFile($dimensions);
		}
	}

	public function getPreviewURL($dimensions = "300x300") {
		$file = $this->getPreviewFile($dimensions);
		return $file->getURL();
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
		return (strpos($this->_filename, $path) !== false);
	}

	public function identicalTo($otherfile) {

		if (is_a($otherfile, 'File') || $otherfile instanceof Filestore\File) {
			// Just compare the hashes.
			//var_dump($this->getHash(), $this, $otherfile->getHash(), $otherfile); die();
			return ($this->getHash() == $otherfile->getHash());
		}
		else {
			// Can't be the same if it doesn't exist!
			if (!file_exists($otherfile)) return false;
			if (!file_exists($this->_filename)) return false;
			$result = exec('diff -q "' . $this->_filename . '" "' . $otherfile . '"', $array, $return);
			return ($return == 0);
		}
	}

	public function exists() {
		return file_exists($this->_filename);
	}

	public function isReadable() {
		return is_readable($this->_filename);
	}

	public function isWritable(){
		return is_writable($this->_filename);
	}

	public function isLocal() {
		// Simple function that indicates if the file is on a local filesystem
		// Please note, even mounted filesystems are considered local for this matter.
		// Amazon S3 and other CDN services.... are not.

		return true;
	}

	/**
	 * Get the modified time for this file as a unix timestamp.
	 *
	 * @return int
	 */
	public function getMTime() {
		if (!$this->exists()) return false;

		return filemtime($this->getFilename());
	}


	/**
	 * Makes directory
	 *
	 * Advanced version of mkdir().  Will try to use ftp functions if provided by the configuration.
	 *
	 * @link http://php.net/manual/en/function.mkdir.php
	 *
	 * @param string $directory The directory path.
	 * @param int    $mode [optional] <p>
	 * The mode is 0777 by default, which means the widest possible
	 * access. For more information on modes, read the details
	 * on the chmod page.
	 * </p>
	 * <p>
	 * mode is ignored on Windows.
	 * </p>
	 * <p>
	 * Note that you probably want to specify the mode as an octal number,
	 * which means it should have a leading zero. The mode is also modified
	 * by the current umask, which you can change using
	 * umask.
	 * </p>
	 * @param bool   $recursive [optional] Default to false.
	 *
	 * @return bool Returns true on success or false on failure.
	 */
	public static function _Mkdir($pathname, $mode = null, $recursive = false) {
		$ftp    = \Core\FTP();
		$tmpdir = TMP_DIR;
		if ($tmpdir{0} != '/') $tmpdir = ROOT_PDIR . $tmpdir; // Needs to be fully resolved

		// Resolve it from its default.
		// This is provided from a config define, (probably).
		if ($mode === null) {
			$mode = (defined('DEFAULT_DIRECTORY_PERMS') ? DEFAULT_DIRECTORY_PERMS : 0777);
		}

		if (!$ftp) {
			if(is_dir($pathname)){
				return false;
			}
			else{
				return mkdir($pathname, $mode, $recursive);
			}

		}
		elseif (strpos($pathname, $tmpdir) === 0) {
			// Tmp files should be written directly.
			if(is_dir($pathname)) return false;
			else return mkdir($pathname, $mode, $recursive);
		}
		else {
			// Trim off the ROOT_PDIR since it'll be relative to the ftp root set in the config.
			if (strpos($pathname, ROOT_PDIR) === 0) $pathname = substr($pathname, strlen(ROOT_PDIR));

			// Because ftp_mkdir doesn't like to create parent directories...
			$paths = explode('/', $pathname);

			foreach ($paths as $p) {
				if(trim($p) == '') continue;

				if (!@ftp_chdir($ftp, $p)) {
					if (!ftp_mkdir($ftp, $p)) return false;
					if (!ftp_chmod($ftp, $mode, $p)) return false;
					ftp_chdir($ftp, $p);
				}
			}

			// woot...
			return true;
		}
	}

	public static function _Rename($oldpath, $newpath){
		$ftp    = \Core\FTP();

		if(!$ftp){
			// Traditional FTP
			return rename($oldpath, $newpath);
		}
		else{
			// Trim off the ROOT_PDIR since it'll be relative to the ftp root set in the config.
			if (strpos($oldpath, ROOT_PDIR) === 0) $oldpath = substr($oldpath, strlen(ROOT_PDIR));
			if (strpos($newpath, ROOT_PDIR) === 0) $newpath = substr($newpath, strlen(ROOT_PDIR));

			return ftp_rename($ftp, $oldpath, $newpath);
		}
	}

	/**
	 * Write a string to a file
	 *
	 * @link http://php.net/manual/en/function.file-put-contents.php
	 *
	 * @param string $filename <p>
	 * Path to the file where to write the data.
	 * </p>
	 * @param mixed  $data <p>
	 * The data to write. Can be either a string, an
	 * array or a stream resource.
	 * </p>
	 * <p>
	 * If data is a stream resource, the
	 * remaining buffer of that stream will be copied to the specified file.
	 * This is similar with using stream_copy_to_stream.
	 * </p>
	 * <p>
	 * You can also specify the data parameter as a single
	 * dimension array. This is equivalent to
	 * file_put_contents($filename, implode('', $array)).
	 * </p>
	 *
	 * @return bool Returns true on success or false on failure.
	 */
	public static function _PutContents($filename, $data) {
		$ftp    = \Core\FTP();
		$tmpdir = TMP_DIR;
		if ($tmpdir{0} != '/') $tmpdir = ROOT_PDIR . $tmpdir; // Needs to be fully resolved

		// Resolve it from its default.
		// This is provided from a config define, (probably).
		$mode = (defined('DEFAULT_FILE_PERMS') ? DEFAULT_FILE_PERMS : 0644);

		if (!$ftp) {
			$ret = file_put_contents($filename, $data);
			if ($ret === false) return $ret;
			chmod($filename, $mode);
			return $ret;
		}
		elseif (strpos($filename, $tmpdir) === 0) {
			// Tmp files should be written directly.
			$ret = file_put_contents($filename, $data);
			if ($ret === false) return $ret;
			chmod($filename, $mode);
			return $ret;
		}
		else {
			// Trim off the ROOT_PDIR since it'll be relative to the ftp root set in the config.
			if (strpos($filename, ROOT_PDIR) === 0) $filename = substr($filename, strlen(ROOT_PDIR));
			//$filename = ConfigHandler::Get('/core/ftp/path') . $filename;
			// FTP requires a filename, not data...
			$tmpfile = $tmpdir . 'ftpupload-' . Core::RandomHex(4);
			file_put_contents($tmpfile, $data);

			if (!ftp_put($ftp, $filename, $tmpfile, FTP_BINARY)) {
				// Well, delete the temp file anyway...
				unlink($tmpfile);
				return false;
			}

			if (!ftp_chmod($ftp, $mode, $filename)) return false;

			// woot... but cleanup the trash first.
			unlink($tmpfile);
			return true;
		}
	}


	/**
	 * Get an array of the various resize components from a given dimension set.
	 * These include: width, height, mode, key.
	 *
	 * @param $dimensions
	 *
	 * @return array
	 */
	private function _getReizedKeyComponents($dimensions){
		// The legacy support for simply a number.
		if (is_numeric($dimensions)) {
			$width  = $dimensions;
			$height = $dimensions;
			$mode = '';
		}
		elseif ($dimensions === null) {
			$width  = 300;
			$height = 300;
			$mode = '';
		}
		elseif($dimensions === false){
			$width = false;
			$height = false;
			$mode = '';
		}
		else {
			// Allow some special modifiers.
			if(strpos($dimensions, '^') !== false){
				// Fit the smallest dimension instead of the largest, (useful for overflow tricks)
				$mode = '^';
				$dimensions = str_replace('^', '', $dimensions);
			}
			elseif(strpos($dimensions, '!') !== false){
				// Absolutely resize, regardless of aspect ratio
				$mode = '!';
				$dimensions = str_replace('!', '', $dimensions);
			}
			elseif(strpos($dimensions, '>') !== false){
				// Only increase images.
				$mode = '>';
				$dimensions = str_replace('>', '', $dimensions);
			}
			elseif(strpos($dimensions, '<') !== false){
				// Only decrease images.
				$mode = '<';
				$dimensions = str_replace('<', '', $dimensions);
			}
			else{
				// Default mode
				$mode = '';
			}
			// New method. Split on the "x" and that should give me the width/height.
			$vals   = explode('x', strtolower($dimensions));
			$width  = (int)$vals[0];
			$height = (int)$vals[1];
		}

		// The basename is for SEO purposes, that way even resized images still contain the filename.
		// The hash is just to ensure that no two files conflict, ie: /public/a/file1.png and /public/b/file1.png
		//  might conflict without this hash.
		// Finally, the width and height dimensions are there just because as well; it gives more of a human
		//  touch to the file. :p
		$key = str_replace(' ', '-', $this->getBasename(true)) . '-' . $this->getHash() . '-' . $width . 'x' . $height . $mode . '.png';

		return array(
			'width' => $width,
			'height' => $height,
			'mode' => $mode,
			'key' => $key,
		);
	}


	private function _getResizedImage($width, $height, $mode = '') {
		$m = $this->getMimetype();

		if ($this->isImage()) {
			switch ($m) {
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
			if ($img) {
				$sW = imagesx($img);
				$sH = imagesy($img);

				$nW = $sW;
				$nH = $sH;


				switch($mode){
					// Standard mode, images are scaled down (only) while preserving aspect ratio
					case '':
					case '<':
						if ($nW > $width) {
							$nH = $width * $sH / $sW;
							$nW = $width;
						}

						if ($nH > $height) {
							$nW = $height * $sW / $sH;
							$nH = $height;
						}
						break;
					// Only resize up
					case '>':
						if ($nW < $width) {
							$nH = $width * $sH / $sW;
							$nW = $width;
						}

						if ($nH < $height) {
							$nW = $height * $sW / $sH;
							$nH = $height;
						}
						break;
					// Resize to new size, regardless about aspect ratio
					case '!':
						$nW = $width;
						$nH = $height;
						break;
					// Resize image based on smallest dimension
					case '^':
						if(($width * $sH / $sW) > ($height * $sW / $sH)){
							$nH = $width * $sH / $sW;
							$nW = $width;
						}
						else{
							$nH = $height;
							$nW = $height * $sW / $sH;
						}
						break;
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
	 *    so an array is returned with all possibilities for that extension.
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
