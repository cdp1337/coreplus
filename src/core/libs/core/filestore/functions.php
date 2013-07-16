<?php
/**
 * File for the common file functions useful throughout Core.
 * 
 * @package Core\Filestore
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130530.1735
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

namespace Core\Filestore;

use Core\Filestore\CDN;


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
		$dir = CDN_LOCAL_ASSETDIR;

		switch(CDN_TYPE){
			case 'local':
				// If the configuration subsystem is not available, this will be null.
				if($dir){
					// Needs to be fully resolved
					if ($dir{0} != '/') $dir = ROOT_PDIR . $dir;
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
		$dir = CDN_LOCAL_PUBLICDIR;

		switch(CDN_TYPE){
			case 'local':
				// If the configuration subsystem is not available, this will be null.
				if($dir){
					// Needs to be fully resolved
					if ($dir{0} != '/') $dir = ROOT_PDIR . $dir;
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
		//$dir = CDN_LOCAL_PRIVATEDIR;
		$dir = \ConfigHandler::Get('/core/filestore/privatedir');

		switch(CDN_TYPE){
			case 'local':
				// If the configuration subsystem is not available, this will be null.
				if($dir){
					// Needs to be fully resolved
					if ($dir{0} != '/') $dir = ROOT_PDIR . $dir;
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

	// I need to check the custom, current theme, and finally default locations for the file.
	$theme = \ConfigHandler::Get('/theme/selected');
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
		case 'csv':
			return 'text/csv';
		case 'css':
			return 'text/css';
		case 'html':
		case 'htm':
			return 'text/html';
		case 'ics':
			return 'text/calendar';
		case 'js':
			return 'text/javascript';
		case 'json':
			return 'application/json';
		case 'otf':
			return 'font/otf';
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