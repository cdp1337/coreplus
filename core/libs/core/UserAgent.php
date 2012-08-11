<?php
/**
 * User Agent object, directly based off of the UASparser object from http://user-agent-string.info
 *
 * This was retrieved from version 0.51, but heavily modified to work better with Core Plus.
 *
 * @package    UASparser
 * @author     Jaroslav Mallat (http://mallat.cz/)
 * @copyright  Copyright (c) 2008 Jaroslav Mallat
 * @copyright  Copyright (c) 2010 Alex Stanev (http://stanev.org)
 * @copyright  Copyright (c) 2012 Martin van Wingerden (http://www.copernica.com)
 * @version    0.51~core1
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link       http://user-agent-string.info/download/UASparser
 */

class UserAgent {
	private static $updateInterval =   604800; // 1 week

	private static $_ini_url    =   'http://user-agent-string.info/rpc/get_data.php?key=free&format=ini';
	private static $_ver_url    =   'http://user-agent-string.info/rpc/get_data.php?key=free&format=ini&ver=y';
	private static $_md5_url    =   'http://user-agent-string.info/rpc/get_data.php?format=ini&md5=y';
	private static $_info_url   =   'http://user-agent-string.info';

	/**
	 * Array of user agent cache.  This is useful for checking MANY useragents, with may have duplicates.
	 * @var array
	 */
	private static $_Cache = array();

	// initialize the return value
	public $type             = 'unknown';
	public $ua_family        = 'unknown';
	public $ua_name          = 'unknown';
	public $ua_version       = 'unknown';
	public $ua_url           = 'unknown';
	public $ua_company       = 'unknown';
	public $ua_company_url   = 'unknown';
	public $ua_icon          = 'unknown.png';
	public $ua_info_url      = 'unknown';
	public $os_family        = 'unknown';
	public $os_name          = 'unknown';
	public $os_url           = 'unknown';
	public $os_company       = 'unknown';
	public $os_company_url   = 'unknown';
	public $os_icon          = 'unknown.png';

	/**
	 * Constructor with an user agent string.
	 *
	 * If no string is provided, the current user's string is used.
	 *
	 * @param string $useragent
	 */
	public function __construct($useragent = null) {
		if($useragent === null) $useragent = $_SERVER['HTTP_USER_AGENT'];

		// if we haven't loaded the data yet, do it now
		$_data = $this->_loadData();

		// we have no data or no valid user agent, just return the default data
		if(!$_data || !isset($useragent)) {
			return;
		}

		if(isset(self::$_Cache[$useragent])){
			$this->fromArray(self::$_Cache[$useragent]);
			return;
		}

		$os_id = false;
		$browser_id = false;

		// crawler
		foreach ($_data['robots'] as $test) {
			if ($test[0] == $useragent) {
				$this->type                            = 'Robot';
				if ($test[1]) $this->ua_family        = $test[1];
				if ($test[2]) $this->ua_name          = $test[2];
				if ($test[3]) $this->ua_url           = $test[3];
				if ($test[4]) $this->ua_company       = $test[4];
				if ($test[5]) $this->ua_company_url   = $test[5];
				if ($test[6]) $this->ua_icon          = $test[6];
				if ($test[7]) { // OS set
					$os_data = $_data['os'][$test[7]];
					if ($os_data[0]) $this->os_family       =   $os_data[0];
					if ($os_data[1]) $this->os_name         =   $os_data[1];
					if ($os_data[2]) $this->os_url          =   $os_data[2];
					if ($os_data[3]) $this->os_company      =   $os_data[3];
					if ($os_data[4]) $this->os_company_url  =   $os_data[4];
					if ($os_data[5]) $this->os_icon         =   $os_data[5];
				}
				if ($test[8]) $this->ua_info_url      = self::$_info_url.$test[8];
				self::$_Cache[$useragent] = $this->asArray();
				return;
			}
		}

		// find a browser based on the regex
		foreach ($_data['browser_reg'] as $test) {
			if (@preg_match($test[0],$useragent,$info)) { // $info may contain version
				$browser_id = $test[1];
				break;
			}
		}

		// a valid browser was found
		if ($browser_id) { // browser detail
			$browser_data = $_data['browser'][$browser_id];
			if ($_data['browser_type'][$browser_data[0]][0]) $this->type    = $_data['browser_type'][$browser_data[0]][0];
			if (isset($info[1]))    $this->ua_version     = $info[1];
			if ($browser_data[1])   $this->ua_family      = $browser_data[1];
			if ($browser_data[1])   $this->ua_name        = $browser_data[1].(isset($info[1]) ? ' '.$info[1] : '');
			if ($browser_data[2])   $this->ua_url         = $browser_data[2];
			if ($browser_data[3])   $this->ua_company     = $browser_data[3];
			if ($browser_data[4])   $this->ua_company_url = $browser_data[4];
			if ($browser_data[5])   $this->ua_icon        = $browser_data[5];
			if ($browser_data[6])   $this->ua_info_url    = self::$_info_url.$browser_data[6];
		}

		// browser OS, does this browser match contain a reference to an os?
		if (isset($_data['browser_os'][$browser_id])) { // os detail
			$os_id = $_data['browser_os'][$browser_id][0]; // Get the os id
			$os_data = $_data['os'][$os_id];
			if ($os_data[0])    $this->os_family      = $os_data[0];
			if ($os_data[1])    $this->os_name        = $os_data[1];
			if ($os_data[2])    $this->os_url         = $os_data[2];
			if ($os_data[3])    $this->os_company     = $os_data[3];
			if ($os_data[4])    $this->os_company_url = $os_data[4];
			if ($os_data[5])    $this->os_icon        = $os_data[5];

			self::$_Cache[$useragent] = $this->asArray();
			return;
		}

		// search for the os
		foreach ($_data['os_reg'] as $test) {
			if (@preg_match($test[0],$useragent)) {
				$os_id = $test[1];
				break;
			}
		}

		// a valid os was found
		if ($os_id) { // os detail
			$os_data = $_data['os'][$os_id];
			if ($os_data[0]) $this->os_family       = $os_data[0];
			if ($os_data[1]) $this->os_name         = $os_data[1];
			if ($os_data[2]) $this->os_url          = $os_data[2];
			if ($os_data[3]) $this->os_company      = $os_data[3];
			if ($os_data[4]) $this->os_company_url  = $os_data[4];
			if ($os_data[5]) $this->os_icon         = $os_data[5];
		}
	}

	/**
	 * Get this user agent as an associative array.
	 */
	public function asArray(){
		return array(
			'type' => $this->type,
			'ua_family'      =>	$this->ua_family,
			'ua_name'        =>	$this->ua_name,
			'ua_version'     =>	$this->ua_version,
			'ua_url'         =>	$this->ua_url,
			'ua_company'     =>	$this->ua_company,
			'ua_company_url' =>	$this->ua_company_url,
			'ua_icon'        =>	$this->ua_icon,
			'ua_info_url'    =>	$this->ua_info_url,
			'os_family'      =>	$this->os_family,
			'os_name'        =>	$this->os_name,
			'os_url'         =>	$this->os_url,
			'os_company'     =>	$this->os_company,
			'os_company_url' =>	$this->os_company_url,
			'os_icon'        =>	$this->os_icon,
		);
		
		return array(
			$this->type,
			$this->ua_family,
			$this->ua_name,
			$this->ua_version,
			$this->ua_url,
			$this->ua_company,
			$this->ua_company_url,
			$this->ua_icon,
			$this->ua_info_url,
			$this->os_family,
			$this->os_name,
			$this->os_url,
			$this->os_company,
			$this->os_company_url,
			$this->os_icon,
		);
	}

	/**
	 * Set the appropriate data on this user agent from an associative array.
	 */
	private function fromArray($dat){
		$this->type           = $dat['type'];
		$this->ua_family      = $dat['ua_family'];
		$this->ua_name        = $dat['ua_name'];
		$this->ua_version     = $dat['ua_version'];
		$this->ua_url         = $dat['ua_url'];
		$this->ua_company     = $dat['ua_company'];
		$this->ua_company_url = $dat['ua_company_url'];
		$this->ua_icon        = $dat['ua_icon'];
		$this->ua_info_url    = $dat['ua_info_url'];
		$this->os_family      = $dat['os_family'];
		$this->os_name        = $dat['os_name'];
		$this->os_url         = $dat['os_url'];
		$this->os_company     = $dat['os_company'];
		$this->os_company_url = $dat['os_company_url'];
		$this->os_icon        = $dat['os_icon'];
	}

	/**
	 *  Load the data from the files
	 */
	private function _loadData() {

		$data = Cache::GetSystemCache()->get('useragent-cache-ini', 3600);
		if($data){
			// It's already ready! :)
			return $data;
		}
		else{
			// I have to open the file for the data... this of course involves downloading it if necessary.
			$file = Core::File('tmp/useragent.cache.ini');

			// Doesn't exist? download it!
			if(!$file->exists()){
				$remote = Core::File(self::$_ini_url);
				$remote->copyTo($file);
			}

			// Too old? download it!
			if($file->getMTime() < (Time::GetCurrent() - self::$updateInterval)){
				$remote = Core::File(self::$_ini_url);
				$remote->copyTo($file);
			}

			$data = parse_ini_file($file->getFilename(), true);

			// Cache it and return it!
			Cache::GetSystemCache()->set('useragent-cache-ini', $data);
			return $data;
		}
	}
}
