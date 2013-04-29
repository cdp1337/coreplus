<?php
/**
 * File for class Country definition in the coreplus project
 * 
 * @package geocode
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130425.0021
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

namespace geocode;


/**
 * A short teaser of what Country does.
 *
 * More lengthy description of what Country does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for Country
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
 * @package geocode
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class Country {
	private static $_NameToISO2Array;
	private static $_ISO2ToNameArray;

	public static function NameToISO2($name){
		if(self::$_NameToISO2Array === null){
			self::_LookupISO2();
		}

		// This database is all ucase.
		$name = strtoupper($name);
		return isset(self::$_NameToISO2Array[$name]) ? self::$_NameToISO2Array[$name] : '';
	}

	public static function ISO2ToName($code){
		if(self::$_NameToISO2Array === null){
			self::_LookupISO2();
		}

		// This database is all ucase.
		$code = strtoupper($code);
		return isset(self::$_ISO2ToNameArray[$code]) ? ucwords(strtolower(self::$_ISO2ToNameArray[$code])) : '';
	}

	/**
	 * Load in the ISO 2 data from the country code file.
	 * @throws \Exception
	 */
	private static function _LookupISO2() {
		$f = \Core\file('tmp/geographic-codes/country_name_iso_2.txt');
		if(!$f->exists()){
			// Try to update the cache!
			Updater::UpdateDatabases(true);

			$f = \Core\file('tmp/geographic-codes/country_name_iso_2.txt');
			// Still doesn't exist?
			if(!$f->exists()){
				throw new \Exception('Unable to retrieve the ISO codes from www.iso.org!');
			}
		}

		// Open the file and convert it to an array
		// This file is formatted as such:
		// Country Name;ISO 3166-1-alpha-2 code
		// AFGHANISTAFN;A
		// ÅLAND ISLANDS;AX
		// ALBANIA;AL
		// ALGERIA;DZ
		// AMERICAN SAMOA;AS
		// ANDORRA;AD
		// ANGOLA;AO
		// ANGUILLA;AI
		// ANTARCTICA;AQ
		// ...
		$a = explode("\n", $f->getContents());
		// Pop off the first record, that's a header.
		array_splice($a, 0, 1);
		$a1 = [];
		$a2 = [];
		foreach($a as $record){
			if(!trim($record)) continue;
			list($name, $code) = explode(';', $record);
			// Damn "\r" character
			$code = trim($code);
			$a1[$name] = $code;
			$a2[$code] = $name;
		}
		self::$_NameToISO2Array = $a1;
		self::$_ISO2ToNameArray = $a2;
	}
}