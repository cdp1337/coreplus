<?php

/**
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 *
 * Copyright (C) 2010  Charlie Powell <charlie@eval.bz>
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
 * @author Charlie Powell <charlie@eval.bz>
 */

// I expect some configuration options....
if(PHP_VERSION < '6.0.0' && ini_get('magic_quotes_gpc')){
	die('This application cannot run with magic_quotes_gpc enabled, please disable them now!');
}

if (PHP_VERSION < '5.4.0') {
	die('This application requires at least PHP 5.4 to run!');
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
require_once(__DIR__ . '/../core/libs/core/utilities/logger/functions.php');
$profiler = new Core\Utilities\Profiler\Profiler('Core Plus');

// gogo i18n!
mb_internal_encoding('UTF-8');


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
Core\Utilities\Logger\write_debug('Starting Application');


/********************** Critical file inclusions ******************************/
Core\Utilities\Logger\write_debug('Loading pre-include files');
require_once(__DIR__ . '/../core/bootstrap_preincludes.php');


require_once(ROOT_PDIR . 'core/libs/core/templates/TemplateInterface.php');
require_once(ROOT_PDIR . 'core/libs/core/templates/Exception.php');
require_once(ROOT_PDIR . 'core/libs/core/templates/Template.php');
require_once(ROOT_PDIR . 'core/libs/core/templates/backends/PHTML.php');

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
		ConfigHandler::LoadConfigFile('configuration');
	}
	catch (Exception $e) {
		// Yeah... I probably don't care at this stage... but maybe I do...
		error_log($e->getMessage());
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


// These are the installer steps, put them in the order that they need to be executed!
$steps = array(
	'PreflightCheckStep',
	'InstallHTAccessStep',
	'SetupConfigurationStep',
	'InstallConfigurationStep',
	'PerformInstallStep',
);

foreach ($steps as $step) {
	require_once(ROOT_PDIR . 'install/steps/' . $step . '.php');
	$reflection = new ReflectionClass('Core\\Installer\\' . $step);
	/** @var $stepobject Core\Installer\InstallerStep */
	$stepobject = $reflection->newInstance();

	if(!$stepobject->hasPassed()){
		$stepobject->execute();
		$stepobject->render();
		die();
	}
}
