#!/usr/bin/env php
<?php
/**
 * Reinstall, (or install), the core, all components, and the themes on this site.
 * Useful for automated testing and building with ant.
 *
 * @package Core\Utilities
 * @since 2.5.0
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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
define('ROOT_URL_SSL', ROOT_WDIR);
define('ROOT_URL_NOSSL', ROOT_WDIR);
define('TMP_DIR', sys_get_temp_dir() . '/coreplus-installer/');
define('CUR_CALL', ROOT_WDIR . 'install/');

// Make this page load appear as a standard web request instead of a CLI one.
//unset($_SERVER['SHELL']);
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
require_once(ROOT_PDIR . 'core/libs/core/utilities/profiler/Profiler.php');
require_once(ROOT_PDIR . 'core/libs/core/utilities/logger/functions.php');

require_once(ROOT_PDIR . "core/libs/core/ConfigHandler.class.php");

// If the configuration file has been written already, load up those config options!
if(file_exists(ROOT_PDIR . 'config/configuration.xml')){
	require_once(ROOT_PDIR . 'core/libs/core/datamodel/DMI.class.php');
	require_once(ROOT_PDIR . "core/libs/core/HookHandler.class.php");
	try {
		HookHandler::singleton();
		ConfigHandler::LoadConfigFile('configuration');
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


require_once(ROOT_PDIR . 'core/libs/core/Core.class.php');
require_once(ROOT_PDIR . 'core/libs/core/ComponentHandler.class.php');
require_once(ROOT_PDIR . 'core/helpers/UpdaterHelper.class.php');

// Is the system not installed yet?
//if(!\Core\DB()->tableExists(DB_PREFIX . 'component')){


\Core::LoadComponents();

$allpages = [];

// And perform an actual reinstall
foreach(\Core::GetComponents() as $c){
	/** @var Component_2_1 $c */

	// Get the pages, (for the cleanup operation)
	$allpages = array_merge($allpages, $c->getPagesDefined());

	echo 'Reinstalling ' . $c->getName() . "...";
	$change = $c->reinstall();

	if($change === false){
		echo '   No changes' . "\n";
	}
	else{
		echo "\n" . implode("\n", $change) . "\n";
	}
}

/*
echo 'Installing default theme...';
$change = \ThemeHandler::GetTheme('default')->install();
if($change === false){
	echo '   No changes' . "\n";
}
else{
	echo "\n" . implode("\n", $change) . "\n";
}
*/


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

echo 'Cleaning up non-existent pages...' . "\n";
foreach(\Core\Datamodel\Dataset::Init()->select('baseurl')->table('page')->where('admin = 1')->execute() as $row){
	$baseurl = $row['baseurl'];

	// This page existed already, no need to do anything :)
	if(isset($allpages[$baseurl])) continue;

	// Otherwise, this page was deleted or for some reason doesn't exist in the component list.....
	// BUH BAI
	echo "Deleting page " . $baseurl . "\n";
	\Core\Datamodel\Dataset::Init()->delete()->table('page')->where('baseurl = ' . $baseurl)->execute();
	\Core\Datamodel\Dataset::Init()->delete()->table('page_meta')->where('baseurl = ' . $baseurl)->execute();
}

echo 'Synchronizing searchable models...' . "\n";
foreach(\Core::GetComponents() as $c){
	/** @var Component_2_1 $c */

	foreach($c->getClassList() as $class => $file){
		if($class == 'model'){
			continue;
		}
		if(strrpos($class, 'model') !== strlen($class) - 5){
			// If the class doesn't explicitly end with "Model", it's also not a model.
			continue;
		}
		if(strpos($class, '\\') !== false){
			// If this "Model" class is namespaced, it's not a valid model!
			// All Models MUST reside in the global namespace in order to be valid.
			continue;
		}

		$ref = new ReflectionClass($class);
		if(!$ref->getProperty('HasSearch')->getValue()){
			// This model doesn't have the searchable flag, skip it.
			continue;
		}

		echo "Syncing $class\n";
		$fac = new ModelFactory($class);
		foreach($fac->get() as $m){
			/** @var Model $m */
			$m->set('search_index_pri', '!');
			$m->save();
		}
	}
}