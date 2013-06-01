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
 * Function to act as Factory for the underlying Filestore system.
 * This will parse the incoming URI and return the appropriate type based on Core settings and filetype.
 *
 * @param $uri
 *
 * @return File
 */
function factory($uri){
	//var_dump($uri);

	// base64 comes first.  If the filename is encoded in that, decode it first.
	if (strpos($uri, 'base64:') === 0){
		$uri = base64_decode(substr($uri, 7));
	}

	// Allow FTP files to be requested here!
	// This needs to be before the :// check, because technically FTP can be a remote file,
	// but it has extra functionality, (namely being able to write or perform other operations through FTP)
	if(strpos($uri, 'ftp://') === 0){
		return new Backends\FileFTP($uri);
	}

	// Allow remote files to be requested here too!
	if(strpos($uri, '://') !== false){
		return new Backends\FileRemote($uri);
	}

	// Is this an asset request?
	if(
		strpos($uri, 'asset/') === 0 ||
		strpos($uri, 'assets/') === 0 ||
		strpos($uri, get_asset_path()) === 0
	){
		return resolve_asset_file($uri);
	}

	// Is this a public request?
	if(
		strpos($uri, 'public/') === 0 ||
		strpos($uri, get_public_path()) === 0
	){
		return resolve_public_file($uri);
	}

	// Is this a private request?
	if(
		strpos($uri, 'private/') === 0 ||
		strpos($uri, get_private_path()) === 0
	){
		return new CDN\FileAsset($uri);
	}

	// Is this a tmp request?
	if(strpos($uri, 'tmp/') === 0){
		return new Backends\FileLocal(get_tmp_path() . substr($uri, 4));
	}
	elseif(strpos($uri, get_tmp_path()) === 0){
		return new Backends\FileLocal($uri);
	}

	// Umm.... ok
	return new Backends\FileLocal($uri);
}

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

	switch ($file->getMimetype()) {
		case 'application/x-gzip':
			// gzip can be a wrapper around a lot of things.
			// Some of them even have their own content functions.
			if (strtolower($file->getExtension()) == 'tgz'){
				$class = 'ContentTGZ';
			}
			else{
				$class = 'ContentGZ';
			}
			break;

		case 'text/plain':
			// Sometimes these are actually other files based on the extension.
			if (strtolower($file->getExtension()) == 'asc'){
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
			if($file->getExtension() == 'zip'){
				$class = 'ContentZIP';
			}
			else{
				error_log('@fixme Unknown extension for application/octet-stream mimetype [' . $file->getExtension() . ']');
				$class = 'ContentUnknown';
			}
			break;

		default:
			error_log('@fixme Unknown file mimetype [' . $file->getMimetype() . '] with extension [' . $file->getExtension() . ']');
			$class = 'ContentUnknown';
	}

	// Prefix the class with the necessary namespace.
	$resolved = '\\Core\\Filestore\\Contents\\' . $class;

	// Make sure that class exists!
	// In core, even if it doesn't, it should be able to locate the file dynamically.
	// If it can't, then maybe core isn't available yet or this script has been migrated to a different platform.
	// Did you migrate this script to a different platform????
	if(!class_exists($class)){
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
				$custom  = new Backends\FileFTP($resolved  . 'custom/' . $filename);
				$themed  = new Backends\FileFTP($resolved  . $theme . '/' . $filename);
				$default = new Backends\FileFTP($resolved  . 'default/' . $filename);
			}
			else{
				$custom  = new Backends\FileLocal($resolved  . 'custom/' . $filename);
				$themed  = new Backends\FileLocal($resolved  . $theme . '/' . $filename);
				$default = new Backends\FileLocal($resolved  . 'default/' . $filename);
			}

			break;
		default:
			throw new \Exception('Unsupported CDN type: ' . CDN_TYPE);
			break;
	}

	if($custom->exists()){
		// If there is a custom asset installed, USE THAT FIRST!
		$custom->_type = File::TYPE_ASSET;
		return $custom;
	}
	elseif($themed->exists()){
		// Otherwise, the themes can override component assets too.
		$themed->_type = File::TYPE_ASSET;
		return $themed;
	}
	else{
		$default->_type = File::TYPE_ASSET;
		return $default;
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