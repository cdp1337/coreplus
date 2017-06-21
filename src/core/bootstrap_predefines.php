<?php
/**
 * Core bootstrap helper file that handles all the core defines for the system
 *
 * This file is the core of the application; it's responsible for setting up
 *  all the necessary paths, settings and includes.
 *
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2017  Charlie Powell
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


if (PHP_VERSION < '6.0.0' && ini_get('magic_quotes_gpc')) {
	die('This application cannot run with magic_quotes_gpc enabled, please disable them now!' . "\n");
}

if (PHP_VERSION < '5.4.0') {
	die('This application requires at least PHP 5.4 to run!' . "\n");
}


/********************* Initial system defines *********************************/

// Right off the bat, I need to decide which mode I'm running in, either as a CLI script or regular.
// In addition, there are some other things that need to be retrieved early on, such as root path and what not.
if (isset($_SERVER['SHELL']) || php_sapi_name() == "cli") {
	$em = 'CLI';
	// Using __DIR__ is more accurate for files including the core in other directories.
	$rpdr = realpath(__DIR__ . '/../') . '/';
	$rwdr = null;
	$rip  = '127.0.0.1';
}
else {
	$em  = 'WEB';
	$rip = '127.0.0.1';
	// Set the constants for the root directory (relative) and root directory (full path).

	// I must use realpath here because if the script is symlinked to a different location,
	// that would throw off the SCRIPT_FILENAME path.
	// This is because apache sees the symlinked path, but php will see the actual file path.
	$rpdr = pathinfo(realpath($_SERVER['SCRIPT_FILENAME']), PATHINFO_DIRNAME);
	if ($rpdr != '/') $rpdr .= '/'; // Append a slash if it's not the root dir itself.

	// The web path is simplier
	$rwdr = pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME);
	if ($rwdr != '/') $rwdr .= '/'; // Append a slash if it's not the root dir itself.

	// And the remote IP is even easier, (proxy systems are NOT taken into account yet)
	$rip = $_SERVER['REMOTE_ADDR'];
}

/**
 * The execution mode of the page.
 * This is used because scripts can run in the command line as well as a webpage.
 *
 * Either 'CLI' or 'WEB'.
 * @var string
 */
define('EXEC_MODE', $em);
/**
 * The physical directory of the Core Plus installation.
 * DOES have a trailing slash.
 *
 * Example: /home/someone/public_html/myinstall/
 * @var string
 */
if (!defined('ROOT_PDIR')) define('ROOT_PDIR', $rpdr);
/**
 * The location of the root installation based on the browser get string.
 * DOES have a trailing slash.
 *
 * Example: /~someone/myinstall/
 * @var string
 */
if (!defined('ROOT_WDIR')) define('ROOT_WDIR', $rwdr);
/**
 * The remote IP of the connecting computer.
 * Based dynamically off the $_SERVER variable.
 *
 * @var string
 */
define('REMOTE_IP', $rip);

/**
 * FULL_DEBUG is useful for the core development of the platform.
 *
 * @var boolean
 */
define('FULL_DEBUG', false);
//define('FULL_DEBUG', true);

define('NL', "\n");
define('TAB', "\t");

/**
 * define shorthand directory separator constant
 */
define('DS', DIRECTORY_SEPARATOR);

##
# Gimme some colors!
# These are used to prettify the terminal.
# Color 1 is always standard and
# Color 2 is always the bold version.

/*

*/

if(EXEC_MODE == 'CLI'){
	/**
	 * Black
	 */
	define('COLOR_BLK1', "\033[0;30m");
	/**
	 * Bold Black
	 */
	define('COLOR_BLK2', "\033[1;30m");
	/**
	 * Red
	 */
	define('COLOR_RED1', "\033[0;31m");
	/**
	 * Bright Red
	 */
	define('COLOR_RED2', "\033[1;31m");
	/**
	 * Green
	 */
	define('COLOR_GRN1', "\033[0;32m");
	/**
	 * Bright Green
	 */
	define('COLOR_GRN2', "\033[1;32m");
	/**
	 * Yellow
	 */
	define('COLOR_YLW1', "\033[0;33m");
	/**
	 * Bright Yellow
	 */
	define('COLOR_YLW2', "\033[1;33m");
	/**
	 * Blue
	 */
	define('COLOR_BLU1', "\033[0;34m");
	/**
	 * Bright Blue
	 */
	define('COLOR_BLU2', "\033[1;34m");
	/**
	 * Purple
	 */
	define('COLOR_PRP1', "\033[0;35m");
	/**
	 * Bright Purple
	 */
	define('COLOR_PRP2', "\033[1;35m");
	/**
	 * Cyan
	 */
	define('COLOR_CYN1', "\033[0;36m");
	/**
	 * Light Cyan
	 */
	define('COLOR_CYN2', "\033[1;36m");
	/**
	 * White
	 */
	define('COLOR_WHT1', "\033[0;37m");
	/**
	 * Bold White
	 */
	define('COLOR_WHT2', "\033[1;37m");
	/**
	 * Line color, the separating characters
	 */
	define('COLOR_LINE', COLOR_BLK1);
	/**
	 * Heading color
	 */
	define('COLOR_HEADER', COLOR_CYN2);
	/**
	 *  Success color
	 */
	define('COLOR_SUCCESS', COLOR_GRN2);
	/**
	 *  Warning color
	 */
	define('COLOR_WARNING', COLOR_YLW2);
	/**
	 *  Error color
	 */
	define('COLOR_ERROR', COLOR_RED2);
	/**
	 *  Debug color
	 */
	define('COLOR_DEBUG', COLOR_BLU1);
	// Normal color, alias of RESET for CLI operation, but has other meaning on WEB operation.
	define('COLOR_NORMAL', "\033[0m");
	// Reset color
	define('COLOR_RESET', "\033[0m");
	// Space character
	define('NBSP', ' ');
}
else{
	// Line color, the separating characters
	define('COLOR_LINE', "<span style='color:grey; font-family:Courier,mono;'>");
	// Heading color
	define('COLOR_HEADER', "<span style='color:cyan; font-weight:bold; font-family:Courier,mono;'>");
	// Success color
	define('COLOR_SUCCESS', "<span style='color:green; font-weight:bold; font-family:Courier,mono;'>");
	// Warning color
	define('COLOR_WARNING', "<span style='color:#9e9e00; font-weight:bold; font-family:Courier,mono;'>");
	// Error color
	define('COLOR_ERROR', "<span style='color:red; font-weight:bold; font-family:Courier,mono;'>");
	// Debug color
	define('COLOR_DEBUG', "<span style='color:lightskyblue; font-family:Courier,mono;'>");
	// Normal color, no styles applied, required because any RESET (</span>) needs a start span.
	define('COLOR_NORMAL', "<span style='font-family:Courier,mono;'>");
	// Reset color
	define('COLOR_RESET', "</span>");
	// Space character
	define('NBSP', '&nbsp;');
}


// Cleanup!
unset($em, $rpdr, $rwdr, $rip);


// A few little convenience options, particularly useful for the cache system.
define('SECONDS_ONE_MINUTE', 60);
define('SECONDS_ONE_HOUR',   3600);
define('SECONDS_TWO_HOUR',   7200);
define('SECONDS_ONE_DAY',    86400);
define('SECONDS_ONE_WEEK',   604800);  // 7 days
define('SECONDS_TWO_WEEK',   1209600); // 14 days
define('SECONDS_ONE_MONTH',  2629800); // 30.4375 days
define('SECONDS_TWO_MONTH',  5259600); // 60.8750 days

define('LOG_LEVEL_ERROR', 1);
define('LOG_LEVEL_WARNING', 2);
define('LOG_LEVEL_INFO', 3);
define('LOG_LEVEL_DEBUG', 4);
define('LOG_LEVEL_VERBOSE', 5);
