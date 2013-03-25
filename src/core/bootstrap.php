<?php
/**
 * Core bootstrap file that kicks off the entire application
 *
 * This file is the core of the application; it's responsible for setting up
 *  all the necessary paths, settings and includes.
 *
 * @package Core Plus\Core
 * @since 0.1
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
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
// This will be saved into the Core once that's available.
$start_time = microtime(true);

// gogo i18n!
mb_internal_encoding('UTF-8');

/********************* Initial system defines *********************************/
require_once(__DIR__ . '/bootstrap_predefines.php');

$predefines_time = microtime(true);


/********************** Critical file inclusions ******************************/

require_once(__DIR__ . '/bootstrap_preincludes.php');


// __TODO__ Make this errorHandler accept 'hooks' to be fired when a critical error is occured.
// This can include rendering an HTML file to the browser, or some other action.
//error_reporting ( E_ALL ) ;
//require_once("core/classes/ErrorHandler.class.php");


// Load the hook handler, which will allow cross-library/module communication abstractly.
Debug::Write('Loading hook handler');
require_once(ROOT_PDIR . "core/libs/core/HookHandler.class.php");


// Pre includes are ready.
$preincludes_time = microtime(true);

// And start the core!
Debug::Write('Loading core system');
//require_once(ROOT_PDIR . 'core/libs/core/InstallTask.class.php');
require_once(ROOT_PDIR . 'core/libs/core/Core.class.php');
//Core::Singleton();


// Configuration handler, for loading any config variable/constant from XML data or the database.
Debug::Write('Loading configs');
require_once(ROOT_PDIR . "core/libs/core/ConfigHandler.class.php");
ConfigHandler::Singleton();


// Give me core settings!
// This will do the defines for the site, and provide any core variables to get started.
$core_settings = ConfigHandler::LoadConfigFile("configuration");

if (!$core_settings) {
	if(EXEC_MODE == 'WEB'){
		$newURL = 'install/';
		header('HTTP/1.1 302 Moved Temporarily');
		header("Location:" . $newURL);
		die("If your browser does not refresh, please <a href=\"{$newURL}\">Click Here</a>");
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
	$rooturl             = null;
	$rooturlNOSSL        = null;
	$rooturlSSL          = null;
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
	else $servername .= $_SERVER ['HTTP_HOST'];

	// First things are first... if site_url is set, it's expected that THAT should
	//  be the only valid URL to use.  If I wait until post-rendering, bad things
	//  can happen.
	if ($core_settings['site_url'] != '' && $_SERVER['HTTP_HOST'] != $core_settings['site_url']) {
		$newURL = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $core_settings['site_url'] . $_SERVER['REQUEST_URI'];
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
	$ssl = (isset($_SERVER['HTTPS']));

	$tmpdir = $core_settings['tmp_dir_web'];

	$gnupgdir = false;
}

/**
 * Full URL of server.
 * ie: http://www.example.com or https://127.0.0.1:8443
 */
define('SERVERNAME', $servername);
/**
 * Full URL of the server forced non-ssl mode.
 * ie: http://www.example.com
 */
define('SERVERNAME_NOSSL', $servernameNOSSL);
/**
 * Full URL of the server forced SSL mode.
 * ie: https://www.example.com or https://127.0.0.1:8443
 */
define('SERVERNAME_SSL', $servernameSSL);
/**
 * URL of web root.
 * ie: http://www.example.com/foo/man/choo/
 */
define('ROOT_URL', $rooturl);
/**
 * URL of web root.
 * ie: http://www.example.com/foo/man/choo/
 */
define('ROOT_URL_NOSSL', $rooturlNOSSL);
/**
 * URL of web root.
 * ie: https://www.example.com/foo/man/choo/
 */
define('ROOT_URL_SSL', $rooturlSSL);
/**
 * Current call/request.
 * ie: /foo/man/choo/?somevariable=true&somethingelse=false
 */
define('CUR_CALL', $curcall);

/**
 * Relative requested path.
 * ie: /User/Login or '/' for the index.
 */
define('REL_REQUEST_PATH', $relativerequestpath);

/**
 * Simple true/false if current page call is via SSL.
 * @var boolean
 */
define('SSL', $ssl);

/**
 * SSL Mode for SSL being disabled completely
 * @var string
 */
define('SSL_MODE_DISABLED', 'disabled');
/**
 * SSL is allowed on pages that require it only, (standard pages redirect to non-ssl)
 * @var string
 */
define('SSL_MODE_ONDEMAND', 'ondemand');
/**
 * SSL is allowed on any page throughout the site
 * @var string
 */
define('SSL_MODE_ALLOWED',  'allowed');
/**
 * SSL is always required for all pages
 * @var string
 */
define('SSL_MODE_REQUIRED', 'required');

/**
 * Temp directory
 * @var string
 */
define('TMP_DIR', $tmpdir);

/**
 * Temporary directory for web only
 * (useful in the packager)
 * @var string
 */
define('TMP_DIR_WEB', $core_settings['tmp_dir_web']);

/**
 * Temporary directory for cli only
 * (useful in the packager)
 * @var string
 */
define('TMP_DIR_CLI', $core_settings['tmp_dir_cli']);

/**
 * Host is a more human-friendly version of SERVERNAME.
 * It does not include port number or protocol, but just the hostname itself.
 * @var string
 */
define('HOST', $host);

// The TMP_DIR needs to be writable!
if (!is_dir(TMP_DIR)) {
	mkdir(TMP_DIR, 0777, true);
}
//var_dump(ENABLE_SSL, SSL_MODE, SSL, ROOT_URL_SSL . substr(REL_REQUEST_PATH, 1)); die();

// Is this site configured to require SSL mode?  If so, might as well require that here.
if(SSL_MODE == SSL_MODE_REQUIRED && !SSL){
	// Skip the 301 when not in production because it makes things rather annoying to debug at times...
	// ie: the browser remembers it was a permanent redirect and doesn't even try the nonSSL version.
	if(!DEVELOPMENT_MODE) header("HTTP/1.1 301 Moved Permanently");
	header('Location: ' . ROOT_URL_SSL . substr(REL_REQUEST_PATH, 1));
	die('This site requires SSL, if it does not redirect you automatically, please <a href="' . ROOT_URL_SSL . substr(REL_REQUEST_PATH, 1) . '">Click Here</a>.');
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

// Cleanup!
unset($servername, $servernameNOSSL, $servernameSSL, $rooturl, $rooturlNOSSL, $rooturlSSL, $curcall, $ssl);
$maindefines_time = microtime(true);


// Now the core of the application, config handler, and all necessary core
//  settings should be available.


/**************************  START EXECUTION *****************************/

// Record the times thus far
Core::AddProfileTime('application_start', $start_time);
Core::AddProfileTime('predefines_complete', $predefines_time);
Core::AddProfileTime('preincludes_complete', $preincludes_time);
Core::AddProfileTime('maindefines_complete', $maindefines_time);

// Datamodel, GOGO!
require_once(ROOT_PDIR . 'core/libs/datamodel/DMI.class.php');
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
		header('HTTP/1.1 302 Moved Temporarily');
		header('Location: ' . ROOT_WDIR . 'install');
		die();
	}

	else {
		require(ROOT_PDIR . 'core/templates/halt_pages/fatal_error.inc.html');
		die();
	}
}


unset($start_time, $predefines_time, $preincludes_time, $maindefines_time);




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
	define('CDN_LOCAL_ASSETDIR', ConfigHandler::Get('/core/filestore/assetdir'));
}
if(!defined('CDN_LOCAL_PUBLICDIR')){
	define('CDN_LOCAL_PUBLICDIR', ConfigHandler::Get('/core/filestore/publicdir'));
}



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

// Give me some other useful core systems.
if (EXEC_MODE == 'WEB') {
	try {
		// Sessions are always useful for web apps
		require_once(ROOT_PDIR . 'core/libs/core/Session.class.php');
	}
	catch (DMI_Exception $e) {
		// There was a DMI exception... it may not have been installed.
		// Reload to the install page and let that take care.
		if (DEVELOPMENT_MODE) {
			header('HTTP/1.1 302 Moved Temporarily');
			header('Location: ' . ROOT_WDIR . 'install');
			die();
		}
		else {
            require(ROOT_PDIR . 'core/libs/fatal_errors/database.php');
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
Core::AddProfileTime('components_load_complete');

/**
 * All the post includes, these are here for performance reasons, (they can get compiled into the compiled bootstrap)
 */
require_once(__DIR__ . '/bootstrap_postincludes.php');

HookHandler::DispatchHook('/core/components/ready');
Core::AddProfileTime('components_ready_complete');
