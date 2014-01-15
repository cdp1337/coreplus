<?php
/**
 * File for class GeocodeResponse definition in the Alliance One project
 * 
 * @package Google\Maps
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130521.1512
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

namespace Google\Maps;


/**
 * A short teaser of what GeocodeResponse does.
 *
 * More lengthy description of what GeocodeResponse does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for GeocodeResponse
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
 * @package Google\Maps
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class GeocodeResponse {

	public $status;
	public $results;

	public function __construct($response){
		$this->status  = $response['status'];
		$this->results = $response['results'];
	}

	/**
	 * Get the number of results found.
	 * @return int
	 */
	public function getNumberOfResults(){
		return sizeof($this->results);
	}

	/**
	 * Get the lat, lng as an array for the given result set.
	 *
	 * @param int $result
	 *
	 * @return array
	 */
	public function getLatLng($result = 0){
		if(!isset($this->results[$result])){
			return [null, null];
		}

		return [
			$this->results[$result]['geometry']['location']['lat'],
			$this->results[$result]['geometry']['location']['lng'],
		];
	}
}