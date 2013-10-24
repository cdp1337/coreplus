<?php
/**
 * [PAGE DESCRIPTION HERE]
 *
 * @package Core Plus\Core
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


abstract class ComponentFactory {

	/**
	 * Cache of versions in the database already.  Useful for reducing the number of lookups.
	 * @var array
	 */
	private static $_DBCache = null;

	/**
	 * Internal function to lookup the saved data for a given component based on its name.
	 *
	 * Will return null if it doesn't exist or an array.
	 *
	 * @param string $componentname The name of the component to lookup
	 *
	 * @return array | null
	 */
	public static function _LookupComponentData($componentname) {
		if (self::$_DBCache === null) {
			self::$_DBCache = array();

			// Try to load the components
			try {
				$res = Core\Datamodel\Dataset::Init()->table('component')->select('*')->execute();
			}
				// But since this function is called during the installer, it might fail... that's acceptable.
			catch (DMI_Exception $e) {
				return false;
			}

			foreach ($res as $r) {
				$n                  = strtolower($r['name']);
				self::$_DBCache[$n] = $r;
			}
		}

		$componentname = strtolower($componentname);

		return (isset(self::$_DBCache[$componentname])) ? self::$_DBCache[$componentname] : null;
	}

	/**
	 * Load a Component of the appropriate version based on the XML file.
	 *
	 * Will return either a Component if API 0.1, or a Component_2_1 if API 2.1
	 *
	 * @param string $filename
	 *
	 * @return Component_2_1 || Component
	 */
	public static function Load($filename) {
		//$filename = ROOT_PDIR . 'components/' . $file . '/component.xml';

		// Check this version of the file.
		$fh = fopen($filename, 'r');
		if (!$fh) return null;

		$line = fread($fh, 512);
		fclose($fh);

		// The 2.4 is the newest API version released.  It's compatible with 2.1
		// This was released around Jan 2013
		if (strpos($line, 'http://corepl.us/api/2_4/component.dtd') !== false) {
			return new Component_2_1($filename);
		}
		// The 2.1 API version, released in early 2012.
		elseif (strpos($line, 'http://corepl.us/api/2_1/component.dtd') !== false) {
			return new Component_2_1($filename);
		}
		else {
			// Component version 0.1 still requires the basename.
			$name = substr($filename, 0, -14);
			$name = substr($name, strrpos($name, '/') + 1);
			return new Component($name);
		}
	}

	/**
	 * Resolve a component's name to its XML file, NOT fully resolved.
	 *
	 * @static
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public static function ResolveNameToFile($name) {
		// Makes lookups easier.
		$name = strtolower($name);

		if ($name == 'core') return 'core/component.xml';
		elseif (file_exists(ROOT_PDIR . 'components/' . $name . '/component.xml')) return 'components/' . $name . '/component.xml';
		else return false;
	}
}