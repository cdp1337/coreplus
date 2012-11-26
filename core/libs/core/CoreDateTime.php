<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 10/17/12
 * Time: 3:09 AM
 * To change this template use File | Settings | File Templates.
 */
class CoreDateTime {
	/**
	 * @var DateTime
	 */
	private $_dt;

	public function __construct($datetime = null){
		if($datetime){
			$this->setDate($datetime);
		}
		else{
			// Just load right now.
			$this->_dt = new DateTime();
		}
	}

	/**
	 * Set the data/time of this object.
	 *
	 * If a unix timestamp is used, it is automatically set as GMT.
	 * If a formatted date it used, the TIME_DEFAULT_TIMEZONE is used instead.
	 *
	 * @param $datetime string|int
	 */
	public function setDate($datetime){
		//echo "Incoming date: [" . $datetime . "]<br/>"; die();
		// If the number coming in is strictly numeric, interpret that as a unix timestamp in GMT.
		if(is_numeric($datetime)){
			$this->_dt = new DateTime(null, self::_GetTimezone('GMT'));
			$this->_dt->setTimestamp($datetime);
		}
		else{
			$this->_dt = new DateTime($datetime, self::_GetTimezone(TIME_DEFAULT_TIMEZONE));
		}
	}

	public function getTimezoneName(){
		if(!$this->_dt) return null;

		return $this->_dt->getTimezone()->getName();
	}

	public function isGMT(){
		if(!$this->_dt) return false;

		return ($this->_dt->getTimezone()->getName() == 'UTC');
	}


	public function getFormatted($format, $desttimezone = Time::TIMEZONE_USER){
		if($format == 'RELATIVE'){
			//return Time::GetRelativeAsString($date, Time::TIMEZONE_USER);
			return $this->getRelative();
		}
		else{
			// Check the offsets.  If they're the same, no change really needed.
			$tzto = self::_GetTimezone($desttimezone);

			if($tzto->getName() == $this->_dt->getTimezone()->getName()){
				return $this->_dt->format($format);
			}
			// Else, I need to clone the object so I can change the timezone, then return that formatted.
			// In this case, changing the timezone will handle all translation operations internally :)
			$clone = clone $this->_dt;
			$clone->setTimezone($tzto);
			return $clone->format($format);
		}
	}

	/**
	 * Get a string to represent the relative time from right now.
	 * Will return something similar to 'Yesterday at 5:40p' or 'Today at 4:20a', etc...
	 *
	 * @param $dateformat string The formatting to use for dates.
	 * @param $timeformat string The formatting to use for times.
	 * @param $accuracy int The level of accuracy,
	 *                      2 will return today|yesterday|tomorrow,
	 *                      3 will return up to a week, ie: Monday at 4:40pm
	 *  @param $timezone int The timezone to display the result as.
	 *
	 *
	 *
	 * @return string
	 */
	public function getRelative($dateformat = 'M j, Y', $timeformat = 'g:ia', $accuracy = 3, $timezone = Time::TIMEZONE_DEFAULT) {
		// First, get the day of now and the time that's being compared.
		// They will form an int in the format of YYYYMMDD.
		$now = new DateTime();
		$now->setTimezone(self::_GetTimezone($timezone));

		$nowStamp = $now->format('Ymd');
		$cStamp   = $this->getFormatted('Ymd', $timezone);

		// The first couple days will always be converted, today and tomorrow/yesterday.
		if ($nowStamp - $cStamp == 0) return 'Today at ' . $this->getFormatted($timeformat, $timezone);
		elseif ($nowStamp - $cStamp == 1) return 'Yesterday at ' . $this->getFormatted($timeformat, $timezone);
		elseif ($nowStamp - $cStamp == -1) return 'Tomorrow at ' . $this->getFormatted($timeformat, $timezone);

		// If accuracy is the minimum and neither today/tomorrow/yesterday, simply return the date.
		if ($accuracy <= 2) return $this->getFormatted($dateformat, $timezone);

		// If it's too high/low from a week, just return the date.
		if (abs($nowStamp - $cStamp) > 6) return $this->getFormatted($dateformat, $timezone);

		// Else, return the day of the week, followed by the time.
		return $this->getFormatted('l \a\t ' . $timeformat, $timezone);
	}




	/**
	 * Get a valid DateTimeZone from its name.  Useful for caching timezone objects.
	 *
	 * @param string $timezone
	 *
	 * @return DateTimeZone
	 */
	private static function _GetTimezone($timezone) {
		static $timezones = array();

		if ($timezone == Time::TIMEZONE_USER) {
			// Convert this to the user's timezone.
			$timezone = \Core\user()->get('timezone');

			if($timezone === null) $timezone = date_default_timezone_get();

			// Users must have valid timezone strings too!
			if (is_numeric($timezone)) $timezone = Time::TIMEZONE_DEFAULT;
		}

		if($timezone === Time::TIMEZONE_GMT || $timezone === 'GMT'){
			$timezone = 'UTC';
		}
		elseif($timezone == Time::TIMEZONE_DEFAULT){
			$timezone = TIME_DEFAULT_TIMEZONE;
		}


		if (!isset($timezones[$timezone])) {
			$timezones[$timezone] = new DateTimeZone($timezone);
		}

		return $timezones[$timezone];
	}
}
