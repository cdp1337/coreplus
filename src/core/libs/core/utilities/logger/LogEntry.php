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

/**
 * Description of LogEntry
 *
 * @author charlie
 */
class LogEntry {
	
	/**
	 * @var string The level of this log entry.
	 * 
	 * LOG_LEVEL_ERROR (1)
	 * LOG_LEVEL_WARNING (2)
	 * LOG_LEVEL_INFO (3)
	 * LOG_LEVEL_DEBUG (4)
	 * LOG_LEVEL_VERBOSE (5)
	 */
	public $level = \LOG_LEVEL_INFO;
	
	/**
	 * @var string Type of log entry, debug, info, error, security, or some custom string
	 */
	public $type = 'info';
	
	/**
	 * @var string The actual message to log
	 */
	public $message = null;
	
	/**
	 * @var string An optional identifier for this log entry
	 */
	public $code = null;
	
	/**
	 * @var string An optional lengthy description for this log entry
	 */
	public $details = null;
	
	/**
	 * @var string An optional user ID if this log entry affects a given user
	 */
	public $user = null;
	
	/**
	 * @var string An optional icon to display along with this log message.
	 */
	public $icon = null;
	
	/**
	 * @var string An optional source to keep track of what type of message this comes from.
	 */
	public $source = null;
}
