<?php
/**
 * File for the common file functions useful throughout Core.
 * 
 * @package Core\Filestore
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130530.1735
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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

namespace Core\Filestore;

use Core\Filestore\CDN;
use Core\Filestore\FTP\FTPConnection;


/**
 * Utility function to translate a filesize in bytes into a human-readable version.
 *
 * @param int $filesize Filesize in bytes
 * @param int $round Precision to round to
 *
 * @return string
 */
function format_size($filesize, $round = 2) {
	$suf = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
	$c   = 0;
	while ($filesize >= 1024) {
		$c++;
		$filesize = $filesize / 1024;
	}
	return (round($filesize, $round) . ' ' . $suf[$c]);
}

/**
 * Function to get the fully resolved asset path
 *
 * @return string
 *
 * @throws \Exception
 */
function get_asset_path(){
	static $_path;

	if ($_path === null) {

		switch(CDN_TYPE){
			case 'local':
				$dir = CDN_LOCAL_ASSETDIR;
				// If the configuration subsystem is not available, this will be null.
				if($dir){
					// Needs to be fully resolved
					if ($dir{0} != '/') $dir = ROOT_PDIR . $dir;
					// Needs to end in a '/'
					if (substr($dir, -1) != '/') $dir = $dir . '/';

					$_path = $dir;
				}
				break;
			case 'ftp':
				$dir = CDN_FTP_ASSETDIR;
				// If the configuration subsystem is not available, this will be null.
				if($dir){
					// Needs to be fully resolved
					if ($dir{0} != '/') $dir = CDN_FTP_PATH . $dir;
					// Needs to end in a '/'
					if (substr($dir, -1) != '/') $dir = $dir . '/';

					$_path = $dir;
				}
				break;
			default:
				throw new \Exception('Unsupported CDN type: ' . CDN_TYPE);
		}
	}

	return $_path;
}

/**
 * Function to get the fully resolved public path
 *
 * @return string
 *
 * @throws \Exception
 */
function get_public_path(){
	static $_path;

	if ($_path === null) {


		switch(CDN_TYPE){
			case 'local':
				$dir = CDN_LOCAL_PUBLICDIR;
				// If the configuration subsystem is not available, this will be null.
				if($dir){
					// Needs to be fully resolved
					if ($dir{0} != '/') $dir = ROOT_PDIR . $dir;
					// Needs to end in a '/'
					if (substr($dir, -1) != '/') $dir = $dir . '/';

					$_path = $dir;
				}
				break;
			case 'ftp':
				$dir = CDN_FTP_PUBLICDIR;
				// If the configuration subsystem is not available, this will be null.
				if($dir){
					// Needs to be fully resolved
					if ($dir{0} != '/') $dir = CDN_FTP_PATH . $dir;
					// Needs to end in a '/'
					if (substr($dir, -1) != '/') $dir = $dir . '/';

					$_path = $dir;
				}
				break;
			default:
				throw new \Exception('Unsupported CDN type: ' . CDN_TYPE);
		}
	}

	return $_path;
}

/**
 * Function to get the fully resolved private path
 *
 * @return string
 *
 * @throws \Exception
 */
function get_private_path(){
	static $_path;

	if ($_path === null) {

		switch(CDN_TYPE){
			case 'local':
				$dir = CDN_LOCAL_PRIVATEDIR;
				// If the configuration subsystem is not available, this will be null.
				if($dir){
					// Needs to be fully resolved
					if ($dir{0} != '/') $dir = ROOT_PDIR . $dir;
					// Needs to end in a '/'
					if (substr($dir, -1) != '/') $dir = $dir . '/';

					$_path = $dir;
				}
				break;
			case 'ftp':
				$dir = CDN_FTP_PRIVATEDIR;
				// If the configuration subsystem is not available, this will be null.
				if($dir){
					// Needs to be fully resolved
					if ($dir{0} != '/') $dir = CDN_FTP_PATH . $dir;
					// Needs to end in a '/'
					if (substr($dir, -1) != '/') $dir = $dir . '/';

					$_path = $dir;
				}
				break;
			default:
				throw new \Exception('Unsupported CDN type: ' . CDN_TYPE);
		}
	}

	return $_path;
}

/**
 * Function to get the fully resolved tmp path
 *
 * @return string
 *
 * @throws \Exception
 */
function get_tmp_path(){
	static $_path;

	if ($_path === null) {
		$dir = TMP_DIR;
		// If the configuration subsystem is not available, this will be null.
		if($dir){
			// Needs to be fully resolved
			if ($dir{0} != '/') $dir = ROOT_PDIR . $dir;
			// Needs to end in a '/'
			if (substr($dir, -1) != '/') $dir = $dir . '/';

			$_path = $dir;
		}
	}

	return $_path;
}


/**
 * Resolve the correct contents object type for the requested file.
 * This mainly operates off the mimetype.
 *
 * @param File $file
 *
 * @return Contents
 *
 * @throws \Exception
 */
function resolve_contents_object(File $file){
	// The class name to instantiate based on the incoming filetype.
	$class = null;

	$ext = $file->getExtension();
	$mime = $file->getMimetype();

	switch ($mime) {
		case 'application/x-gzip':
		case 'application/gzip':
			// gzip can be a wrapper around a lot of things.
			// Some of them even have their own content functions.
			if ($ext == 'tgz'){
				$class = 'ContentTGZ';
			}
			elseif($ext == 'tar.gz'){
				$class = 'ContentTGZ';
			}
			else{
				$class = 'ContentGZ';
			}
			break;

		case 'text/plain':
			// Sometimes these are actually other files based on the extension.
			if ($ext == 'asc'){
				$class = 'ContentASC';
			}
			else{
				$class = 'ContentUnknown';
			}
			break;

		case 'text/xml':
		case 'application/xml':
			$class = 'ContentXML';
			break;

		case 'application/pgp-signature':
			$class = 'ContentASC';
			break;

		case 'application/zip':
			$class = 'ContentZIP';
			break;

		case 'text/csv':
			$class = 'ContentCSV';
			break;

		case 'application/octet-stream':
			// These are fun... basically I'm relying on the extension here.
			if($ext == 'zip'){
				$class = 'ContentZIP';
			}
			else{
				error_log('@fixme Unknown extension for application/octet-stream mimetype [' . $ext . ']');
				$class = 'ContentUnknown';
			}
			break;

		default:
			error_log('@fixme Unknown file mimetype [' . $mime . '] with extension [' . $ext . ']');
			$class = 'ContentUnknown';
	}

	// Prefix the class with the necessary namespace.
	$resolved = '\\Core\\Filestore\\Contents\\' . $class;

	// Make sure that class exists!
	// In core, even if it doesn't, it should be able to locate the file dynamically.
	// If it can't, then maybe core isn't available yet or this script has been migrated to a different platform.
	// Did you migrate this script to a different platform????
	if(!class_exists($resolved)){
		// Hmm.... well
		if(file_exists(ROOT_PDIR . 'core/libs/core/filestore/contents/' . $class . '.php')){
			require_once(ROOT_PDIR . 'core/libs/core/filestore/contents/' . $class . '.php');
		}
		else{
			throw new \Exception('Unable to locate file for class [' . $class . ']');
		}
	}

	$ref = new \ReflectionClass($resolved);
	return $ref->newInstance($file);
}


/**
 * Get an extension from a given filename.
 *
 * Will return just the extension itself without the ".", or a blank string if empty.
 *
 * @param $str
 *
 * @return string
 */
function get_extension_from_string($str) {
	// File doesn't have any extension... easy enough!
	if (strpos($str, '.') === false) return '';

	// strtolower for simplicity.
	$str = strtolower($str);

	$ext = substr($str, strrpos($str, '.') + 1);

	if($ext == 'gz' && substr($str, -7) == '.tar.gz'){
		return 'tar.gz';
	}

	return $ext;
}

/**
 * Resolve a name for an asset to an actual file.
 *
 * @param $filename
 *
 * @return \Core\Filestore\File
 *
 * @throws \Exception
 */
function resolve_asset_file($filename){
	$resolved = get_asset_path();

	// I need to check the custom, current theme, and finally default locations for the file.
	$theme = \ConfigHandler::Get('/theme/selected');

	if (strpos($filename, 'assets/') === 0) {
		// Allow "assets/blah" to be passed in
		$filename = substr($filename, 7);
	}
	elseif(strpos($filename, 'asset/') === 0){
		// Allow "asset/blah" to be passed in.
		$filename = substr($filename, 6);
	}
	elseif(strpos($filename, $resolved) === 0){
		// Allow the fully resolved name to be passed in
		// The caveat here is that the fully resolve file will probably have "default/" or "themename/" in it too.
		// I need to trim that off as well.
		if(strpos($filename, $resolved . 'custom/') === 0){
			$filename = substr($filename, strlen($resolved . 'custom/'));
		}
		elseif(strpos($filename, $resolved . $theme . '/') === 0){
			$filename = substr($filename, strlen($resolved . $theme . '/'));
		}
		elseif(strpos($filename, $resolved . 'default/') === 0){
			$filename = substr($filename, strlen($resolved . 'default/'));
		}
		else{
			$filename = substr($filename, strlen($resolved));
		}
	}


	switch(CDN_TYPE){
		case 'local':
			if(\Core\ftp()){
				// FTP has its own sub-type.
				/*$custom  = new Backends\FileFTP($resolved  . 'custom/' . $filename);
				$themed  = new Backends\FileFTP($resolved  . $theme . '/' . $filename);
				$default = new Backends\FileFTP($resolved  . 'default/' . $filename);*/
				return new Backends\FileFTP($resolved  . $filename);
			}
			else{
				/*$custom  = new Backends\FileLocal($resolved  . 'custom/' . $filename);
				$themed  = new Backends\FileLocal($resolved  . $theme . '/' . $filename);
				$default = new Backends\FileLocal($resolved  . 'default/' . $filename);*/
				return new Backends\FileLocal($resolved  . $filename);
			}
/*
			if($custom->exists()){
				return $custom;
			}
			elseif($themed->exists()){
				return $themed;
			}
			else{
				return $default;
			}*/
			break;

		case 'ftp':
			/*
			$custom  = new Backends\FileFTP($resolved  . 'custom/' . $filename, cdn_ftp());
			$themed  = new Backends\FileFTP($resolved  . $theme . '/' . $filename, cdn_ftp());
			$default = new Backends\FileFTP($resolved  . 'default/' . $filename, cdn_ftp());
			*/
			return new Backends\FileFTP($resolved  . $filename, cdn_ftp());
			break;

		default:
			throw new \Exception('Unsupported CDN type: ' . CDN_TYPE);
			break;
	}
}


/**
 * Resolve a name for a public to an actual file.
 *
 * @param $filename
 *
 * @return \Core\Filestore\File
 *
 * @throws \Exception
 */
function resolve_public_file($filename){
	$resolved = get_public_path();

	if (strpos($filename, 'public/') === 0) {
		// Allow "assets/blah" to be passed in
		$filename = substr($filename, 7);
	}
	elseif(strpos($filename, $resolved) === 0){
		// Allow the fully resolved name to be passed in
		$filename = substr($filename, strlen($resolved));
	}

	switch(CDN_TYPE){
		case 'local':
			if(\Core\ftp()){
				// FTP has its own sub-type.
				return new Backends\FileFTP($resolved . $filename);
			}
			else{
				return new Backends\FileLocal($resolved . $filename);
			}
			break;

		case 'ftp':
			return new Backends\FileFTP($resolved  . $filename, cdn_ftp());
			break;

		default:
			throw new \Exception('Unsupported CDN type: ' . CDN_TYPE);
			break;
	}
}

/**
 * Resolve a name for a private to an actual file.
 *
 * @param $filename
 *
 * @return \Core\Filestore\File
 *
 * @throws \Exception
 */
function resolve_private_file($filename){
	$resolved = get_private_path();

	if (strpos($filename, 'private/') === 0) {
		// Allow "assets/blah" to be passed in
		$filename = substr($filename, 8);
	}
	elseif(strpos($filename, $resolved) === 0){
		// Allow the fully resolved name to be passed in
		$filename = substr($filename, strlen($resolved));
	}

	switch(CDN_TYPE){
		case 'local':
			if(\Core\ftp()){
				// FTP has its own sub-type.
				return new Backends\FileFTP($resolved . $filename);
			}
			else{
				return new Backends\FileLocal($resolved . $filename);
			}
			break;

		case 'ftp':
			return new Backends\FileFTP($resolved  . $filename, cdn_ftp());
			break;

		default:
			throw new \Exception('Unsupported CDN type: ' . CDN_TYPE);
			break;
	}
}

/**
 * Resolve a name for an asset to an actual file.
 *
 * @param $filename
 *
 * @return \Core\Filestore\Directory
 *
 * @throws \Exception
 */
function resolve_asset_directory($filename){
	$resolved = get_asset_path();

	if (strpos($filename, 'assets/') === 0) {
		// Allow "assets/blah" to be passed in
		$filename = substr($filename, 7);
	}
	elseif(strpos($filename, 'asset/') === 0){
		// Allow "asset/blah" to be passed in.
		$filename = substr($filename, 6);
	}
	elseif(strpos($filename, $resolved) === 0){
		// Allow the fully resolved name to be passed in
		$filename = substr($filename, strlen($resolved));
	}

	//var_dump($filename);

	// I need to check the custom, current theme, and finally default locations for the file.
	$theme = \ConfigHandler::Get('/theme/selected');
	switch(CDN_TYPE){
		case 'local':
			if(\Core\ftp()){
				// FTP has its own sub-type.
				$custom  = new Backends\DirectoryFTP($resolved  . 'custom/' . $filename);
				$themed  = new Backends\DirectoryFTP($resolved  . $theme . '/' . $filename);
				$default = new Backends\DirectoryFTP($resolved  . 'default/' . $filename);
			}
			else{
				$custom  = new Backends\DirectoryLocal($resolved  . 'custom/' . $filename);
				$themed  = new Backends\DirectoryLocal($resolved  . $theme . '/' . $filename);
				$default = new Backends\DirectoryLocal($resolved  . 'default/' . $filename);
			}

			break;
		default:
			throw new \Exception('Unsupported CDN type: ' . CDN_TYPE);
			break;
	}

	if($custom->exists()){
		// If there is a custom asset installed, USE THAT FIRST!
		return $custom;
	}
	elseif($themed->exists()){
		// Otherwise, the themes can override component assets too.
		return $themed;
	}
	else{
		return $default;
	}
}


/**
 * Resolve a name for a public to an actual file.
 *
 * @param $filename
 *
 * @return \Core\Filestore\Directory
 *
 * @throws \Exception
 */
function resolve_public_directory($filename){
	$resolved = get_public_path();

	if (strpos($filename, 'public/') === 0) {
		// Allow "assets/blah" to be passed in
		$filename = substr($filename, 7);
	}
	elseif(strpos($filename, $resolved) === 0){
		// Allow the fully resolved name to be passed in
		$filename = substr($filename, strlen($resolved));
	}

	// I need to check the custom, current theme, and finally default locations for the file.
	$theme = \ConfigHandler::Get('/theme/selected');
	switch(CDN_TYPE){
		case 'local':
			if(\Core\ftp()){
				// FTP has its own sub-type.
				return new Backends\DirectoryFTP($resolved . $filename);
			}
			else{
				return new Backends\DirectoryLocal($resolved . $filename);
			}

			break;
		default:
			throw new \Exception('Unsupported CDN type: ' . CDN_TYPE);
			break;
	}
}

/**
 * Function to translate a PHP upload error into a human-readable string.
 *
 * If no error is seen, a blank string is returned.
 *
 * @param int
 * @return string
 */
function translate_upload_error($errno){
	switch($errno){
		case UPLOAD_ERR_OK:
			return '';
		case UPLOAD_ERR_INI_SIZE:
			if(DEVELOPMENT_MODE){
				return 'The uploaded file exceeds the upload_max_filesize directive in php.ini [' . ini_get('upload_max_filesize') . ']';
			}
			else{
				return 'The uploaded file is too large, maximum size is ' . ini_get('upload_max_filesize');
			}
		case UPLOAD_ERR_FORM_SIZE:
			if(DEVELOPMENT_MODE){
				return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form. ';
			}
			else{
				return 'The uploaded file is too large.';
			}
		default:
			return 'An error occurred while trying to upload the file.';
	}
}

/**
 * Check a file mimetype against a base.
 *
 * Useful for upload scripts.
 *
 * @param $acceptlist string List of accept types to accept.
 *                    Can be wildcard types too, for example image/*, or simply *.
 * @param $mimetype string File Mimetype to check against
 * @param $extension string Extension of the file to check against.
 *                  (not required, but provides better true positives, [and potentially more false positives] ).
 *
 * @return string Empty string if passed, else the error.
 */
function check_file_mimetype($acceptlist, $mimetype, $extension = null){
	$acceptgood = false;
	$accepts = array_map(
		'trim',
		explode(
			',',
			strtolower($acceptlist)
		)
	);

	// Also lowercase the incoming extension.
	$extension = strtolower($extension);

	foreach($accepts as $accepttype){
		// '*' is the wildcard to accept any filetype....
		// why would this even be set?!?
		if($accepttype == '*'){
			$acceptgood = true;
			break;
		}
		// accepts that are standard full mimetypes are also pretty easy.
		elseif(preg_match('#^[a-z\-\+]+/[0-9a-z\-\+\.]+#', $accepttype)){
			if($accepttype == $mimetype){
				$acceptgood = true;
				break;
			}
		}
		// wildcard mimetypes are allowed too.
		elseif(preg_match('#^[a-z\-\+]+/\*#', $accepttype)){
			if(strpos($mimetype, substr($accepttype, 0, -1)) === 0){
				$acceptgood = true;
				break;
			}
		}
		// extensions are allowed as well, (if provided)
		elseif($extension && preg_match('#^\.*#', $accepttype)){
			if(substr($accepttype, 1) == $extension){
				$acceptgood = true;
				break;
			}
		}
		// Umm....
		else{
			return 'Unsupported accept option, ' . $accepttype;
		}
	}

	// Now that all the mimetypes have run through, I can see if one matched.
	if(!$acceptgood){
		if(sizeof($accepts) > 1){
			$err = 'matches one of [ ' . implode(', ', $accepts) . ' ]';
		}
		else{
			$err = 'is a ' . $accepts[0] . ' file';
		}
		return 'Invalid file uploaded, please ensure it ' . $err;
	}
	else{
		return '';
	}
}


/**
 * Simple function to try to guess the mimetype for a given file extension.
 * This is not guaranteed to be the actual mimetype of the file, but just the most common for a given extension.
 *
 * @param string $ext
 *
 * @return string
 */
function extension_to_mimetype($ext){
	switch($ext){
		case 'atom':
			return 'application/atom+xml';
		case 'css':
			return 'text/css';
		case 'csv':
			return 'text/csv';
		case 'gif':
			return 'image/gif';
		case 'html':
		case 'htm':
			return 'text/html';
		case 'ics':
			return 'text/calendar';
		case 'jpg':
		case 'jpeg':
			return 'image/jpeg';
		case 'js':
			return 'text/javascript';
		case 'json':
			return 'application/json';
		case 'otf':
			return 'font/otf';
		case 'png':
			return 'image/png';
		case 'rss':
			return 'application/rss+xml';
		case 'ttf':
			return 'font/ttf';
		case 'xhtml':
			return 'application/xhtml+xml';
		case 'xml':
			return 'application/xml';
		default:
			return 'application/octet-stream';
	}
}

/**
 * Convert a common mimetype to its extension.
 *
 * @param $mimetype
 *
 * @return string
 */
function mimetype_to_extension($mimetype){
	switch($mimetype){
		case 'application/atom+xml':
			return 'atom';
		case 'application/json':
			return 'json';
		case 'application/rss+xml':
			return 'rss';
		case 'application/xhtml+xml':
			return 'xhtml';
		case 'application/xml':
			return 'xml';
		case 'font/otf':
			return 'otf';
		case 'font/ttf':
			return 'ttf';
		case 'image/gif':
			return 'gif';
		case 'image/jpeg':
			return 'jpeg';
		case 'image/png':
			return 'png';
		case 'text/calendar':
			return 'ics';
		case 'text/css':
			return 'css';
		case 'text/csv':
			return 'csv';
		case 'text/html':
			return 'html';
		case 'text/javascript':
			return 'js';
		default:
			return '';
	}
}

/**
 * Get the resource for FTP based CDN connections.
 *
 * Returns the FTP resource or false on failure.
 *
* @return FTPConnection | false
 */
function cdn_ftp(){
	static $ftp = null;

	if($ftp === null){

		$ftp = new FTPConnection();
		$ftp->host = CDN_FTP_HOST;
		$ftp->username = CDN_FTP_USERNAME;
		$ftp->password = CDN_FTP_PASSWORD;
		$ftp->root = CDN_FTP_PATH;
		$ftp->url = CDN_FTP_URL;
/*
		try{
			$ftp->connect();

			$ftp->reset();
		}
		catch(\Exception $e){
			\Core\ErrorManagement\exception_handler($e);
			$ftp = false;
			return false;
		}
		*/
	}
/*
	if($ftp && $ftp instanceof FTPConnection){
		try{
			$ftp->reset();
		}
		catch(\Exception $e){
			\Core\ErrorManagement\exception_handler($e);
			$ftp = false;
			return false;
		}
	}
*/

	return $ftp;
}


/**
 * Get an array of the various resize components from a given dimension set.
 * These include: width, height, mode, key.
 *
 * @param string|int $dimensions
 * @param File $file
 *
 * @return array
 */
function get_resized_key_components($dimensions, $file){
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

	$ext = $file->getExtension();
	// Ensure that an extension is used if none present, (may happen with temporary files).
	if(!$ext){
		$ext = mimetype_to_extension($file->getMimetype());
	}

	// The basename is for SEO purposes, that way even resized images still contain the filename.
	// The hash is just to ensure that no two files conflict, ie: /public/a/file1.png and /public/b/file1.png
	//  might conflict without this hash.
	// Finally, the width and height dimensions are there just because as well; it gives more of a human
	//  touch to the file. :p
	// Also, keep the original file extension, this way PNGs remain PNGs, GIFs remain GIFs, JPEGs remain JPEGs.
	// This is critical particularly when it comes to animated GIFs.
	$key = str_replace(' ', '-', $file->getBasename(true)) . '-' . $file->getHash() . '-' . $width . 'x' . $height . $mode . '.' . $ext;

	// The directory can be used with the new File backend to create this file in a correctly nested subdirectory.
	$dir = dirname($file->getFilename(false)) . '/';

	if(substr($dir, 0, 7) == 'public/'){
		// Replace the necessary prefix with a more useful one.
		// Anything within public/ needs to be remapped to public/tmp
		$dir = 'public/tmp/' . substr($dir, 7);
	}
	else{
		// Everything else gets prepended to public/tmp/
		// so if the original file is in themes/blah/imgs/blah.jpg,
		// it will be copied to public/tmp/blah.jpg
		$dir = 'public/tmp/';
	}

	return array(
		'width'  => $width,
		'height' => $height,
		'mode'   => $mode,
		'key'    => $key,
		'ext'    => $ext,
		'dir'    => $dir,
	);
}