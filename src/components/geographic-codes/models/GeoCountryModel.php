<?php
/**
 * File for class GeoCountryModel definition in the tenant-visitor project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20131009.1524
 * @copyright Copyright (C) 2009-2016  Charlie Powell
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


/**
 * A short teaser of what GeoCountryModel does.
 *
 * More lengthy description of what GeoCountryModel does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for GeoCountryModel
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
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class GeoCountryModel extends Model {
	public static $Schema = array(
		'iso2' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 2,
		),
		'iso3' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 3,
		),
		'name' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
		),
	);

	public static $Indexes = array(
		'unique:iso2' => ['iso2'],
		'unique:iso3' => ['iso3'],
	);

	/** @var array Cache of the models by ISO2 codes */
	private static $Cache2 = [];

	/** @var array Cache of the models by ISO3 codes */
	private static $Cache3 = [];

	/** @var array Cache of the models by name */
	private static $CacheName = [];

	/**
	 * Construct a new CountryModel by its ISO2 code with caching enabled.
	 *
	 * @param string $iso2
	 *
	 * @return GeoCountryModel|null
	 */
	public static function ConstructByISO2($iso2){
		if(!isset(self::$Cache2[$iso2])){
			self::$Cache2[$iso2] = self::Find(['iso2 = ' . $iso2], 1);
		}

		return self::$Cache2[$iso2];
	}

	/**
	 * Construct a new CountryModel by its ISO3 code with caching enabled.
	 *
	 * @param string $iso3
	 *
	 * @return GeoCountryModel|null
	 */
	public static function ConstructByISO3($iso3){
		if(!isset(self::$Cache3[$iso3])){
			self::$Cache3[$iso3] = self::Find(['iso3 = ' . $iso3], 1);
		}

		return self::$Cache3[$iso3];
	}

	/**
	 * Construct a new CountryModel by its name with caching enabled.
	 *
	 * @param string $name
	 *
	 * @return GeoCountryModel|null
	 */
	public static function ConstructByName($name){
		if(!isset(self::$CacheName[$name])){
			self::$CacheName[$name] = self::Find(['name = ' . $name], 1);
		}

		return self::$CacheName[$name];
	}

	/**
	 * Translate a country name to its ISO-2 code in all uppercase.
	 *
	 * @param $name
	 *
	 * @return string
	 */
	public static function NameToISO2($name){
		$class = self::ConstructByName($name);
		return ($class) ? $class->get('iso2') : '';
	}

	/**
	 * Translate a country ISO-2 code to its name.
	 *
	 * @param $code
	 *
	 * @return string
	 */
	public static function ISO2ToName($code){
		$class = self::ConstructByISO2($code);
		return ($class) ? $class->get('name') : '';
	}
}