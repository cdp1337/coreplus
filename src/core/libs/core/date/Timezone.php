<?php
/**
 * File for class Timezone definition in the Ticketing Application project
 * 
 * @package Core\Date
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20140122.1808
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

namespace Core\Date;


/**
 * A short teaser of what Timezone does.
 *
 * More lengthy description of what Timezone does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for Timezone
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
 * @package Core\Date
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class Timezone {
	const TIMEZONE_GMT     = 0;
	const TIMEZONE_DEFAULT = 100;
	const TIMEZONE_USER    = 101;

	/**
	 * Get a valid \DateTimeZone from its name.  Useful for caching timezone objects.
	 *
	 * @param string|null|\DateTimeZone $timezone
	 *
	 * @return \DateTimeZone
	 */
	public static function GetTimezone($timezone) {
		static $timezones = array();

		if($timezone instanceof \DateTimeZone){
			// No conversion required!
			return $timezone;
		}
		elseif ($timezone == Timezone::TIMEZONE_USER) {
			// Convert this to the user's timezone.
			$timezone = \Core\user()->get('timezone');

			// Users must have valid timezone strings too!

			if($timezone === null){
				$timezone = date_default_timezone_get();
			}
			elseif(is_numeric($timezone)){
				$timezone = date_default_timezone_get();
			}
		}
		elseif($timezone === Timezone::TIMEZONE_GMT || $timezone === 'GMT' || $timezone === null){
			$timezone = 'UTC';
		}
		elseif($timezone == Timezone::TIMEZONE_DEFAULT){
			$timezone = date_default_timezone_get();
		}


		if (!isset($timezones[$timezone])) {
			$timezones[$timezone] = new \DateTimeZone($timezone);
		}

		return $timezones[$timezone];
	}
} 