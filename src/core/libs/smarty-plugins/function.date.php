<?php
/**
 * @package Core
 * @since 2.1.3
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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

/**
 * Take a GMT date and return the formatted string.
 *
 * @param $params
 * @param $template
 *
 * @throws SmartyException
 *
 * @return string
 */
function smarty_function_date($params, $template){

	if(array_key_exists('date', $params)){
		$date = $params['date'];
	}
	elseif(isset($params[0])){
		$date = $params[0];
	}
	else{
		// Use "now" as the time.
		$date = \Core\Date\DateTime::Now(Time::FORMAT_RFC2822);
	}

	if(!$date){
		if(DEVELOPMENT_MODE){
			return 'Parameter [date] was empty, corwardly refusing to format an empty string.';
		}
		else{
			return '';
		}
	}


	$format = isset($params['format']) ? $params['format'] : \Core\Date\DateTime::RELATIVE;
	//$timezone = isset($params['timezone']) ? $params['timezone'] : Time::TIMEZONE_GMT;

	$coredate = new \Core\Date\DateTime($date);
	return $coredate->format($format);
}
