#!/usr/bin/env php
<?php
/**
 * Reinstall, (or install), the core, all components, and the themes on this site.
 * Useful for automated testing and building with ant.
 *
 * @package Core\Utilities
 * @since 2.5.0
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2016  Charlie Powell
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

use Core\CLI\CLI;

if(!isset($_SERVER['SHELL'])){
	die("Please run this script from the command line.");
}

define('ROOT_PDIR', realpath(dirname(__DIR__) . '/src/') . '/');
// I need to override some defines here...
define('ROOT_WDIR', '/cli-installer/');
define('ROOT_URL', ROOT_WDIR);
define('ROOT_URL_SSL', ROOT_WDIR);
define('ROOT_URL_NOSSL', ROOT_WDIR);
//define('TMP_DIR', sys_get_temp_dir() . '/coreplus-installer/');
define('CUR_CALL', ROOT_WDIR . 'install/');
define('SERVERNAME', 'localhost');

/**
 * The GnuPG home directory to store keys in.
 */
if (!defined('GPG_HOMEDIR')) {
	define('GPG_HOMEDIR', ROOT_PDIR . 'gnupg');
}
// PECL expects this variable to be set, so set it!
putenv('GNUPGHOME=' . GPG_HOMEDIR);

// Make this page load appear as a standard web request instead of a CLI one.
//unset($_SERVER['SHELL']);
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['REQUEST_URI'] = '/cli-installer';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_USER_AGENT'] = 'Core Plus cli-installer Script';


// Initial system defines
require_once(ROOT_PDIR . 'core/bootstrap_predefines.php');
// Critical file inclusions
require_once(ROOT_PDIR . 'core/bootstrap_preincludes.php');
require_once(ROOT_PDIR . 'core/libs/core/templates/TemplateInterface.php');
require_once(ROOT_PDIR . 'core/libs/core/templates/Exception.php');
require_once(ROOT_PDIR . 'core/libs/core/templates/backends/PHTML.php');
require_once(ROOT_PDIR . 'install/classes/InstallerStep.php');
require_once(ROOT_PDIR . 'core/functions/Core.functions.php');
require_once(ROOT_PDIR . 'core/libs/core/utilities/logger/LogEntry.php');
require_once(ROOT_PDIR . 'core/libs/core/utilities/logger/Logger.php');
require_once(ROOT_PDIR . 'install/utilities.php');
require_once(ROOT_PDIR . 'core/libs/core/utilities/profiler/Profiler.php');
require_once(ROOT_PDIR . 'core/libs/core/utilities/profiler/DatamodelProfiler.php');


require_once(ROOT_PDIR . "core/libs/core/ConfigHandler.class.php");

// If the configuration file has been written already, load up those config options!
if(file_exists(ROOT_PDIR . 'config/configuration.xml')){
	require_once(ROOT_PDIR . 'core/libs/core/datamodel/DMI.class.php');
	require_once(ROOT_PDIR . "core/libs/core/HookHandler.class.php");
	try {
		HookHandler::singleton();
		$core_settings = ConfigHandler::LoadConfigFile('configuration');

		$tmpdir = $core_settings['tmp_dir_web'];
		if(!defined('TMP_DIR')) {
			/**
			 * Temporary directory
			 */
			define('TMP_DIR', $tmpdir);
		}
	}
	catch (Exception $e) {
		// Yeah... I probably don't care at this stage... but maybe I do...
		\Core\ErrorManagement\exception_handler($e);
	}
}


if(!defined('TMP_DIR')) {
	/**
	 * Temporary directory
	 */
	define('TMP_DIR', sys_get_temp_dir() . '/coreplus-installer/');
}
// Since this can act as part of the installer, I cannot simply require the bootstrap.
if(!is_dir(TMP_DIR)){
	mkdir(TMP_DIR, 0755, true);
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
require_once(ROOT_PDIR . 'core/libs/core/cli/CLI.class.php');
require_once(ROOT_PDIR . 'core/libs/core/cli/Arguments.php');
require_once(ROOT_PDIR . 'core/libs/core/cli/Argument.php');

$verbosity = $assets = $onlyCore = $onlyComponent = $onlyTheme = null;

$arguments = new \Core\CLI\Arguments([
	'verbosity' => [
		'description' => "Verbosity level of output.\n\t0: Summary-Only Output\n\t1: (default) Real-Time Output\n\t2: Verbose Real-Time Output",
		'value' => true,
		'default' => 1,
	    'assign' => &$verbosity,
	],
	'assets' => [
		'description' => 'Only process assets',
		'value' => false,
		'shorthand' => [],
	    'assign' => &$assets,
	],
	'core' => [
		'description' => 'Reinstall only Core.  (Overrides --component and --theme)',
		'value' => false,
		'shorthand' => [],
	    'assign' => &$onlyCore,
	],
	'component' => [
		'description' => 'Reinstall only a requested component. (Overrides --theme)',
		'value' => true,
		'shorthand' => ['c'],
	    'assign' => &$onlyComponent,
	],
	'theme' => [
		'description' => 'Reinstall only a requested theme.',
		'value' => true,
		'shorthand' => ['t'],
	    'assign' => &$onlyTheme,
	],
]);

$arguments->usageHeader = 'This utility will perform a reinstallation of all assets and models on the site';

$arguments->processArguments();

\Core::LoadComponents();
try {
	DMI::GetSystemDMI();
	ConfigHandler::_DBReadyHook();
}
	// This catch statement should be hit anytime the database is not available,
	// core table doesn't exist, or the like.
catch (Exception $e) {
	echo $e->getMessage();
	die();
}

$allpages = [];
$changes = [];

// And perform an actual reinstall
foreach(\Core::GetComponents() as $c){
	/** @var Component_2_1 $c */

	// Get the pages, (for the cleanup operation)
	$allpages = array_merge($allpages, $c->getPagesDefined());

	if(
		$onlyCore && $c->getKeyName() == 'core' ||
		$onlyComponent && ($c->getKeyName() == $onlyComponent || $c->getName() == $onlyComponent) ||
		($onlyTheme === null && $onlyComponent === null && $onlyCore === null)
	){
		if($verbosity == 1){
			CLI::PrintLine('');
			CLI::PrintLine('Reinstalling Component ' . $c->getName());
		}
		elseif($verbosity == 2){
			CLI::PrintHeader('Reinstalling Component ' . $c->getName());
		}

		if($assets){
			$change = $c->_parseAssets(true, $verbosity);
		}
		else{
			$change = $c->reinstall($verbosity);
		}

		if($change !== false){
			$changes = array_merge($changes, $change);
		}
	}
}


// And the current theme if it's different.
$theme    = ConfigHandler::Get('/theme/selected');
if($theme != 'default'){
	$t = \ThemeHandler::GetTheme($theme);

	if(
		$onlyTheme && ($t->getKeyName() == $onlyTheme || $t->getName() == $onlyTheme) ||
		($onlyTheme === null && $onlyComponent === null && $onlyCore === null)
	){
		if($verbosity == 1){
			CLI::PrintLine('');
			CLI::PrintLine('Reinstalling Theme ' . $theme);
		}
		elseif($verbosity == 2){
			CLI::PrintHeader('Reinstalling Theme ' . $theme);
		}

		if($assets){
			$change = $t->_parseAssets(true, $verbosity);
		}
		else{
			$change = $t->reinstall($verbosity);
		}

		if($change !== false){
			$changes = array_merge($changes, $change);
		}
	}
}



if(CDN_TYPE != 'local'){
	if($verbosity > 0){
		CLI::PrintHeader('Synchronizing Public Files');
	}

	// Check to see if any public files need to be deployed to the CDN.
	// This behaves the same as asset deployment, but is a utility-only function that is beyond the normal reinstallation procedure.
	// However, seeing as this is a utility script... :)
	$public  = new \Core\Filestore\Backends\DirectoryLocal(CDN_LOCAL_PUBLICDIR);
	$dirname = $public->getPath();
	$dirlen  = strlen($dirname);
	foreach($public->ls(null, true) as $file){
		if($file instanceof \Core\Filestore\Backends\FileLocal){
			/** @var \Core\Filestore\Backends\FileLocal $file */

			$filename   = $file->getFilename();
			$remotename = 'public/' . substr($filename, $dirlen);

			CLI::PrintActionStart('Copying public file ' . $remotename);

			$deployed = \Core\Filestore\Factory::File($remotename);
			if($deployed->identicalTo($file)){
				CLI::PrintActionStatus('skip');
				continue;
			}

			$file->copyTo($deployed);
			CLI::PrintActionStatus('ok');
			$changes[] = 'Deployed public file ' . $remotename;
		}
	}
}


if($onlyTheme === null && $onlyComponent === null && $onlyCore === null && !$assets){
	if($verbosity == 1){
		CLI::PrintLine('');
		CLI::PrintLine('Cleaning up non-existent pages');
	}
	elseif($verbosity == 2){
		CLI::PrintHeader('Cleaning up non-existent pages');
	}
	$pageremovecount = 0;
	foreach(\Core\Datamodel\Dataset::Init()->select('baseurl')->table('page')->where('admin = 1')->execute() as $row){
		$baseurl = $row['baseurl'];

		// This page existed already, no need to do anything :)
		if(isset($allpages[$baseurl])) continue;

		++$pageremovecount;

		// Otherwise, this page was deleted or for some reason doesn't exist in the component list.....
		// BUH BAI
		CLI::PrintActionStart('Deleting page ' . $baseurl);
		$changes[] = "Deleted page " . $baseurl;
		\Core\Datamodel\Dataset::Init()->delete()->table('page')->where('baseurl = ' . $baseurl)->execute();
		\Core\Datamodel\Dataset::Init()->delete()->table('page_meta')->where('baseurl = ' . $baseurl)->execute();
		CLI::PrintActionStatus('ok');
	}
	if($pageremovecount == 0 && $verbosity > 0){
		CLI::PrintLine('No pages flushed');
	}
}



if($onlyTheme === null && $onlyComponent === null && $onlyCore === null && !$assets) {
	if($verbosity == 1){
		CLI::PrintLine('');
		CLI::PrintLine('Synchronizing Searchable Models');
	}
	elseif($verbosity == 2) {
		CLI::PrintHeader('Synchronizing Searchable Models');
	}
	foreach(\Core::GetComponents() as $c) {
		/** @var Component_2_1 $c */

		foreach($c->getClassList() as $class => $file) {
			if($class == 'model') {
				continue;
			}
			if(strrpos($class, 'model') !== strlen($class) - 5) {
				// If the class doesn't explicitly end with "Model", it's also not a model.
				continue;
			}
			if(strpos($class, '\\') !== false) {
				// If this "Model" class is namespaced, it's not a valid model!
				// All Models MUST reside in the global namespace in order to be valid.
				continue;
			}

			$ref = new ReflectionClass($class);
			if(!$ref->getProperty('HasSearch')->getValue()) {
				// This model doesn't have the searchable flag, skip it.
				continue;
			}

			if($verbosity > 0) {
				CLI::PrintActionStart("Syncing searchable model $class");
			}
			$good = false;
			$fac  = new ModelFactory($class);
			foreach($fac->get() as $m) {
				/** @var Model $m */
				$m->set('search_index_pri', '!');
				if($m->save()) {
					// Set this for the entire group!
					// eg: if 1 saves and 2 skips, I still want to display :OK!:
					$good = true;
				}
			}

			if($verbosity > 0) {
				CLI::PrintActionStatus($good ? 'ok' : 'skip');
			}

			if($good) {
				$changes[] = "Synced searchable model " . $class;
			}
		}
	}
}

// Flush the system cache, just in case
\Core\Cache::Flush();
\Core\Templates\Backends\Smarty::FlushCache();


if($verbosity > 0){
	CLI::PrintHeader('DONE!');
}

foreach($changes as $line){
	CLI::PrintLine($line);
}