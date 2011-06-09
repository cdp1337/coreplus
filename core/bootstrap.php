<?php
/**
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 * 
 * Copyright (C) 2009  Charlie Powell <powellc@powelltechs.com>
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
 * Welcome to the CAE2 Bootstrapper!
 * 
 * This file is the core of the application; it's responsible for setting up
 *  all the necessary paths, settings and includes.
 */



/********************* Pre-instantiation system checks ************************/

// The bootstrap cannot be called directly.
if(basename($_SERVER['SCRIPT_NAME']) == 'bootstrap.php') die('You cannot call that file directly.');


// I expect some configuration options....
if(PHP_VERSION < '6.0.0' && ini_get('magic_quotes_gpc')){
	die('This application cannot run with magic_quotes_gpc enabled, please disable them now!');
}

if(PHP_VERSION < '5.3.0'){
	die('This application requires at least PHP 5.3 to run!');
}

// Start a timer for performance tuning purposes.
// This will be saved into the Core once that's available.
$start_time = microtime(true);



/********************* Initial system defines *********************************/
require_once('core/bootstrap_predefines.php');

$predefines_time = microtime(true);



/********************** Critical file inclusions ******************************/

// These are generally files required for getting the rest of the system loadable.
require_once(ROOT_PDIR . 'core/libs/core/Debug.class.php');
require_once(ROOT_PDIR . "core/libs/core/ISingleton.interface.php");
//require_once("core/classes/IDatabaseClass.interface.php");
require_once(ROOT_PDIR . 'core/libs/core/XMLLoader.class.php');
//require_once(ROOT_PDIR . 'core/libs/core/JSLibrary.class.php');
require_once(ROOT_PDIR . 'core/libs/core/SQLBuilder.class.php');
require_once(ROOT_PDIR . 'core/libs/core/InstallArchive.class.php');
require_once(ROOT_PDIR . 'core/libs/core/InstallArchiveAPI.class.php');
require_once(ROOT_PDIR . 'core/libs/core/Component.class.php');
// Many of these are needed because some systems, such as the installer
// execute before the ComponentHandler has loaded the class locations.
require_once(ROOT_PDIR . 'core/libs/core/ComponentHandler.class.php');
require_once(ROOT_PDIR . 'core/libs/cachecore/icachecore.interface.php');
require_once(ROOT_PDIR . 'core/libs/cachecore/cachecore.class.php');
require_once(ROOT_PDIR . 'core/libs/cachecore/cachefile.class.php');
require_once(ROOT_PDIR . 'core/libs/core/Cache.class.php');

// The PHP elements of the MVC framework.
require_once(ROOT_PDIR . 'core/libs/core/Model.class.php');
require_once(ROOT_PDIR . 'core/libs/core/Controller.class.php');

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
require_once(ROOT_PDIR . 'core/libs/core/InstallTask.class.php');
require_once(ROOT_PDIR . 'core/libs/core/Core.class.php');
//Core::Singleton();


// Configuration handler, for loading any config variable/constant from XML data or the database.
Debug::Write('Loading configs');
require_once(ROOT_PDIR . "core/libs/core/ConfigHandler.class.php");
ConfigHandler::Singleton();


// Give me core settings!
// This will do the defines for the site, and provide any core variables to get started.
$core_settings = ConfigHandler::LoadConfigFile("core");


/**
 * If the site is not in "development mode", force errors to be hidden.
 * This is useful to override any common server settings.
 * 
 * (php default is to display them after all...)
 */ 
if(!DEVELOPMENT_MODE){
	error_reporting(0);
	ini_set('display_errors', 0);
}



// Site not configured yet?
// This config-based constant will be set automatically after an installation to mark it's done.
if(!SITE_CONFIGURED){
	die('This site has not been configured yet.  If you are the administrator, please edit the XML files in "config" and be sure to set "SITE_CONFIGURED" to true when done.');
	//header('Location: install/');
	//die('If your browser does not refresh, please <a href="install.php">Click Here</a>');
}

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
if(EXEC_MODE == 'CLI'){
	$servername = null;
	$servernameSSL = null;
	$servernameNOSSL = null;
	$rooturl = null;
	$rooturlNOSSL = null;
	$rooturlSSL = null;
	$curcall = null;
}
else{
	/**
	 * Full URL of server.
	 * ie: http://www.example.com or https://127.0.0.1:8443
	 */
	if (isset ( $_SERVER [ 'HTTPS' ] )) $servername = "https://" ;
	else $servername = "http://" ;
	
	if($core_settings['site_url'] != '') $servername .= $core_settings['site_url'];
	else $servername .= $_SERVER [ 'HTTP_HOST' ];
	
	// First things are first... if site_url is set, it's expected that THAT should
	//  be the only valid URL to use.  If I wait until post-rendering, bad things
	//  can happen.
	if($core_settings['site_url'] != '' && $_SERVER['HTTP_HOST'] != $core_settings['site_url']){
	    $newURL = (isset($_SERVER['HTTPS'])? 'https://' : 'http://') . $core_settings['site_url'] . $_SERVER['REQUEST_URI'];
	    header("Location:" . $newURL);
	    die("If your browser does not refresh, please <a href=\"{$newURL}\">Click Here</a>");
	}
	
	/**
	 * Full URL of server in non-SSL mode.
	 * ie: http://www.example.com:80 or http://localhost:880
	 */
	// Create the server name with no SSL.  This can be used to go from an SSL page to a regular page.
	$servernameNOSSL = str_replace('https://', 'http://', $servername);
	// Check the last several digits of the serverName to see if there's a port number.
	if(preg_match('/\:\d+$/', substr($servernameNOSSL, -6))){
		$servernameNOSSL = preg_replace('/\:\d+$/', ':' . PORT_NUMBER, $servernameNOSSL);
	}
	else{
		$servernameNOSSL .= ':' . PORT_NUMBER;
	}
	// Default port number?
	if(PORT_NUMBER == 80){
		$servernameNOSSL = str_replace(':80', '', $servernameNOSSL);
	}
	
	
	/**
	 * Full URL of server in SSL mode.
	 * ie: https://www.example.com:443 or https://localhost:8443
	 * 
	 * (defaults back to SERVERNAME_NOSSL if ENABLE_SSL is disabled).
	 */
	if(ENABLE_SSL){
		// Create the server name for SSL connections.  This should override any previous port number.
		$servernameSSL = str_replace('http://', 'https://', SERVERNAME);
		// Check the last several digits of the serverName to see if there's a port number.
		if(preg_match('/\:\d+$/', substr($servernameSSL, -6))){
			$servernameSSL = preg_replace('/\:\d+$/', ':' . PORT_NUMBER_SSL, $servernameSSL);
		}
		else{
			$servernameSSL .= ':' . PORT_NUMBER_SSL;
		}
		// Default port number?
		if(PORT_NUMBER_SSL == 443){
			$servernameSSL = str_replace(':443', '', $servernameSSL);
		}
	}
	else{
		$servernameSSL = $servernameNOSSL;
	}
	
	$rooturl = $servername . ROOT_WDIR;
	$rooturlNOSSL = $servernameNOSSL . ROOT_WDIR;
	$rooturlSSL = $servername . ROOT_WDIR;
	$curcall = $rooturl . '?' . $_SERVER['QUERY_STRING'];
}

/**
 * Full URL of server.
 * ie: http://www.example.com or https://127.0.0.1:8443
 */
define ( 'SERVERNAME', $servername );
/**
 * Full URL of the server forced non-ssl mode.
 * ie: http://www.example.com
 */
define ( 'SERVERNAME_NOSSL', $servernameNOSSL );
/**
 * Full URL of the server forced SSL mode.
 * ie: https://www.example.com or https://127.0.0.1:8443
 */
define ( 'SERVERNAME_SSL', $servernameSSL );
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

// Cleanup!
unset($servername, $servernameNOSSL, $servernameSSL, $rooturl, $rooturlNOSSL, $rooturlSSL, $curcall);
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
try{
	$dbconn = DMI::GetSystemDMI();
}
catch(Exception $e){
	// Couldn't establish connection... do something fun!
	if(DEVELOPMENT_MODE){
		header('Location: ' . ROOT_WDIR . 'install');
		die();
	}
	else{
		require(ROOT_PDIR . 'core/fatal_error.inc.html');
		die();
	}
}


HookHandler::DispatchHook('db_ready');
unset($start_time, $predefines_time, $preincludes_time, $maindefines_time);
Core::_LoadFromDatabase();

// Does the core require an update?
if(Core::GetComponent()->needsUpdated()){
	// w00t for silent upgrades!
	Core::GetComponent()->upgrade();
}
Core::AddProfileTime('core_ready');


// Give me some other useful core systems.
require_once(ROOT_PDIR . 'core/libs/core/Time.class.php');
if(EXEC_MODE == 'WEB'){
	// Sessions are always useful for web apps
	require_once(ROOT_PDIR . 'core/libs/core/Session.class.php');
	session_start();
}



/*********   LIBRARY BOOTSTRAPPER CLASS, WILL INCLUDE EVERY LIBRARY AND ENSURE DEPENDENCIES, ETC  ***********/

//Debug::Write('Loading library handler');
//require_once("core/classes/LibraryHandler.class.php");
//LibraryHandler::singleton();



/* Send this hook to notify any library that every other library has been loaded
 *  into memory; This should be the instantiation event for any library that does 
 *  not need other libraries loaded.
 */
//HookHandler::DispatchHook('libraries_loaded');
// Some libraries may like to know when the other libraries are actually ready, send this off now.
//HookHandler::DispatchHook('libraries_ready');
//Core::AddProfileTime('library_load_complete');


// Load in the ViewClass object, will help with the connection of page requests and the classes behind them.
//require_once("core/classes/ViewClass.class.php");



// Load all the themes on the system.
require_once(ROOT_PDIR . 'core/libs/core/ThemeHandler.class.php');
ThemeHandler::Load();


/**
 * Load all the components, these are hybrid library/modules, created for convenience to the developer.
 */
require_once(ROOT_PDIR . 'core/libs/core/ComponentHandler.class.php');
ComponentHandler::Singleton();
ComponentHandler::Load();
//var_dump(ComponentHandler::Singleton());die();
//var_dump(LibraryHandler::singleton());

HookHandler::DispatchHook('components_loaded');
//var_dump(ComponentHandler::Singleton());
HookHandler::DispatchHook('components_ready');

Core::AddProfileTime('components_load_complete');
