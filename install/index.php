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

require_once('../core/bootstrap_preincludes.php');


require_once(ROOT_PDIR . "core/libs/core/HookHandler.class.php");
HookHandler::singleton();
require_once(ROOT_PDIR . "core/libs/core/ConfigHandler.class.php");
ConfigHandler::singleton();

// Give me core settings!
// This will do the defines for the site, and provide any core variables to get started.
$core_settings = ConfigHandler::LoadConfigFile("configuration");


// Setup some necessary defines that are normally done in the bootstrap file.
$tmpdir = $core_settings['tmp_dir_web'];
/**
 * Temp directory 
 * @var string
 */
define('TMP_DIR', $tmpdir);

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

//if(!DEVELOPMENT_MODE){
//	die('Installation cannot proceed while site is NOT in Development mode.');
//}



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
	//$dbinfo = ConfigHandler::LoadConfigFile('db');
	$dbuser = $core_settings['database_user'];
	$dbname = $core_settings['database_name'];
	$dbpass = $core_settings['database_pass'];

	// Different connection backends will have different instructions.
	switch($core_settings['database_type']){
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

<p>Please execute the following commands with mysql or another interface, (like phpMyAdmin or toad).</p>
<pre>CREATE USER '$dbuser' IDENTIFIED BY '$dbpass';
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


// Is the system not installed yet?
if(!\Core\DB()->tableExists('component')){
	
	// I need some core settings before I can do anything!
	
	
	$core = ComponentFactory::Create(ROOT_PDIR . 'core/component.xml');
	$core->load();
	//var_dump($core, $core->getBaseDir());
	
	$changes = $core->install();
	var_dump($core, $changes);
	die();
}


try{
	$res = Dataset::Init()->table('component')->select('*')->limit(1)->execute();
}
catch(Exception $e){
	Core::LoadComponents();
	
	//$corecomponent = Core::GetComponent();
	//$corecomponent->load();
	
	//if(!$corecomponent->isInstalled()){
	if(!Core::GetComponent('core')->isInstalled()){
		// Install das core!
		$corecomponent->install();
		InstallPage::SetVariable('body', 'Installed the core framework, please refresh.');
		InstallPage::Render();
	}
}


try{
	HookHandler::DispatchHook('db_ready');

	// Get the preinstalled components in the system.
	$csingleton = ComponentHandler::Singleton();
	
	// Just to make sure.
	$changes = array();
	
	foreach(ComponentHandler::GetAllComponents() as $c){
		if(!$c->isInstalled()) continue;

		$c->reinstall();
		$changes[] = 'Reinstalled component ' . $c->getName();
	}
	
	foreach(ThemeHandler::GetAllThemes() as $t){
		if(!$t->isInstalled()) continue;

		if($t->reinstall()){
			$changes[] = 'Reinstalled theme ' . $t->getName();
		}
	}

	// Flush the system cache, just in case
	Core::Cache()->flush();

	//ComponentHandler::Singleton();

}
catch(Exception $e){
	//$stack = '<pre>' . $e->getTraceAsString() . '</pre>';
	
	InstallPage::SetVariable('error', $e->getMessage());
	InstallPage::SetVariable('body', 'An error occured, please check and fix it.');// . $stack);
	InstallPage::Render();
}

// In theory, everything should be installed now.
header('Location:../');
