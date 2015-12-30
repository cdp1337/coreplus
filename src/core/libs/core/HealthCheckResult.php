<?php
/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 12/29/15
 * Time: 4:54 PM
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