<?php
/**
 * User Agent object, fancy alternative PHP's native get_browser function.
 *
 * @package    Core
 * @author     Charlie Powell <charlie@eval.bz>
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link       http://php.net/manual/en/function.get-browser.php
 * @link       http://tempdownloads.browserscap.com/
 */

namespace Core;

/**
 * User Agent object, fancy alternative PHP's native get_browser function.
 *
 * <h3>Usage Exaples</h3>
 *
 * <h4>Standard Usage</h4>
 * <p>Just instantiate a new UserAgent object and inspect it.</p>
 * <code>
 * $ua = new \Core\UserAgent();
 *
 * // Prints "Firefox", "Chrome", "Safari", etc.
 * echo $ua->browser;
 *
 * // If you're on a mobile device, this will print true.
 * print_r($ua->isMobile());
 * </code>
 *
 * <h4>Specify a User Agent</h4>
 * <p>A user agent can also be specified in the constructor.  This is useful for log scans or other lookups.</p>
 *
 * <code>
 * // In this example, the UA is a mobile android device, so...
 * $someuastring = 'Mozilla/5.0 (Android; Mobile; rv:18.0) Gecko/18.0 Firefox/18.0';
 * $ua = new \Core\UserAgent($someuastring);
 *
 * // true
 * print_r($ua->isMobile());
 *
 * // "Android"
 * print_r($ua->platform);
 * </code>
 *
 * @package  Core
 * @author   Charlie Powell <charlie@eval.bz>
 * @link     http://php.net/manual/en/function.get-browser.php
 * @link     http://tempdownloads.browserscap.com/
 *
 *
 */
class UserAgent {
	private static $updateInterval =   604800; // 1 week

	/**
	 * @var string Location of the browscap file.
	 */
	private static $_ini_url    =   'http://browscap.org/stream?q=Full_PHP_BrowsCapINI';

	/**
	 * Options for regex patterns.
	 *
	 * REGEX_DELIMITER: Delimiter of all the regex patterns in the whole class.
	 * REGEX_MODIFIERS: Regex modifiers.
	 */
	const REGEX_DELIMITER = '@';
	const REGEX_MODIFIERS = 'i';

	/**
	 * The values to quote in the ini file
	 */
	const VALUES_TO_QUOTE = 'Browser|Parent';

	/**
	 * Definitions of the function used by the uasort() function to order the
	 * userAgents array.
	 *
	 * ORDER_FUNC_ARGS: Arguments that the function will take.
	 * ORDER_FUNC_LOGIC: Internal logic of the function.
	 */
	const ORDER_FUNC_ARGS = '$a, $b';
	const ORDER_FUNC_LOGIC = '$a=strlen($a);$b=strlen($b);return$a==$b?0:($a<$b?1:-1);';


	/**
	 * A map of entries from the default browscap properties to this system's propery list.
	 *
	 * @static
	 * @var array
	 */
	public static $Map = [
		'Parent' => 'parent',
		'Platform_Version' => 'platform_version',
		'Comment' => 'comment',
		'Browser' => 'browser',
		'Version' => 'version',
		'MajorVer' => 'major_ver',
		'MinorVer' => 'minor_ver',
		'Platform' => 'platform',
		'Frames' => 'frames',
		'IFrames' => 'iframes',
		'Tables' => 'tables',
		'Cookies' => 'cookies',
		'JavaScript' => 'javascript',
		'isMobileDevice' => 'is_mobile_device',
		'CssVersion' => 'css_version',
		'Device_Name' => 'device_name',
		'Device_Maker' => 'device_maker',
		'RenderingEngine_Name' => 'rendering_engine_name',
		'RenderingEngine_Version' => 'rendering_engine_version',
		'RenderingEngine_Description' => 'rendering_engine_description',
		'Platform_Description' => 'platform_description',
		'Alpha' => 'alpha',
		'Beta' => 'beta',
		'Win16' => 'win16',
		'Win32' => 'win32',
		'Win64' => 'win64',
		'BackgroundSounds' => 'background_sounds',
		'VBScript' => 'vbscript',
		'JavaApplets' => 'java_applets',
		'ActiveXControls' => 'activex_controls',
		'isSyndicationReader' => 'is_syndication_reader',
		'Crawler' => 'crawler',
		'AolVersion' => 'aol_version',
	];

	/**
	 * @var string Full string of the useragent
	 */
	public $useragent                    = null;
	/**
	 * @var string Parent user agent, can generally be safely ignored.
	 */
	public $parent                       = null;
	/**
	 * @var string Operating system version,
	 */
	public $platform_version             = null;
	public $comment                      = null;
	/**
	 * @var string The name of the user agent, ie: "Firefox", "Chrome", etc.
	 */
	public $browser                      = null;
	/**
	 * @var float Full version of the user agent
	 */
	public $version                      = 0.0;
	/**
	 * @var int Major version of the user agent
	 */
	public $major_ver                    = 0;
	/**
	 * @var int Minor version of the user agent
	 */
	public $minor_ver                    = 0;
	/**
	 * @var string Operating System, (or platform), of the host.
	 * Example: "Linux", "Win7", "Win8", etc.
	 */
	public $platform                     = null;
	/**
	 * @var bool true/false if frames are supported.
	 */
	public $frames                       = false;
	/**
	 * @var bool true/false if iframes are supported.
	 */
	public $iframes                      = false;
	/**
	 * @var bool true/false if tables are supported.
	 */
	public $tables                       = false;
	/**
	 * @var bool true/false if cookies are supported.
	 */
	public $cookies                      = false;
	/**
	 * @var bool true/false if javascript is supported.
	 */
	public $javascript                   = false;
	/**
	 * @var bool true/false if this is a mobile device.
	 */
	public $is_mobile_device             = false;
	/**
	 * @var int The highest CSS version supported.
	 */
	public $css_version                  = 0;
	/**
	 * @var string "PC"
	 */
	public $device_name                  = null;
	/**
	 * @var string "Various"
	 */
	public $device_maker                 = null;
	/**
	 * @var string Rendering engine, ie: "Gecko", "WebKit", "Trident", etc.
	 */
	public $rendering_engine_name        = null;
	public $rendering_engine_version     = null;
	public $rendering_engine_description = null;
	public $platform_description         = null;
	/**
	 * @var bool
	 */
	public $alpha                        = false;
	/**
	 * @var bool
	 */
	public $beta                         = false;
	/**
	 * @var bool
	 */
	public $win16                        = false;
	/**
	 * @var bool
	 */
	public $win32                        = false;
	/**
	 * @var bool
	 */
	public $win64                        = false;
	/**
	 * @var bool
	 */
	public $background_sounds            = false;
	/**
	 * @var bool
	 */
	public $vbscript                     = false;
	/**
	 * @var bool
	 */
	public $java_applets                 = false;
	/**
	 * @var bool
	 */
	public $activex_controls             = false;
	/**
	 * @var bool
	 */
	public $is_syndication_reader        = false;
	/**
	 * @var bool
	 */
	public $crawler                      = false;
	public $aol_version                  = null;

	/**
	 * @var array Cache container for the Construct static method.
	 */
	protected static $_Cache = [];

	/**
	 * Constructor with a user agent string.
	 *
	 * If no string is provided, the current user's string is used.
	 *
	 * @param string $useragent
	 */
	public function __construct($useragent = null) {
		if($useragent === null) $useragent = $_SERVER['HTTP_USER_AGENT'];

		// if we haven't loaded the data yet, do it now
		// This will also return the cached data.
		$data = self::_LoadData();

		$browser = array();
		foreach ($data['patterns'] as $key => $pattern) {
			if (preg_match($pattern . 'i', $useragent)) {
				$browser = array(
					$useragent, // Original useragent
					trim(strtolower($pattern), self::REGEX_DELIMITER),
					$data['useragents'][$key]
				);

				$browser = $value = $browser + $data['browsers'][$key];

				while (array_key_exists(3, $value) && $value[3]) {
					$value = $data['browsers'][$value[3]];
					$browser += $value;
				}

				if (!empty($browser[3])) {
					$browser[3] = $data['useragents'][$browser[3]];
				}

				break;
			}
		}

		// Add the keys for each property
		$this->useragent = $useragent;
		foreach ($browser as $key => $value) {
			if ($value === 'true') {
				$value = true;
			} elseif ($value === 'false') {
				$value = false;
			}

			$key = $data['properties'][$key];
			if(isset(self::$Map[$key])){
				$prop = self::$Map[$key];
				$this->$prop = $value;
			}
		}

		// Try to guess if there are still empty slots!
		if($this->platform == 'unknown'){
			if(stripos($this->useragent, 'linux') !== false){
				$this->platform = 'Linux';
			}
		}
		if($this->browser == 'Default Browser'){
			if(stripos($this->useragent, 'firefox/') !== false){
				$this->browser = 'Firefox';
				$this->javascript = true;
				$this->cookies = true;
				$this->tables = true;
				$this->frames = true;
				$this->iframes = true;
			}
		}
		if($this->version == 0.0){
			if(preg_match('#' . $this->browser . '/[0-9\.]+#', $this->useragent) !== 0){
				$this->version = preg_replace('#.*' . $this->browser . '/([0-9\.]+).*#', '$1', $this->useragent);
				$this->major_ver = substr($this->version, 0, strpos($this->version, '.'));
				$this->minor_ver = substr($this->version, strpos($this->version, '.')+1);
			}
		}
		if($this->rendering_engine_name == 'unknown'){
			if(stripos($this->useragent, 'gecko/') !== false){
				$this->rendering_engine_name = 'Gecko';
			}
		}
	}

	/**
	 * Guesses if this user request was a bot.
	 *
	 * @return boolean
	 */
	public function isBot(){
		return $this->crawler;
	}

	/**
	 * Simple check if this is a mobile user agent
	 *
	 * @return bool
	 */
	public function isMobile(){
		return $this->is_mobile_device;
	}

	/**
	 * Get this user agent as an associative array.
	 */
	public function asArray(){
		$ret = array();
		$ret['useragent'] = $this->useragent;

		foreach(self::$Map as $k => $v){
			$ret[$v] = $this->$v;
		}

		return $ret;
	}

	/**
	 * Get a pseudo-unique identifier for this user agent.
	 *
	 * This is useful for identifying a certain type of UA for caching reasons.
	 *
	 * @param bool $as_array
	 *
	 * @return array|string
	 */
	public function getPseudoIdentifier($as_array = false){
		$a = [];
		$a[] = 'ua-browser-' . $this->browser;
		$a[] = 'ua-engine-' . $this->rendering_engine_name;
		$a[] = 'ua-browser-version-' . $this->major_ver;
		$a[] = 'ua-platform-' . $this->platform;
		if($this->isMobile()) $a[] = 'ua-is-mobile';

		// Does the user want an array or a flat hash?
		if($as_array){
			return $a;
		}
		else{
			return strtolower(implode(';', $a));
		}
	}

	/**
	 *  Load the data from the files
	 */
	private static function _LoadData() {

		// The key for this data, must be unique on the system.
		$cachekey = 'useragent-browsecap-data';
		// The number of seconds to have Core cache the records.
		$cachetime = 7200;

		$cache = \Core\Cache::Get($cachekey, $cachetime);

		if($cache === false){
			$file = \Core\Filestore\Factory::File('tmp/php_browscap.ini');
			$remote = \Core\Filestore\Factory::File(self::$_ini_url);

			// Doesn't exist? download it!
			if(!$file->exists()){
				$remote->copyTo($file);
			}

			// Too old? download it!
			if($file->getMTime() < (\Time::GetCurrent() - self::$updateInterval)){
				$remote->copyTo($file);
			}

			$_browsers = parse_ini_file($file->getFilename(), true, INI_SCANNER_RAW);
			$patterns = [];
			$browsers = [];

			// Trim the header off the file.
			array_shift($_browsers);


			// Grab all the property keys for browsers.
			$properties = array_keys($_browsers['DefaultProperties']);
			array_unshift(
				$properties,
				'browser_name',
				'browser_name_regex',
				'browser_name_pattern',
				'Parent'
			);

			// This creates a sorted set of user agents... used by the internal logic.
			$uas = array_keys($_browsers);
			usort(
				$uas,
				create_function(self::ORDER_FUNC_ARGS, self::ORDER_FUNC_LOGIC)
			);


			$user_agents_keys = array_flip($uas);
			$properties_keys = array_flip($properties);

			$search = array('\*', '\?');
			$replace = array('.*', '.');

			foreach ($uas as $user_agent) {
				$browser = [];
				$pattern = preg_quote($user_agent, self::REGEX_DELIMITER);
				$patterns[] = self::REGEX_DELIMITER
					. '^'
					. str_replace($search, $replace, $pattern)
					. '$'
					. self::REGEX_DELIMITER;

				if (!empty($_browsers[$user_agent]['Parent'])) {
					$parent = $_browsers[$user_agent]['Parent'];
					$_browsers[$user_agent]['Parent'] = $user_agents_keys[$parent];
				}

				foreach ($_browsers[$user_agent] as $key => $value) {
					//$key = $properties_keys[$key] . ".0";
					$key = $properties_keys[$key];
					$browser[$key] = $value;
				}

				$browsers[] = $browser;
				unset($browser);
			}
			unset($user_agents_keys, $properties_keys, $_browsers);

			\Core\Cache::Set(
				$cachekey,
				[
					'browsers'   => $browsers,
					'useragents' => $uas,
					'patterns'   => $patterns,
					'properties' => $properties,
				],
				$cachetime
			);
		}

		return \Core\Cache::Get($cachekey, $cachetime);
	}

	/**
	 * Construct a UserAgent object with full caching enabled.
	 *
	 * @param $useragent
	 *
	 * @return UserAgent
	 */
	public static function Construct($useragent = null){
		if($useragent === null) $useragent = $_SERVER['HTTP_USER_AGENT'];

		$cachekey = 'useragent-constructor-' . md5($useragent);
		$cache = \Core\Cache::Get($cachekey);
		if(!$cache){
			$cache = new UserAgent($useragent);
			\Core\Cache::Set($cachekey, $cache, 3600);
		}

		return $cache;
	}
}
