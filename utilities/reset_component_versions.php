#!/usr/bin/env php
<?php
/**
 * Script to reset the component versions back to what is currently on the filesystem.
 *
 * This is useful for switching between different branches.
 *
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20131103.1802
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
require_once(ROOT_PDIR . 'core/libs/core/datamodel/Dataset.php');

$db = DMI::GetSystemDMI();

$ds = new \Core\Datamodel\Dataset();
$ds->delete()->table('component')->execute($db);

// And now execute reinstall.
exec('"' . ROOT_PDIR . '../utilities/reinstall.php' . '"');
