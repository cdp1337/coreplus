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
use Core\Session;


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
class DatamodelProfiler {
	private $_name;

	private $_events = [];

	private $_last = [];

	private static $_DefaultProfiler;

	private $_reads = 0;

	private $_writes = 0;

	public function __construct($name){
		$this->_name = $name;

		// Do I need to register to the global "singleton" scope?
		if(self::$_DefaultProfiler === null){
			self::$_DefaultProfiler = $this;
		}
	}

	/**
	 * Get the number of reads that have been performed on this page load.
	 *
	 * @return int
	 */
	public function readCount(){
		return $this->_reads;
		//return Session::Get('datamodel_profiler_events/reads', 0);
	}

	/**
	 * Get the number of writes that have been performed on this page load.
	 *
	 * @return int
	 */
	public function writeCount(){
		return $this->_writes;
		//return Session::Get('datamodel_profiler_events/writes', 0);
	}

	/**
	 * Start recording a given query.
	 *
	 * @param string $type "read" or "write", (usually).
	 * @param string $query Human-readable version of the query string.
	 */
	public function start($type, $query){
		// Record this query!
		// This needs to include the query itself, what type it was, how long it took to execute,
		// any errors it produced, and where in the code it was called.

		if(FULL_DEBUG || (DEVELOPMENT_MODE && sizeof($this->_events) < 40)){
			// By skipping this in production, memory usage is cut by nearly 50% on über DB heavy pages!
			// (This occurs on pages that have more than 10k queries.
			$debug = debug_backtrace();
			$callinglocation = array();
			$count = 0;
			$totalcount = 0;
			foreach($debug as $d){
				$class = (isset($d['class'])) ? $d['class'] : null;
				++$totalcount;

				if(strpos($class, 'Core\\Datamodel') === 0) continue;
				if(strpos($class, 'Core\\Utilities\\Profiler') === 0) continue;
				if($class == 'Model') continue;

				$file = (isset($d['file'])) ? (substr($d['file'], strlen(ROOT_PDIR))) : 'anonymous';
				$line = (isset($d['line'])) ? (':' . $d['line']) : '';
				$func = ($class !== null) ? ($d['class'] . $d['type'] . $d['function']) : $d['function'];

				$callinglocation[] = $file . $line . ', [' . $func . '()]';
				++$count;
				if($count >= 3 && sizeof($debug) >= $totalcount + 2){
					$callinglocation[] = '...';
					break;
				}
			}
		}
		else{
			$callinglocation = ['**SKIPPED**  Please enable FULL_DEBUG to see the calling stack.'];
		}

		\Core\log_verbose('DatamodelProfiler: [' . $type . '] ' . $query);

		$this->_last[] = [
			'start' => microtime(true),
			'type' => $type,
			'query' => $query,
			'caller' => $callinglocation,
			'memory'  => memory_get_usage(true),
		];
	}

	public function stopSuccess($count){
		if(sizeof($this->_last) == 0){
			// Nothing to do, you must use start first!
			return;
		}

		$last = array_pop($this->_last);

		$time = microtime(true) - $last['start'];
		$timeFormatted = \Core\time_duration_format($time, 2);

		if($last['type'] == 'read'){
			++$this->_reads;
		}
		else{
			++$this->_writes;
		}

		if(DEVELOPMENT_MODE && class_exists('\\Core\\Session')){
			// Add this data to the SESSION if the site is currently in DEV mode.
			$events = Session::Get('datamodel_profiler_events/events', []);
			$events[] = array(
				'query'  => $last['query'],
				'type'   => $last['type'],
				'time'   => $time,
				'errno'  => null,
				'error'  => '',
				'caller' => $last['caller'],
				'rows'   => $count
			);
			Session::Set('datamodel_profiler_events/events', $events);

			if($last['type'] == 'read'){
				Session::Set('datamodel_profiler_events/reads', Session::Get('datamodel_profiler_events/reads') + 1);
			}
			else{
				Session::Set('datamodel_profiler_events/writes', Session::Get('datamodel_profiler_events/writes') + 1);
			}
		}

		if(defined('DMI_QUERY_LOG_TIMEOUT') && DMI_QUERY_LOG_TIMEOUT >= 0){
			if(DMI_QUERY_LOG_TIMEOUT == 0 || ($time * 1000) >= DMI_QUERY_LOG_TIMEOUT ){
				\Core\log_warning('[' . $timeFormatted . '] ' . $last['query'], 'Slow Query');
			}
		}
	}

	public function stopError($code, $error){
		if(sizeof($this->_last) == 0){
			// Nothing to do, you must use start first!
			return;
		}

		$last = array_pop($this->_last);

		$time = microtime(true) - $last['start'];
		$timeFormatted = \Core\time_duration_format($time, 2);

		if($last['type'] == 'read'){
			++$this->_reads;
		}
		else{
			++$this->_writes;
		}

		if(DEVELOPMENT_MODE) {
			// Add this data to the SESSION if the site is currently in DEV mode.
			$events   = Session::Get('datamodel_profiler_events/events', []);
			$events[] = [
				'query'  => $last['query'],
				'type'   => $last['type'],
				'time'   => $time,
				'errno'  => $code,
				'error'  => $error,
				'caller' => $last['caller'],
				'rows'   => 0
			];
			Session::Set('datamodel_profiler_events/events', $events);

			if($last['type'] == 'read') {
				Session::Set('datamodel_profiler_events/reads', Session::Get('datamodel_profiler_events/reads') + 1);
			}
			else {
				Session::Set('datamodel_profiler_events/writes', Session::Get('datamodel_profiler_events/writes') + 1);
			}
		}

		if(defined('DMI_QUERY_LOG_TIMEOUT') && DMI_QUERY_LOG_TIMEOUT >= 0){
			if(DMI_QUERY_LOG_TIMEOUT == 0 || ($time * 1000) >= DMI_QUERY_LOG_TIMEOUT ){
				\Core\log_warning('[' . $timeFormatted . '] ' . $last['query'], 'Slow Query');
			}
		}
	}

	/**
	 * Get all the recorded events of this profiler as an array.
	 *
	 * @return array
	 */
	public function getEvents(){
		return Session::Get('datamodel_profiler_events/events', []);
	}

	/**
	 * Get the overall execution time of this profiler.
	 *
	 * This will be rounded and formatted as such:
	 * "# µs", "# ms", "# s", "# m # s", or "# h # m".
	 *
	 * @return string
	 */
	public function getTimeFormatted($time){

		// 0.00010 = 100 µs
		// 0.00100 = 1 ms
		// 0.01000 = 10 ms
		// 0.10000 = 100 ms
		// 1.00000 = 1 second
		// 60.0000 = 1 minute
		// 3600.00 = 1 hour

		if($time < 0.001){
			return round($time, 4) * 1000000 . ' µs';
		}
		elseif($time < 2.0){
			return round($time, 4) * 1000 . ' ms';
		}
		elseif($time < 120){
			return round($time, 0) . ' s';
		}
		elseif($time < 3600) {
			$m = round($time, 0) / 60;
			$s = round($time - $m*60, 0);
			return $m . ' m ' . $s . ' s';
		}
		else{
			$h = round($time, 0) / 3600;
			$m = round($time - $h*3600, 0);
			return $h . ' h ' . $m . ' m';
		}
	}

	/**
	 * Get the breakdown of recorded events and their time into the profiler operation.
	 *
	 * @return string
	 */
	public function getEventTimesFormatted(){
		$out = '';

		$ql = $this->getEvents();
		$qls = sizeof($this->_events);
		foreach($ql as $i => $dat){
			if($i > 1000){
				$out .= 'Plus ' . ($qls - 1000) . ' more!' . "\n";
				break;
			}

			if($dat['type'] === 'read'){
				$typecolor = '#88F';
				$tpad = '  ';
				
				if($dat['time'] <= .001)    $icolor = COLOR_SUCCESS;
				elseif($dat['time'] <= .01) $icolor = COLOR_WARNING;
				else                         $icolor = COLOR_ERROR;
			}
			else{
				$typecolor = '#005';
				$tpad = ' ';
				
				if($dat['time'] <= .01)    $icolor = COLOR_SUCCESS;
				elseif($dat['time'] <= .1) $icolor = COLOR_WARNING;
				else                        $icolor = COLOR_ERROR;
			}
			
			$reset  = COLOR_RESET;
			$type   = $dat['type'];
			$time   = str_pad(\Core\time_duration_format($dat['time'], 2), 9, '0', STR_PAD_LEFT);
			$query = htmlentities($dat['query'], ENT_QUOTES | ENT_HTML5);
			$caller = print_r($dat['caller'], true);
			if($dat['rows'] !== null){
				$caller .= "\n" . 'Number of affected rows: ' . $dat['rows'];
			}
			$out .= <<<EOL
<span title='$caller'><span style="color:$typecolor;">[$type]</span>${tpad}[${icolor}${time}${reset}] <code class="sql">$query</code></span>\n
EOL;
		}

		// Purge the output.
		Session::UnsetKey('datamodel_profiler_events/*');

		return $out;
	}

	/**
	 * Get the first instance of the profiler
	 *
	 * @return DatamodelProfiler
	 */
	public static function GetDefaultProfiler(){
		if(self::$_DefaultProfiler === null){
			// Try to find the global one.
			global $datamodelprofiler;
			if($datamodelprofiler){
				self::$_DefaultProfiler = $datamodelprofiler;
			}
			else{
				self::$_DefaultProfiler = new self('Query Log');
			}
		}

		return self::$_DefaultProfiler;
	}
}
