<?php
/**
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 * 
 * Copyright (C) 2009  Charlie Powell <powellc@powelltechs.com>
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


// I expect some configuration options....
if(PHP_VERSION < '6.0.0' && ini_get('magic_quotes_gpc')){
	die('This application cannot run with magic_quotes_gpc enabled, please disable them now!');
}


// Start a traditional session.
session_start();

// I need to override some defines here...
$rpdr = pathinfo(dirname($_SERVER['SCRIPT_FILENAME' ]), PATHINFO_DIRNAME );
define('ROOT_PDIR', $rpdr . '/');


// Try to load as much of the system as possible.
require_once('../core/bootstrap_predefines.php');
require_once('../core/libs/core/Debug.class.php');
require_once("../core/libs/core/ISingleton.interface.php");
require_once('../core/libs/core/XMLLoader.class.php');
require_once('../core/libs/core/InstallArchive.class.php');
require_once('../core/libs/core/InstallArchiveAPI.class.php');
require_once("../core/libs/core/HookHandler.class.php");
HookHandler::singleton();
require_once("../core/libs/core/ConfigHandler.class.php");
ConfigHandler::singleton();




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





$dir = ROOT_PDIR . 'config/';
if(!($dh = opendir($dir))) die('Cannot open ' . $dir . ' for reading.');

while($file = readdir($dh)){
	if($file{0} == '.') continue;
	if(substr($file, -4) != '.xml') continue;
	$base = substr($file, 0, -4);
	
	$configs = ConfigHandler::LoadConfigFile($base);
	var_dump($configs);
}



















die(); // Sigh.... again


// Try to make a connection to the FTP server first of all.
$ftpconn = ftp_connect('localhost');
if(!$ftpconn){
	InstallPage::SetVariable('error', 'Unable to connect to FTP on localhost, please ensure it is running.');
	InstallPage::Render();
}



// Did they submit the ftpcreds?
if(isset($_POST['submittype']) && $_POST['submittype'] == 'ftpcreds'){
	$user = $_POST['username'];
	$pass = $_POST['password'];
	// Try to connect first.
	$ftpresult = @ftp_login($ftpconn, $user, $pass);
	if(!$ftpresult){
		InstallPage::SetVariable('error', 'Unable to login to FTP with the provided credentials.');
	}
	else{
		// Save this data.
		$_SESSION['FTPUSER'] = $user;
		$_SESSION['FTPPASS'] = $pass;
	}
	unset($user, $pass, $ftpresult);
}
elseif(isset($_SESSION['FTPUSER'])){
	// Make the FTP connection for anything to use.
	ftp_login($ftpconn, $_SESSION['FTPUSER'], $_SESSION['FTPPASS']);
}


// For part of the installation (and for security)... ensure FTP credentials have been provided.
if(!isset($_SESSION['FTPUSER'])){
	$body = <<< EOD
<p class="message-note rounded">Please provide your FTP credentials to continue.</p>
<form action="" method="POST">
	<input type="hidden" name="submittype" value="ftpcreds"/>
	
	<label for="username">Username</label><br/>
	<input type="text" name="username" id="username"/><br/><br/>

	<label for="password">Password</label><br/>
	<input type="password" name="password" id="password"/><br/><br/>
	
	<input type="submit" value="Continue"/>
</form>
EOD;
	InstallPage::SetVariable('body', $body);
	InstallPage::Render();
}






if(isset($_POST['submittype']) && $_POST['submittype'] == 'ftpmkdir'){
	$dir = urldecode($_REQUEST['dir']);
	ftp_chdir($ftpconn, $dir);
	ftp_mkdir($ftpconn, $_REQUEST['newdir']);
	$_REQUEST['ftpcheckdir'] = $dir . '/' . $_REQUEST['newdir'];
}

if(isset($_REQUEST['submittype']) && $_REQUEST['submittype'] == 'ftpdir'){
	$dir = urldecode($_REQUEST['dir']);
	$dir = str_replace('../', '', $dir);
	if($dir{0} != '.') $dir = '.' . $dir;
	// And save this directory.
	$_SESSION['FTPDIR'] = $dir;
}


if(!isset($_SESSION['FTPDIR'])){
	if(!isset($_REQUEST['ftpcheckdir'])) $dir = '.';
	else $dir = urldecode($_REQUEST['ftpcheckdir']);

	$dir = str_replace('../', '', $dir);
	if($dir{0} != '.') $dir = '.' . $dir;

	$contents = ftp_rawlist($ftpconn, $dir);
	//$contents = ftp_nlist($ftpconn, '.');
	// Sift out the directories in this list.

	$body = '<p class="message-note rounded">Please select the directory to install CAE2 into.</p>';
	$body .= '<fieldset>';

	if(strpos($dir, '/') !== false){
		$ds = '';
		// Make the tree of directories.
		foreach(explode('/', $dir) as $d){
			$ds .= (($ds == '')? '' : '/') . $d;
			$body .= '<a href="install.php?ftpcheckdir=' . urlencode($ds) . '">' . $d . '</a>&nbsp;/&nbsp;';
		}
	}
	else{
		$body .= '<a href="install.php?ftpcheckdir=' . urlencode($dir) . '">' . $dir . '</a>&nbsp;/&nbsp;';
	}
	
	$body .= '<br/><br/>';
	
	$body .= '<a href="install.php?submittype=ftpdir&dir=' . urlencode($dir) . '">(install here)</a>';
	$body .= '<br/><br/>or browse...<br/><br/>';


	foreach($contents as $c){
		if($c{0} != 'd') continue; // Skip non-directories
		// The filename should be the xth character.
		$c = substr($c, 62);
		$body .= '<a href="install.php?ftpcheckdir=' . urlencode($dir . '/' . $c) . '">' . $c . '</a><br/>';
	}

	$body .= '<br/><br/>or make new directory here...<br/><br/>';
	$body .= '<form action="" method="POST"><input type="hidden" name="submittype" value="ftpmkdir"/><input type="hidden" name="dir" value="' . urlencode($dir) . '"/><input type="text" name="newdir"/><input type="submit" value="Create"/></form>';

	$body .= '</fieldset>';


	InstallPage::SetVariable('body', $body);
	InstallPage::Render();
}


// An explicit deny script for .htaccess use would be appropriate too.
$htdeny = <<< EOD
# This is specifically created to prevent access to ANYTHING in this directory.
#  Under no situation should anything in the config directory be readable
#  by anyone at any time.

<Files *>
Order deny,allow
Deny from All
</Files>
EOD;

// This data needs to be a stream so I can write it to the FTP server... :/
$hthandle = fopen('php://temp', 'r+');
fwrite($hthandle, $htdeny);
rewind($hthandle);



// Setup this directory and prep it for installation of CAE2 if it isn't already.
$dirs = array(
	// "Dropins" directory for easily installing new components, libraries, etc.
	array('dir' => 'dropins', 'perm' => 'deny'),
	// "Config" directory for storing flat-file XML-based configuration files.
	array('dir' => 'config', 'perm' => 'deny'),
	// "Assets" directory for storing all "goodies" an application may need
	array('dir' => 'assets', 'perm' => 'allow'),
	// "Public Assets" directory for non-secured files.
	array('dir' => 'assets/public', 'perm' => 'allow'),
	// "Private Assets" directory for non-accessable files, blocked via .htaccess or other means.
	array('dir' => 'assets/private', 'perm' => 'deny'),
	// "Static Assets" directory for theme files, images, CSS, JS, essentially any file which may be retrieved directly by the browser.
	array('dir' => 'assets/static', 'perm' => 'allow'),
);

foreach($dirs as $d){
	var_dump(ftp_size($ftpconn, $d['dir']));
	if(ftp_size($ftpconn, $d['dir']) == -1) ftp_mkdir($ftpconn, $d['dir']);
	ftp_chmod($ftpconn, 0755, $d['dir']);
	if($d['perm'] == 'deny'){
		ftp_fput($ftpconn, $d['dir'] . '/.htaccess', $hthandle, FTP_BINARY);
	}
}



























die('Halting install script here!');
/**
 * The main installer, but I need the bootstrap still.
 */
require_once('core/bootstrap.php');

// First of all, if the site is not configured yet, (ie: new install), redriect.
//if(SITE_CONFIGURED){ 
if(Core::IsInstalled()){
	header('Location: index.php');
	die('If your browser does not refresh, please <a href="index.php">Click Here</a>');
}

// Try to use SSL if available.
CAEUtils:: RequireSSL();


// I need a valid session, one way or another!
if(!ComponentHandler::IsComponentAvailable('Session')) session_start();

if(!ComponentHandler::IsComponentAvailable('CurrentPage')){
	ob_start();
	// Give some basic HTML starting goodies.
	echo '<html>
	<head>
		<title>KaiToo Installer</title>
		<!-- Load the google javascript API, which will give me jQuery -->
		<script src="http://www.google.com/jsapi"></script>
		<script type="text/javascript">
			// Load jQuery
			google.load("jquery", "1.3.2");
			
			// Anytime an input is changed, set a flag to warn the user when they click on the "next" link.
			var has_changes = false;
			google.setOnLoadCallback(function() {
				$(":input").change(function(){ has_changes = true; });
				
				$("a").click(function(){
					if(!has_changes) return true;
					
					if( ($(this).html()).match(/next/i) != null ){
						var result = confirm("!!                     You have unsaved changes                     !!\n\nIf you want to save them, click Ok and \'Update Settings\'\n\nTo abandon them and continue next, click Cancel.");
						if(result){
							$(this).replaceWith("<span>Saving...</span>");
							$("form").submit();
							return false;
						}
						else{
							return true;
						}
						
					}
				});
			});
		</script>

	</head>
	<body>';
}


//$_SESSION['installer_steps'] = null;

if(!isset($_SESSION['installer_steps']) || empty($_SESSION['installer_steps'])){
	$_SESSION['installer_steps'] = array(
		'check_requirements' => false,
		'configure_core' => false,
		'configure_db' => false,
		'test_install_db' => false,
		'install_db' => false,
		'finished' => false,
	);
}

if(isset($_GET['next'])){
	if(isset($_SESSION['installer_steps'][$_GET['next']])){
		// A few checks, ie: is the database installed?!?
		//if($_GET['next'] == 'test_install_db' && !LibraryHandler::IsLibraryAvailable('db')){
		//	CAEUtils::SetMessage('You have not configured or tested the database settings yet.', 'error');
		//}
		//else{
			$_SESSION['installer_steps'][$_GET['next']] = true;
		//}
	}
}
if(isset($_GET['prev'])){
	if(isset($_SESSION['installer_steps'][$_GET['prev']])){
		$prev = false;
		foreach($_SESSION['installer_steps'] as $k => $v){
			if($k == $_GET['prev']){
				if($prev) $_SESSION['installer_steps'][$prev] = false;
				break;
			}
			$prev = $k;
		}
	}
}

// Run through the array of installer steps and get the next to do.
foreach($_SESSION['installer_steps'] as $step => $val){
	if(!$val){
		include(ROOT_PDIR . 'core/installer/' . $step . '.php');
		break;
	}
}

// Now that whatever data should be dispalyed to the screen... get the template.

if(class_exists('Template') && (!isset($_GET['template']) || strtolower($_GET['template']) == 'html')){
	$page = new Template();
	
	// Set any output to the body.
	$page->assign('body', ob_get_contents());
    ob_clean();
    // Assign the theme URL path correctly.
    $page->assign('theme_url', ROOT_WDIR . 'templates/default');
    
    // Give the time rendered in, (used in development mode.)
    $time_end = microtime(true);
	$time = round($time_end - $time_start, 4);
	$page->assign('_pageLoadTime', $time);
	
	$page->assign('messages', CAEUtils::getMessages());
	$page->assign('head', '<script type="text/javascript" src="' . ROOT_URL . 'libraries/jquery/dist/jquery.js"></script>
	<script type="text/javascript" src="' . ROOT_URL . 'libraries/jquery/dist/jquery-ui.js"></script>
	<link rel="stylesheet" type="text/css" href="' . ROOT_URL . 'libraries/jquery/css/ui.all.css" />');
	
    $page->display(ROOT_PDIR . 'templates/default/index.tpl');
}
