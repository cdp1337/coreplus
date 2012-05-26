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


class Time {

	const TIMEZONE_GMT     = 0;
	const TIMEZONE_DEFAULT = 100;
	const TIMEZONE_USER    = 101;

	const FORMAT_ISO8601 = 'c';
	const FORMAT_RFC2822 = 'r';

	const FORMAT_FULLDATETIME = self::FORMAT_ISO8601;

	private static $_Instance = null;

	private $timezones = array();

	private function __construct() {
		// This is required because of a change to the Time system in API 2.1
		if (is_numeric(TIME_DEFAULT_TIMEZONE)) {
			throw new Exception('Please ensure that the constant TIME_DEFAULT_TIMEZONE is set to a valid timezone string.');
		}
		$this->timezones[0]   = new DateTimeZone('GMT');
		$this->timezones[100] = new DateTimeZone(TIME_DEFAULT_TIMEZONE);
	}

	/**
	 * Get a valid DateTimeZone from the intiger of it.
	 *
	 * Note, these will all be the generic GMT-5 timezones.
	 *
	 * @param string $timezone
	 *
	 * @return DateTimeZone
	 */
	private function _getTimezone($timezone) {
		if ($timezone == Time::TIMEZONE_USER) {
			// Conver this to the user's timezone.
			$timezone = \Core\user()->getPreference('timezone')->value;

			// Users must have valid timezone strings too!
			if (is_numeric($timezone)) $timezone = Time::TIMEZONE_DEFAULT;
		}

		if (!isset($this->timezones[$timezone])) {
			$this->timezones[$timezone] = new DateTimeZone($timezone);
		}

		return $this->timezones[$timezone];
	}

	/**
	 *
	 * @return Time
	 */
	private static function _Singleton() {
		if (self::$_Instance === null) {
			self::$_Instance = new self();
		}
		return self::$_Instance;
	}

	/**
	 * Will return the current GMT time corrected via the server GMT_OFFSET config setting.
	 *
	 * @param $format string
	 *
	 * @return string
	 */
	public static function GetCurrentGMT($format = 'U') {
		$date = new DateTime(null, self::_Singleton()->_getTimezone(0));
		return $date->format($format);
	}

	/**
	 * Get the current time for the given timezone formatted as per requested
	 *
	 * @param int    $timezone
	 *            int value of the timezone requested
	 * @param string $formatting
	 *               date formatting to return, as per PHP's date format
	 *
	 * @see http://us3.php.net/date
	 *
	 * @return string
	 */
	public static function GetCurrent($timezone = Time::TIMEZONE_GMT, $format = 'U') {
		$date = new DateTime(null, self::_Singleton()->_getTimezone($timezone));
		return $date->format($format);
	}

	/**
	 * Get a string to represent the relative time from right now.
	 * Will return something similar to 'Yesterday at 5:40p' or 'Today at 4:20a', etc...
	 *
	 * @param $time int The time, (in GMT), to get the relative from now.
	 * @param $timezone int The timezone to display the result as.
	 * @param $accuracy int The level of accuracy,
	 *                      2 will return today|yesterday|tomorrow,
	 *                      3 will return up to a week, ie: Monday at 4:40pm
	 * @param $timeformat string The formatting to use for times.
	 * @param $dateformat string The formatting to use for dates.
	 *
	 * @return string
	 */
	public static function GetRelativeAsString($time, $timezone = Time::TIMEZONE_GMT, $accuracy = 3, $timeformat = 'g:ia', $dateformat = 'M j, Y') {
		// First, get the day of now and the time that's being compared.
		// They will form an int in the format of YYYYMMDD.
		$nowStamp = Time::GetCurrent($timezone, 'Ymd');
		$cStamp   = Time::FormatGMT($time, $timezone, 'Ymd');

		// The first couple days will always be converted, today and tomorrow/yesterday.
		if ($nowStamp - $cStamp == 0) return 'Today at ' . Time::FormatGMT($time, $timezone, $timeformat);
		elseif ($nowStamp - $cStamp == 1) return 'Yesterday at ' . Time::FormatGMT($time, $timezone, $timeformat);
		elseif ($nowStamp - $cStamp == -1) return 'Tomorrow at ' . Time::FormatGMT($time, $timezone, $timeformat);

		// If accuracy is the minimum and neither today/tomorrow/yesterday, simply return the date.
		if ($accuracy <= 2) return Time::FormatGMT($time, $timezone, $dateformat);

		// If it's too high/low from a week, just return the date.
		if (abs($nowStamp - $cStamp) > 6) return Time::FormatGMT($time, $timezone, $dateformat);

		// Else, return the day of the week, followed by the time.
		return Time::FormatGMT($time, $timezone, 'l \a\t ' . $timeformat);
	}

	/**
	 * Format a given GMT time as $format and return in timezone $timezone.
	 *
	 * (assumes a corrected GMT value)
	 *
	 * @param $timeInGMT int
	 * @param $timezone int
	 * @param $format string
	 *
	 * @return string
	 */
	public static function FormatGMT($timeInGMT, $timezone = Time::TIMEZONE_GMT, $format = 'U') {
		// Allow null to be sent for those of us who are lazy.
		if ($timezone === null) $timezone = self::TIMEZONE_GMT;

		// DateTime is a little finicky with unix times for some reason...
		if (is_numeric($timeInGMT)) $timeInGMT = '@' . $timeInGMT;

		$date = new DateTime($timeInGMT, self::_Singleton()->_getTimezone(0));
		// Apply the new timezone.
		if ($timezone != Time::TIMEZONE_GMT) $date->setTimezone(self::_Singleton()->_getTimezone($timezone));
		return $date->format($format);
	}
}
