<?php
/**
 * Core bootstrap helper file that handles all the core defines for the system
 *
 * This file is the core of the application; it's responsible for setting up
 *  all the necessary paths, settings and includes.
 *
 * @package Core Plus\Core
 * @author Charlie Powell <powellc@powelltechs.com>
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


if(PHP_VERSION < '6.0.0' && ini_get('magic_quotes_gpc')){
	die('This application cannot run with magic_quotes_gpc enabled, please disable them now!' . "\n");
}

if(PHP_VERSION < '5.3.0'){
	die('This application requires at least PHP 5.3 to run!' . "\n");
}


/********************* Initial system defines *********************************/

// Right off the bat, I need to decide which mode I'm running in, either as a CLI script or regular.
// In addition, there are some other things that need to be retrieved early on, such as root path and what not.
if(isset($_SERVER['SHELL'])){
	$em = 'CLI';
	// Using __DIR__ is more accurate for files including the core in other directories.
	$rpdr = realpath(__DIR__ . '/../') . '/';
	$rwdr = null;
	$rip = '127.0.0.1';
}
else{
	$em = 'WEB';
	$rip = '127.0.0.1';
	// Set the constants for the root directory (relative) and root directory (full path).
	$rpdr = pathinfo($_SERVER['SCRIPT_FILENAME' ], PATHINFO_DIRNAME );
	if($rpdr != '/') $rpdr .= '/'; // Append a slash if it's not the root dir itself.
	$rwdr = pathinfo($_SERVER['SCRIPT_NAME' ],     PATHINFO_DIRNAME );
	if($rwdr != '/') $rwdr .= '/'; // Append a slash if it's not the root dir itself.
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
 * The physical directory of the CAE2 installation.
 * DOES have a trailing slash.
 *
 * Example: /home/someone/public_html/myinstall/
 * @var string
 */
if(!defined('ROOT_PDIR')) define('ROOT_PDIR', $rpdr);
/**
 * The location of the root installation based on the browser get string.
 * DOES have a trailing slash.
 *
 * Example: /~someone/myinstall/
 * @var string
 */
if(!defined('ROOT_WDIR')) define('ROOT_WDIR', $rwdr);
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



// Cleanup!
unset($em, $rpdr, $rwdr, $rip);
