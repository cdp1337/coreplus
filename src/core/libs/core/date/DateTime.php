<?php
/**
 * Provides the Core\Date\DateTime class
 *
 * @package Core\Date
 * @since 3.1.0
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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
			$format = \Core\i18n\Loader::Get('FORMAT_FULLDATE');
		}
		elseif($format == DateTime::SHORTDATE){
			$format = \Core\i18n\Loader::Get('FORMAT_SHORTDATE');
		}
		elseif($format == DateTime::FULLDATETIME){
			$format = \Core\i18n\Loader::Get('FORMAT_FULLDATETIME');
		}
		elseif($format == DateTime::SHORTDATETIME){
			$format = \Core\i18n\Loader::Get('FORMAT_SHORTDATETIME');
		}
		elseif($format == DateTIme::TIME){
			$format = \Core\i18n\Loader::Get('FORMAT_TIME');
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
		return $clone->format($format, $desttimezone);
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

		// @todo Locale Setting, g:i A needs to be the actual locale time instead.

		// The first couple days will always be converted, today and tomorrow/yesterday.
		if ($nowStamp - $cStamp == 0) return 'Today at ' . $this->format('g:i A', $timezone);
		elseif ($nowStamp - $cStamp == 1) return 'Yesterday at ' . $this->format('g:i A', $timezone);
		elseif ($nowStamp - $cStamp == -1) return 'Tomorrow at ' . $this->format('g:i A', $timezone);

		// If accuracy is the minimum and neither today/tomorrow/yesterday, simply return the date.
		if ($accuracy <= 2) return $this->format(DateTime::SHORTDATE, $timezone);

		// If it's too high/low from a week, just return the date.
		if (abs($nowStamp - $cStamp) > 6) return $this->format(DateTime::SHORTDATE, $timezone);

		// Else, return the day of the week, followed by the time.
		return $this->format('l \a\t ' . 'g:i A', $timezone);
	}

	/**
	 * Get the day of the week of this event, 0 being Sunday and 6 being Saturday.
	 *
	 * This is just a shortcut function that calls format('w').
	 *
	 * @return int
	 */
	public function getDayOfWeek(){
		return $this->format('w');
	}

	/**
	 * Skip ahead to the "next" month
	 *
	 * This is a skip to increase the day to the same day, (if possible), the next month of the gregorian calendar.
	 *
	 * If the day does not exist, the closest possible day will be selected. (such as Jan 30th -> Feb 28th)
	 *
	 * @param $jump int Amount of months to jump, (default: 1)
	 */
	public function nextMonth($jump = 1){
		$y = $this->format('Y', $this->getTimezone());
		$m = $this->format('n', $this->getTimezone());
		$d = $this->format('d', $this->getTimezone());

		$m += $jump;
		while($m > 12){
			$m -= 12;
			++$y;
		}

		$this->setDate($y, $m, 1);
		$d = min($this->format('t', $this->getTimezone()), $d);
		$this->setDate($y, $m, $d);
	}

	/**
	 * Skip behind to the "previous" month
	 *
	 * This is a skip to increase the day to the same day, (if possible), the previous month of the gregorian calendar.
	 *
	 * If the day does not exist, the closest possible day will be selected. (such as Mar 30th -> Feb 28th)
	 *
	 * @param $jump int Amount of months to jump, (default: 1)
	 */
	public function prevMonth($jump = 1){
		$y = $this->format('Y', $this->getTimezone());
		$m = $this->format('n', $this->getTimezone());
		$d = $this->format('d', $this->getTimezone());

		$m -= $jump;
		while($m <= 12){
			$m += 12;
			--$y;
		}

		$this->setDate($y, $m, 1);
		$d = min($this->format('t', $this->getTimezone()), $d);
		$this->setDate($y, $m, $d);
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

	/**
	 * Shortcut function for getting the GMT time at "now".
	 *
	 * @param string $format the format to return, by default will return unix timestamp.
	 *
	 * @return string
	 */
	public static function NowGMT($format = 'U'){
		$d = new DateTime();
		return $d->format($format, Timezone::TIMEZONE_GMT);
	}

	/**
	 * Shortcut function for formatting a timestamp or date string into another format and timezone.
	 *
	 * @param     $datetime
	 * @param     $format
	 * @param int $timezone
	 *
	 * @return string
	 */
	public static function FormatString($datetime, $format, $timezone = Timezone::TIMEZONE_DEFAULT){
		$d = new DateTime($datetime);
		return $d->format($format, $timezone);
	}
}
