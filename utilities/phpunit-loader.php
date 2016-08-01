<?php
//namespace CorePlusTest;

//use RuntimeException;

define('ROOT_PDIR', realpath(dirname(__DIR__) . '/src/') . '/');
define('ROOT_WDIR', '/');
define('TMP_DIR', sys_get_temp_dir() . '/coreplus-phpunit-tests/');

// Remove the tmp directory if it exists already.
if(is_dir(TMP_DIR)){
	exec('rm -fr ' . escapeshellarg(TMP_DIR));
	mkdir(TMP_DIR);
}


// I need to load up the configuration file to get some settings first... namely the site_url.
// This is because if the site url doesn't match the incoming HTTP_HOST... the system is going to redirect without executing anything.
$settingsxml = new SimpleXMLElement(ROOT_PDIR . 'config/configuration.xml', 0, true);
$siteurl = 'localhost';
foreach($settingsxml->return as $node){
	/** @var $node SimpleXMLElement */
	if($node->attributes()['name'] == 'site_url' && (string)$node->value){
		$siteurl = (string)$node->value;
		break;
	}
}

// Make this page load appear as a standard web request instead of a CLI one.
//unset($_SERVER['SHELL']);
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['REQUEST_URI'] = '/phpunit-test';
$_SERVER['HTTP_HOST'] = $siteurl;
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_USER_AGENT'] = 'Core Plus phpunit Tester Script';


// Cleanup some variables that I don't need anymore.
unset($path, $settingsxml, $siteurl, $node);


// When working on the core, it's best to switch this back to core/bootstrap.php!
// Set this to true to skip checking for the compiled version.
$skipcompiled = true;

if(!$skipcompiled && file_exists(ROOT_PDIR . 'core/bootstrap.compiled.php')) require_once(ROOT_PDIR . 'core/bootstrap.compiled.php');
else require_once(ROOT_PDIR . 'core/bootstrap.php');

// quiet!
// CLI mode shouldn't have HTML error reporting.
ini_set('html_errors', 0);
// These tests require more memory allocated as well.
// This may hinder some bugs that pop up with low-memory allocations, but oh well.
ini_set('memory_limit', '512M');