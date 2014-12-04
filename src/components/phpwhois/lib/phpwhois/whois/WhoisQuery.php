<?php
/**
 * File for class WhoisQuery definition in the phpwhois project
 * 
 * @package phpwhois\whois
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20141202.1524
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

namespace phpwhois\whois;
use phpwhois\idna_convert;


/**
 * A short teaser of what WhoisQuery does.
 *
 * More lengthy description of what WhoisQuery does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for WhoisQuery
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
 * @package phpwhois\whois
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class WhoisQuery {
	// Deep whois ?
	public $deep_whois = true;

	// Windows based ?
	public $windows = false;

	// Recursion allowed ?
	public $gtld_recurse = true;

	// Support for non-ICANN tld's
	public $non_icann = false;

	public $status = '';

	/** @var string Server to perform the lookup with */
	public $server = '';

	/** @var string The actual query to perform */
	public $query = '';

	/** @var string Any arguments to send to the server */
	public $args = '';
	/** @var string Filename containing the handler to parse the result with */
	public $file = '';
	/** @var string Name of the handler to parse the result with */
	public $handler = '';
	/** @var string Type of lookup, one of "domain", "gtld", or "ip" */
	public $type = '';

	public $host_ip = '';

	public $host_name = '';
	/** @var string Top Level Domain of the lookup */
	public $tld = '';

	// Default WHOIS port
	public $port = 43;

	// Maximum number of retries on connection failure
	public $retry = 0;

	// Time to wait between retries
	public $sleep = 2;

	// Read buffer size (0 == char by char)
	public $buffer = 1024;

	// Communications timeout
	public $timeout = 10;

	public $errstr = [];

	// This release of the package
	public $CODE_VERSION = '5.0.0';

	/**
	 * List of servers and handlers
	 * @var array
	 */
	public $DATA = [
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
	public $NON_UTF8 = [
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
	public $WHOIS_PARAM = [
		'com.whois-servers.net' => 'domain =$',
		'net.whois-servers.net' => 'domain =$',
		'de.whois-servers.net'  => '-T dn,ace $',
		'jp.whois-servers.net'  => 'DOM $/e'
	];

	/**
	 * TLD's that have special whois servers or that can only be reached via HTTP
	 * @var array
	 */
	public $WHOIS_SPECIAL = [
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
	public $WHOIS_GTLD_HANDLER = [
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
	public $WHOIS_NON_ICANN = [
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

	// Network Solutions registry server
	public static $NSI_REGISTRY = 'whois.nsiregistry.net';

	/**
	 * Constructor function
	 */
	public function __construct() {
		$this->windows = (substr(php_uname(), 0, 7) == 'Windows');
	}

	/**
	 * Use special whois server
	 *
	 * @param string $tld    TLD of domain to override for this query
	 * @param string $server Server hostname or IP for this TLD
	 */
	public function useServer($tld, $server) {
		$this->WHOIS_SPECIAL[ $tld ] = $server;
	}

	/**
	 * Lookup query
	 *
	 * @param string $query  IP or hostname to lookup
	 * @param bool   $is_utf Require UTF-8
	 *
	 * @return array
	 */

	public function lookup($query = '', $is_utf = true) {
		// start clean
		$this->reset();

		$query = trim($query);

		$IDN = new idna_convert();

		if($is_utf) {
			$query = $IDN->encode($query);
		}
		else {
			$query = $IDN->encode(utf8_encode($query));
		}

		// If domain to query was not set
		if (!isset($query) || $query == '') {
			// Configure to use default whois server
			$this->server = self::$NSI_REGISTRY;
			return [];
		}

		// If the query is an IP range, just drop off the network identifier.
		// The whois agent should still be able to lookup the network base.
		if(preg_match('/^[0-9\.]*\/[0-9]+$/', $query)){
			$query = substr($query, 0, strpos($query, '/'));
		}

		// Set domain to query in query array

		$this->query = $domain = strtolower($query);

		// If query is an ip address do ip lookup

		if($query == long2ip(ip2long($query))) {
			// IPv4 Prepare to do lookup via the 'ip' handler
			$ip = gethostbyname($query);

			if(isset($this->WHOIS_SPECIAL['ip'])) {
				$this->server = $this->WHOIS_SPECIAL['ip'];
				$this->args   = $ip;
			}
			else {
				$this->server  = 'whois.arin.net';
				$this->args    = "n $ip";
				$this->file    = 'whois.ip.php';
				$this->handler = 'ip';
			}
			$this->host_ip   = $ip;
			$this->query     = $ip;
			$this->tld       = 'ip';
			$this->host_name = gethostbyaddr($ip);

			// And return.
			return $this->getData('', $this->deep_whois);
		}

		if(strpos($query, ':')) {
			// IPv6 AS Prepare to do lookup via the 'ip' handler
			$ip = gethostbyname($query);

			if(isset($this->WHOIS_SPECIAL['ip'])) {
				$this->server = $this->WHOIS_SPECIAL['ip'];
			}
			else {
				$this->server  = 'whois.ripe.net';
				$this->file    = 'whois.ip.ripe.php';
				$this->handler = 'ripe';
			}
			$this->query = $ip;
			$this->tld   = 'ip';

			return $this->getData('', $this->deep_whois);
		}

		if(!strpos($query, '.')) {
			// AS Prepare to do lookup via the 'ip' handler
			$ip =  gethostbyname($query);
			if(strtolower(substr($ip, 0, 2)) == 'as') {
				$as = substr($ip, 2);
			}
			else {
				$as = $ip;
			}

			$this->server  = 'whois.arin.net';
			$this->args    = "a $as";
			$this->file    = 'whois.ip.php';
			$this->handler = 'ip';
			$this->query   = $ip;
			$this->tld     = 'as';

			return $this->getData('', $this->deep_whois);
		}

		// Build array of all possible tld's for that domain

		$tld      = '';
		$server   = '';
		$dp       = explode('.', $domain);
		$np       = count($dp) - 1;
		$tldtests = [];

		for($i = 0; $i < $np; $i++) {
			array_shift($dp);
			$tldtests[] = implode('.', $dp);
		}

		// Search the correct whois server

		if($this->non_icann){
			$special_tlds = array_merge($this->WHOIS_SPECIAL, $this->WHOIS_NON_ICANN);
		}
		else{
			$special_tlds = $this->WHOIS_SPECIAL;
		}

		foreach($tldtests as $tld) {
			// Test if we know in advance that no whois server is
			// available for this domain and that we can get the
			// data via http or whois request

			if(isset($special_tlds[ $tld ])) {
				$val = $special_tlds[ $tld ];

				if($val == '') return $this->Unknown();

				$domain = substr($query, 0, -strlen($tld) - 1);
				$val    = str_replace('{domain}', $domain, $val);
				$server = str_replace('{tld}', $tld, $val);
				break;
			}
		}

		if($server == ''){
			foreach($tldtests as $tld) {
				// Determine the top level domain, and it's whois server using
				// DNS lookups on 'whois-servers.net'.
				// Assumes a valid DNS response indicates a recognised tld (!?)

				$cname = $tld . '.whois-servers.net';

				if(gethostbyname($cname) == $cname) continue;
				$server = $tld . '.whois-servers.net';
				break;
			}
		}

		if($tld && $server) {
			// If found, set tld and whois server in query array
			$this->server = $server;
			$this->tld    = $tld;
			$handler      = '';

			foreach($tldtests as $htld) {
				// special handler exists for the tld ?

				if(isset($this->DATA[ $htld ])) {
					$handler = $this->DATA[ $htld ];
					break;
				}

				// Regular handler exists for the tld ?
				if(file_exists(__DIR__ . '/domain/whois.' . $htld . '.php') && is_readable(__DIR__ . '/domain/whois.' . $htld . '.php')){
					$this->handler = $htld;
					$this->type    = 'domain';
					$this->file    = __DIR__ . "/domain/whois.$htld.php";
					break;
				}
				//if(($fp = @fopen('whois.' . $htld . '.php', 'r', 1)) and fclose($fp)) {
				//	$handler = $htld;
				//	break;
				//}
			}

			// If there is a handler set it

			if($handler != '') {
				$this->file    = "whois.$handler.php";
				$this->handler = $handler;
			}

			// Special parameters ?

			if(isset($this->WHOIS_PARAM[ $server ])){
				$this->server .= '?' . str_replace('$', $domain, $this->WHOIS_PARAM[ $server ]);
			}

			$result = $this->getData('', $this->deep_whois);
			$this->_checkdns($result);

			return $result;
		}

		// If tld not known, and domain not in DNS, return error
		return $this->Unknown();
	}




	/**
	 * Get the version of this library as a human-friendly string.
	 * @return string
	 */
	public function getVersion(){
		return sprintf("phpWhois v%s", $this->CODE_VERSION);
	}

	/**
	 * Perform lookup of the query and return raw data
	 *
	 * @param string $query
	 *
	 * @return array
	 */
	public function getRawData($query) {

		$this->query = $query;

		// clear error description
		if(isset($this->errstr)) unset($this->errstr);

		if(!isset($this->server)) {
			$this->status   = 'error';
			$this->errstr[] = 'No server specified';

			return [];
		}

		// Check if protocol is http

		if(substr($this->server, 0, 7) == 'http://' || substr($this->server, 0, 8) == 'https://') {
			$output = $this->_httpQuery($this->server);

			if(!$output) {
				$this->status   = 'error';
				$this->errstr[] = 'Connect failed to: ' . $this->server;

				return [];
			}

			$this->args   = substr(strchr($this->server, '?'), 1);
			$this->server = strtok($this->server, '?');

			if(substr($this->server, 0, 7) == 'http://'){
				$this->port = 80;
			}
			else{
				$this->port = 483;
			}
		}
		else {
			// Get args

			if(strpos($this->server, '?')) {
				$parts                 = explode('?', $this->server);
				$this->server = trim($parts[0]);
				$query_args            = trim($parts[1]);

				// replace substitution parameters
				$query_args = str_replace('{query}', $query, $query_args);
				$query_args = str_replace('{version}', $this->getVersion(), $query_args);

				if(strpos($query_args, '{ip}') !== false) {
					$query_args = str_replace('{ip}', self::_GetClientIP(), $query_args);
				}

				if(strpos($query_args, '{hname}') !== false) {
					$query_args = str_replace('{hname}', gethostbyaddr(self::_GetClientIP()), $query_args);
				}
			}
			else {
				if(empty($this->args)){
					$query_args = $query;
				}
				else{
					$query_args = $this->args;
				}
			}

			$this->args = $query_args;

			if(substr($this->server, 0, 9) == 'rwhois://') {
				$this->server = substr($this->server, 9);
			}

			if(substr($this->server, 0, 8) == 'whois://') {
				$this->server = substr($this->server, 8);
			}

			// Get port

			if(strpos($this->server, ':')) {
				$parts                      = explode(':', $this->server);
				$this->server      = trim($parts[0]);
				$this->port = trim($parts[1]);
			}

			// Connect to whois server, or return if failed

			$ptr = $this->_connect();

			if($ptr < 0) {
				$this->status   = 'error';
				$this->errstr[] = 'Connect failed to: ' . $this->server;

				return [];
			}

			stream_set_timeout($ptr, $this->timeout);
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
					if(stream_select($r, $null, $null, $this->timeout) !== false) {
						$raw .= fgets($ptr, $this->buffer);
					}
				}

				if(time() - $start > $this->timeout) {
					$this->status   = 'error';
					$this->errstr[] = 'Timeout reading from ' . $this->server;

					return [];
				}
			}

			if(array_key_exists($this->server, $this->NON_UTF8)) {
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
		$query = !empty($query) ? $query : $this->query;

		$output = $this->getRawData($query);

		// Create result and set 'rawdata'
		$result = ['rawdata' => $output];
		$result = $this->set_whois_info($result);

		// Return now on error
		if(empty($output)) return $result;

		// If we have a handler, post-process it with it
		if(isset($this->handler)) {
			// Keep server list
			$servers = $result['regyinfo']['servers'];
			unset($result['regyinfo']['servers']);

			// Process data
			$result = $this->_process($result, $deep_whois);

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
		if(isset($this->errstr)) $result['errstr'] = $this->errstr;

		// Fix/add nameserver information
		if(method_exists($this, 'FixResult') && $this->tld != 'ip') $this->FixResult($result, $query);

		return $result;
	}

	/*
	*   Adds whois server query information to result
	*/
	function set_whois_info($result) {
		$info = [
			'server' => $this->server,
		    'args'   => (!empty($this->args)) ? $this->args : $this->query,
		    'port'   => $this->port,
		];

		if(isset($result['regyinfo']['whois'])) unset($result['regyinfo']['whois']);

		if(isset($result['regyinfo']['rwhois'])) unset($result['regyinfo']['rwhois']);

		$result['regyinfo']['servers'][] = $info;

		return $result;
	}

	/**
	 * Get nameservers if missing
	 */
	private function _checkdns(&$result) {
		if ($this->deep_whois && empty($result['regrinfo']['domain']['nserver']) && function_exists('dns_get_record')) {
			$ns = dns_get_record($this->query, \DNS_NS);
			if (!is_array($ns)) return;
			$nserver = [];
			foreach($ns as $row){
				$nserver[] = $row['target'];
			}

			if (count($nserver) > 0){
				$result['regrinfo']['domain']['nserver'] = $this->_fixNameServer($nserver);
			}
		}
	}

	/*
	*   Convert html output to plain text
	*/
	private function _httpQuery() {

		$lines = file($this->server);

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
	private function _connect($server = '') {

		if($server == '') $server = $this->server;

		// Fail if server not set
		if($server == '') return (-1);

		// Get rid of protocol and/or get port
		$port = $this->port;

		$pos = strpos($server, '://');

		if($pos !== false) $server = substr($server, $pos + 3);

		$pos = strpos($server, ':');

		if($pos !== false) {
			$port   = substr($server, $pos + 1);
			$server = substr($server, 0, $pos);
		}

		// Enter connection attempt loop
		$retry = 0;

		while($retry <= $this->retry) {
			// Set query status
			$this->status = 'ready';

			// Connect to whois port
			$ptr = fsockopen($server, $port, $errno, $errstr, $this->timeout);

			if($ptr > 0) {
				$this->status = 'ok';

				return ($ptr);
			}

			// Failed this attempt
			$this->status  = 'error';
			$this->errstr[] = $errstr;
			$retry++;

			// Sleep before retrying
			sleep($this->sleep);
		}

		// If we get this far, it hasn't worked
		return (-1);
	}

	/*
	 * Post-process result with handler class. On success, returns the result
	 * from the handler. On failure, returns passed result unaltered.
	 */
	protected function _process(&$result, $deep_whois = true) {

		$handler_name = str_replace('.', '_', $this->handler);

		if(!$this->gtld_recurse && $this->file == 'whois.gtld.php') return $result;

		// Pass result to handler
		$object = $handler_name . '_handler';
		$class  = '\\phpwhois\\whois\\' . ((isset($this->type) && $this->type) ?  $this->type . '\\' : '') . $object;

		if(!class_exists($class)){
			require_once($this->file);
		}
		$ref = new \ReflectionClass($class);
		$handler = $ref->newInstance();

		// If handler returned an error, append it to the query errors list
		if(isset($handler->errstr)) $this->errstr[] = $handler->errstr;

		$handler->deep_whois = $deep_whois;

		// Process
		$res = $handler->parse($result, $this->query);

		// Return the result
		return $res;
	}

	/**
	 * Perform a deeper whois ...
	 */
	protected function _deepWhois($query, $result) {

		if(!isset($result['regyinfo']['whois'])) return $result;

		$this->server = $wserver = $result['regyinfo']['whois'];
		unset($result['regyinfo']['whois']);
		$subresult = $this->getRawData($query);

		if(!empty($subresult)) {
			$result            = $this->set_whois_info($result);
			$result['rawdata'] = $subresult;

			if(isset($this->WHOIS_GTLD_HANDLER[ $wserver ])){
				$this->handler = $this->WHOIS_GTLD_HANDLER[ $wserver ];
				$this->type = 'gtld';
			}
			else {
				$parts = explode('.', $wserver);
				$hname = strtolower($parts[1]);

				if(file_exists(__DIR__ . '/gtld/whois.gtld.' . $hname . '.php') && is_readable(__DIR__ . '/gtld/whois.gtld.' . $hname . '.php')){
					$this->handler = $hname;
					$this->type = 'gtld';
				}
				//if(($fp = @fopen('whois.gtld.' . $hname . '.php', 'r', 1)) && fclose($fp)){
				//	$this->handler = $hname;
				//}
			}

			if(!empty($this->handler)) {
				$this->file = 'gtld/' . sprintf('whois.gtld.%s.php', $this->handler);
				$regrinfo            = $this->_process($subresult); //$result['rawdata']);
				$result['regrinfo']  = $this->merge_results($result['regrinfo'], $regrinfo);
				//$result['rawdata'] = $subresult;
			}
		}

		return $result;
	}

	/**
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

	private function _fixNameServer($nserver) {
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

	/* Unsupported domains */

	function Unknown() {
		unset($this->server);
		$this->status                          = 'error';
		$result['rawdata'][] = $this->errstr[] = $this->query . ' domain is not supported';
		$this->FixResult($result, $this->query);

		return $result;
	}

	/*
	 *  Fix and/or add name server information
	 */

	function FixResult(&$result, $domain) {
		// Add usual fields
		$result['regrinfo']['domain']['name'] = $domain;

		// Check if nameservers exist

		if(!isset($result['regrinfo']['registered'])) {
			if(function_exists('checkdnsrr') && checkdnsrr($domain, 'NS')) $result['regrinfo']['registered'] = 'yes';
			else
				$result['regrinfo']['registered'] = 'unknown';
		}

		// Normalize nameserver fields

		if(isset($result['regrinfo']['domain']['nserver'])) {
			if(!is_array($result['regrinfo']['domain']['nserver'])) {
				unset($result['regrinfo']['domain']['nserver']);
			}
			else{
				$result['regrinfo']['domain']['nserver'] = $this->_fixNameServer($result['regrinfo']['domain']['nserver']);
			}
		}
	}

	public function reset(){
		$this->status = '';
		$this->errstr = [];
	}

	private static function _GetClientIP() {
		if(!empty($_SERVER['HTTP_CLIENT_IP']) && self::_ValidIP($_SERVER['HTTP_CLIENT_IP']) ){
			return $_SERVER['HTTP_CLIENT_IP'];
		}

		if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
			foreach(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']) as $ip) {
				if(self::_ValidIP(trim($ip))) return $ip;
			}
		}

		if(!empty($_SERVER['HTTP_X_FORWARDED']) && self::_ValidIP($_SERVER['HTTP_X_FORWARDED']) ){
			return $_SERVER['HTTP_X_FORWARDED'];
		}

		if(!empty($_SERVER['HTTP_FORWARDED_FOR']) && self::_ValidIP($_SERVER['HTTP_FORWARDED_FOR']) ){
			return $_SERVER['HTTP_FORWARDED_FOR'];
		}

		if(!empty($_SERVER['HTTP_FORWARDED']) && self::_ValidIP($_SERVER['HTTP_FORWARDED']) ){
			return $_SERVER['HTTP_FORWARDED'];
		}

		if(!empty($_SERVER['HTTP_X_FORWARDED']) && self::_ValidIP($_SERVER['HTTP_X_FORWARDED']) ){
			return $_SERVER['HTTP_X_FORWARDED'];
		}

		return $_SERVER['REMOTE_ADDR'];
	}

	private static function _ValidIP($ip) {

		if(empty($ip)) return false;

		if((ip2long($ip) == -1) or (ip2long($ip) === false)) return false;

		$reserved_ips = [
			['0.0.0.0', '2.255.255.255'],
			['10.0.0.0', '10.255.255.255'],
			['127.0.0.0', '127.255.255.255'],
			['169.254.0.0', '169.254.255.255'],
			['172.16.0.0', '172.31.255.255'],
			['192.0.2.0', '192.0.2.255'],
			['192.168.0.0', '192.168.255.255'],
			['255.255.255.0', '255.255.255.255']
		];

		foreach($reserved_ips as $r) {
			$min = ip2long($r[0]);
			$max = ip2long($r[1]);
			if((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
		}

		return true;
	}
} 