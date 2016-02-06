<?php
/**
 * File for class GeocodeResponse definition
 * 
 * @package Google\Maps
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130521.1512
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
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class GeocodeResponse {

	/**
	 * Indicates that no errors occurred; the address was successfully parsed and at least one geocode was returned.
	 */
	const ERROR_OK = 'OK';
	/**
	 * Indicates that the geocode was successful but returned no results.
	 * This may occur if the geocode was passed a non-existent address or a latlng in a remote location.
	 */
	const ERROR_ZERO_RESULTS = 'ZERO_RESULTS';
	/**
	 * Indicates that you are over your quota.
	 */
	const ERROR_OVER_QUERY_LIMIT = 'OVER_QUERY_LIMIT';
	/**
	 * Indicates that your request was denied, generally because of lack of a sensor parameter.
	 */
	const ERROR_REQUEST_DENIED = 'REQUEST_DENIED';
	/**
	 * Generally indicates that the query (address or latlng) is missing.
	 */
	const ERROR_INVALID_REQUEST = 'INVALID_REQUEST';
	/**
	 * No response or empty response from Google.
	 */
	const ERROR_NO_RESPONSE = '_NO_RESPONSE';
	/**
	 * Invalid response from Google.
	 */
	const ERROR_BAD_RESPONSE = '_BAD_RESPONSE';
	/**
	 * Indicates that the request could not be processed due to a server error. The request may succeed if you try again.
	 */
	const ERROR_UNKNOWN_ERROR = 'UNKNOWN_ERROR';


	public $status = GeocodeResponse::ERROR_ZERO_RESULTS;
	public $results;

	public function __construct($response = null){
		if($response){
			$this->processResponse($response);
		}
	}


	/**
	 * Process a response from Google servers.
	 * Usually called from within the Geocode Request system.
	 *
	 * @param mixed $response
	 */
	public function processResponse($response){
		if(!$response){
			$this->status = self::ERROR_NO_RESPONSE;
		}
		elseif(!is_array($response)){
			$this->status = self::ERROR_BAD_RESPONSE;
		}
		else{
			$this->status = $response['status'];
			$this->results = $response['results'];
		}
	}

	/**
	 * Get if this response is a valid response.
	 * @return bool
	 */
	public function isValid(){
		return $this->status == self::ERROR_OK;
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

	/**
	 * Get the latitude for the given result.
	 *
	 * Shorthand of getLatLng()
	 *
	 * @param int $result
	 *
	 * @return float|null
	 */
	public function getLat($result = 0){
		return $this->getLatLng($result)[0];
	}

	/**
	 * Get the longitude for the given result.
	 *
	 * Shorthand of getLatLng()
	 *
	 * @param int $result
	 *
	 * @return float|null
	 */
	public function getLng($result = 0){
		return $this->getLatLng($result)[1];
	}
}