<?php
/**
 * File for class Factory definition in the coreplus project
 * 
 * @package Core\Filestore
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130604.2130
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

use Core\Filestore\Backends;


/**
 * A short teaser of what Factory does.
 *
 * More lengthy description of what Factory does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for Factory
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
 * @package Core\Filestore
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
abstract class Factory {
	/**
	 * Array of file objects that have been instantiated to act as a cache.
	 *
	 * @var array
	 */
	protected static $_Files = array();

	/**
	 * Array of file objects that have been instantiated to act as a cache.
	 *
	 * @var array
	 */
	protected static $_Directories = array();

	/**
	 * Static function to act as Factory for the underlying Filestore system.
	 * This will parse the incoming URI and return the appropriate type based on Core settings and filetype.
	 *
	 * @param $uri
	 *
	 * @return File
	 */
	public static function File($uri) {

		// GOGO caching ;)
		if(isset(self::$_Files[$uri])){
			return self::$_Files[$uri];
		}

		$originaluri = $uri;

		// self::$_Files[$originaluri]

		//var_dump($uri);

		// base64 comes first.  If the filename is encoded in that, decode it first.
		if (strpos($uri, 'base64:') === 0){
			$uri = base64_decode(substr($uri, 7));
		}

		// Allow FTP files to be requested here!
		// This needs to be before the :// check, because technically FTP can be a remote file,
		// but it has extra functionality, (namely being able to write or perform other operations through FTP)
		if(strpos($uri, 'ftp://') === 0){
			self::$_Files[$originaluri] = new Backends\FileFTP($uri);
			return self::$_Files[$originaluri];
		}

		// If this is a local file, just the URL version.... allow that remap too!
		if(strpos($uri, ROOT_URL_NOSSL) === 0){
			$uri = ROOT_PDIR . substr($uri, strlen(ROOT_URL_NOSSL));
		}
		elseif(strpos($uri, ROOT_URL_SSL) === 0){
			$uri = ROOT_PDIR . substr($uri, strlen(ROOT_URL_SSL));
		}

		// Allow remote files to be requested here too!
		if(strpos($uri, '://') !== false){
			self::$_Files[$originaluri] = new Backends\FileRemote($uri);
			return self::$_Files[$originaluri];
		}

		// Is this an asset request?
		if(
			strpos($uri, 'asset/') === 0 ||
			strpos($uri, 'assets/') === 0 ||
			strpos($uri, get_asset_path()) === 0
		){
			self::$_Files[$originaluri] = resolve_asset_file($uri);
			return self::$_Files[$originaluri];
		}

		// Is this a public request?
		if(
			strpos($uri, 'public/') === 0 ||
			strpos($uri, get_public_path()) === 0
		){
			self::$_Files[$originaluri] = resolve_public_file($uri);
			return self::$_Files[$originaluri];
		}

		// Is this a private request?
		if(
			strpos($uri, 'private/') === 0 ||
			strpos($uri, get_private_path()) === 0
		){
			// @todo
			return new CDN\FileAsset($uri);
		}

		// Is this a tmp request?
		if(strpos($uri, 'tmp/') === 0){
			self::$_Files[$originaluri] = new Backends\FileLocal(get_tmp_path() . substr($uri, 4));
			return self::$_Files[$originaluri];
		}
		elseif(strpos($uri, get_tmp_path()) === 0){
			self::$_Files[$originaluri] = new Backends\FileLocal($uri);
			return self::$_Files[$originaluri];
		}

		// Umm.... ok
		self::$_Files[$originaluri] = new Backends\FileLocal($uri);
		return self::$_Files[$originaluri];
	}

	/**
	 * Static function to act as Factory for the underlying Filestore system.
	 * This will parse the incoming URI and return the appropriate type based on Core settings and filetype.
	 *
	 * @param $uri
	 *
	 * @return Directory
	 */
	static function Directory($uri){
		//var_dump($uri);

		// base64 comes first.  If the filename is encoded in that, decode it first.
		if (strpos($uri, 'base64:') === 0){
			$uri = base64_decode(substr($uri, 7));
		}

		// Allow FTP files to be requested here!
		// This needs to be before the :// check, because technically FTP can be a remote file,
		// but it has extra functionality, (namely being able to write or perform other operations through FTP)
		if(strpos($uri, 'ftp://') === 0){
			return new Backends\DirectoryFTP($uri);
		}

		// Allow remote files to be requested here too!
		//if(strpos($uri, '://') !== false){
		//	return new Backends\FileRemote($uri);
		//}

		// Is this an asset request?
		if(
			strpos($uri, 'asset/') === 0 ||
			strpos($uri, 'assets/') === 0 ||
			strpos($uri, get_asset_path()) === 0
		){
			return resolve_asset_directory($uri);
		}

		// Is this a public request?
		if(
			strpos($uri, 'public/') === 0 ||
			strpos($uri, get_public_path()) === 0
		){
			return resolve_public_directory($uri);
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
			return new Backends\DirectoryLocal(get_tmp_path() . substr($uri, 4));
		}
		elseif(strpos($uri, get_tmp_path()) === 0){
			return new Backends\DirectoryLocal($uri);
		}

		// Umm.... ok
		return new Backends\DirectoryLocal($uri);
	}
}