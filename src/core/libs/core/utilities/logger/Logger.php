<?php

/*
 * @copyright Copyright (C) 2009-2017  charlie
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

/**
 * Description of Logger
 *
 * @author charlie
 */
class Logger {
	
	private static $_LogFiles = [];
	
	public static function Log(LogEntry $entry){
		
		// Load the various logging levels enabled on the system.
		try{
			if(class_exists('ConfigHandler')){
				$logLevelDB = \ConfigHandler::Get('/core/logs/db/level');
				$logLevelFile = \ConfigHandler::Get('/core/logs/level');
				
				if($logLevelDB === null){
					$logLevelDB = LOG_LEVEL_INFO;
				}
				if($logLevelFile === null){
					$logLevelFile = LOG_LEVEL_WARNING;
				}
			}
			else{
				$logLevelDB = LOG_LEVEL_INFO;
				$logLevelFile = LOG_LEVEL_WARNING;
			}
		}
		catch(\Exception $ex){
			$logLevelDB = LOG_LEVEL_INFO;
			$logLevelFile = LOG_LEVEL_WARNING;
		}
		
		
		// If the site is currently in DEV mode, record this log entry to the profiler.
		// Levels <= 4 will be recorded for stdout
		// and level 5 will be recorded if FULL_DEBUG is enabled as well.
		if(
			(defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE && $entry->level <= LOG_LEVEL_DEBUG) ||
			(defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE && FULL_DEBUG)
		){
			// Format the line prefix for the profiler
			switch($entry->level){
				case LOG_LEVEL_ERROR:
					$prefix = COLOR_ERROR . '(' . $entry->type . ')' . COLOR_RESET . ' ';
					break;
				case LOG_LEVEL_WARNING:
					$prefix = COLOR_WARNING . '(' . $entry->type . ')' . COLOR_RESET . ' ';
					break;
				case LOG_LEVEL_DEBUG:
				case LOG_LEVEL_VERBOSE:
					$prefix = COLOR_DEBUG . '(' . $entry->type . ')' . COLOR_RESET . ' ';
					break;
				default:
					$prefix = '';
			}
			
			Profiler::GetDefaultProfiler()->record($prefix . $entry->message);
		}
		
		// If the site is configured to record a flat file for log entries, do that now.
		if($entry->level <= $logLevelFile){
			try{
				if(class_exists('Core\\Utilities\\Logger\\LogFile')){
					$log = self::_GetLogFile($entry->type);
					$log->write($entry->message, $entry->code);
				}
				else{
					error_log('[' . $entry->type . '] ' . $entry->message);
				}
			}
			catch (Exception $ex) {
				error_log('[' . $entry->type . '] ' . $entry->message);
				error_log('Additionally ' . $ex->getMessage());
			}	
		}
		
		if($entry->level <= $logLevelDB && class_exists('\\SystemLogModel')){
			try{
				$log = \SystemLogModel::Factory();
				$log->setFromArray([
					'type'             => $entry->type,
					'code'             => $entry->code,
					'message'          => $entry->message,
					'details'          => $entry->details,
					'icon'             => $entry->icon,
					'affected_user_id' => $entry->user,
					'source'           => $entry->source,
				]);
				$log->save();
			}
			catch (Exception $ex) {
				error_log('Unable to record DB log entry due to: ' . $ex->getMessage());
			}
		}
	}
	
	private static function _GetLogFile($type): LogFile{
		if(!isset(self::$_LogFiles[$type])){
			self::$_LogFiles[$type] = new LogFile($type);
		}
		
		return self::$_LogFiles[$type];
	}
}
