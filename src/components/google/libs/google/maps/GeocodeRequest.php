<?php
/**
 * File for class GeocodeRequest definition
 *
 * @package Google\Maps
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130521.1504
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

namespace Google\Maps;


/**
 * A short teaser of what GeocodeRequest does.
 *
 * More lengthy description of what GeocodeRequest does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for GeocodeRequest
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
class GeocodeRequest {

	/** @var string Set to the address line 1 of the location to lookup */
	public $address1;
	/** @var string Set to the address line 2 of the location to lookup */
	public $address2;
	/** @var string Set to the city to lookup */
	public $city;
	/** @var string Set to the state to lookup */
	public $state;
	/** @var string Set to the postal/zip code to lookup */
	public $postal;
	/** @var string Set to the country (two-letter code) to restrict the region to */
	public $country = 'US';
	/** @var string Set to any Google-parsable string, may or may not succeed */
	public $fullAddress;

	public $lat;
	public $lng;

	public $sensor = false;

	/**
	 * @return GeocodeResponse
	 */
	public function lookup(){
		if($this->lat && $this->lng){
			// @todo reverse lookup
		}
		else{
			return $this->_lookup();
		}
	}

	private function _lookup() {

		// At least address or city are required.
		if(!(
			($this->address1 && $this->city) ||
			$this->city ||
			$this->postal ||
			$this->fullAddress
		)){
			throw new \Exception('At least the address or city are required for geocode lookups.');
		}

		// a couple of required vars to sign the request url
		$private_key = \ConfigHandler::Get('/googlemaps/enterprise/privatekey');
		$clientname = \ConfigHandler::Get('/googlemaps/enterprise/clientname');

		$params = [
			'address' => null, // Set below!
			'sensor' => ($this->sensor ? 'true' : 'false'),
		];

		if($this->address1 && $this->city){
			$params['address'] = $this->address1 . ($this->city ? ', '.$this->city : '') . ($this->state ? ', ' . $this->state : '');
		}
		elseif($this->postal){
			$params['address'] = $this->postal;
		}
		elseif($this->fullAddress){
			$params['address'] = $this->fullAddress;
		}

		if($this->country){
			$params['region'] = $this->country;
		}

		if($clientname){
			// Only add the client parameter if it's set in the config, otherwise it's a guest connection.
			$params['client'] = $clientname;
		}

		// Make a request to google and update the record.
		// http://maps.googleapis.com/maps/api/geocode/json?address=1600+Amphitheatre+Parkway,+Mountain+View,+CA&sensor=true_or_false
		$ps = [];
		foreach($params as $k => $v){
			$ps[] = $k . '=' . urlencode(trim($v));
		}

		if($private_key){
			// Use the enterprise-friendly URL.
			// the url to sign
			$signurl = '/maps/api/geocode/json?' . implode('&', $ps);

			//zee signature!
			$signature = hash_hmac("sha1", $signurl, base64_decode(strtr($private_key, '-_', '+/')), true);
			$signature = strtr(base64_encode($signature), '+/', '-_');

			//var_dump($signature); die();

			$url = 'http://maps.googleapis.com/maps/api/geocode/json?' . implode('&', $ps) . '&signature=' . $signature;
		}
		else{
			$url = 'http://maps.googleapis.com/maps/api/geocode/json?' . implode('&', $ps);;
		}

		// Make the request
		// Since this is making use of Core's native File system, caching of remote files is builtin for free :)
		$request = \Core\Filestore\Factory::File($url);
		$json = $request->getContents();
		$contents = json_decode($json, true);

		$result = new GeocodeResponse($contents);
		return $result;
	}
}