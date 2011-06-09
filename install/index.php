<?php

/**
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 *
 * Copyright (C) 2010  Charlie Powell <powellc@powelltechs.com>
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
 *
 * @package [packagename]
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date [date]
 */

// I expect some configuration options....
if(PHP_VERSION < '6.0.0' && ini_get('magic_quotes_gpc')){
	die('This application cannot run with magic_quotes_gpc enabled, please disable them now!');
}


// Start a traditional session.
session_start();

// I need to override some defines here...
$rpdr = pathinfo(dirname($_SERVER['SCRIPT_FILENAME' ]), PATHINFO_DIRNAME );
define('ROOT_PDIR', $rpdr . '/');


// Start a timer for performance tuning purposes.
// This will be saved into the Core once that's available.
$start_time = microtime(true);



/********************* Initial system defines *********************************/
require_once('../core/bootstrap_predefines.php');

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


require_once(ROOT_PDIR . "core/libs/core/HookHandler.class.php");
HookHandler::singleton();
require_once(ROOT_PDIR . "core/libs/core/ConfigHandler.class.php");
ConfigHandler::singleton();

// Give me core settings!
// This will do the defines for the site, and provide any core variables to get started.
$core_settings = ConfigHandler::LoadConfigFile("core");


if(!DEVELOPMENT_MODE){
	die('Installation cannot proceed while site is NOT in Development mode.');
}



// Create a stupid-simple template system that's as minimalistic as possible.
class InstallPage {
	public static $TEMPLATE = 'install.tpl';
	
	private static $_VARS = array();

	private function __construct(){
		// Nothing to do here
	}

	public static function SetVariable($var, $value){
		self::$_VARS[$var] = $value;
	}

	public static function Render(){
		$in = file_get_contents(self::$TEMPLATE);

		// Replace the varaibles in the appropriate places.
		foreach(self::$_VARS as $k => $v){
			$in = str_replace('%' . $k . '%', $v, $in);
		}

		// Allow for basic logic in the template.
		//preg_replace('/%if\(([^\)]*)\)%(.*)%\/if%/emU', 'return ($1)? $2 : "";', $in);
		//preg_replace('/%if\((.*)\)%(.*)%fi%/eU', '(str_replace(\'\"\', \'"\', $1))? "$2" : "";', $in);
		$in = preg_replace('/\{if\((.*)\)\}(.*)\{\/if\}/eis', '(eval("return ($1);"))? "$2" : "";', $in);

		echo $in;

		// Once the page is rendered stop execution.
		die();
	}
}

// Default variables
InstallPage::SetVariable('head', '');
InstallPage::SetVariable('title', 'Installation of CAE2');
InstallPage::SetVariable('error', '');


// Datamodel, GOGO!
require_once(ROOT_PDIR . 'core/libs/datamodel/DMI.class.php');
try{
	$dbconn = DMI::GetSystemDMI();
}
catch(Exception $e){
	// Couldn't establish connection... do something fun!

	InstallPage::SetVariable('error', $e->getMessage());
	$dbinfo = ConfigHandler::LoadConfigFile('db');
	$dbuser = $dbinfo['user'];
	$dbname = $dbinfo['name'];

	// Different connection backends will have different instructions.
	switch($dbinfo['type']){
		case 'cassandra':
			$body = <<<EOD
<h2>Cassandra Installation Instructions</h2>
<p class="message-note">You currently have the "type" variable in 
config/db.xml set to "cassandra".  This will use the Apache Cassandra data 
storage engine for the default site datamodel store.  If this is incorrect, please
correct this <em>before</em> proceeding.  Otherwise... please verify that the 
settings in config/core.xml and config/db.xml are as desired and continue.</p>

<p>Please execute the following commands with cassandra-cli or another interface.</p>
<pre>create keyspace $dbname;</pre>
<p>Refresh the page when this has been done.</p>
EOD;
			break;
		case 'mysql':
		case 'mysqli':
			$body = <<<EOD
<h2>MySQL/MySQLi Installation Instructions</h2>
<p class="message-note">You currently have the "type" variable in 
config/db.xml set to "mysql" or "mysqli".  This will use the 
<strike>MySQL</strike> <strike>Sun</strike> Oracle MySQL backend storage engine 
for the default site datamodel store.  If this is incorrect, please correct this 
<em>before</em> proceeding.  Otherwise... please verify that the settings in 
config/core.xml and config/db.xml are as desired and continue.</p>

<p>Please execute the following commands with mysql or another interface.</p>
<pre>CREATE USER '$dbuser' IDENTIFIED BY 'the.password.in.db.xml';
CREATE DATABASE IF NOT EXISTS $dbname;
GRANT ALL ON $dbname.* TO '$dbuser';
FLUSH PRIVILEGES;
</pre>
<p>Refresh the page when this has been done.</p>
EOD;
			break;
		default:
			$body = "<p class='error-message'>I don't know what datamodel store you're trying to use, but I don't support it...</p>";
			break;
	}

	InstallPage::SetVariable('body', $body);
	InstallPage::Render();
}



// Data model backend should be ready now.
require_once(ROOT_PDIR . 'core/libs/core/Core.class.php');
require_once(ROOT_PDIR . 'core/libs/core/ComponentHandler.class.php');

// Get the preinstalled components in the system.
$csingleton = ComponentHandler::Singleton();
//$components = ComponentHandler::Singleton()->GetAllComponents();
//var_dump($components);

ComponentHandler::Load();




// GO!
$dm = new Dataset('config');
$dm->select('*');
$rs = $dm->execute();

var_dump($rs);
