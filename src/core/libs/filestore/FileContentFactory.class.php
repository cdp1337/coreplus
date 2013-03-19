<?php
/**
 * DESCRIPTION
 *
 * @package Core Plus\Core
 * @since 1.9
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


class FileContentFactory {
	public static function GetFromFile(File_Backend $file) {
		// The class name to instantiate based on the incoming filetype.
		$class = null;

		switch ($file->getMimetype()) {
			case 'application/x-gzip':
				// gzip can be a wrapper around a lot of things.  
				// Some of them even have their own content functions.
				if (strtolower($file->getExtension()) == 'tgz'){
					$class = 'File_tgz_contents';
				}
				else{
					$class = 'File_gz_contents';
				}
				break;

			case 'text/plain':
				// Sometimes these are actually other files based on the extension.
				if (strtolower($file->getExtension()) == 'asc'){
					$class = 'File_asc_contents';
				}
				else{
					$class = 'File_unknown_contents';
				}
				break;

			case 'text/xml':
			case 'application/xml':
				$class = 'File_xml_contents';
				break;

			case 'application/pgp-signature':
				$class = 'File_asc_contents';
				break;

			case 'application/zip':
				$class = 'File_zip_contents';
				break;

			case 'application/octet-stream':
				// These are fun... basically I'm relying on the extension here.
				if($file->getExtension() == 'zip'){
					$class = 'File_zip_contents';
				}
				else{
					error_log('@fixme Unknown extension for application/octet-stream mimetype [' . $file->getExtension() . ']');
					$class = 'File_unknown_contents';
				}
				break;

			default:
				error_log('@fixme Unknown file mimetype [' . $file->getMimetype() . '] with extension [' . $file->getExtension() . ']');
				$class = 'File_unknown_contents';
		}

		// Make sure that class exists!
		// In core, even if it doesn't, it should be able to locate the file dynamically.
		// If it can't, then maybe core isn't available yet or this script has been migrated to a different platform.
		// Did you migrate this script to a different platform????
		if(!class_exists($class)){
			// Hmm.... well
			if(file_exists(ROOT_PDIR . 'core/libs/filestore/contents/' . $class . '.class.php')){
				require_once(ROOT_PDIR . 'core/libs/filestore/contents/' . $class . '.class.php');
			}
			else{
				throw new Exception('Unable to locate file for class [' . $class . ']');
			}
		}

		return new $class($file);
	}
}
