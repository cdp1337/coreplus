<?php
//namespace CorePlusTest;

//use RuntimeException;

define('ROOT_PDIR', realpath(dirname(__DIR__) . '/src/') . '/');
define('ROOT_WDIR', '/');


// I need to load up the configuration file to get some settings first... namely the site_url.
// This is because if the site url doesn't match the incoming HTTP_HOST... the system is going to redirect without executing anything.
$settingsxml = new SimpleXMLElement(ROOT_PDIR . 'config/configuration.xml', 0, true);
$siteurl = 'localhost';
foreach($settingsxml->return as $node){
	/** @var $node SimpleXMLElement */
	if($node->attributes()['name'] == 'site_url'){
		$siteurl = (string)$node->value;
		break;
	}
}

// Make this page load appear as a standard web request instead of a CLI one.
unset($_SERVER['SHELL']);
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['REQUEST_URI'] = '/phpunit-test';
$_SERVER['HTTP_HOST'] = $siteurl;
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_USER_AGENT'] = 'Core Plus phpunit Tester Script';


// Cleanup some variables that I don't need anymore.
unset($path, $settingsxml, $siteurl, $node);



require(ROOT_PDIR . 'core/bootstrap.compiled.php');

// quiet!
// CLI mode shouldn't have HTML error reporting.
ini_set('html_errors', 0);
