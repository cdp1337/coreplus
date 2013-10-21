<?php
/**
 * File for class Profiler definition in the coreplus project
 *
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130410.1753
 * @package Core\Utilities\Logger
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
	// And format it all perrrrty like.
	$time = str_pad(number_format(round($time, 6) * 1000, 2), 7, '0', STR_PAD_LEFT);

	if (EXEC_MODE == 'CLI'){
		// CLI gets no formatting and is just written to the screen.
		echo '[ DEBUG ' . $time . ' ms ] - ' . $message . "\n";
	}
	elseif($level == DEBUG_LEVEL_LOG){
		// LOG level messages just get error logged.
		error_log('[ DEBUG ' . $time . ' ms ] - ' . $message);
	}
	else{
		echo '<pre class="xdebug-var-dump screen">[' . $time . ' ms] ' . $message . '</pre>';
	}
}

/**
 * Append a message onto the end of a given log file.
 *
 * @param string $filebase The log type base to write to, (used to create ${filebase}.log).
 * @param string $message  The message to append.
 *
 * @throws \Exception
 */
function append_to($filebase, $message, $code = null){
	$logpath = ROOT_PDIR . 'logs/';
	$outfile = $logpath . $filebase . '.log';

	if(!is_dir($logpath)){
		if(!is_writable(ROOT_PDIR)){
			// Not writable, no log!
			throw new \Exception('Unable to open ' . $logpath . ' for writing, access denied on parent directory!');
		}

		if(!mkdir($logpath)){
			// Can't create log directory, no log!
			throw new \Exception('Unable to create directory ' . $logpath . ', access denied on parent directory!');
		}

		$htaccessfh = fopen($logpath . '.htaccess', 'w');
		if(!$htaccessfh){
			// Couldn't open the htaccess for writing!
			throw new \Exception('Unable to create protective .htaccess file in ' . $logpath . '!');
		}

		$htaccesscontents = <<<EOD
<Files *>
	Order deny,allow
	Deny from All
</Files>
EOD;

		fwrite($htaccessfh, $htaccesscontents);
		fclose($htaccessfh);
	}
	elseif(!is_writable($logpath)){
		throw new \Exception('Unable to write to log directory ' . $logpath . '!');
	}
	// No else needed, else is everything dandy!


	// Generate a nice line header to prepend on the line.
	$header = '[' . \Time::GetCurrent(\Time::TIMEZONE_DEFAULT, 'r') . '] ';
	if(EXEC_MODE == 'WEB'){
		$header .= '[client: ' . REMOTE_IP . '] ';
	}
	else{
		$header .= '[client: CLI] ';
	}

	if($code) $header .= '[' . $code . '] ';

	$logfh = fopen($outfile, 'a');
	if(!$logfh){
		throw new \Exception('Unable to open ' . $logpath . 'sfsync.log for appending!');
	}
	foreach(explode("\n", $message) as $line){
		fwrite($logfh, $header . $line . "\n");
	}
	fclose($logfh);
}