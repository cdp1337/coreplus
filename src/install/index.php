<?php

/**
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 *
 * Copyright (C) 2010  Charlie Powell <charlie@evalagency.com>
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
 * @package Core\Installer
 * @author Charlie Powell <charlie@evalagency.com>
 */

if (PHP_VERSION < '7.0.0') {
	die('Please install PHP 7.0 or greater to run this application.  For a howto, you may refer to https://portal.eval.bz/tech-guides/install-php-on-linux');
}

// Damn suPHP, I can handle my own permissions, TYVM
umask(0);

// Override development mode here
// This is necessary to override the more strict restrictions that come inherit with production mode.s
// ie: installed packages in production do not get automatically enabled.
define('DEVELOPMENT_MODE', true);


// I need to override some defines here...
define('ROOT_PDIR', realpath(dirname(__DIR__)) . '/');
define('ROOT_WDIR', str_replace('//', '/', dirname(dirname($_SERVER['SCRIPT_NAME'])) . '/'));
define('ROOT_URL', ROOT_WDIR);
define('ROOT_URL_SSL', ROOT_WDIR);
define('ROOT_URL_NOSSL', ROOT_WDIR);
define('TMP_DIR', sys_get_temp_dir() . '/coreplus-installer/');
define('CUR_CALL', ROOT_WDIR . 'install/');


// Start a timer for performance tuning purposes.
require_once(__DIR__ . '/../core/libs/core/utilities/profiler/Profiler.php');
require_once(__DIR__ . '/../core/libs/core/utilities/profiler/DatamodelProfiler.php');
require_once(__DIR__ . '/../core/libs/core/utilities/logger/LogEntry.php');
require_once(__DIR__ . '/../core/libs/core/utilities/logger/Logger.php');
$profiler = new Core\Utilities\Profiler\Profiler('Core Plus');


if(function_exists('mb_internal_encoding')){
	// gogo i18n!
	mb_internal_encoding('UTF-8');
}

if(!is_dir(TMP_DIR)){
	mkdir(TMP_DIR, 0755, true);
}

// Start a traditional session.
if(!file_exists(TMP_DIR . 'sessions')){
	mkdir(TMP_DIR . 'sessions', 0700, true);
}
session_save_path(TMP_DIR . 'sessions');
session_start();

/********************* Initial system defines *********************************/
require_once(__DIR__ . '/../core/bootstrap_predefines.php');

/********************** Critical file inclusions ******************************/
require_once(ROOT_PDIR . 'core/libs/core/ISingleton.interface.php');
require_once(ROOT_PDIR . 'core/libs/core/templates/TemplateInterface.php');
require_once(ROOT_PDIR . 'core/libs/core/templates/Exception.php');
require_once(ROOT_PDIR . 'core/libs/core/templates/Template.php');
require_once(ROOT_PDIR . 'core/libs/core/templates/backends/PHTML.php');
require_once(ROOT_PDIR . 'core/libs/core/filestore/functions.php');
require_once(ROOT_PDIR . 'core/libs/core/filestore/Factory.php');
require_once(ROOT_PDIR . 'core/libs/core/filestore/File.interface.php');
require_once(ROOT_PDIR . 'core/libs/core/filestore/backends/FileLocal.php');
require_once(ROOT_PDIR . 'core/libs/core/XMLLoader.class.php');
require_once(ROOT_PDIR . 'core/libs/core/filestore/Contents.interface.php');

//require_once(ROOT_PDIR . 'core/libs/core/Exception.php');
require_once(ROOT_PDIR . 'install/classes/InstallerStep.php');
require_once(ROOT_PDIR . 'core/functions/Core.functions.php');
require_once(ROOT_PDIR . 'install/utilities.php');

require_once(ROOT_PDIR . "core/libs/core/ConfigHandler.class.php");

// If the configuration file has been written already, load up those config options!
if(file_exists(ROOT_PDIR . 'config/configuration.xml')){
	require_once(ROOT_PDIR . 'core/libs/core/datamodel/DMI.class.php');
	require_once(ROOT_PDIR . "core/libs/core/HookHandler.class.php");
	try {
		HookHandler::singleton();
		// Register some low-level hooks so it doesn't complain.
		HookHandler::RegisterNewHook('/core/model/presave');
		HookHandler::RegisterNewHook('/core/model/postsave');
		$core_settings = ConfigHandler::LoadConfigFile('configuration');

		// It was able to pull a valid configuration file... see if I can connect to the database
		$dbconn = DMI::GetSystemDMI();

		// And the backend connection...
		$backend = $dbconn->connection();

		// If I can poll the components table.... just stop right the fuck here!
		// This is a pretty good indication that the system is installed.
		if($backend->tableExists('component')){
			die('Core Plus has been successfully installed!  <a href="../">Continue</a>');
		}
	}
	catch (Exception $e) {
		// Yeah... I probably don't care at this stage... but maybe I do...
		\Core\ErrorManagement\exception_handler($e);
	}
}



if(!defined('CDN_TYPE')){
	define('CDN_TYPE', 'local');
}
if(!defined('CDN_LOCAL_ASSETDIR')){
	define('CDN_LOCAL_ASSETDIR', 'files/assets/');
}
if(!defined('CDN_LOCAL_PUBLICDIR')){
	define('CDN_LOCAL_PUBLICDIR', 'files/public/');
}
if(!defined('CDN_LOCAL_PRIVATEDIR')){
	define('CDN_LOCAL_PRIVATEDIR', 'files/private/');
}
if(!defined('FTP_USERNAME')){
	define('FTP_USERNAME', '');
}
if(!defined('FTP_PASSWORD')){
	define('FTP_PASSWORD', '');
}





// Try to determine the server distro that's running.
// This helps to provide accurate fix instructions.
$distro = 'unknown';
$version = 'unknown';
$family = 'unknown';
if(file_exists('/etc/lsb-release')){
	$contents = explode("\n", file_get_contents('/etc/lsb-release'));
	foreach($contents as $line){
		if(strpos($line, 'DISTRIB_ID=') === 0){
			$distro = substr($line, 11);
		}
		elseif(strpos($line, 'DISTRIB_RELEASE=') === 0){
			$version = substr($line, 16);
		}
	}

	if($distro == 'Ubuntu' || $distro == 'Debian'){
		$family = 'debian';
	}
}
elseif(file_exists('/etc/debian_version')){
	$distro = 'Debian';
	$version = file_get_contents('/etc/debian_version');
	$family = 'debian';
}
elseif(file_exists('/etc/redhat-release')){
	$line = file_get_contents('/etc/redhat-release');
	$family = 'redhat';
	if(strpos($line, 'release') !== false){
		$distro = trim(substr($line, 0, strpos($line, 'release')));
		$version = substr($line, strpos($line, 'release')+8);
		$version = trim(preg_replace('#[^0-9\.]#', '', $version));
	}
}
elseif(file_exists('/etc/SuSE-release')){
	$contents = explode("\n", file_get_contents('/etc/SuSE-release'));
	$distro = 'SuSE';
	$family = 'suse';
	foreach($contents as $line){
		if(strpos($line, 'VERSION = ') === 0){
			$version = substr($line, 10);
		}
	}
}
define('SERVER_DISTRO', $distro);
define('SERVER_VERSION', $version);
define('SERVER_FAMILY', $family);

unset($distro, $version, $family);


// These are the installer steps, put them in the order that they need to be executed!
$steps = array(
	'PreflightCheckStep',
	'InstallHTAccessStep',
	'SetupConfigurationStep',
	'InstallConfigurationStep',
	'PerformInstallStep',
);

// Use the GET variable to track the current step.
if(isset($_GET['step'])){
	$stepUser = (int)$_GET['step'];
}
else{
	$stepUser = 1;
}

foreach ($steps as $idx => $step) {
	require_once(ROOT_PDIR . 'install/steps/' . $step . '.php');
	$reflection = new ReflectionClass('Core\\Installer\\' . $step);
	/** @var $stepobject Core\Installer\InstallerStep */
	$stepobject = $reflection->newInstance();
	$stepobject->stepCurrent = $idx + 1;

	if($stepobject->stepCurrent == $stepUser){
		$stepobject->stepTotal   = sizeof($steps);
		$stepobject->stepProgress = round($stepobject->stepCurrent / $stepobject->stepTotal * 100, 0);
		
		$stepobject->execute();
		$stepobject->render();
		die();
	}
}
