<?php
/**
 * File for class Lookup definition in the coreplus project
 * 
 * @package geocode
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140327.0647
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

namespace geocode;
use Core\Cache;
use Core\Filestore\Factory;


/**
 * A short teaser of what Lookup does.
 *
 * More lengthy description of what Lookup does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for Lookup
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
 * @package geocode
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class IPLookup {

	/** @var string The city of the lookup */
	public $city;
	/** @var string The 2-digit province/state of the lookup */
	public $province;
	/** @var string The 2-digit country of the lookup */
	public $country;
	/** @var string The timezone of the lookup */
	public $timezone;
	/** @var string The postal code of the lookup */
	public $postal;

	public function __construct($ip_addr) {
		try{
			if(\Core\is_ip_private($ip_addr)){
				$cache = [
					'city'     => 'LOCAL',
					'province' => '',
					'country'  => '',
					'timezone' => date_default_timezone_get(),
					'postal'   => '',
				];
			}
			else{
				$cacheKey = 'iplookup-' . $ip_addr;
				
				$cache = Cache::Get($cacheKey);
				
				if(!$cache){
					$reader = new \GeoIp2\Database\Reader(ROOT_PDIR . 'components/geographic-codes/libs/maxmind-geolite-db/GeoLite2-City.mmdb');

					/** @var \GeoIp2\Model\CityIspOrg $geo */
					$geo = $reader->cityIspOrg($ip_addr);
					//$geo = $reader->cityIspOrg('67.149.214.236');

					$reader->close();
					
					$sd = isset($geo->subdivisions[0]) ? $geo->subdivisions[0] : null;

					$cache = [
						'city'     => $geo->city->name,
						'province' => $sd ? $sd->isoCode : '',
						'country'  => $geo->country->isoCode,
						'timezone' => $geo->location->timeZone,
						'postal'   => $geo->postal->code,
					];
					
					Cache::Set($cacheKey, $cache, SECONDS_ONE_WEEK);
				}
			}
		}
		catch(\Exception $e){
			// Well, we tried!  Load something at least.
			$cacheKey = 'iplookup-' . $ip_addr;
			$cache = [
				'city'     => 'McMurdo Base',
				'province' => '',
				'country'  => 'AQ',
				'timezone' => 'CAST',
				'postal'   => '',
			];
			Cache::Set($cacheKey, $cache, SECONDS_ONE_HOUR);
		}

		$this->city     = $cache['city'];
		$this->province = $cache['province'];
		$this->country  = $cache['country'];
		$this->timezone = $cache['timezone'];
		$this->postal   = $cache['postal'];
	}

	public function getCountryName() {
		return $this->country ? \GeoCountryModel::ISO2ToName($this->country) : 'Unknown';
	}

	public function getCountryIcon($dimensions = '20x20') {
		if(!$this->country) {
			$f = Factory::File('assets/images/placeholders/generic.png');
		}
		else {
			$f = Factory::File('assets/images/iso-country-flags/' . strtolower($this->country) . '.png');
		}

		return $f->getPreviewURL($dimensions);
	}
	
	public function getAsHTML($getflag){
		if($this->city == 'LOCAL'){
			$country = 'LOCAL';
			$cname = 'Local/Internal Connection';
			$flag = 'assets/images/iso-country-flags/intl.png';
			$check = false;
		}
		else{
			$country = $this->country;
			$cname = $this->getCountryName();
			$flag = 'assets/images/iso-country-flags/' . strtolower($this->country) . '.png';
			$check = true;
		}

		if($getflag){
			$file = \Core\Filestore\Factory::File($flag);

			if($file->exists()){
				$out = '<img src="' . $file->getPreviewURL('20x20') . '" title="' . $cname . '" alt="' . $country . '"/> ';
			}
			else{
				$out = '';
			}
		}
		else{
			$out = '';
		}


		if($check && $this->province && $this->city){
			$out .= $this->city . ', ' . $this->province;	
		}
		elseif($check && $this->province){
			$out .= $this->province;
		}
		elseif($check && $this->city){
			$out .= $this->city;
		}
		elseif($country){
			$out .= $country;
		}

		return $out;
	}
} 