<?php
/**
 * File for class Profiler definition in the coreplus project
 *
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130410.1753
 * @package Core\Utilities\Logger
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

namespace Core\Utilities\Logger;

use Core\Utilities\Profiler\Profiler;

const DEBUG_LEVEL_LOG = 1; // Basic debugging written to the error log.
// 2 ??
// 3 ??
// 4 ??
const DEBUG_LEVEL_FULL = '5'; // Core debug level, typically not required unless working on the core.

function write_debug($message, $level = DEBUG_LEVEL_FULL){
	// Only allow full debug messages to continue through if FULL DEBUG is enabled.
	if($level >= DEBUG_LEVEL_FULL && !FULL_DEBUG) return;

	$profiler = Profiler::GetDefaultProfiler();
	// Grab how many ms have passed since the application started.
	$time = $profiler->getTime();

	// Format this into a human readable format.
	$time = \Core\time_duration_format($time, 2);

	$time = str_pad($time, 10, '0', STR_PAD_LEFT);
	
	if (EXEC_MODE == 'CLI'){
		// CLI gets no formatting and is just written to the screen.
		echo '[ DEBUG ' . $time . ' ] - ' . $message . "\n";
	}
	elseif($level == DEBUG_LEVEL_LOG){
		// LOG level messages just get error logged.
		error_log('[ DEBUG ' . $time . ' ] - ' . $message);
	}
	else{
		echo '<pre class="xdebug-var-dump screen">[' . $time . '] ' . $message . '</pre>';
	}
}

/**
 * Append a message onto the end of a given log file.
 *
 * @param string      $filebase The log type base to write to, (used to create ${filebase}.log).
 * @param string      $message  The message to append.
 * @param null|string $code     Code or error type to prefix the log with.
 *
 * @throws \Exception
 */
function append_to($filebase, $message, $code = null){
	if(class_exists('Core\\Utilities\\Logger\\LogFile')){
		$log = new LogFile($filebase);
		$log->write($message, $code);
	}
	else{
		error_log($message);
	}

}