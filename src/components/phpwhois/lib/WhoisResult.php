<?php
/**
 * File for class WhoisResult definition in the coreplus project
 *
 * @package   phpwhois
 * @author    Charlie Powell <charlie@evalagency.com>
 * @date      20130424.2145
 * @copyright Copyright (C) 2009-2017  Charlie Powell
 * @license   GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
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
 * A short teaser of what WhoisResult does.
 *
 * More lengthy description of what WhoisResult does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo    Write documentation for WhoisResult
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
 * @package phpwhois
 * @author  Charlie Powell <charlie@evalagency.com>
 *
 */
class WhoisResult {

	protected $_query;
	protected $_disclaimer;
	protected $_registered = false;
	protected $_admininfo = [];
	protected $_techinfo = [];
	protected $_networkinfo = [];
	protected $_owner = [];
	protected $_domain = [];
	protected $_raw;


	/**
	 * Load and parse a result returned from the underlying whois lookup.
	 *
	 * @param $result
	 * @param $query
	 */
	public function __construct($result, $query) {
		$this->_query = $query;
		$this->_raw = $result;

		if(isset($result['regrinfo']['disclaimer'])) {
			$this->_disclaimer = implode("\n", $result['regrinfo']['disclaimer']);
		}

		if(isset($result['regrinfo']['network'])) {
			$this->_networkinfo = $result['regrinfo']['network'];
		}
		elseif(function_exists('dns_get_record')) {
			$dns = dns_get_record($query, DNS_A);
			if(sizeof($dns)) {
				// w00t
				$this->_networkinfo = $dns[0];
			}
		}

		$anycontactrecord = null;

		$this->_registered = (isset($result['regrinfo']['registered']) && $result['regrinfo']['registered'] == 'yes');

		if(isset($result['regrinfo']['admin'])) {
			$this->_admininfo = $result['regrinfo']['admin'];
			$anycontactrecord = $result['regrinfo']['admin'];
		}

		if(isset($result['regrinfo']['tech'])) {
			$this->_techinfo  = $result['regrinfo']['tech'];
			$anycontactrecord = $result['regrinfo']['tech'];
		}

		if(isset($result['regrinfo']['owner'])) {
			$this->_owner     = $result['regrinfo']['owner'];
			$anycontactrecord = $result['regrinfo']['owner'];
		}

		if(isset($result['regrinfo']['domain'])){
			$this->_domain = $result['regrinfo']['domain'];

			if(empty($this->_domain['nserver']) && function_exists('dns_get_record')) {
				$ns = dns_get_record($query, DNS_NS);
				if(is_array($ns)){
					$this->_domain['nserver'] = [];
					foreach($ns as $row){
						$t = $this->fixNameServer($row['target']);
						$this->_domain['nserver'][ $t['host'] ] = $t['ip'];
					}
				}
			}
		}

		// And fill in any missing records.
		if(!$this->_admininfo && $anycontactrecord) $this->_admininfo = $anycontactrecord;
		if(!$this->_techinfo && $anycontactrecord) $this->_techinfo = $anycontactrecord;
		if(!$this->_owner && $anycontactrecord) $this->_owner = $anycontactrecord;
	}

	/**
	 * Get the country, (2-letter ISO), that this IP or name is registered in.
	 *
	 * @return string
	 */
	public function getCountry() {

		if(isset($this->_networkinfo['country'])) {
			// This should be available within the [network] array.
			$country = $this->_networkinfo['country'];
		}
		elseif(isset($this->_techinfo['address']) && isset($this->_techinfo['address']['country'])) {
			// This may be the name or (possibly) the code.
			$country = $this->_techinfo['address']['country'];
		}
		elseif(isset($this->_techinfo['address'])) {
			// Or if in the tech info address, it's probably the last entry.
			$a       = $this->_techinfo['address'];
			$country = array_pop($a);
		}
		elseif(isset($this->_owner['address']) && isset($this->_owner['address']['country'])) {
			// This may be the name or (possibly) the code.
			$country = $this->_owner['address']['country'];
		}
		elseif(isset($this->_owner['address'])) {
			// Or if in the tech info address, it's probably the last entry.
			$a       = $this->_owner['address'];
			$country = array_pop($a);
		}
		elseif(isset($this->_owner[0]['address']) && isset($this->_owner[0]['address']['country'])) {
			// Or if in the tech info address, it's probably the last entry.
			$country = $this->_owner[0]['address']['country'];
		}
		elseif(isset($this->_owner[0]['address'])) {
			// Or if in the tech info address, it's probably the last entry.
			$a       = $this->_owner[0]['address'];
			$country = array_pop($a);
		}
		else {
			return '';
		}


		// This may be the name or (possibly) the code.
		if(strlen($country) == 2){
			return strtoupper($country);
		}
		else{
			\GeoCountryModel::ISO2ToName($country);
		}
	}

	public function getCountryName() {
		$c = $this->getCountry();
		return $c ? \GeoCountryModel::ISO2ToName($c) : 'Unknown';
	}

	public function getCountryIcon($dimensions = '20x20') {
		$c = $this->getCountry();
		if(!$c) {
			$f = \Core\Filestore\Factory::File('assets/images/placeholders/generic.png');
		}
		else {
			$f =
				\Core\Filestore\Factory::File(
					'assets/images/iso-country-flags/' . strtolower($c) . '.png'
				);
		}

		return $f->getPreviewURL($dimensions);
	}

	/**
	 * Get the ISP or organization name this block or domain is registered to.
	 *
	 * @return string
	 */
	public function getOrganization() {
		$org = null;

		if(isset($this->_owner['organization'])) {
			$org = $this->_owner['organization'];
		}

		if(is_array($org)) {
			$org = $org[0];
		}

		return $org;
	}

	/**
	 * Get the CIDR directive of this IP's network.
	 *
	 * @return string
	 */
	public function getNetwork() {
		return $this->getNetworkIP() . '/' . $this->getCIDR();
	}

	public function getIP() {
		if(isset($this->_networkinfo['ip'])) {
			return $this->_networkinfo['ip']; // Return a single IP.
		}
		elseif(isset($this->_networkinfo['inetnum'])) {
			return $this->_query;
			//list($start, $end) = array_map('trim', explode('-', $this->_networkinfo['inetnum']));
			//return $start;
		}
		elseif(isset($this->_networkinfo[0]['inetnum'])) {
			return $this->_query;
		}
		else {
			return '';
		}
	}

	public function getNetworkIP() {
		if(isset($this->_networkinfo['ip'])) {
			return $this->_networkinfo['ip']; // Return a single IP.
		}
		elseif(isset($this->_networkinfo['inetnum'])) {
			$parts = $this->splitINetNum($this->_networkinfo['inetnum']);

			return $parts['start'];
		}
		elseif(isset($this->_networkinfo[0]['inetnum'])) {
			$clone = $this->_networkinfo;
			$inet  = array_pop($clone);
			// Using pop because this will be a stack of allocations, starting with the top-most-level at index 0.
			// ie: if company A owns a /16 and subleases a few /18's out, then company B, C, or D may be the lower indexes.
			$parts = $this->splitINetNum($inet['inetnum']);

			return $parts['start'];
		}
		else {
			return '';
		}
	}

	public function getCIDR() {
		if(isset($this->_networkinfo['ip'])) {
			return 32; // Return a single IP.
		}
		elseif(isset($this->_networkinfo['inetnum'])) {
			$parts = $this->splitINetNum($this->_networkinfo['inetnum']);

			return $parts['cidr'];
		}
		elseif(isset($this->_networkinfo[0]['inetnum'])) {
			$clone = $this->_networkinfo;
			$inet  = array_pop($clone);
			// Using pop because this will be a stack of allocations, starting with the top-most-level at index 0.
			// ie: if company A owns a /16 and subleases a few /18's out, then company B, C, or D may be the lower indexes.
			$parts = $this->splitINetNum($inet['inetnum']);

			return $parts['cidr'];
		}
		else {
			return '';
		}
	}

	/**
	 * Get an array of the address of this entry, optionally a specific type.
	 *
	 * The resulting array contains the keys street, city, state, pcode, & country
	 *
	 * @param string $type "owner", "admin", "tech".
	 *
	 * @return array
	 */
	public function getAddress($type = 'any'){

		$ret = [
			'street' => null,
			'city' => null,
			'state' => null,
			'pcode' => null,
			'country' => null,
		];

		if(($type == 'any' || $type == 'owner') && isset($this->_owner['address']) && is_array($this->_owner['address'])){
			$src = $this->_owner['address'];
		}
		elseif(($type == 'any' || $type == 'admin') && isset($this->_admininfo['address']) && is_array($this->_admininfo['address'])){
			$src = $this->_admininfo['address'];
		}
		elseif(($type == 'any' || $type == 'tech') && isset($this->_techinfo['address']) && is_array($this->_techinfo['address'])){
			$src = $this->_techinfo['address'];
		}
		elseif(($type == 'any' || $type == 'network') && isset($this->_networkinfo['address']) && is_array($this->_networkinfo['address'])){
			$src = $this->_networkinfo['address'];
		}
		else{
			$src = null;
		}

		if(!$src){
			// No source found, just return an empty array.
			return $ret;
		}

		foreach($ret as $k => $v){
			if(isset($src[$k])){
				$ret[$k] = $src[$k];
			}
		}

		return $ret;

		/*
		if(isset($this->_networkinfo['country'])) {
			// This should be available within the [network] array.
			$country = $this->_networkinfo['country'];
		}
		elseif(isset($this->_techinfo['address']) && isset($this->_techinfo['address']['country'])) {
			// This may be the name or (possibly) the code.
			$country = $this->_techinfo['address']['country'];
		}
		elseif(isset($this->_techinfo['address'])) {
			// Or if in the tech info address, it's probably the last entry.
			$a       = $this->_techinfo['address'];
			$country = array_pop($a);
		}
		elseif(isset($this->_owner['address']) && isset($this->_owner['address']['country'])) {
			// This may be the name or (possibly) the code.
			$country = $this->_owner['address']['country'];
		}
		elseif(isset($this->_owner['address'])) {
			// Or if in the tech info address, it's probably the last entry.
			$a       = $this->_owner['address'];
			$country = array_pop($a);
		}
		elseif(isset($this->_owner[0]['address']) && isset($this->_owner[0]['address']['country'])) {
			// Or if in the tech info address, it's probably the last entry.
			$country = $this->_owner[0]['address']['country'];
		}
		elseif(isset($this->_owner[0]['address'])) {
			// Or if in the tech info address, it's probably the last entry.
			$a       = $this->_owner[0]['address'];
			$country = array_pop($a);
		}
		*/
	}

	/**
	 * Split a single string of 10.10.10.0-10.10.10.255 (for example), into start and CIDR
	 *
	 * @param string $num
	 *
	 * @return array
	 */
	private function splitINetNum($num) {
		if(strpos($num, '-') !== false) {
			// It's in the format of "startip - endip"
			list($start, $end) = array_map('trim', explode('-', $num));
			$startlong = ip2long($start);
			$endlong   = ip2long($end);
			$hosts     = $endlong - $startlong;

			$h    = $hosts + 1;
			$cidr = 33;
			while($cidr > 1 && $h) {
				//var_dump($h, $h >> 1);
				$h = $h >> 1;
				$cidr--;
			}

			return [
				'start' => $start,
				'cidr'  => $cidr,
			];
		}
		elseif(strpos($num, '/') !== false) {
			// It's already a cidr address!
			list($start, $cidr) = explode('/', $num);

			return [
				'start' => $start,
				'cidr'  => $cidr,
			];
		}
		else {
			// Umm.....
			return [
				'start' => $num,
				'cidr'  => 32,
			];
		}
	}

	/**
	 * Fix a nameserver record from [something to something?]
	 *
	 * The resulting array that is returned contains the hostname and IP of the DNS record
	 *
	 * @param string $val
	 *
	 * @return array
	 */
	private function fixNameServer($val) {
		$val   = str_replace(['[', ']', '(', ')'], '', trim($val));
		$val   = str_replace("\t", ' ', $val);
		$parts = explode(' ', $val);
		$host  = '';
		$ip    = '';

		foreach($parts as $p) {
			if(substr($p, -1) == '.') $p = substr($p, 0, -1);

			if((ip2long($p) == -1) or (ip2long($p) === false)) {
				// Hostname ?
				if($host == '' && preg_match('/^[\w\-]+(\.[\w\-]+)+$/', $p)) {
					$host = $p;
				}
			}
			else
				// IP Address
				$ip = $p;
		}

		// Get ip address

		if($ip == '') {
			$ip = gethostbyname($host);
			if($ip == $host) $ip = '(DOES NOT EXIST)';
		}

		if(substr($host, -1, 1) == '.') $host = substr($host, 0, -1);

		return [
			'host' => strtolower($host),
		    'ip'   => $ip
		];
	}
}