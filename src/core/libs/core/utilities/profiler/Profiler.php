<?php
/**
 * File for class Profiler definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130410.1753
 * @package Core\Utilities\Profiler
 * @copyright Copyright (C) 2009-2017  Charlie Powell
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

namespace Core\Utilities\Profiler;
use Core\i18n\I18NLoader;


/**
 * Profiler gives a simple performance profiler for scripts and utilities.
 *
 * <h3>Usage</h3>
 *
 * <h4>System Profiler</h4>
 * <p>
 * Core has a system profiler running from the start of the application.
 * If FULL_DEBUG is set to true, then any event recorded there will be displayed at the end of the page execution.
 * </p>
 *
 * <code>
 * $profiler = \Core\Utilities\Profiler\Profiler::GetDefaultProfiler();
 * $profiler->record('my awesome event');
 * </code>
 *
 * <h4>Custom Profiler</h4>
 * <p>
 * To create a new profiler, (and a new timer), the following code will do that job.
 * </p>
 *
 * <code>
 * $profiler = new \Core\Utilities\Profiler\Profiler('this set');
 *
 * // Do some logic that takes some amount of time
 * // ...
 * // ...
 * $profiler->record('done with step one');
 *
 * // More stuff that takes a long time
 * // ...
 * // ...
 * $profiler->record('Finished!');
 *
 * // Display the overall time!
 * echo '&lt;h1&gt;Finished in ' . $profiler-&gt;getTimeFormatted() . '&lt;/h1&gt;';
 *
 * // Or if you want a breakdown of the events themselves...
 * echo '&lt;pre&gt;' . $profiler-&gt;getEventTimesFormatted() . '&lt;/pre&gt;';
 * </code>
 * 
 * @package Core\Utilities\Profiler
 * @author Charlie Powell <charlie@evalagency.com>
 */
class Profiler {
	private $_name;

	private $_events = array();

	private $_microtime;

	private static $_DefaultProfiler;

	public function __construct($name){
		$this->_name = $name;

		// Start the root timer
		$this->_microtime = microtime(true);

		$this->record('Starting profiler ' . $name);

		// Do I need to register to the global "singleton" scope?
		if(self::$_DefaultProfiler === null){
			self::$_DefaultProfiler = $this;
		}
	}

	/**
	 * Record an event and its profile time from the start of the application.
	 * @param $event
	 */
	public function record($event){
		// The current microtime.
		$now = microtime(true);

		// Find the differences between the first and now.
		$time = $now - $this->_microtime;

		$event = array(
			'event'     => $event,
			'microtime' => $now,
			'timetotal' => $time,
			'memory'  => memory_get_usage(false),
		);
		
		/*if(EXEC_MODE == 'CLI'){
			// Print these immediately instead of recording.
			echo $this->_formatEvent($event) . "\n";
		}
		else{*/
			// Otherwise record!
			$this->_events[] = $event;
		//}
	}

	/**
	 * Sometimes you just want to know how many milliseconds passed between the start of the app and now
	 * This is useful for logging utilities.
	 *
	 * @return float
	 */
	public function getTime(){
		return microtime(true) - $this->_microtime;
	}

	/**
	 * Get all the recorded events of this profiler as an array.
	 *
	 * @return array
	 */
	public function getEvents(){
		return $this->_events;
	}

	/**
	 * Get the overall execution time of this profiler.
	 *
	 * This will be rounded and formatted as such:
	 * "# µs", "# ms", "# s", "# m # s", or "# h # m".
	 *
	 * @return string
	 */
	public function getTimeFormatted(){
		$time = $this->getTime();
		
		return \Core\time_duration_format($time, 4);
	}

	/**
	 * Get the breakdown of recorded events and their time into the profiler operation.
	 * 
	 * @return string
	 */
	public function getEventTimesFormatted(){
		$out = '';
		foreach ($this->getEvents() as $t) {
			$out .= $this->_formatEvent($t) . "\n";
		}

		return $out;
	}
	
	private function _formatEvent($event){
		$in = round($event['timetotal'], 5) * 1000;
		$dcm = class_exists('I18NLoader') ? I18NLoader::GetLocaleConv('decimal_point') : '.';

		if ($in == 0){
			$time = '0000' . $dcm . '00 ms';
		}
		else{
			$parts = explode($dcm, $in);
			$whole = str_pad($parts[0], 4, 0, STR_PAD_LEFT);
			$dec   = (isset($parts[1])) ? str_pad($parts[1], 2, 0, STR_PAD_RIGHT) : '00';
			$time = $whole . $dcm . $dec . ' ms';
		}

		$mem = '[mem: ' . \Core\Filestore\format_size($event['memory']) . '] ';

		$event = $event['event'];

		return "[$time] $mem- $event";
	}

	/**
	 * Get the first instance of the profiler
	 *
	 * @return Profiler
	 */
	public static function GetDefaultProfiler(){
		if(self::$_DefaultProfiler === null){
			// Try to find the global one.
			global $profiler;
			if($profiler){
				self::$_DefaultProfiler = $profiler;
			}
			else{
				self::$_DefaultProfiler = new self('Default');
			}
		}

		return self::$_DefaultProfiler;
	}
}
