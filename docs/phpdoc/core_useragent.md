Core\UserAgent
===============

User Agent object, fancy alternative PHP&#039;s native get_browser function.

<h3>Usage Exaples</h3>

<h4>Standard Usage</h4>
<p>Just instantiate a new UserAgent object and inspect it.</p>
<code>
$ua = new \Core\UserAgent();

// Prints "Firefox", "Chrome", "Safari", etc.
echo $ua->browser;

// If you're on a mobile device, this will print true.
print_r($ua->isMobile());
</code>

<h4>Specify a User Agent</h4>
<p>A user agent can also be specified in the constructor.  This is useful for log scans or other lookups.</p>

<code>
// In this example, the UA is a mobile android device, so...
$someuastring = 'Mozilla/5.0 (Android; Mobile; rv:18.0) Gecko/18.0 Firefox/18.0';
$ua = new \Core\UserAgent($someuastring);

// true
print_r($ua->isMobile());

// "Android"
print_r($ua->platform);
</code>


* Class name: UserAgent
* Namespace: Core



Constants
----------


### REGEX_DELIMITER

    const REGEX_DELIMITER = '@'





### REGEX_MODIFIERS

    const REGEX_MODIFIERS = 'i'





### VALUES_TO_QUOTE

    const VALUES_TO_QUOTE = 'Browser|Parent'





### ORDER_FUNC_ARGS

    const ORDER_FUNC_ARGS = '$a, $b'





### ORDER_FUNC_LOGIC

    const ORDER_FUNC_LOGIC = '$a=strlen($a);$b=strlen($b);return$a==$b?0:($a<$b?1:-1);'





Properties
----------


### $updateInterval

    private mixed $updateInterval = 604800





* Visibility: **private**
* This property is **static**.


### $_ini_url

    private string $_ini_url = 'http://repo.corepl.us/full_php_browscap.ini.gz'





* Visibility: **private**
* This property is **static**.


### $Map

    public array $Map = array('Parent' => 'parent', 'Platform_Version' => 'platform_version', 'Comment' => 'comment', 'Browser' => 'browser', 'Version' => 'version', 'MajorVer' => 'major_ver', 'MinorVer' => 'minor_ver', 'Platform' => 'platform', 'Frames' => 'frames', 'IFrames' => 'iframes', 'Tables' => 'tables', 'Cookies' => 'cookies', 'JavaScript' => 'javascript', 'isMobileDevice' => 'is_mobile_device', 'CssVersion' => 'css_version', 'Device_Name' => 'device_name', 'Device_Maker' => 'device_maker', 'RenderingEngine_Name' => 'rendering_engine_name', 'RenderingEngine_Version' => 'rendering_engine_version', 'RenderingEngine_Description' => 'rendering_engine_description', 'Platform_Description' => 'platform_description', 'Alpha' => 'alpha', 'Beta' => 'beta', 'Win16' => 'win16', 'Win32' => 'win32', 'Win64' => 'win64', 'BackgroundSounds' => 'background_sounds', 'VBScript' => 'vbscript', 'JavaApplets' => 'java_applets', 'ActiveXControls' => 'activex_controls', 'isSyndicationReader' => 'is_syndication_reader', 'Crawler' => 'crawler', 'AolVersion' => 'aol_version')

A map of entries from the default browscap properties to this system's propery list.



* Visibility: **public**
* This property is **static**.


### $useragent

    public string $useragent = null





* Visibility: **public**


### $parent

    public string $parent = null





* Visibility: **public**


### $comment

    public string $comment = null





* Visibility: **public**


### $browser

    public string $browser = null





* Visibility: **public**


### $browser_short_name

    public null $browser_short_name = null





* Visibility: **public**


### $version

    public float $version = 0.0





* Visibility: **public**


### $major_ver

    public integer $major_ver





* Visibility: **public**


### $minor_ver

    public integer $minor_ver





* Visibility: **public**


### $platform

    public string $platform = null





* Visibility: **public**


### $platform_version

    public string $platform_version = null





* Visibility: **public**


### $platform_architecture

    public string $platform_architecture = null





* Visibility: **public**


### $platform_bits

    public string $platform_bits = null





* Visibility: **public**


### $platform_short_name

    public null $platform_short_name = null





* Visibility: **public**


### $frames

    public boolean $frames = false





* Visibility: **public**


### $iframes

    public boolean $iframes = false





* Visibility: **public**


### $tables

    public boolean $tables = false





* Visibility: **public**


### $cookies

    public boolean $cookies = false





* Visibility: **public**


### $javascript

    public boolean $javascript = false





* Visibility: **public**


### $is_mobile_device

    public boolean $is_mobile_device = false





* Visibility: **public**


### $css_version

    public integer $css_version





* Visibility: **public**


### $device_name

    public string $device_name = null





* Visibility: **public**


### $device_maker

    public string $device_maker = null





* Visibility: **public**


### $rendering_engine_name

    public string $rendering_engine_name = null





* Visibility: **public**


### $rendering_engine_version

    public mixed $rendering_engine_version = null





* Visibility: **public**


### $rendering_engine_description

    public mixed $rendering_engine_description = null





* Visibility: **public**


### $platform_description

    public mixed $platform_description = null





* Visibility: **public**


### $alpha

    public boolean $alpha = false





* Visibility: **public**


### $beta

    public boolean $beta = false





* Visibility: **public**


### $win16

    public boolean $win16 = false





* Visibility: **public**


### $win32

    public boolean $win32 = false





* Visibility: **public**


### $win64

    public boolean $win64 = false





* Visibility: **public**


### $background_sounds

    public boolean $background_sounds = false





* Visibility: **public**


### $vbscript

    public boolean $vbscript = false





* Visibility: **public**


### $java_applets

    public boolean $java_applets = false





* Visibility: **public**


### $activex_controls

    public boolean $activex_controls = false





* Visibility: **public**


### $is_syndication_reader

    public boolean $is_syndication_reader = false





* Visibility: **public**


### $crawler

    public boolean $crawler = false





* Visibility: **public**


### $aol_version

    public mixed $aol_version = null





* Visibility: **public**


### $_Cache

    protected array $_Cache = array()





* Visibility: **protected**
* This property is **static**.


Methods
-------


### __construct

    mixed Core\UserAgent::__construct(string $useragent)

Constructor with a user agent string.

If no string is provided, the current user's string is used.

* Visibility: **public**


#### Arguments
* $useragent **string**



### isBot

    boolean Core\UserAgent::isBot()

Guesses if this user request was a bot.



* Visibility: **public**




### isMobile

    boolean Core\UserAgent::isMobile()

Simple check if this is a mobile user agent



* Visibility: **public**




### asArray

    mixed Core\UserAgent::asArray()

Get this user agent as an associative array.



* Visibility: **public**




### getAsHTML

    string Core\UserAgent::getAsHTML()

Get the info for this user agent as a pretty HTML string.



* Visibility: **public**




### getPseudoIdentifier

    array|string Core\UserAgent::getPseudoIdentifier(boolean $as_array)

Get a pseudo-unique identifier for this user agent.

This is useful for identifying a certain type of UA for caching reasons.

* Visibility: **public**


#### Arguments
* $as_array **boolean**



### _fixVersion

    mixed Core\UserAgent::_fixVersion()





* Visibility: **private**




### _getAsHTMLBrowser

    string Core\UserAgent::_getAsHTMLBrowser()

Get the browser component of this useragent as pretty HTML.



* Visibility: **private**




### _getAsHTMLPlatform

    string Core\UserAgent::_getAsHTMLPlatform()

Get the platform/OS component of this useragent as pretty HTML.



* Visibility: **private**




### _getAsHTMLDevice

    string Core\UserAgent::_getAsHTMLDevice()

Get the device component of this useragent as pretty HTML.



* Visibility: **private**




### _LoadData

    mixed Core\UserAgent::_LoadData()

Load the data from the files



* Visibility: **private**
* This method is **static**.




### Construct

    \Core\UserAgent Core\UserAgent::Construct($useragent)

Construct a UserAgent object with full caching enabled.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $useragent **mixed**


