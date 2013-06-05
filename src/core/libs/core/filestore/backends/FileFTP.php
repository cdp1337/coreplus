<?php
/**
 * File for class FileFTP definition in the coreplus project
 * 
 * @package Core\Filestore\Backends
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130530.1917
 * @copyright Copyright (C) 2009-2013  Author
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
use Core\Filestore\Contents;
use Core\Filestore;


/**
 * A short teaser of what FileFTP does.
 *
 * More lengthy description of what FileFTP does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for FileFTP
 * <h4>Example 1</h4>
 * <p>Description 1</p>
 * <code>
 * // Some code for example 1
 * $a = $b;
 * </code>
 *
 *
 * <h4>Example 2</h4>
 * <p>Description 2</p>
 * <code>
 * // Some code for example 2
 * $b = $a;
 * </code>
 *
 * 
 * @package Core\Filestore\Backends
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class FileFTP implements Filestore\File{

	public $_type = Filestore\File::TYPE_OTHER;

	/**
	 * The backend FTP resource.
	 * This is a native PHP object.
	 *
	 * @var
	 */
	protected $_ftp;

	protected $_prefix;
	protected $_filename;

	protected $_tmplocal;


	/**
	 * Set to true if this FTP connection is the proxy for local files.
	 * @var bool
	 */
	protected $_islocal = false;

	/**
	 * Construct a new File object with the requested filename or URL.
	 *
	 * @param null $filename
	 * @param mixed $ftpobject
	 */
	public function __construct($filename = null, $ftpobject = null) {
		if($ftpobject !== null){
			$this->_ftp = $ftpobject;
		}
		else{
			$this->_ftp = \Core\FTP();
		}

		if($this->_ftp == \Core\FTP()){
			$this->_islocal = true;
		}

		if($filename){
			$this->setFilename($filename);
		}
	}

	/**
	 * Get the filesize of this file object, as either raw bytes or a formatted string.
	 *
	 * @param bool $formatted
	 *
	 * @return string|int
	 */
	public function getFilesize($formatted = false) {
		$f = ftp_size($this->_ftp, $this->_filename);

		if($f == -1){
			return 0;
		}

		return ($formatted) ? Filestore\format_size($f, 2) : $f;
	}

	/**
	 * Get the mimetype of this file.
	 *
	 * @return string
	 */
	public function getMimetype() {
		return $this->_getTmpLocal()->getMimetype();
	}

	/**
	 * Get the extension of this file, (without the ".")
	 *
	 * @return string
	 */
	public function getExtension() {
		return \Core::GetExtensionFromString($this->getBasename());
	}

	/**
	 * Get a filename that can be retrieved from the web.
	 * Resolves with the ROOT_DIR prefix already attached.
	 *
	 * @return string|boolean
	 */
	public function getURL() {
		$file = $this->_getTmpLocal();
		return $file->getURL();
	}

	/**
	 * Get a serverside-resized thumbnail url for this file.
	 *
	 * @param string $dimensions
	 *
	 * @return string
	 */
	public function getPreviewURL($dimensions = "300x300") {
		$file = $this->_getTmpLocal()->getPreviewFile($dimensions);
		return $file->getURL();
	}

	/**
	 * Get the filename of this file resolved to a specific directory, usually ROOT_PDIR or ROOT_WDIR.
	 */
	public function getFilename($prefix = \ROOT_PDIR) {
		if($this->_islocal){
			// Map this to the underlying local version.
			return $this->_getTmpLocal()->getFilename($prefix);
		}
		else{
			$full = $this->_prefix . $this->_filename;

			return $full;
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
	 *
	 * Alias of getBasename
	 *
	 * @param boolean $withoutext Set to true to drop the extension.
	 *
	 * @return string
	 */
	public function getBaseFilename($withoutext = false) {
		return $this->getBasename($withoutext);
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
	 * Get the hash for this file.
	 * This is generally an MD5 sum of the file contents.
	 *
	 * @return string
	 */
	public function getHash() {
		if(!$this->exists()) return null;
		// To increase performance here, I can check and see if the current FTP connection is Core's FTP connection.
		// If it is, then I can safely assume that the file is local,
		// it's just being used over FTP for write access.

		if($this->_islocal){
			// :)
			return md5_file(ROOT_PDIR . $this->_filename);
		}
		else{
			// *tear*, I need to download the file and check that.
			return md5_file($this->getLocalFilename());
		}
	}

	/**
	 * Get an ascii hash of the filename.
	 * useful for transposing this file to another page call.
	 *
	 * @return string The encoded string
	 */
	public function getFilenameHash() {
		$full = $this->getFilename();

		if ($this->_type == 'asset'){
			$base = 'assets/';
			$filename = substr($full, strlen(Filestore\get_asset_path()));
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
			$filename = 'public/' . substr($full, strlen(Filestore\get_public_path() ));
		}
		elseif ($this->_type == 'private'){
			$filename = 'private/' . substr($full, strlen(Filestore\get_private_path() ));
		}
		elseif ($this->_type == 'tmp'){
			$filename = 'tmp/' . substr($full, strlen(Filestore\get_tmp_path() ));
		}
		else{
			$filename = $full;
		}

		return 'base64:' . base64_encode($filename);
	}

	/**
	 * Delete this file from the filesystem.
	 *
	 * @return boolean
	 */
	public function delete() {
		return ftp_delete($this->_ftp, $this->_filename);
	}

	/**
	 * Rename this file to a new name
	 *
	 * @param $newname
	 *
	 * @return boolean
	 */
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
			$newname = dirname($this->_filename) . '/' . $newname;
		}

		$status = ftp_rename($this->_ftp, $this->_filename, $newname);

		if($status){
			$this->_filename = $newname;
			$this->_tmplocal = null;
		}

		return $status;
	}

	/**
	 * Shortcut function to see if this file's mimetype is image/*
	 *
	 * @return boolean
	 */
	public function isImage() {
		$m = $this->getMimetype();
		return (preg_match('/image\/jpeg|image\/png|image\/gif/', $m) ? true : false);
	}

	/**
	 * Shortcut function to see if this file's mimetype is text/*
	 *
	 * @return boolean
	 */
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
	 * @param string|int $dimensions    A string of the dimensions to create the image at, widthxheight.
	 *                                  Also supports the previous version of simply "dimension", as an int.
	 * @param boolean    $includeHeader Include the correct mimetype header or no.
	 */
	public function displayPreview($dimensions = "300x300", $includeHeader = true) {
		return $this->_getTmpLocal()->displayPreview($dimensions, $includeHeader);
	}

	/**
	 * Get the mimetype icon for this file.
	 *
	 * @param string $dimensions
	 *
	 * @return string
	 */
	public function getMimetypeIconURL($dimensions = '32x32') {
		return $this->_getTmpLocal()->getMimetypeIconURL($dimensions);
	}

	/**
	 * Get the preview file object without actually populating the sources.
	 * This is useful for checking to see if the file exists before resizing it over.
	 *
	 * WARNING, this will NOT check if the file exists and/or copy data over!
	 *
	 * @param string $dimensions
	 *
	 * @return Filestore\File
	 */
	public function getQuickPreviewFile($dimensions = '300x300') {
		return $this->_getTmpLocal()->getQuickPreviewFile($dimensions);
	}

	/**
	 * Get the preview file with the contents copied over resized/previewed.
	 *
	 * @param string $dimensions
	 *
	 * @return Filestore\File
	 */
	public function getPreviewFile($dimensions = '300x300') {
		return $this->_getTmpLocal()->getPreviewFile($dimensions);
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
		return (strpos($this->_prefix . $this->_filename, $path) !== false);
	}

	public function identicalTo($otherfile) {
		return $this->_getTmpLocal()->identicalTo($otherfile);
	}

	/**
	 * Copies the file to the requested destination.
	 * If the destination is a directory (ends with a '/'), the same filename is used, (if possible).
	 * If the destination is relative, ('.' or 'subdir/'), it is assumed relative to the current file.
	 *
	 * @param string  $dest
	 * @param boolean $overwrite
	 *
	 * @return Filestore\File
	 */
	public function copyTo($dest, $overwrite = false) {
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

	/**
	 * Make a copy of a source Filestore\File into this Filestore\File.
	 *
	 * (Generally only useful internally)
	 *
	 * @param Filestore\File $src       Source file backend
	 * @param bool $overwrite true to overwrite existing file
	 *
	 * @throws \Exception
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

		// Resolve it from its default.
		// This is provided from a config define, (probably).
		$mode = (defined('DEFAULT_FILE_PERMS') ? DEFAULT_FILE_PERMS : 0644);

		// Make sure the directory exists first!
		$this->_mkdir(dirname($this->_filename), null, true);

		// FTP requires a filename, not data...
		// WELL how bout that!  I happen to have a local filename ;)
		if (!ftp_put($this->_ftp, $this->_filename, $localfilename, FTP_BINARY)) {
			throw new \Exception(error_get_last()['message']);
			return false;
		}

		if (!ftp_chmod($this->_ftp, $mode, $this->_filename)){
			throw new \Exception(error_get_last()['message']);
			return false;
		}

		// woot...
		return true;
	}

	/**
	 * Get the raw contents of this file
	 *
	 * Essentially file_get_contents()
	 *
	 * @return mixed
	 */
	public function getContents() {
		// Grab a local copy of the file.
		// the getTmpLocal method is already optimized for retrieving files, so use that.
		$local = $this->_getTmpLocal();
		return $local->getContents();
	}

	/**
	 * Write the raw contents of this file
	 *
	 * Essentially file_put_contents()
	 *
	 * @param mixed $data
	 *
	 * @return boolean
	 */
	public function putContents($data) {
		// Resolve it from its default.
		// This is provided from a config define, (probably).
		$mode = (defined('DEFAULT_FILE_PERMS') ? DEFAULT_FILE_PERMS : 0644);

		// FTP requires a filename, not data...
		$tmpfile = Filestore\get_tmp_path() . 'ftpupload-' . \Core::RandomHex(4);
		file_put_contents($tmpfile, $data);

		if (!ftp_put($this->_ftp, $this->_filename, $tmpfile, FTP_BINARY)) {
			// Well, delete the temp file anyway...
			unlink($tmpfile);
			return false;
		}

		if (!ftp_chmod($this->_ftp, $mode, $this->_filename)) return false;

		// woot... but cleanup the trash first.
		unlink($tmpfile);
		$this->_tmplocal = null;
		return true;
	}

	/**
	 * Get the contents object that can then be manipulated in more detail,
	 * ie: an image can be displayed, compressed files can be uncompressed, etc.
	 *
	 * @return Contents
	 */
	public function getContentsObject() {
		return $this->_getTmpLocal()->getContentsObject();
	}

	/**
	 * Check if this file exists on the filesystem currently.
	 *
	 * @return boolean
	 */
	public function exists() {
		$f = ftp_size($this->_ftp, $this->_filename);

		return ($f != -1);
	}

	/**
	 * Check if this file is readable.
	 *
	 * @return boolean
	 */
	public function isReadable() {
		return $this->exists();
	}

	/**
	 * Check if this file is writable.
	 *
	 * @return boolean
	 */
	public function isWritable() {
		return $this->exists();
	}

	/**
	 * Get the modified time for this file as a unix timestamp.
	 *
	 * @return int
	 */
	public function getMTime() {
		if (!$this->exists()) return false;

		return ftp_mdtm($this->_ftp, $this->_filename);
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

		$cwd = ftp_pwd($this->_ftp);

		if(strpos($filename, ROOT_PDIR) === 0){
			// If the file starts with the PDIR... trim that off!
			$filename = substr($filename, strlen(ROOT_PDIR));
			$prefix = ROOT_PDIR;
		}
		elseif(strpos($filename, $cwd) === 0){
			// If the file already starts with the CWD... trim that off!
			$filename = substr($filename, strlen($cwd));
			$prefix = $cwd;
		}
		else{
			$prefix = $cwd;
		}

		// Make sure that prefix ends with a '/'.
		if(substr($prefix, -1) != '/') $prefix .= '/';

		// Resolve if this is an asset, public, etc.
		// This is to speed up the other functions so they don't have to perform this operation.
		if(strpos($prefix . $filename, Filestore\get_asset_path()) === 0){
			$this->_type = 'asset';
		}
		elseif(strpos($prefix . $filename, Filestore\get_public_path()) === 0){
			$this->_type = 'public';
		}

		$this->_filename = $filename;
		$this->_prefix = $prefix;
		// Clear the local cache too
		$this->_tmplocal = null;
	}


	private function _mkdir($pathname, $mode = null, $recursive = false) {
		if (strpos($pathname, ROOT_PDIR) === 0){
			// Trim off the ROOT_PDIR since it'll be relative to the ftp root set in the config.
			$pathname = substr($pathname, strlen(ROOT_PDIR));
		}

		// Resolve it from its default.
		// This is provided from a config define, (probably).
		if ($mode === null) {
			$mode = (defined('DEFAULT_DIRECTORY_PERMS') ? DEFAULT_DIRECTORY_PERMS : 0777);
		}

		// Because ftp_mkdir doesn't like to create parent directories...
		$paths = explode('/', $pathname);

		$cwd = ftp_pwd($this->_ftp);

		foreach ($paths as $p) {
			if(trim($p) == '') continue;

			if (!@ftp_chdir($this->_ftp, $p)) {
				if (!ftp_mkdir($this->_ftp, $p)) return false;
				if (!ftp_chmod($this->_ftp, $mode, $p)) return false;
				ftp_chdir($this->_ftp, $p);
			}
		}

		// And go back to root.
		ftp_chdir($this->_ftp, $cwd);

		// woot...
		return true;
	}

	/**
	 * Get the temporary local version of the file.
	 * This is useful for doing operations such as hash and identicalto.
	 *
	 * @return FileLocal
	 */
	private function _getTmpLocal() {
		if ($this->_tmplocal === null) {
			// If this FTP object is simply a proxy for the local file store, I can cheat and not actually request the files over FTP.
			// This makes it quicker.
			if($this->_islocal){
				$this->_tmplocal = new FileLocal(ROOT_PDIR . $this->_filename);
			}
			else{
				$f = md5($this->getFilename());

				$this->_tmplocal = Filestore\Factory::File('tmp/remotefile-cache/' . $f);

				ftp_get($this->_ftp, $this->_tmplocal->getFilename(), $this->_filename, FTP_BINARY);
			}
		}
		return $this->_tmplocal;
	}
}