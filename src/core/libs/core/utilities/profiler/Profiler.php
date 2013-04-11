<?php
/**
 * File for class Profiler definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130410.1753
 * @package Core\Utilities\Profiler
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

namespace Core\Utilities\Profiler;


/**
 * Class Profiler description
 * 
 * @package Core\Utilities\Profiler
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

		// And record!
		$this->_events[] = array(
			'event'     => $event,
			'microtime' => $now,
			'timetotal' => $time
		);
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

	public function getEvents(){
		return $this->_events;
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
