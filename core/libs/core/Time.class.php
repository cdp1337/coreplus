<?php
/**
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 * 
 * Copyright (C) 2009  Charlie Powell <powellc@powelltechs.com>
 * @author Charlie Powell <powellc@powelltechs.com>
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
class Time{
	
	const TIMEZONE_GMT = 0;
	const TIMEZONE_SERVER = 100;
	const TIMEZONE_USER = 101;
	
	/**
	 * Will return the current GMT time corrected via the server GMT_OFFSET config setting.
	 * 
	 * @param $format string
	 * @return string
	 */
	public static function GetCurrentGMT($format = 'U'){
		//$dt = new DateTime('now', 0);
		return date($format, time() + ConfigHandler::getValue('core', 'gmt_offset'));
	}
	
	/**
   * Get the current time for the given timezone formatted as per requested
   *
   * @param int $timezone 
   *            int value of the timezone requested
   * @param string $formatting 
   *               date formatting to return, as per PHP's date format
   * @see http://us3.php.net/date
   * 
   * @return string
   */
	public static function GetCurrent($timezone = Time::TIMEZONE_GMT, $format = 'U'){
		// @todo Bug found...
		//       If the format requested is 'r', the timezone is included.
		//       This will default to the timezone the server is set to use.
		//       Since the user can request other timezones, this poses a bit of a problem...
		//       A solution for this can be derived from use of the new DateTime system in PHP5.2...
		// @see http://us3.php.net/manual/en/datetime.settimezone.php
		return date($format, Time::ConvertGMT(Time::GetCurrentGMT(), $timezone));
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
	 * @return string
	 */
	public static function GetRelativeAsString($time, $timezone = Time::TIMEZONE_GMT, $accuracy = 3, $timeformat = 'g:ia', $dateformat = 'M j, Y'){
	  // First, get the day of now and the time that's being compared.
	  // They will form an int in the format of YYYYMMDD.
	  $nowStamp = Time::GetCurrent($timezone, 'Ymd');
	  $cStamp = Time::FormatGMT($time, $timezone, 'Ymd');
	  
	  // The first couple days will always be converted, today and tomorrow/yesterday.
	  if($nowStamp - $cStamp == 0) return 'Today at ' . Time::FormatGMT($time, $timezone, $timeformat);
	  elseif($nowStamp - $cStamp == 1) return 'Yesterday at ' . Time::FormatGMT($time, $timezone, $timeformat);
	  elseif($nowStamp - $cStamp == -1) return 'Tomorrow at ' . Time::FormatGMT($time, $timezone, $timeformat);
	  
	  // If accuracy is the minimum and neither today/tomorrow/yesterday, simply return the date.
	  if($accuracy <= 2) return Time::FormatGMT($time, $timezone, $dateformat);
	  
	  // If it's too high/low from a week, just return the date.
	  if(abs($nowStamp - $cStamp) > 6) return Time::FormatGMT($time, $timezone, $dateformat);
	  
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
	 * @return string
	 */
	public static function FormatGMT($timeInGMT, $timezone = Time::TIMEZONE_GMT, $format = 'U'){
		return date($format, Time::ConvertGMT($timeInGMT, $timezone));
	}
	
	/**
	 * Convert a given GMT time to another timezone.
	 * 
	 * (assumes a corrected GMT value)
	 * 
	 * @param $time int
	 * @param $timezone int
	 * @return int
	 * @access private
	 */
	private static function ConvertGMT($timeInGMT, $timezone){
		switch($timezone){
			case Time::TIMEZONE_SERVER:
				return $timeInGMT + (ConfigHandler::getValue('core', 'timezone') * 3600);
			case Time::TIMEZONE_USER:
				return $timeInGMT + (CurrentUser::GetUser()->getPreference('timezone')->value * 3600);
			default:
				return $timeInGMT + ($timezone * 3600);
		}
	}
	
}
