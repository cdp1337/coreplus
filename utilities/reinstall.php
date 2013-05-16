#!/usr/bin/env php
<?php
/**
 * Reinstall, (or install), the core, all components, and the themes on this site.
 * Useful for automated testing and building with ant.
 *
 * @package Core Plus\Utilities
 * @since 2.5.0
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

if(!isset($_SERVER['SHELL'])){
	die("Please run this script from the command line.");
}

define('ROOT_PDIR', realpath(dirname(__DIR__) . '/src/') . '/');
// I need to override some defines here...
define('ROOT_WDIR', '/cli-installer/');
define('ROOT_URL', ROOT_WDIR);
define('TMP_DIR', sys_get_temp_dir() . '/coreplus-installer/');
define('CUR_CALL', ROOT_WDIR . 'install/');

// Make this page load appear as a standard web request instead of a CLI one.
unset($_SERVER['SHELL']);
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['REQUEST_URI'] = '/cli-installer';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_USER_AGENT'] = 'Core Plus cli-installer Script';


// Since this can act as part of the installer, I cannot simply require the bootstrap.

if(!is_dir(TMP_DIR)){
	mkdir(TMP_DIR, 0755, true);
}

// Initial system defines
require_once(ROOT_PDIR . 'core/bootstrap_predefines.php');
// Critical file inclusions
require_once(ROOT_PDIR . 'core/bootstrap_preincludes.php');
require_once(ROOT_PDIR . 'core/libs/core/templates/TemplateInterface.php');
require_once(ROOT_PDIR . 'core/libs/core/templates/Exception.php');
require_once(ROOT_PDIR . 'core/libs/core/templates/backends/PHTML.php');
require_once(ROOT_PDIR . 'install/classes/InstallerStep.php');
require_once(ROOT_PDIR . 'core/functions/Core.functions.php');
require_once(ROOT_PDIR . 'core/libs/core/utilities/logger/functions.php');
require_once(ROOT_PDIR . 'install/utilities.php');

require_once(ROOT_PDIR . "core/libs/core/ConfigHandler.class.php");

// If the configuration file has been written already, load up those config options!
if(file_exists(ROOT_PDIR . 'config/configuration.xml')){
	require_once(ROOT_PDIR . 'core/libs/datamodel/DMI.class.php');
	require_once(ROOT_PDIR . "core/libs/core/HookHandler.class.php");
	try {
		HookHandler::singleton();
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


require_once(ROOT_PDIR . 'core/libs/core/Core.class.php');
require_once(ROOT_PDIR . 'core/libs/core/ComponentHandler.class.php');
require_once(ROOT_PDIR . 'core/helpers/UpdaterHelper.class.php');

// Is the system not installed yet?
//if(!\Core\DB()->tableExists(DB_PREFIX . 'component')){


\Core::LoadComponents();

// And perform an actual reinstall
foreach(\Core::GetComponents() as $c){
	echo 'Reinstalling ' . $c->getName() . "...";
	$change = $c->reinstall();

	if($change === false){
		echo '   No changes' . "\n";
	}
	else{
		echo "\n" . implode("\n", $change) . "\n";
	}
}

echo 'Installing default theme...';
$change = \ThemeHandler::GetTheme('default')->install();
if($change === false){
	echo '   No changes' . "\n";
}
else{
	echo "\n" . implode("\n", $change) . "\n";
}


// And the current theme if it's different.
$theme    = ConfigHandler::Get('/theme/selected');
if($theme != 'default'){
	echo 'Installing ' . $theme . ' theme...';
	\ThemeHandler::GetTheme($theme)->install();
	if($change === false){
		echo '   No changes' . "\n";
	}
	else{
		echo "\n" . implode("\n", $change) . "\n";
	}
}
