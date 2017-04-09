<?php
/**
 * 
 *
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
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

namespace Core;


class HealthCheckResult {
	
	const RESULT_GOOD  = 'GOOD';
	const RESULT_WARN  = 'WARN';
	const RESULT_ERROR = 'ERRR';
	const RESULT_SKIP  = 'SKIP';
	
	public $result = self::RESULT_SKIP;
	
	public $title       = '';
	public $description = '';
	public $link        = '';

	/**
	 * Convenience function to create a new successful check
	 * 
	 * @param $title
	 * @param $description
	 *
	 * @return HealthCheckResult
	 */
	public static function ConstructGood($title, $description){
		$result = new HealthCheckResult();
		$result->result = self::RESULT_GOOD;
		$result->title = $title;
		$result->description = $description;
		return $result;
	}

	/**
	 * Convenience function to create a new warning check
	 *
	 * @param $title
	 * @param $description
	 *
	 * @return HealthCheckResult
	 */
	public static function ConstructWarn($title, $description, $fixLink){
		$result = new HealthCheckResult();
		$result->result = self::RESULT_WARN;
		$result->title = $title;
		$result->description = $description;
		$result->link = $fixLink;
		return $result;
	}

	/**
	 * Convenience function to create a new error check
	 *
	 * @param $title
	 * @param $description
	 *
	 * @return HealthCheckResult
	 */
	public static function ConstructError($title, $description, $fixLink){
		$result = new HealthCheckResult();
		$result->result = self::RESULT_ERROR;
		$result->title = $title;
		$result->description = $description;
		$result->link = $fixLink;
		return $result;
	}

	/**
	 * Convenience function to create a new warning check
	 *
	 * @param $title
	 * @param $description
	 *
	 * @return HealthCheckResult
	 */
	public static function ConstructSkip($title, $description){
		$result = new HealthCheckResult();
		$result->result = self::RESULT_SKIP;
		$result->title = $title;
		$result->description = $description;
		return $result;
	}
}