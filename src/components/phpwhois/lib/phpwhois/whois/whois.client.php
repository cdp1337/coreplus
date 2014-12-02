<?php
/**
 * @todo Description of whois.client.php
 *
 * @package phpwhois
 *
 * @copyright 1999,2005 easyDNS Technologies Inc. & Mark Jeftovic
 * @author David Saez
 * @link http://www.phpwhois.org Original version of phpwhois
 *
 * @author Dmitry Lukashin <http://lukashin.ru/en/>
 * @link http://phpwhois.pw/ Revisited version of phpwhois
 *
 * @author Charlie Powell
 *
 * @license GPLv2
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace phpwhois\whois;

use phpwhois\WhoisResult;

class WhoisClient {

	// Recursion allowed ?
	public $gtld_recurse = false;

	// Default WHOIS port
	public $PORT = 43;

	// Maximum number of retries on connection failure
	public $RETRY = 0;

	// Time to wait between retries
	public $SLEEP = 2;

	// Read buffer size (0 == char by char)
	public $BUFFER = 1024;

	// Communications timeout
	public $STIMEOUT = 10;

	// Array to contain all query variables
	public $Query = [
		'tld'   => '',
		'type'  => 'domain',
		'query' => '',
		'status',
		'server'
	];

	// This release of the package
	public static $CODE_VERSION = '4.2.2~core1';

	/**
	 * List of servers and handlers
	 * @var array
	 */
	public static $DATA = [
		'agency'   => 'gtld',
		'bz'       => 'gtld',
		'com'      => 'gtld',
		'jobs'     => 'gtld',
		'li'       => 'ch',
		'net'      => 'gtld',
		'su'       => 'ru',
		'tv'       => 'gtld',
		'za.org'   => 'zanet',
		'za.net'   => 'zanet',
			// Punicode
		'xn--p1ai' => 'ru'
	];

	/* Non UTF-8 servers */

	/**
	 * List of servers that do not support UTF8
	 * @var array
	 */
	public static $NON_UTF8 = [
		'br.whois-servers.net'  => 1,
		'ca.whois-servers.net'  => 1,
		'cl.whois-servers.net'  => 1,
		'hu.whois-servers.net'  => 1,
		'is.whois-servers.net'  => 1,
		'pt.whois-servers.net'  => 1,
		'whois.interdomain.net' => 1,
		'whois.lacnic.net'      => 1,
		'whois.nicline.com'     => 1,
		'whois.ripe.net'        => 1
	];

	/**
	 * If whois Server needs any parameters, enter it here
	 * @var array
	 */
	public static $WHOIS_PARAM = [
		'com.whois-servers.net' => 'domain =$',
		'net.whois-servers.net' => 'domain =$',
		'de.whois-servers.net'  => '-T dn,ace $',
		'jp.whois-servers.net'  => 'DOM $/e'
	];

	/**
	 * TLD's that have special whois servers or that can only be reached via HTTP
	 * @var array
	 */
	public static $WHOIS_SPECIAL = [
		'ad'      => '',
		'ae'      => 'whois.aeda.net.ae',
		'af'      => 'whois.nic.af',
		'ai'      => 'http://whois.offshore.ai/cgi-bin/whois.pl?domain-name={domain}.ai',
		'al'      => '',
		'az'      => '',
		'ba'      => '',
		'bb'      => 'http://domains.org.bb/regsearch/getdetails.cfm?DND={domain}.bb',
		'bg'      => 'http://www.register.bg/bg-nic/displaydomain.pl?domain={domain}.bg&search=exist',
		'bh'      => 'whois.nic.bh',
		'bi'      => 'whois.nic.bi',
		'bj'      => 'whois.nic.bj',
		'by'      => '',
		'bz'      => 'whois2.afilias-grs.net',
		'cy'      => '',
		'es'      => '',
		'fj'      => 'whois.usp.ac.fj',
		'fm'      => 'http://www.dot.fm/query_whois.cfm?domain={domain}&tld=fm',
		'jobs'    => 'jobswhois.verisign-grs.com',
		'ke'      => 'kenic.or.ke',
		'la'      => 'whois.centralnic.net',
		'gr'      => '',
		'gs'      => 'http://www.adamsnames.tc/whois/?domain={domain}.gs',
		'gt'      => 'http://www.gt/Inscripcion/whois.php?domain={domain}.gt',
		'me'      => 'whois.meregistry.net',
		'mobi'    => 'whois.dotmobiregistry.net',
		'ms'      => 'http://www.adamsnames.tc/whois/?domain={domain}.ms',
		'mt'      => 'http://www.um.edu.mt/cgi-bin/nic/whois?domain={domain}.mt',
		'nl'      => 'whois.domain-registry.nl',
		'ly'      => 'whois.nic.ly',
		'pe'      => 'kero.rcp.net.pe',
		'pr'      => 'whois.uprr.pr',
		'pro'     => 'whois.registry.pro',
		'sc'      => 'whois2.afilias-grs.net',
		'tc'      => 'http://www.adamsnames.tc/whois/?domain={domain}.tc',
		'tf'      => 'http://www.adamsnames.tc/whois/?domain={domain}.tf',
		've'      => 'whois.nic.ve',
		'vg'      => 'http://www.adamsnames.tc/whois/?domain={domain}.vg',
			// Second level
		'net.au'  => 'whois.aunic.net',
		'ae.com'  => 'whois.centralnic.net',
		'br.com'  => 'whois.centralnic.net',
		'cn.com'  => 'whois.centralnic.net',
		'de.com'  => 'whois.centralnic.net',
		'eu.com'  => 'whois.centralnic.net',
		'hu.com'  => 'whois.centralnic.net',
		'jpn.com' => 'whois.centralnic.net',
		'kr.com'  => 'whois.centralnic.net',
		'gb.com'  => 'whois.centralnic.net',
		'no.com'  => 'whois.centralnic.net',
		'qc.com'  => 'whois.centralnic.net',
		'ru.com'  => 'whois.centralnic.net',
		'sa.com'  => 'whois.centralnic.net',
		'se.com'  => 'whois.centralnic.net',
		'za.com'  => 'whois.centralnic.net',
		'uk.com'  => 'whois.centralnic.net',
		'us.com'  => 'whois.centralnic.net',
		'uy.com'  => 'whois.centralnic.net',
		'gb.net'  => 'whois.centralnic.net',
		'se.net'  => 'whois.centralnic.net',
		'uk.net'  => 'whois.centralnic.net',
		'za.net'  => 'whois.za.net',
		'za.org'  => 'whois.za.net',
		'co.za'   => 'http://co.za/cgi-bin/whois.sh?Domain={domain}.co.za',
		'org.za'  => 'http://www.org.za/cgi-bin/rwhois?domain={domain}.org.za&format=full'
	];

	/**
	 * handled gTLD whois servers
	 * @var array
	 */
	public static $WHOIS_GTLD_HANDLER = [
		'whois.bulkregister.com' => 'enom',
		'whois.dotregistrar.com' => 'dotster',
		'whois.namesdirect.com'  => 'dotster',
		'whois.psi-usa.info'     => 'psiusa',
		'whois.www.tv'           => 'tvcorp',
		'whois.tucows.com'       => 'opensrs',
		'whois.35.com'           => 'onlinenic',
		'whois.nominalia.com'    => 'genericb',
		'whois.encirca.com'      => 'genericb',
		'whois.corenic.net'      => 'genericb'
	];

	/**
	 * Non ICANN TLD's
	 */
	public static $WHOIS_NON_ICANN = [
		'agent'    => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'agente'   => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'america'  => 'http://www.adns.net/whois.php?txtDOMAIN={domain}.{tld}',
		'amor'     => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'amore'    => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'amour'    => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'arte'     => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'artes'    => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'arts'     => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'asta'     => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'auction'  => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'auktion'  => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'boutique' => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'chat'     => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'chiesa'   => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'church'   => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'cia'      => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'ciao'     => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'cie'      => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'club'     => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'clube'    => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'com2'     => 'http://www.adns.net/whois.php?txtDOMAIN={domain}.{tld}',
		'deporte'  => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'ditta'    => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'earth'    => 'http://www.adns.net/whois.php?txtDOMAIN={domain}.{tld}',
		'eglise'   => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'enchere'  => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'escola'   => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'escuela'  => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'esporte'  => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'etc'      => 'http://www.adns.net/whois.php?txtDOMAIN={domain}.{tld}',
		'famiglia' => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'familia'  => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'familie'  => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'family'   => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'free'     => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'hola'     => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'game'     => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'ges'      => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'gmbh'     => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'golf'     => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'gratis'   => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'gratuit'  => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'iglesia'  => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'igreja'   => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'inc'      => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'jeu'      => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'jogo'     => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'juego'    => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'kids'     => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'kirche'   => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'krunst'   => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'law'      => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'legge'    => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'lei'      => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'leilao'   => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'ley'      => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'liebe'    => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'lion'     => 'http://www.adns.net/whois.php?txtDOMAIN={domain}.{tld}',
		'llc'      => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'llp'      => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'loi'      => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'loja'     => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'love'     => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'ltd'      => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'makler'   => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'med'      => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'mp3'      => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'not'      => 'http://www.adns.net/whois.php?txtDOMAIN={domain}.{tld}',
		'online'   => 'http://www.adns.net/whois.php?txtDOMAIN={domain}.{tld}',
		'recht'    => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'reise'    => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'resto'    => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'school'   => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'schule'   => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'scifi'    => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'scuola'   => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'shop'     => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'soc'      => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'spiel'    => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'sport'    => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'subasta'  => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'tec'      => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'tech'     => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'tienda'   => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'travel'   => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'turismo'  => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'usa'      => 'http://www.adns.net/whois.php?txtDOMAIN={domain}.{tld}',
		'verein'   => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'viaje'    => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'viagem'   => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'video'    => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'voyage'   => 'http://www.new.net/search_whois.tp?domain={domain}&tld={tld}',
		'z'        => 'http://www.adns.net/whois.php?txtDOMAIN={domain}.{tld}'
	];


	/*
	 * Constructor function
	 */
	public function __construct() {

	}

	/**
	 * Get the version of this library as a human-friendly string.
	 * @return string
	 */
	public function getVersion(){
		return sprintf("phpWhois v%s", self::$CODE_VERSION);
	}

	/**
	 * Perform lookup of the query and return raw data
	 *
	 * @param string $query
	 *
	 * @return array
	 */
	public function getRawData($query) {

		$this->Query['query'] = $query;

		// clear error description
		if(isset($this->Query['errstr'])) unset($this->Query['errstr']);

		if(!isset($this->Query['server'])) {
			$this->Query['status']   = 'error';
			$this->Query['errstr'][] = 'No server specified';

			return [];
		}

		// Check if protocol is http

		if(substr($this->Query['server'], 0, 7) == 'http://' || substr($this->Query['server'], 0, 8) == 'https://') {
			$output = $this->httpQuery($this->Query['server']);

			if(!$output) {
				$this->Query['status']   = 'error';
				$this->Query['errstr'][] = 'Connect failed to: ' . $this->Query['server'];

				return [];
			}

			$this->Query['args']   = substr(strchr($this->Query['server'], '?'), 1);
			$this->Query['server'] = strtok($this->Query['server'], '?');

			if(substr($this->Query['server'], 0, 7) == 'http://') $this->Query['server_port'] = 80;
			else
				$this->Query['server_port'] = 483;
		}
		else {
			// Get args

			if(strpos($this->Query['server'], '?')) {
				$parts                 = explode('?', $this->Query['server']);
				$this->Query['server'] = trim($parts[0]);
				$query_args            = trim($parts[1]);

				// replace substitution parameters			
				$query_args = str_replace('{query}', $query, $query_args);
				$query_args = str_replace('{version}', $this->getVersion(), $query_args);

				if(strpos($query_args, '{ip}') !== false) {
					$query_args = str_replace('{ip}', phpwhois_getclientip(), $query_args);
				}

				if(strpos($query_args, '{hname}') !== false) {
					$query_args = str_replace('{hname}', gethostbyaddr(phpwhois_getclientip()), $query_args);
				}
			}
			else {
				if(empty($this->Query['args'])) $query_args = $query;
				else
					$query_args = $this->Query['args'];
			}

			$this->Query['args'] = $query_args;

			if(substr($this->Query['server'], 0, 9) == 'rwhois://') {
				$this->Query['server'] = substr($this->Query['server'], 9);
			}

			if(substr($this->Query['server'], 0, 8) == 'whois://') {
				$this->Query['server'] = substr($this->Query['server'], 8);
			}

			// Get port

			if(strpos($this->Query['server'], ':')) {
				$parts                      = explode(':', $this->Query['server']);
				$this->Query['server']      = trim($parts[0]);
				$this->Query['server_port'] = trim($parts[1]);
			}
			else
				$this->Query['server_port'] = $this->PORT;

			// Connect to whois server, or return if failed

			$ptr = $this->Connect();

			if($ptr < 0) {
				$this->Query['status']   = 'error';
				$this->Query['errstr'][] = 'Connect failed to: ' . $this->Query['server'];

				return [];
			}

			stream_set_timeout($ptr, $this->STIMEOUT);
			stream_set_blocking($ptr, 0);

			// Send query
			fputs($ptr, trim($query_args) . "\r\n");

			// Prepare to receive result
			$raw   = '';
			$start = time();
			$null  = null;
			$r     = [$ptr];

			while(!feof($ptr)) {
				if(!empty($r)) {
					if(stream_select($r, $null, $null, $this->STIMEOUT) !== false) {
						$raw .= fgets($ptr, $this->BUFFER);
					}
				}

				if(time() - $start > $this->STIMEOUT) {
					$this->Query['status']   = 'error';
					$this->Query['errstr'][] = 'Timeout reading from ' . $this->Query['server'];

					return [];
				}
			}

			if(array_key_exists($this->Query['server'], self::$NON_UTF8)) {
				$raw = utf8_encode($raw);
			}

			$output = explode("\n", $raw);

			// Drop empty last line (if it's empty! - saleck)
			if(empty($output[ count($output) - 1 ])) unset($output[ count($output) - 1 ]);
		}

		return $output;
	}

	/*
	 * Perform lookup. Returns an array. The 'rawdata' element contains an
	 * array of lines gathered from the whois query. If a top level domain
	 * handler class was found for the domain, other elements will have been
	 * populated too.
	 */
	function getData($query = '', $deep_whois = true) {

		// If domain to query passed in, use it, otherwise use domain from initialisation
		$query = !empty($query) ? $query : $this->Query['query'];

		$output = $this->getRawData($query);

		// Create result and set 'rawdata'
		$result = ['rawdata' => $output];
		$result = $this->set_whois_info($result);

		// Return now on error
		if(empty($output)) return $result;

		// If we have a handler, post-process it with it
		if(isset($this->Query['handler'])) {
			// Keep server list
			$servers = $result['regyinfo']['servers'];
			unset($result['regyinfo']['servers']);

			// Process data
			$result = $this->Process($result, $deep_whois);

			// Add new servers to the server list
			if(isset($result['regyinfo']['servers'])) $result['regyinfo']['servers'] =
				array_merge($servers, $result['regyinfo']['servers']);
			else
				$result['regyinfo']['servers'] = $servers;

			// Handler may forget to set rawdata
			if(!isset($result['rawdata'])) $result['rawdata'] = $output;
		}

		// Type defaults to domain
		if(!isset($result['regyinfo']['type'])) $result['regyinfo']['type'] = 'domain';

		// Add error information if any
		if(isset($this->Query['errstr'])) $result['errstr'] = $this->Query['errstr'];

		// Fix/add nameserver information
		if(method_exists($this, 'FixResult') && $this->Query['tld'] != 'ip') $this->FixResult($result, $query);

		$result = new WhoisResult($result, $query);

		return $result;
	}

	/*
	*   Adds whois server query information to result
	*/

	function set_whois_info($result) {
		$info = [
			'server' => $this->Query['server'],
		];

		if(!empty($this->Query['args'])) $info['args'] = $this->Query['args'];
		else
			$info['args'] = $this->Query['query'];

		if(!empty($this->Query['server_port'])) $info['port'] = $this->Query['server_port'];
		else
			$info['port'] = 43;

		if(isset($result['regyinfo']['whois'])) unset($result['regyinfo']['whois']);

		if(isset($result['regyinfo']['rwhois'])) unset($result['regyinfo']['rwhois']);

		$result['regyinfo']['servers'][] = $info;

		return $result;
	}

	/*
	*   Convert html output to plain text
	*/
	function httpQuery($query) {

		//echo ini_get('allow_url_fopen');

		//if (ini_get('allow_url_fopen'))
		$lines = @file($this->Query['server']);

		if(!$lines) return false;

		$output = '';
		$pre    = '';

		while(list($key, $val) = each($lines)) {
			$val = trim($val);

			$pos = strpos(strtoupper($val), '<PRE>');
			if($pos !== false) {
				$pre = "\n";
				$output .= substr($val, 0, $pos) . "\n";
				$val = substr($val, $pos + 5);
			}
			$pos = strpos(strtoupper($val), '</PRE>');
			if($pos !== false) {
				$pre = '';
				$output .= substr($val, 0, $pos) . "\n";
				$val = substr($val, $pos + 6);
			}
			$output .= $val . $pre;
		}

		$search = [
			'<BR>',
			'<P>',
			'</TITLE>',
			'</H1>',
			'</H2>',
			'</H3>',
			'<br>',
			'<p>',
			'</title>',
			'</h1>',
			'</h2>',
			'</h3>'
		];

		$output = str_replace($search, "\n", $output);
		$output = str_replace('<TD', ' <td', $output);
		$output = str_replace('<td', ' <td', $output);
		$output = str_replace('<tr', "\n<tr", $output);
		$output = str_replace('<TR', "\n<tr", $output);
		$output = str_replace('&nbsp;', ' ', $output);
		$output = strip_tags($output);
		$output = explode("\n", $output);

		$rawdata = [];
		$null    = 0;

		while(list($key, $val) = each($output)) {
			$val = trim($val);
			if($val == '') {
				if(++$null > 2) continue;
			}
			else $null = 0;
			$rawdata[] = $val;
		}

		return $rawdata;
	}

	/*
	 * Open a socket to the whois server.
	 *
	 * Returns a socket connection pointer on success, or -1 on failure.
	 */
	function Connect($server = '') {

		if($server == '') $server = $this->Query['server'];

		// Fail if server not set
		if($server == '') return (-1);

		// Get rid of protocol and/or get port
		$port = $this->Query['server_port'];

		$pos = strpos($server, '://');

		if($pos !== false) $server = substr($server, $pos + 3);

		$pos = strpos($server, ':');

		if($pos !== false) {
			$port   = substr($server, $pos + 1);
			$server = substr($server, 0, $pos);
		}

		// Enter connection attempt loop
		$retry = 0;

		while($retry <= $this->RETRY) {
			// Set query status
			$this->Query['status'] = 'ready';

			// Connect to whois port
			$ptr = @fsockopen($server, $port, $errno, $errstr, $this->STIMEOUT);

			if($ptr > 0) {
				$this->Query['status'] = 'ok';

				return ($ptr);
			}

			// Failed this attempt
			$this->Query['status']  = 'error';
			$this->Query['error'][] = $errstr;
			$retry++;

			// Sleep before retrying
			sleep($this->SLEEP);
		}

		// If we get this far, it hasn't worked
		return (-1);
	}

	/*
	 * Post-process result with handler class. On success, returns the result
	 * from the handler. On failure, returns passed result unaltered.
	 */
	function Process(&$result, $deep_whois = true) {

		$handler_name = str_replace('.', '_', $this->Query['handler']);

		// If the handler has not already been included somehow, include it now
		//$HANDLER_FLAG = sprintf("__%s_HANDLER__", strtoupper($handler_name));

		//if(!defined($HANDLER_FLAG)) include($this->Query['file']);

		// If the handler has still not been included, append to query errors list and return
		//if(!defined($HANDLER_FLAG)) {
		//	$this->Query['errstr'][] = "Can't find $handler_name handler: " . $this->Query['file'];
		//
		//	return ($result);
		//}

		if(!$this->gtld_recurse && $this->Query['file'] == 'whois.gtld.php') return $result;

		// Pass result to handler
		$object = $handler_name . '_handler';

		if(isset($this->Query['type']) && $this->Query['type']){
			$ref = new \ReflectionClass('\\phpwhois\\whois\\' . $this->Query['type'] . '\\' . $object);
		}
		else{
			$ref = new \ReflectionClass('\\phpwhois\\whois\\' . $object);
		}


		if(!class_exists($ref->getName())){
			require_once($this->Query['file']);
		}

		$handler = $ref->newInstance();

		// If handler returned an error, append it to the query errors list
		if(isset($handler->Query['errstr'])) $this->Query['errstr'][] = $handler->Query['errstr'];

		$handler->deep_whois = $deep_whois;

		// Process
		$res = $handler->parse($result, $this->Query['query']);

		// Return the result
		return $res;
	}

	/*
	 * Does more (deeper) whois ...
	 */

	function DeepWhois($query, $result) {

		if(!isset($result['regyinfo']['whois'])) return $result;

		$this->Query['server'] = $wserver = $result['regyinfo']['whois'];
		unset($result['regyinfo']['whois']);
		$subresult = $this->getRawData($query);

		if(!empty($subresult)) {
			$result            = $this->set_whois_info($result);
			$result['rawdata'] = $subresult;

			if(isset(self::$WHOIS_GTLD_HANDLER[ $wserver ])){
				$this->Query['handler'] = self::$WHOIS_GTLD_HANDLER[ $wserver ];
				$this->Query['type'] = 'gtld';
			}
			else {
				$parts = explode('.', $wserver);
				$hname = strtolower($parts[1]);

				if(file_exists(__DIR__ . '/gtld/whois.gtld.' . $hname . '.php') && is_readable(__DIR__ . '/gtld/whois.gtld.' . $hname . '.php')){
					$this->Query['handler'] = $hname;
					$this->Query['type'] = 'gtld';
				}
				//if(($fp = @fopen('whois.gtld.' . $hname . '.php', 'r', 1)) && fclose($fp)){
				//	$this->Query['handler'] = $hname;
				//}
			}

			if(!empty($this->Query['handler'])) {
				$this->Query['file'] = 'gtld/' . sprintf('whois.gtld.%s.php', $this->Query['handler']);
				$regrinfo            = $this->Process($subresult); //$result['rawdata']);
				$result['regrinfo']  = $this->merge_results($result['regrinfo'], $regrinfo);
				//$result['rawdata'] = $subresult;
			}
		}

		return $result;
	}

	/*
	 *  Merge results
	 */

	function merge_results($a1, $a2) {

		reset($a2);

		while(list($key, $val) = each($a2)) {
			if(isset($a1[ $key ])) {
				if(is_array($val)) {
					if($key != 'nserver') $a1[ $key ] = $this->merge_results($a1[ $key ], $val);
				}
				else {
					$val = trim($val);
					if($val != '') $a1[ $key ] = $val;
				}
			}
			else
				$a1[ $key ] = $val;
		}

		return $a1;
	}

	function FixNameServer($nserver) {
		$dns = [];

		foreach($nserver as $val) {
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

			// Valid host name ?

			if($host == '') continue;

			// Get ip address

			if($ip == '') {
				$ip = gethostbyname($host);
				if($ip == $host) $ip = '(DOES NOT EXIST)';
			}

			if(substr($host, -1, 1) == '.') $host = substr($host, 0, -1);

			$dns[ strtolower($host) ] = $ip;
		}

		return $dns;
	}
}
