<?php
/**
 * Provides the Core\Date\DateTime class
 *
 * @package Core\Date
 * @since 3.1.0
 * @author Charlie Powell <charlie@eval.bz>
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
 * Class DateTime extends the default DateTime object with Core-specific functionality
 * such as automatic timezones and automatic localization support.
 *
 * @package Core\Date
 */
class DateTime extends \DateTime{

	/**
	 * Full date format, ran through localization.
	 */
	const FULLDATE = 'FD';

	/**
	 * Short date format, ran through localization.
	 */
	const SHORTDATE = 'SD';

	/**
	 * Full date time format, ran through localization.
	 */
	const FULLDATETIME = 'FDT';

	/**
	 * Short date time format, ran through localization.
	 */
	const SHORTDATETIME = 'SDT';

	/**
	 * Time format, ran through localization.
	 */
	const TIME = 'TIME';

	/**
	 * Date or time format
	 */
	const RELATIVE = 'RELATIVE';

	/**
	 * Datetime format for seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
	 *
	 * @example 1355592396
	 */
	const EPOCH = 'U';


	/**
	 * @link http://php.net/manual/en/datetime.construct.php
	 *
	 * @param string|null $datetime        String representation of the date to manipulate
	 * @param \DateTimeZone|null $timezone String representation or DateTimeZone object of the timezone, null for automatic
	 * @todo Support Locale on datetime objects.
	 *
	 * @return DateTime
	 *
	 * @throws \Exception
	 */
	public function __construct($datetime = null, $timezone = null){

		// If the timezone is not set, try to resolve it automatically.
		if($timezone === null && is_numeric($datetime)){
			// unix timestamps are stored in GMT/UTC, so assume that.
			$timezone = Timezone::GetTimezone('UTC');
		}
		elseif($timezone === null && $datetime !== null){
			// Other dates are probably in the default timezone, so use that.
			$timezone = Timezone::GetTimezone(Timezone::TIMEZONE_DEFAULT);
		}
		else{
			// Ummm..... Just to be sure.
			$timezone = Timezone::GetTimezone($timezone);
		}

		if($datetime === null){
			// NULL is an alias for now.
			parent::__construct('now', $timezone);
		}
		elseif(is_numeric($datetime)){
			// A numeric datetime string, (unix timestamp), and a null timezone request translates to GMT time.
			// This is because all unix timestamps in Core are by default stored in GMT/UTC time.
			parent::__construct(null, $timezone);
			$this->setTimestamp($datetime);
		}
		else{
			parent::__construct($datetime, $timezone);
		}
	}

	/**
	 * Get the timezone name of this datetime object
	 *
	 * @return string
	 */
	public function getTimezoneName(){
		return $this->getTimezone()->getName();
	}

	/**
	 * Get if this datetime object is GMT/UTC
	 *
	 * @return bool
	 */
	public function isGMT(){
		return ($this->getTimezone()->getName() == 'UTC');
	}


	/**
	 * Returns date formatted according to given format
	 *
	 * @link http://php.net/manual/en/datetime.format.php
	 *
	 * @param string          $format
	 * @param int|string|null $desttimezone
	 *
	 * @return string
	 */
	public function format($format, $desttimezone = Timezone::TIMEZONE_USER){
		if($format == DateTime::RELATIVE){
			return $this->getRelative();
		}
		elseif($format == DateTime::FULLDATE){
			// @todo Locale Setting!
			$format = 'F j, Y';
		}
		elseif($format == DateTime::SHORTDATE){
			// @todo Locale Setting!
			$format = 'n/j/Y';
		}
		elseif($format == DateTime::FULLDATETIME){
			// @todo Locale Setting!
			$format = 'F j, Y \a\t g:i A';
		}
		elseif($format == DateTime::SHORTDATETIME){
			// @todo Locale Setting!
			$format = 'g:i A, n/j/Y';
		}
		elseif($format == DateTIme::TIME){
			// @todo Locale Setting!
			$format = 'g:i A';
		}


		// Check the offsets.  If they're the same, no change really needed.
		$tzto = Timezone::GetTimezone($desttimezone);

		if($tzto->getName() == $this->getTimezone()->getName()){
			return parent::format($format);
		}
		// Else, I need to clone the object so I can change the timezone, then return that formatted.
		// In this case, changing the timezone will handle all translation operations internally :)
		$clone = clone $this;
		$clone->setTimezone($tzto);
		return $clone->format($format);
	}

	/**
	 * Get a string to represent the relative time from right now.
	 * Will return something similar to 'Yesterday at 5:40p' or 'Today at 4:20a', etc...
	 *
	 * @param $accuracy int The level of accuracy,
	 *                      2 will return today|yesterday|tomorrow,
	 *                      3 will return up to a week, ie: Monday at 4:40pm
	 *  @param $timezone int The timezone to display the result as.
	 *
	 *
	 *
	 * @return string
	 */
	public function getRelative($accuracy = 3, $timezone = Timezone::TIMEZONE_DEFAULT) {
		// First, get the day of now and the time that's being compared.
		// They will form an int in the format of YYYYMMDD.

		$now = new DateTime('now', $timezone);

		$nowStamp = $now->format('Ymd');
		$cStamp   = $this->format('Ymd', $timezone);

		// The first couple days will always be converted, today and tomorrow/yesterday.
		if ($nowStamp - $cStamp == 0) return 'Today at ' . $this->format(DateTime::TIME, $timezone);
		elseif ($nowStamp - $cStamp == 1) return 'Yesterday at ' . $this->format(DateTime::TIME, $timezone);
		elseif ($nowStamp - $cStamp == -1) return 'Tomorrow at ' . $this->format(DateTime::TIME, $timezone);

		// If accuracy is the minimum and neither today/tomorrow/yesterday, simply return the date.
		if ($accuracy <= 2) return $this->format(DateTime::SHORTDATE, $timezone);

		// If it's too high/low from a week, just return the date.
		if (abs($nowStamp - $cStamp) > 6) return $this->format(DateTime::SHORTDATE, $timezone);

		// Else, return the day of the week, followed by the time.
		return $this->format('l \a\t ' . DateTime::TIME, $timezone);
	}


	/**
	 * Shortcut function for getting the time now.
	 *
	 * @param string $format
	 * @param int    $timezone
	 *
	 * @return string
	 */
	public static function Now($format = 'Y-m-d', $timezone = Timezone::TIMEZONE_DEFAULT){
		$d = new DateTime();
		return $d->format($format, $timezone);
	}
}
