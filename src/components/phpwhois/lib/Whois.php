<?php
/**
 * File for class Whois definition in the Core Plus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20141202.2209
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


/**
 * A short teaser of what Whois does.
 *
 * More lengthy description of what Whois does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for Whois
 * <h4>Example 1</h4>
 * <p>Description 1</p>
 * <code>
 * // Some code for example 1
 * $a = $b;
 * </code>
 *
 *
 * <h4>Example 2</h4>
 * <p>Description 2</p>
 * <code>
 * // Some code for example 2
 * $b = $a;
 * </code>
 *
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class Whois {

	/**
	 * Lookup query
	 *
	 * @param string $query  IP or hostname to lookup
	 * @param bool   $is_utf Require UTF-8
	 *
	 * @return WhoisResult
	 */
	public static function Lookup($query = '', $is_utf = true) {
		// See if this query has been cached by Core <3
		$cachekey = \Core\str_to_url('whois-' . $query);
		$cached   = \Core\Cache::Get($cachekey);

		if($cached){
			$result = $cached;
		}
		else{
			$whois = new phpwhois\Whois();
			$result = $whois->lookup($query, $is_utf);

			// Cache the results for 6 hours
			\Core\Cache::Set($cachekey, $result, (3600*6));
		}

		if(!is_array($result)){
			return new WhoisNotFoundResult($query);
		}

		if(!sizeof($result)){
			return new WhoisNotFoundResult($query);
		}

		return new WhoisResult($result, $query);
	}
} 