<?php
/**
 * File for class Loader definition in the coreplus project
 * 
 * @package Core\i18n
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20140326.2321
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

namespace Core\i18n;


/**
 * A short teaser of what Loader does.
 *
 * More lengthy description of what Loader does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for Loader
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
 * @package Core\i18n
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class Loader {
	protected static $Strings;

	protected static $IsLoaded = false;

	public static function Init(){

		if(self::$IsLoaded){
			return;
		}

		self::$IsLoaded = true;

		$cachekey = 'core-i18n-strings';
		$cached = \Core\Cache::Get($cachekey, 604800); // Cache here is good for one week.
		if(!DEVELOPMENT_MODE && $cached){
			self::$Strings = $cached;
			return;
		}

		$files = [];

		foreach(\Core::GetComponents() as $c){
			/** @var \Component_2_1 $c */
			$dir = $c->getBaseDir() . 'i18n/';

			if(!is_dir($dir)){
				// No i18n directory defined in this component, simply skip over.
				continue;
			}

			$dh = opendir($dir);
			if(!$dh){
				// Couldn't open directory, skip.
				continue;
			}

			while (($file = readdir($dh)) !== false) {

				// I only want ini files here.
				if(substr($file, -4) != '.ini'){
					continue;
				}
				$files[] = $dir . $file;
			}
			closedir($dh);
		}

		self::$Strings = [];

		foreach($files as $f){
			$ini = parse_ini_file($f, true);
			self::$Strings = array_merge(self::$Strings, $ini);
		}

		// Make sure that each language set has all base directives set too!
		foreach(self::$Strings as $k => $dat){
			if(strpos($k, '_') === false){
				// Skip the root language setting itself.
				continue;
			}

			$base = substr($k, 0, strpos($k, '_'));
			if(!isset(self::$Strings[$base])){
				self::$Strings[$base] = [];
			}
			foreach($dat as $s => $t){
				if(!isset(self::$Strings[$base][$s])){
					self::$Strings[$base][$s] = $t;
				}
			}
		}

		\Core\Cache::Set($cachekey, self::$Strings, 604800); // Cache here is good for one week.
	}

	/**
	 * Lookup a translation string with the requested language.
	 *
	 * Will return the located string, or null if not located.
	 *
	 * @param string $key
	 * @param string $lang
	 *
	 * @return string|null
	 */
	public static function Get($key, $lang){
		$default = 'en';
		if(strpos($lang, '_') !== false){
			// Skip the root language setting itself.
			$base = substr($lang, 0, strpos($lang, '_'));
		}
		else{
			$base = false;
		}

		if(isset(self::$Strings[$lang][$key])){
			return self::$Strings[$lang][$key];
		}
		elseif($base && isset(self::$Strings[$base][$key])){
			return self::$Strings[$default][$key];
		}
		elseif(isset(self::$Strings[$default][$key])){
			return self::$Strings[$default][$key];
		}
		else{
			return null;
		}
	}
} 