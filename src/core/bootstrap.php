<?php
/**
 * Core bootstrap file that kicks off the entire application
 *
 * This file is the core of the application; it's responsible for setting up
 *  all the necessary paths, settings and includes.
 *
 * @package Core
 * @since 0.1
 * @author Charlie Powell <charlie@evalagency.com>
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


/********************* Pre-instantiation system checks ************************/

// The bootstrap cannot be called directly.
if (basename($_SERVER['SCRIPT_NAME']) == 'bootstrap.php') die('You cannot call that file directly.');


// I expect some configuration options....
if (PHP_VERSION < '6.0.0' && ini_get('magic_quotes_gpc')) {
	die('This application cannot run with magic_quotes_gpc enabled, please disable them now!');
}

if (PHP_VERSION < '5.4.0') {
	die('This application requires at least PHP 5.4 to run!');
}

// Damn suPHP, I can handle my own permissions, TYVM
umask(0);

// Start a timer for performance tuning purposes.
require_once(__DIR__ . '/functions/Core.functions.php');
require_once(__DIR__ . '/libs/core/utilities/profiler/Profiler.php');
require_once(__DIR__ . '/libs/core/utilities/profiler/DatamodelProfiler.php');
require_once(__DIR__ . '/libs/core/utilities/logger/functions.php');
$profiler = new Core\Utilities\Profiler\Profiler('Core Plus');

// gogo i18n!
mb_internal_encoding('UTF-8');

/********************* Initial system defines *********************************/
require_once(__DIR__ . '/bootstrap_predefines.php');
Core\Utilities\Logger\write_debug('Starting Application');


/********************** Critical file inclusions ******************************/
Core\Utilities\Logger\write_debug('Loading pre-include files');
require_once(__DIR__ . '/bootstrap_preincludes.php');


// __TODO__ Make this errorHandler accept 'hooks' to be fired when a critical error is occured.
// This can include rendering an HTML file to the browser, or some other action.
//error_reporting ( E_ALL ) ;
//require_once("core/classes/ErrorHandler.class.php");


// Load the hook handler, which will allow cross-library/module communication abstractly.
Core\Utilities\Logger\write_debug('Loading hook handler');
require_once(ROOT_PDIR . "core/libs/core/HookHandler.class.php");


// Pre includes are ready.
$preincludes_time = microtime(true);

// And start the core!
Core\Utilities\Logger\write_debug('Loading core system');
//require_once(ROOT_PDIR . 'core/libs/core/InstallTask.class.php');
require_once(ROOT_PDIR . 'core/libs/core/Core.class.php');
//Core::Singleton();


// Configuration handler, for loading any config variable/constant from XML data or the database.
Core\Utilities\Logger\write_debug('Loading configs');
require_once(ROOT_PDIR . "core/libs/core/ConfigHandler.class.php");
ConfigHandler::Singleton();
\Core\Utilities\Profiler\Profiler::GetDefaultProfiler()->record('Configuration loaded and available');


// Give me core settings!
// This will do the defines for the site, and provide any core variables to get started.
$core_settings = ConfigHandler::LoadConfigFile("configuration");

if (!$core_settings) {
	if(EXEC_MODE == 'WEB'){
		$newURL = 'install/';
		//header('HTTP/1.1 302 Moved Temporarily');
		//header("Location:" . $newURL);
		// This is not just redirected automatically because many browsers remember the redirect and just insist on redirecting from / to /install!
		// The notice about needing to refresh the page is again, because browsers may cache the install message.
		die("Please <a href=\"{$newURL}\">install Core Plus.</a><br/><br/>(You may need to hard-refresh this page a time or two if you just installed)");
	}
	else{
		die('Please install core plus through the web interface first!' . "\n");
	}
}


/**
 * If the site is not in "development mode", force errors to be hidden.
 * This is useful to override any common server settings.
 *
 * (php default is to display them after all...)
 */
if (!DEVELOPMENT_MODE) {
	//error_reporting(0);
	ini_set('display_errors', 0);
	ini_set('html_errors', 0);
}
// Make sure that errors are set to be displayed to the fullest extent.
else{
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_errors', 1);
	ini_set('html_errors', 1);
}
set_error_handler('Core\\ErrorManagement\\error_handler', error_reporting());
//register_shutdown_function('HookHandler::ShutdownHook');
register_shutdown_function('HookHandler::DispatchHook', '/core/shutdown');
register_shutdown_function('Core\\ErrorManagement\\check_for_fatal');


/*******   CALCULATE SEVERAL REQUIRED CONSTANTS, MAINLY ONES FOR PATH AND URL INFORMATION  ********/

/**
 * If the execution mode is as a script, most web-based constants are simply null.
 * This section sets up the following constants:
 * SERVERNAME
 * SERVERNAME_NOSSL
 * SERVERNAME_SSL
 * ROOT_URL
 * ROOT_URL_NOSSL
 * ROOT_URL_SSL
 * CUR_CALL
 */
if (EXEC_MODE == 'CLI') {
	$servername          = null;
	$servernameSSL       = null;
	$servernameNOSSL     = null;
	$rooturl             = isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : null;
	$rooturlNOSSL        = $rooturl;
	$rooturlSSL          = $rooturl;
	$curcall             = null;
	$relativerequestpath = null;
	$ssl                 = false;
	$sslmode             = 'disabled';
	$tmpdir              = $core_settings['tmp_dir_cli'];
	$host                = 'localhost';
	// Check if this user has a .gnupg directory in the home directory.
	// This is because when the user runs a script, (ie: packager or create_repo),
	// it should use his/her private key, (which is not accesable from the website).
	if (isset($_SERVER['HOME']) && is_dir($_SERVER['HOME'] . '/.gnupg')) $gnupgdir = $_SERVER['HOME'] . '/.gnupg/';
	else $gnupgdir = false;

	// CLI mode shouldn't have HTML error reporting.
	ini_set('html_errors', 0);
}
else {
	/**
	 * Full URL of server.
	 * ie: http://www.example.com or https://127.0.0.1:8443
	 */
	if (isset ($_SERVER ['HTTPS'])) $servername = "https://";
	else $servername = "http://";

	if ($core_settings['site_url'] != '') $servername .= $core_settings['site_url'];
	else $servername .= $_SERVER['HTTP_HOST'];

	// First things are first... if site_url is set, it's expected that THAT should
	//  be the only valid URL to use.  If I wait until post-rendering, bad things
	//  can happen.
	if ($core_settings['site_url'] != '' && $_SERVER['HTTP_HOST'] != $core_settings['site_url']) {
		$newURL = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $core_settings['site_url'] . $_SERVER['REQUEST_URI'];
		header('HTTP/1.1 301 Moved Permanently'); // 301 transfers page rank.
		header("Location:" . $newURL);
		die("If your browser does not refresh, please <a href=\"{$newURL}\">Click Here</a>");
	}

	$host = $_SERVER['HTTP_HOST'];

	/**
	 * Full URL of server in non-SSL mode.
	 * ie: http://www.example.com:80 or http://localhost:880
	 */
	// Create the server name with no SSL.  This can be used to go from an SSL page to a regular page.
	$servernameNOSSL = str_replace('https://', 'http://', $servername);
	// Check the last several digits of the serverName to see if there's a port number.
	if (preg_match('/\:\d+$/', substr($servernameNOSSL, -6))) {
		$servernameNOSSL = preg_replace('/\:\d+$/', ':' . PORT_NUMBER, $servernameNOSSL);
	}
	else {
		$servernameNOSSL .= ':' . PORT_NUMBER;
	}
	// Default port number?
	if (PORT_NUMBER == 80) {
		$servernameNOSSL = str_replace(':80', '', $servernameNOSSL);
	}

	/**
	 * Determine how to handle the SSL settings.  This is required because pre 2.2.1, this was a simple boolean.
	 * After, it has mutliple options.
	 */
	if(defined('ENABLE_SSL')){
		// < 2.2.1 configuration

		if(ENABLE_SSL){
			$sslmode = 'ondemand';
		}
		else{
			$sslmode = 'disabled';
		}

		// Now define the constant.
		define('SSL_MODE', $sslmode);
	}
	elseif(defined('SSL_MODE')){
		// >= 2.2.1 configuration

		if(SSL_MODE == 'disabled') $enablessl = false;
		else $enablessl = true;

		// Now define the constant
		define('ENABLE_SSL', $enablessl);
	}
	else{
		// Umm.... what?

		define('SSL_MODE', 'disabled');
		define('ENABLE_SSL', false);
	}


	/**
	 * Full URL of server in SSL mode.
	 * ie: https://www.example.com:443 or https://localhost:8443
	 *
	 * (defaults back to SERVERNAME_NOSSL if ENABLE_SSL is disabled).
	 */
	if (ENABLE_SSL) {
		// Create the server name for SSL connections.  This should override any previous port number.
		$servernameSSL = str_replace('http://', 'https://', $servername);
		// Check the last several digits of the serverName to see if there's a port number.
		if (preg_match('/\:\d+$/', substr($servernameSSL, -6))) {
			$servernameSSL = preg_replace('/\:\d+$/', ':' . PORT_NUMBER_SSL, $servernameSSL);
		}
		else {
			$servernameSSL .= ':' . PORT_NUMBER_SSL;
		}

		// Default port number?  If so I can just drop that part.
		if (PORT_NUMBER_SSL == 443) {
			$servernameSSL = str_replace(':443', '', $servernameSSL);
		}
	}
	else {
		$servernameSSL = $servernameNOSSL;
	}

	$rooturl             = $servername . ROOT_WDIR;
	$rooturlNOSSL        = $servernameNOSSL . ROOT_WDIR;
	$rooturlSSL          = $servernameSSL . ROOT_WDIR;
	$curcall             = $servername . $_SERVER['REQUEST_URI'];
	$relativerequestpath = strtolower('/' . substr($_SERVER['REQUEST_URI'], strlen(ROOT_WDIR)));
	if (strpos($relativerequestpath, '?') !== false) $relativerequestpath = substr($relativerequestpath, 0, strpos($relativerequestpath, '?'));


	/*

	X-Forwarded-For
	a de facto standard for identifying the originating IP address of a client connecting to a web server through an HTTP proxy or load balancer

	X-Forwarded-For: client1, proxy1, proxy2
	X-Forwarded-For: 129.78.138.66, 129.78.64.103

	X-Forwarded-Host
	a de facto standard for identifying the original host requested by the client in the Host HTTP request header,
	since the host name and/or port of the reverse proxy (load balancer) may differ from the origin server handling the request.

	X-Forwarded-Host: en.wikipedia.org:80
	X-Forwarded-Host: en.wikipedia.org
	*/

	// @todo Implement support for trusted proxy IP addresses!
	$ssl = (
		// Standard header provided 99% of the time.
		(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ||

		// a de facto standard for identifying the originating protocol of an HTTP request,
		// since a reverse proxy (load balancer) may communicate with a web server using HTTP even if the request to the reverse proxy is HTTPS.
		(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') ||

		// Non-standard header field used by Microsoft applications and load-balancers
		(isset($_SERVER['FRONT_END_HTTPS']) && $_SERVER['FRONT_END_HTTPS'] == 'on') ||
		(isset($_SERVER['HTTP_HTTPS']) && $_SERVER['HTTP_HTTPS'] == 'on')
	);

	$tmpdir = $core_settings['tmp_dir_web'];

	$gnupgdir = false;
}

/**
 * Full URL of server.
 * eg: http://www.example.com or https://127.0.0.1:8443
 */
define('SERVERNAME', $servername);
/**
 * Full URL of the server forced non-ssl mode.
 * eg: http://www.example.com
 */
define('SERVERNAME_NOSSL', $servernameNOSSL);
/**
 * Full URL of the server forced SSL mode.
 * eg: https://www.example.com or https://127.0.0.1:8443
 */
define('SERVERNAME_SSL', $servernameSSL);
/**
 * URL of web root.
 * eg: http://www.example.com/foo/man/choo/
 */
define('ROOT_URL', $rooturl);
/**
 * URL of web root.
 * eg: http://www.example.com/foo/man/choo/
 */
define('ROOT_URL_NOSSL', $rooturlNOSSL);
/**
 * URL of web root.
 * eg: https://www.example.com/foo/man/choo/
 */
define('ROOT_URL_SSL', $rooturlSSL);
/**
 * Current call/request.
 * eg: /foo/man/choo/?somevariable=true&somethingelse=false
 */
define('CUR_CALL', $curcall);

/**
 * Relative requested path.
 * eg: /User/Login or '/' for the index.
 */
define('REL_REQUEST_PATH', $relativerequestpath);

/**
 * Simple true/false if current page call is via SSL.
 */
define('SSL', $ssl);

/**
 * SSL Mode for SSL being disabled completely
 */
define('SSL_MODE_DISABLED', 'disabled');
/**
 * SSL is allowed on pages that require it only, (standard pages redirect to non-ssl)
 */
define('SSL_MODE_ONDEMAND', 'ondemand');
/**
 * SSL is allowed on any page throughout the site
 */
define('SSL_MODE_ALLOWED',  'allowed');
/**
 * SSL is always required for all pages
 */
define('SSL_MODE_REQUIRED', 'required');


if(!defined('TMP_DIR')) {
	/**
	 * Temporary directory
	 */
	define('TMP_DIR', $tmpdir);
}

/**
 * Temporary directory for web only
 * (useful in the packager)
 */
define('TMP_DIR_WEB', $core_settings['tmp_dir_web']);

/**
 * Temporary directory for cli only
 * (useful in the packager)
 */
define('TMP_DIR_CLI', $core_settings['tmp_dir_cli']);

/**
 * Host is a more human-friendly version of SERVERNAME.
 * It does not include port number or protocol, but just the hostname itself.
 * eg: domain.tld
 */
define('HOST', $host);

// The TMP_DIR needs to be writable!
if (!is_dir(TMP_DIR)) {
	mkdir(TMP_DIR, 0777, true);
}
//var_dump(ENABLE_SSL, SSL_MODE, SSL, ROOT_URL_SSL . substr(REL_REQUEST_PATH, 1)); die();

// Is this site configured to require SSL mode?  If so, might as well require that here.
if(EXEC_MODE == 'WEB' && SSL_MODE == SSL_MODE_REQUIRED && !SSL){
	// Skip the 301 when not in production because it makes things rather annoying to debug at times...
	// ie: the browser remembers it was a permanent redirect and doesn't even try the nonSSL version.
	if(!DEVELOPMENT_MODE) header("HTTP/1.1 301 Moved Permanently");
	header('Location: ' . ROOT_URL_SSL . substr(REL_REQUEST_PATH, 1));
	die('This site requires SSL, if it does not redirect you automatically, please <a href="' . ROOT_URL_SSL . substr(REL_REQUEST_PATH, 1) . '">Click Here</a>.');
}
elseif(EXEC_MODE == 'WEB' && SSL_MODE == SSL_MODE_DISABLED && SSL){
	// Skip the 301 when not in production because it makes things rather annoying to debug at times...
	// ie: the browser remembers it was a permanent redirect and doesn't even try the nonSSL version.
	if(!DEVELOPMENT_MODE) header("HTTP/1.1 301 Moved Permanently");
	header('Location: ' . ROOT_URL_NOSSL . substr(REL_REQUEST_PATH, 1));
	die('This site has SSL disabled, if it does not redirect you automatically, please <a href="' . ROOT_URL_NOSSL . substr(REL_REQUEST_PATH, 1) . '">Click Here</a>.');
}


// If there is a "lock.message" file, open that and stop page execution immediately.
// This is useful for automatic upgrades.
if(file_exists(TMP_DIR . 'lock.message')){

	$logo = "data:image/png;base64,
			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";

	$html  = "<!DOCTYPE html>
				<html>
					<head>
						<style>
							body{text-align:center;background:#eee;}
							.site-logo{margin-top:2em;}
							p{padding:1em;color:#555;font-family:sans-serif;}
						</style>
					</head>
					<body>
						<p><img class='site-logo' src='".$logo."'/></p>";

	$contents = file_get_contents(TMP_DIR . 'lock.message');

	$adminmsg = '(Site is currently locked via ' . TMP_DIR . 'lock.message.  If this is in error, simply remove that file).';
	if(DEVELOPMENT_MODE){
		$html .= "<p>" .$adminmsg . "</p>";
	}
	error_log($adminmsg);

	$html .= "<p>" . $contents . "</p>";
	$html .= "		</body>
				</html>";

	die($html);
}


// (handled by the installer now)
/*
// The TMP_DIR needs to be writable!
if(!is_dir(TMP_DIR)){
	$ds = explode('/', TMP_DIR);
	$d = '';
	foreach($ds as $dir){
		if($dir == '') continue;
		$d .= '/' . $dir;
		if(!is_dir($d)) mkdir($d) or die("Please ensure that " . TMP_DIR . " is writable.");
	}
}
*/

/**
 * The GnuPG home directory to store keys in.
 */
if (!defined('GPG_HOMEDIR')) {
	define('GPG_HOMEDIR', ($gnupgdir) ? $gnupgdir : ROOT_PDIR . 'gnupg');
}
// PECL expects this variable to be set, so set it!
putenv('GNUPGHOME=' . GPG_HOMEDIR);

if(!defined('XHPROF')){
	define('XHPROF', 0);
}

$profilingEnabled = (XHPROF == 100 || (XHPROF > rand(1,100)));

if(function_exists('xhprof_enable')){
	define('ENABLE_XHPROF', $profilingEnabled);
	if($profilingEnabled){
		xhprof_enable(XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);
	}
}
elseif(function_exists('xdebug_start_trace')){
	// Use xdebug instead!
	ini_set('xdebug.profiler_enable_trigger', $profilingEnabled ? 1 : 0);
}

// Cleanup!
unset(
	$enablessl, $servername, $servernameNOSSL, $servernameSSL, $rooturl, $rooturlNOSSL,
	$rooturlSSL, $curcall, $ssl, $gnupgdir, $host, $sslmode, $tmpdir, $relativerequestpath,
	$core_settings
);
$maindefines_time = microtime(true);


// Now the core of the application, config handler, and all necessary core
//  settings should be available.


/**************************  START EXECUTION *****************************/

\Core\Utilities\Profiler\Profiler::GetDefaultProfiler()->record('Core Plus bootstrapped and application starting');

// Datamodel, GOGO!
//require_once(ROOT_PDIR . 'core/libs/core/datamodel/DMI.class.php');
try {
	$dbconn = DMI::GetSystemDMI();
	ConfigHandler::_DBReadyHook();
}
	// This catch statement should be hit anytime the database is not available,
	// core table doesn't exist, or the like.
catch (Exception $e) {
	error_log($e->getMessage());
	// Couldn't establish connection... do something fun!
	// If it's in development mode, redirect back to the installer, which should hopefully
	// get whatever problem this was fixed.
	if (DEVELOPMENT_MODE) {
		//header('HTTP/1.1 302 Moved Temporarily');
		//header('Location: ' . ROOT_WDIR . 'install');
		die('Please <a href="' . ROOT_WDIR . 'install' . '">install Core Plus.</a>');
	}

	else {
		require(ROOT_PDIR . 'core/templates/halt_pages/fatal_error.inc.html');
		die();
	}
}
\Core\Utilities\Profiler\Profiler::GetDefaultProfiler()->record('Core Plus Data Model Interface loaded and ready');


unset($start_time, $predefines_time, $preincludes_time, $maindefines_time, $dbconn);




// < 2.5.0 Hack
// This is to provide support with < 2.5.0 configuration.xml files.
// Many of the CDN and FTP configuration options have been moved into the root configuration.xml file
// so that it's better supported in the installer.
if(!defined('FTP_USERNAME')){
	define('FTP_USERNAME', ConfigHandler::Get('/core/ftp/username'));
}
if(!defined('FTP_PASSWORD')){
	define('FTP_PASSWORD', ConfigHandler::Get('/core/ftp/password'));
}
if(!defined('FTP_PATH')){
	define('FTP_PATH', ConfigHandler::Get('/core/ftp/path'));
}
if(!defined('CDN_TYPE')){
	define('CDN_TYPE', ConfigHandler::Get('/core/filestore/backend'));
}
if(!defined('CDN_LOCAL_ASSETDIR')){
	error_log('Please define the CDN_LOCAL_ASSETDIR in your config.xml file!  This has been migrated from the web config.', E_USER_DEPRECATED);
	define('CDN_LOCAL_ASSETDIR', ConfigHandler::Get('/core/filestore/assetdir'));
}
if(!defined('CDN_LOCAL_PUBLICDIR')){
	error_log('Please define the CDN_LOCAL_PUBLICDIR in your config.xml file!  This has been migrated from the web config.', E_USER_DEPRECATED);
	define('CDN_LOCAL_PUBLICDIR', ConfigHandler::Get('/core/filestore/publicdir'));
}
if(!defined('CDN_LOCAL_PRIVATEDIR')){
	error_log('Please define the CDN_LOCAL_PRIVATEDIR in your config.xml file!  This has been migrated from the web config.', E_USER_DEPRECATED);
	define('CDN_LOCAL_PRIVATEDIR', 'files/private');
}


// Let the core override the server's timezone as well.
// In the case that the timezone isn't set, this will prevent the "strftime unsafe" error.
date_default_timezone_set(TIME_DEFAULT_TIMEZONE);



/*
 * This is all done from within the component handler now.
Core::_LoadFromDatabase();

// Does the core require an update?
if(Core::GetComponent()->needsUpdated()){
	// w00t for silent upgrades!
	Core::GetComponent()->upgrade();
}
Core::AddProfileTime('core_ready');
*/


/**
 * Load all the components
 */
Core::LoadComponents();

// Now I can session_start everything.
// Sessions are always useful for web apps :p
if (EXEC_MODE == 'WEB') {
	try {
		// Start loading the session.
		// If this fails, I can always drop back to the installer, (since it probably isn't installed correctly).
	}
	catch (DMI_Exception $e) {
		// There was a DMI exception... it may not have been installed.
		// Reload to the install page and let that take care.
		if (DEVELOPMENT_MODE) {
			//header('HTTP/1.1 302 Moved Temporarily');
			//header('Location: ' . ROOT_WDIR . 'install');
			die("Please <a href=\"{$newURL}\">install Core Plus.</a>");
		}
		else {
			require(ROOT_PDIR . 'core/templates/halt_pages/fatal_error.inc.html');
			die();
		}
	}
}


//require_once(ROOT_PDIR . 'core/libs/core/ComponentHandler.class.php');
//ComponentHandler::Singleton();

// Load all the themes on the system.
//require_once(ROOT_PDIR . 'core/libs/core/ThemeHandler.class.php');
//ThemeHandler::Load();

HookHandler::DispatchHook('/core/components/loaded');
$profiler->record('Components Load Complete');

/**
 * All the post includes, these are here for performance reasons, (they can get compiled into the compiled bootstrap)
 */
require_once(__DIR__ . '/bootstrap_postincludes.php');


// If the geo-location libraries are available, load the user's location!
if(Core::IsComponentAvailable('geographic-codes') && class_exists('GeoIp2\\Database\\Reader')){
	try{
		if(REMOTE_IP == '127.0.0.1'){
			// Load local connections up with Columbus, OH.
			// Why?  ;)
			$geocity     = 'Columbus';
			$geoprovince = 'OH';
			$geocountry  = 'US';
			$geotimezone = 'America/New_York';
			$geopostal   = '43215';
		}
		else{
			$reader = new GeoIp2\Database\Reader(ROOT_PDIR . 'components/geographic-codes/libs/maxmind-geolite-db/GeoLite2-City.mmdb');
			$profiler->record('Initialized GeoLite Database');

			$geo = $reader->cityIspOrg(REMOTE_IP);
			//$geo = $reader->cityIspOrg('67.149.214.236');
			$profiler->record('Read GeoLite Database');

			$reader->close();
			$profiler->record('Closed GeoLite Database');

			$geocity = $geo->city->name;
			// Some IP addresses do not resolve as a valid province.
			//This tends to happen with privately owned networks.
			if(isset($geo->subdivisions[0]) && $geo->subdivisions[0] !== null){
				/** @var GeoIp2\Record\Subdivision $geoprovinceobj */
				$geoprovinceobj = $geo->subdivisions[0];
				$geoprovince = $geoprovinceobj->isoCode;
			}
			else{
				$geoprovince = '';
			}

			$geocountry  = $geo->country->isoCode;
			$geotimezone = $geo->location->timeZone;
			$geopostal   = $geo->postal->code;

			// Memory cleanup
			unset($geoprovinceobj, $geo, $reader);
		}
	}
	catch(Exception $e){
		// Well, we tried!  Load something at least.
		$geocity     = 'McMurdo Base';
		$geoprovince = '';
		$geocountry  = 'AQ'; // Yes, AQ is Antarctica!
		$geotimezone = 'CAST';
		$geopostal   = null;
	}
}
else{
	// Well, we tried!  Load something at least.
	$geocity     = 'McMurdo Base';
	$geoprovince = '';
	$geocountry  = 'AQ'; // Yes, AQ is Antarctica!
	$geotimezone = 'CAST';
	$geopostal   = null;
}

// And define these.

/**
 * The city of the remote user
 * eg: "Columbus", "", "New York", etc.
 */
define('REMOTE_CITY', $geocity);

/**
 * The province or state ISO code of the remote user
 * eg: "OH", "IN", etc.
 */
define('REMOTE_PROVINCE', $geoprovince);

/**
 * The country ISO code of the remote user
 * eg: "US", "DE", "AQ", etc.
 */
define('REMOTE_COUNTRY', $geocountry);

/**
 * The timezone of the remote user
 * eg: "America/New_York", etc.
 */
define('REMOTE_TIMEZONE', $geotimezone);

/**
 * The postal code of the remote user
 * eg: "43215"
 * Note, this define CAN be NULL if the IP does not resolve to a valid address
 */
define('REMOTE_POSTAL', $geopostal);

// And cleanup the geo information
unset($geocity, $geoprovince, $geocountry, $geotimezone, $geopostal);



HookHandler::DispatchHook('/core/components/ready');


// And we don't need the profiler object anymore.
unset($profiler);