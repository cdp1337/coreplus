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
define('ROOT_WDIR', dirname(dirname($_SERVER['SCRIPT_NAME'])) . '/');
define('ROOT_URL', ROOT_WDIR);
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

die(':)');


// Useful in setting certain directories as globally forbidden via htaccess.
$htaccessdeny = <<<EOD
# This is specifically created to prevent access to ANYTHING in this directory.
#  Under no situation should anything in this directory be world-readable!

<Files *>
	Order deny,allow
	Deny from All
</Files>
EOD;



require_once('../core/libs/core/InstallerException.php');

// This is the entire templating system for the installer.
require_once('InstallPage.class.php');



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
		if(!is_dir($d) && !mkdir($d)){
			$page = new InstallPage();
			$page->assign('error', TMP_DIR . ' is not writable.<br/>You can fix this by executing the following in the terminal:<br/><br/><code>mkdir -p &quot;' . TMP_DIR . '&quot;;<br/>chmod a+w &quot;' . TMP_DIR . '&quot;;</code>');
			$page->template = 'templates/preflight_requirements.tpl';
			$page->render();
		}
	}
}
if(!file_exists(TMP_DIR . '.htaccess')){
	file_put_contents(TMP_DIR . '.htaccess', $htaccessdeny);
}

//if(!DEVELOPMENT_MODE){
//	die('Installation cannot proceed while site is NOT in Development mode.');
//}


// Data model backend should be ready now.
require_once(ROOT_PDIR . 'core/libs/core/Core.class.php');
require_once(ROOT_PDIR . 'core/libs/core/ComponentHandler.class.php');

// Is the system not installed yet?
if(!\Core\DB()->tableExists(DB_PREFIX . 'component')){
	
	// I need some core settings before I can do anything!
	if(!isset($_SESSION['configs'])) $_SESSION['configs'] = array();
	
	
	if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mode']) && $_POST['mode'] == 'configs'){
		unset($_POST['mode']);
		
		// Directories must end with a trailing slash
		if($_POST['/core/filestore/assetdir'] && substr($_POST['/core/filestore/assetdir'], -1) != '/'){
			$_POST['/core/filestore/assetdir'] = $_POST['/core/filestore/assetdir'] . '/';
		}
		if($_POST['/core/filestore/publicdir'] && substr($_POST['/core/filestore/publicdir'], -1) != '/'){
			$_POST['/core/filestore/publicdir'] = $_POST['/core/filestore/publicdir'] . '/';
		}
		
		$_SESSION['configs'] = $_POST;
	}
	
	// The page can be reinitialized after this logic ends if necessary.
	$p = new InstallPage();
	$p->template = 'templates/configs.tpl';
	
	// Set all the options from the configuration that may be used.
	$p->assign('/core/filestore/backend', ConfigHandler::Get('/core/filestore/backend'));
	$p->assign('/core/ftp/username', ConfigHandler::Get('/core/ftp/username'));
	$p->assign('/core/ftp/password', ConfigHandler::Get('/core/ftp/password'));
	$p->assign('/core/ftp/path', ConfigHandler::Get('/core/ftp/path'));
	$p->assign('/core/aws/key', ConfigHandler::Get('/core/aws/key'));
	$p->assign('/core/aws/secretkey', ConfigHandler::Get('/core/aws/secretkey'));
	$p->assign('/core/aws/accountid', ConfigHandler::Get('/core/aws/accountid'));
	$p->assign('/core/aws/canonicalid', ConfigHandler::Get('/core/aws/canonicalid'));
	$p->assign('/core/aws/canonicalname', ConfigHandler::Get('/core/aws/canonicalname'));
	$p->assign('/core/filestore/assetdir', ConfigHandler::Get('/core/filestore/assetdir'));
	$p->assign('/core/filestore/publicdir', ConfigHandler::Get('/core/filestore/publicdir'));
	
	// Look up the settings now.
	$backend = ConfigHandler::Get('/core/filestore/backend');
	
	// Backend isn't set... so set it!
	if($backend === null){
		$p->render();
	}
	elseif($backend == 'local' && ConfigHandler::Get('/core/ftp/username')){
		$ftp = \Core\FTP();
		if(!$ftp){
			$p->assign('error', 'Unable to connect with provided FTP credentials');
			$p->render();
		}
		
		// Check the FTP directory for a presence of an index.php file.
		if(!in_array('index.php', ftp_nlist($ftp, '.'))){
			$p->assign('error', 'Unable to locate index.php inside the FTP relative path, please ensure that it points to the root path of the application.');
			$p->render();
		}
		
		// Make sure it's writable by the current user.
		// This also ensures that the directory points to the correct location.
		// by reading some random data, writing it to the web root, and re-reading it.. it ensures that
		// it's the directory I want it to be.
		$randfh = fopen('/dev/urandom', 'r');
		$randomdat = fread($randfh, 32);
		fclose($randfh);
		$fh = fopen('php://memory', 'r+');
		fputs($fh, $randomdat);
		rewind($fh);
		if(!ftp_fput($ftp, 'random-test-file', $fh, FTP_BINARY, 0)){
			fclose($fh);
			$p->assign('error', 'Unable to write to FTP relative path, please ensure it is correct');
			$p->render();
		}
		
		ftp_chmod($ftp, 0644, 'random-test-file');
		
		$fp = fsockopen((isset($_SERVER['HTTPS']) ? 'ssl://' : '') . $_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT']);
		if($fp) {
			fwrite($fp, "GET " . ROOT_WDIR . "random-test-file HTTP/1.0\r\n\r\n");
			stream_set_timeout($fp, 2);
			if(substr(fread($fp, 2018), -32) != $randomdat){
				ftp_delete($ftp, 'random-test-file');
				fclose($fh);
				$p->assign('error', 'Unable to read temporary file uploaded via FTP, please ensure that it points to the root path of the applicaiton.');
				$p->render();
			}
		}
		// And cleanup
		fclose($fh);
		ftp_delete($ftp, 'random-test-file');
	}
	elseif($backend == 'local'){
		// Regular backend with no FTP use, make sure the directories are writable.
		$dir = \Core\directory(ROOT_PDIR . ConfigHandler::Get('/core/filestore/assetdir'));
		if($dir->mkdir() === false){
			$p->assign('error', 'Unable to write to asset directory [' . ROOT_PDIR . ConfigHandler::Get('/core/filestore/assetdir') . '], please check permissions.');
			$p->render();
		}
		$dir = \Core\directory(ROOT_PDIR . ConfigHandler::Get('/core/filestore/publicdir'));
		if($dir->mkdir() === false){
			$p->assign('error', 'Unable to write to public directory [' . ROOT_PDIR . ConfigHandler::Get('/core/filestore/publicdir') . '], please check permissions.');
			$p->render();
		}
	}
	

	
	// Everything above must have completed alright.... install the core finally!
	$p = new InstallPage();
	$p->template = 'templates/install.tpl';
	$p->assign('component', 'Core');
	$core = ComponentFactory::Load(ROOT_PDIR . 'core/component.xml');
	$core->load();
	$changes = $core->install();
	if($changes) $p->assign('log', implode("\n", $changes));
	else $p->assign('log', 'erm... nothing changed :?');
	// Don't forget to enable the component, (remember, components are not auto-enabled if not in development mode)
	$core->enable();
	$p->assign('location', '');
	$p->render();
}


try{
	HookHandler::DispatchHook('db_ready');

	// Ensure that the core component cache is purged too!
	Core::Cache()->delete('core-components');
	
	// Just to make sure.
	$changes = array();
	Core::Singleton();
	Core::LoadComponents();

	// This advanced logic is required because some components may not be loaded in the order that they are available.
	$list = Core::GetComponents();
	do {
		$size = sizeof($list);
		foreach ($list as $n => $c) {
			/** @var $c Component_2_1 */

			// If the component is installed but disabled, just enable it.
			// This can happen because the act of loading the core will install whatever it can find.
			if($c->isInstalled() && !$c->isEnabled()){
				$c->enable();

				$changes[] = 'Component ' . $c->getName() . '...';
				$changes[] = 'Enabled component, already installed.';

				unset($list[$n]);
				continue;
			}

			// Installed components are ignored
			if($c->isInstalled()){
				// Enable it anyway, (just in case)
				$c->enable();

				unset($list[$n]);
				continue;
			}

			if ($c->isLoadable()) {
				// w00t
				$changes[] = 'Component ' . $c->getName() . '...';
				$change = $c->install();
				$c->loadFiles();

				if($change === false) $changes[] = 'Installed with no changes needed';
				else $changes = array_merge($changes, $change);

				// Don't forget to enable the component, (remember, components are not auto-enabled if not in development mode)
				$c->enable();

				unset($list[$n]);
				continue;
			}
			else{
				echo $c->getName() . ' is not loadable :(<br/>';
			}
		}
	}
	while ($size > 0 && ($size != sizeof($list)));


	foreach(ThemeHandler::GetAllThemes() as $t){
		$t->load();

		if($t->isInstalled()) continue;

		$changes[] = 'Theme ' . $t->getName() . '...';
		$change = $t->install();
		if($change === false) $changes[] = 'No change needed.';
		else $changes = array_merge($changes, $change);
	}
	
	// Flush the system cache, just in case
	Core::Cache()->flush();
	
	// In theory, everything should be installed now.
	//header('Location:../');
	//die();
	
	$p = new InstallPage();
	$p->template = 'templates/install.tpl';
	$p->assign('component', 'Everything else');
	$p->assign('log', implode("\n", $changes));
	$p->assign('location', '../');
	
	$p->render();
}
catch(Exception $e){
	//$stack = '<pre>' . $e->getTraceAsString() . '</pre>';
	
	die($e->getMessage() . "\n<br/>\n<br/>" . 'something broke, please fix it.');
}
