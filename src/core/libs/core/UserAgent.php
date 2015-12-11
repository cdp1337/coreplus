<?php
/**
 * User Agent object, fancy alternative PHP's native get_browser function.
 *
 * @package    Core
 * @author     Charlie Powell <charlie@evalagency.com>
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link       http://php.net/manual/en/function.get-browser.php
 * @link       http://tempdownloads.browserscap.com/
 */

namespace Core;
use Core\Filestore\Contents\ContentGZ;

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
 * @author   Charlie Powell <charlie@evalagency.com>
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
	private static $_ini_url    =   'http://repo.corepl.us/full_php_browscap.ini.gz';
	// Argh, when developers are little bitches and place horribly low rate limits on services :/
	//private static $_ini_url    =   'http://browscap.org/stream?q=Full_PHP_BrowsCapINI';

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
	 * @var string Comment, can generally be ignored.
	 */
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
	 * Example: "Linux", "Windows", "MacOSX", etc.
	 */
	public $platform                     = null;

	/**
	 * @var string Operating system version,
	 */
	public $platform_version             = null;

	/**
	 * @var string OS architecture, eg: x86, powerpc, risc, etc.
	 */
	public $platform_architecture        = null;

	/**
	 * @var string bit address space, generally 8, 16, 32, or 64.
	 */
	public $platform_bits                = null;

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
		
		if(class_exists('DeviceDetector\\DeviceDetector')){
			// Piwik's DeviceDetector is available, use that instead! :)
			$dd = new \DeviceDetector\DeviceDetector($useragent);
			$dd->parse();
			
			$this->useragent = $useragent;
			$c = $dd->getClient();
			if($c !== null){
				$this->browser = $c['name'];
				$this->version = $c['version'];
				$this->rendering_engine_name = isset($c['engine']) ? $c['engine'] : null;
				if($this->rendering_engine_name == 'Text-based' && $this->browser == 'Lynx'){
					$this->rendering_engine_name = 'libwww-FM';
				}
			}
			elseif(($bot = $dd->getBot()) !== null){
				$this->browser = $bot['name'];
			}
			elseif(strpos($this->useragent, 'Core Plus') !== false){
				$this->browser = 'Core Plus';
				$this->version = preg_replace('#.*Core Plus ([0-9]+\.[0-9]+).*#', '$1', $this->useragent);
			}
			
			if($this->browser == 'MJ12 Bot'){
				$this->version = preg_replace('#.*MJ12bot/v([0-9]+\.[0-9]+).*#', '$1', $this->useragent);
			}
			if($this->browser == 'BingBot'){
				$this->version = preg_replace('#.*bingbot/([0-9]+\.[0-9]+).*#', '$1', $this->useragent);
			}
			if($this->browser == 'Baidu Spider'){
				$this->version = preg_replace('#.*Baiduspider/([0-9]+\.[0-9]+).*#', '$1', $this->useragent);
			}
			
			$os = $dd->getOs();
			if($os !== null && sizeof($os)){
				$this->platform = $os['name'];
				$this->platform_architecture = $os['platform'];
				$this->platform_version = $os['version'];
			}
			
			// Expand OSX versions to the full version string.
			if($this->platform == 'Mac'){
				$this->platform = 'MacOSX';
				$this->platform_version = preg_replace('#.*Mac OS X ([0-9\._]+).*#', '$1', $this->useragent);
				$this->platform_version = str_replace('_', '.', $this->platform_version);
			}
			
			// Also expand iOS to the full version string.
			if($this->platform == 'iOS'){
				$this->platform_version = preg_replace('#.*OS ([0-9\._]+).*#', '$1', $this->useragent);
				$this->platform_version = str_replace('_', '.', $this->platform_version);
			}
			
			// That library really doesn't like to expand OS versions to their correct version :/
			// Same for Android!
			if($this->platform == 'Android'){
				$this->platform_version = preg_replace('#.*Android ([0-9\.]+);.*#', '$1', $this->useragent);
			}
			
			if($this->platform_architecture == 'x64'){
				$this->platform_architecture = 'x86';
				$this->platform_bits = 64;
			}
			elseif($this->platform_architecture == 'x86'){
				$this->platform_bits = 32;
			}
			
			$this->crawler = $dd->isBot();
			$this->is_mobile_device = $dd->isMobile();
			$this->device_maker = $dd->getBrandName();
			$this->device_name = $dd->getModel();
			
			$this->_fixVersion();
		}
		else{
			// if we haven't loaded the data yet, do it now
			// This will also return the cached data.
			$data = self::_LoadData();

			if(!isset($data['patterns'])){
				// Catch for failed loads
				$data['patterns'] = [];
			}

			$browser = [];
			foreach ($data['patterns'] as $key => $pattern) {
				if (preg_match($pattern . 'i', $useragent)) {
					$browser = [
						$useragent, // Original useragent
						trim(strtolower($pattern), self::REGEX_DELIMITER),
						$data['useragents'][$key]
					];

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

			if($this->browser == 'Default Browser' || $this->browser === null){
				if(stripos($this->useragent, 'iceweasel/') !== false){
					$this->browser = 'Iceweasel';
					$this->javascript = true;
					$this->cookies = true;
					$this->tables = true;
					$this->frames = true;
					$this->iframes = true;
				}
				elseif(stripos($this->useragent, 'firefox/') !== false){
					$this->browser = 'Firefox';
					$this->javascript = true;
					$this->cookies = true;
					$this->tables = true;
					$this->frames = true;
					$this->iframes = true;
				}
				elseif(stripos($this->useragent, 'googlebot/') !== false){
					$this->browser = 'Googlebot';
					$this->rendering_engine_name = '';
					$this->javascript = true;
					$this->cookies = true;
					$this->tables = true;
					$this->frames = true;
					$this->iframes = true;
					$this->crawler = true;
				}
				elseif(stripos($this->useragent, 'msie ') !== false){
					$this->browser = 'IE';
					$this->javascript = true;
					$this->cookies = true;
					$this->tables = true;
					$this->frames = true;
					$this->iframes = true;

					$this->version = preg_replace('#.*MSIE ([0-9\.]+);.*#', '$1', $this->useragent);
				}
				elseif(stripos($this->useragent, 'lynx/') !== false){
					$this->browser = 'Lynx';
					$this->javascript = false;
					$this->cookies = true;
					$this->tables = true;
					$this->frames = true;
					$this->iframes = true;
					$this->crawler = false;
				}
				elseif(stripos($this->useragent, 'wget/') !== false){
					$this->browser = 'Wget';
					$this->javascript = false;
					$this->cookies = false;
				}
				elseif(stripos($this->useragent, 'MJ12bot/') !== false){
					$this->browser = 'MJ12 Bot';
					$this->javascript = false;
					$this->cookies = false;
					$this->crawler = true;
				}
				elseif(stripos($this->useragent, 'bingbot/') !== false){
					$this->browser = 'BingBot';
					$this->javascript = false;
					$this->cookies = false;
					$this->crawler = true;
				}
				elseif(stripos($this->useragent, 'Baiduspider/') !== false){
					$this->browser = 'Baidu Spider';
					$this->javascript = false;
					$this->cookies = false;
					$this->crawler = true;
				}
				elseif(strpos($this->useragent, 'Core Plus') !== false){
					$this->browser = 'Core Plus';
					$this->version = preg_replace('#.*Core Plus ([0-9]+\.[0-9]+).*#', '$1', $this->useragent);
				}
			}

			// Remap some platform options around to make them more usable.
			switch($this->platform){
				case 'WinXP':
				case 'Win32':
					$this->platform = 'Windows';
					$this->platform_version = 'XP';
					break;
				case 'WinVista':
					$this->platform = 'Windows';
					$this->platform_version = 'Vista';
					break;
				case 'Win7':
					$this->platform = 'Windows';
					$this->platform_version = '7';
					break;
				case 'Win8':
					$this->platform = 'Windows';
					$this->platform_version = '8';
					break;
				case 'Win8.1':
					$this->platform = 'Windows';
					$this->platform_version = '8.1';
					break;
				case 'Win10':
					$this->platform = 'Windows';
					$this->platform_version = '10';
					break;
				case 'Linux':
					if(strpos($this->useragent, 'Ubuntu') !== false){
						$this->platform = 'Ubuntu';
					}
					else{
						$this->platform = 'GNU/Linux';
					}
					break;
				case 'GNU/Linux':
					if(strpos($this->useragent, 'Ubuntu') !== false){
						$this->platform = 'Ubuntu';
					}
					break;
				case 'MacOSX':
					$this->platform_version = preg_replace('#.*Mac OS X ([0-9\._]+).*#', '$1', $this->useragent);
					$this->platform_version = str_replace('_', '.', $this->platform_version);
					break;
				case 'Android':
					$this->platform_version = preg_replace('#.*Android ([0-9\.]+);.*#', '$1', $this->useragent);
					$this->is_mobile_device = true;
					break;
				case 'iOS':
					$this->platform_version = preg_replace('#.*OS ([0-9\._]+).*#', '$1', $this->useragent);
					$this->platform_version = str_replace('_', '.', $this->platform_version);
					$this->is_mobile_device = true;
					break;
			}

			
			if($this->browser == 'Firefox' && stripos($this->useragent, 'iceweasel/') !== false){
				// Iceweasel is incorrectly picked up as Firefox
				$this->browser = 'Iceweasel';
				$this->version = preg_replace('#.*Iceweasel/([0-9]+\.[0-9]+).*#', '$1', $this->useragent);
			}

			if($this->browser == 'MJ12 Bot'){
				$this->version = preg_replace('#.*MJ12bot/v([0-9]+\.[0-9]+).*#', '$1', $this->useragent);
			}
			if($this->browser == 'BingBot'){
				$this->version = preg_replace('#.*bingbot/([0-9]+\.[0-9]+).*#', '$1', $this->useragent);
			}
			if($this->browser == 'Baidu Spider'){
				$this->version = preg_replace('#.*Baiduspider/([0-9]+\.[0-9]+).*#', '$1', $this->useragent);
			}
			if($this->browser == 'IE'){
				$this->browser = 'Internet Explorer';
			}
			
			$this->_fixVersion();

			// Chrome switched from Webkit to Blink after version 28!
			if($this->browser == 'Chrome'){
				$this->rendering_engine_name = $this->version >= 28 ? 'Blink' : 'WebKit';
			}
			
			// Safari is based on Chrome, so same thing as Chrome
			if($this->browser == 'Safari' && $this->rendering_engine_name == 'WebKit' && $this->version >= 28){
				$this->rendering_engine_name = 'Blink';
			}

			// Safari on mobile platforms is actually Mobile Safari.
			if($this->is_mobile_device && $this->browser == 'Safari'){
				$this->browser = 'Mobile Safari';
			}

			// Android on mobile is actually called Android Browser.
			if($this->is_mobile_device && $this->browser == 'Android'){
				$this->browser = 'Android Browser';
			}
		}

		// Try to guess if there are still empty slots!
		if($this->platform == 'unknown' || $this->platform === null){
			if(stripos($this->useragent, 'Ubuntu') !== false){
				// Ubuntu has a useragent of "Ubuntu; Linux x86_64"
				$this->platform = 'Ubuntu';
			}
			elseif(stripos($this->useragent, 'linux') !== false){
				// Generic Linux OS
				$this->platform = 'GNU/Linux';
			}
			elseif(stripos($this->useragent, 'windows nt 5.0') !== false){
				$this->platform = 'Windows';
				$this->platform_version = '2000'; // February 17, 2000
			}
			elseif(stripos($this->useragent, 'windows nt 5.1') !== false){
				$this->platform = 'Windows';
				$this->platform_version = 'XP'; // October 25, 2001
			}
			elseif(stripos($this->useragent, 'windows nt 5.2') !== false){
				$this->platform = 'Windows';
				$this->platform_version = 'XP'; // March 28, 2003
			}
			elseif(stripos($this->useragent, 'windows nt 6.0') !== false){
				$this->platform = 'Windows';
				$this->platform_version = 'Vista'; // January 30, 2007
			}
			elseif(stripos($this->useragent, 'windows nt 6.1') !== false){
				$this->platform = 'Windows';
				$this->platform_version = '7'; // October 22, 2009
			}
			elseif(stripos($this->useragent, 'windows nt 6.2') !== false){
				$this->platform = 'Windows';
				$this->platform_version = '8'; // October 26, 2012
			}
			elseif(stripos($this->useragent, 'windows nt 6.3') !== false){
				$this->platform = 'Windows';
				$this->platform_version = '8.1'; // October 18, 2013
			}
			elseif(stripos($this->useragent, 'windows nt 10') !== false){
				$this->platform = 'Windows';
				$this->platform_version = '10'; // July 29, 2015
			}
			elseif(stripos($this->useragent, 'windows phone 8.0') !== false){
				$this->platform = 'Windows Phone';
				$this->platform_version = '8.0';
				$this->is_mobile_device = true;
			}
			elseif(stripos($this->useragent, 'mozilla/5.0 (mobile;') !== false){
				$this->platform = 'Firefox OS';
				$this->is_mobile_device = true;
			}
		}

		if($this->rendering_engine_name == 'unknown' || $this->rendering_engine_name == null){
			if(stripos($this->useragent, 'gecko/') !== false){
				$this->rendering_engine_name = 'Gecko';
				$this->rendering_engine_version = preg_replace('#.*Gecko/([0-9\.]+).*#i', '$1', $this->useragent);
			}
			elseif(stripos($this->useragent, 'AppleWebKit/') !== false){
				$this->rendering_engine_name = 'WebKit';
				$this->rendering_engine_version = preg_replace('#.*AppleWebKit/([0-9\.]+).*#i', '$1', $this->useragent);
			}
			elseif(stripos($this->useragent, 'trident/') !== false){
				$this->rendering_engine_name = 'Trident';
				$this->rendering_engine_version = preg_replace('#.*trident/([0-9\.]+).*#i', '$1', $this->useragent);
			}
			elseif(strpos($this->useragent, 'MSIE') !== false){
				$this->rendering_engine_name = 'Trident';
			}
			elseif(stripos($this->useragent, 'libwww-fm/') !== false){
				$this->rendering_engine_name = 'libwww-FM';
				$this->rendering_engine_version = preg_replace('#.*libwww-fm/([0-9\.]+).*#i', '$1', $this->useragent);
			}
		}


		// Architecture bits?
		if($this->platform_bits === null){
			if($this->platform == 'Windows'){
				if(strpos($this->useragent, 'WOW64') !== false){
					$this->platform_bits = '64';
					$this->platform_architecture = 'x86';
				}
				else{
					$this->platform_bits = '32';
					$this->platform_architecture = 'x86';
				}
			}
			elseif($this->platform == 'GNU/Linux' || $this->platform == 'Ubuntu'){
				if(strpos($this->useragent, 'x86_64') !== false){
					$this->platform_bits = '64';
					$this->platform_architecture = 'x86';
				}
				elseif(strpos($this->useragent, 'x86') !== false){
					$this->platform_bits = '32';
					$this->platform_architecture = 'x86';
				}
			}
			elseif($this->platform == 'MacOSX'){
				if(strpos($this->useragent, 'Intel Mac') !== false){
					$this->platform_architecture = 'x86';
				}
			}
		}
		
		if($this->platform === null){
			$this->platform = 'unknown';
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
		$ret = [];
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
	
	private function _fixVersion(){
		if($this->version == 0.0){
			if(preg_match('#' . $this->browser . '/[0-9\.]+#', $this->useragent) !== 0){
				$this->version = preg_replace('#.*' . $this->browser . '/([0-9]+)\.([0-9]+).*#', '$1.$2', $this->useragent);
			}
			elseif(strpos($this->useragent, ' Version/') !== false){
				$this->version = preg_replace('#.* Version/([0-9\.]+).*#i', '$1', $this->useragent);
			}
		}

		if($this->major_ver == 0){
			$this->major_ver = substr($this->version, 0, strpos($this->version, '.'));
			$this->minor_ver = substr($this->version, strpos($this->version, '.')+1);

			// Remove extra version strings from Chrome, (ex: 18.0.1025.166)
			if(strpos($this->minor_ver, '.') !== false){
				$this->minor_ver = substr($this->minor_ver, 0, strpos($this->minor_ver, '.'));
			}
		}
	}

	/**
	 *  Load the data from the files
	 */
	private static function _LoadData() {

		// The key for this data, must be unique on the system.
		$cachekey = 'useragent-browsecap-data';
		// The number of seconds to have Core cache the records.
		$cachetime = SECONDS_ONE_WEEK;

		$cache = Cache::Get($cachekey, $cachetime);

		if($cache === false){
			$file   = \Core\Filestore\Factory::File('tmp/php_browscap.ini');
			$remote = \Core\Filestore\Factory::File(self::$_ini_url);

			$rcontents = $remote->getContentsObject();
			if($rcontents instanceof ContentGZ){
				// yay...
				// Core handles all the remote file caching automatically, so no worries about anything here.
				$rcontents->uncompress($file);
			}
			else {
				// Ok, it may be a standard text file then... try the conventional logic.
				// Doesn't exist? download it!
				if(!$file->exists()){
					$remote->copyTo($file);
				}

				// Too old? download it!
				if($file->getMTime() < (\Time::GetCurrent() - self::$updateInterval)){
					$remote->copyTo($file);
				}
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

			$search = ['\*', '\?'];
			$replace = ['.*', '.'];

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

			Cache::Set(
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

		return Cache::Get($cachekey, $cachetime);
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
		$cache = Cache::Get($cachekey);
		if(!$cache){
			$cache = new UserAgent($useragent);
			Cache::Set($cachekey, $cache, SECONDS_ONE_WEEK);
		}

		return $cache;
	}
}
