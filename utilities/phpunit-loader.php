<?php
//namespace CorePlusTest;

//use RuntimeException;

// Make this page load appear as a standard web request instead of a CLI one.
unset($_SERVER['SHELL']);
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['REQUEST_URI'] = '/phpunit-test';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_USER_AGENT'] = 'Core Plus phpunit Tester Script';

// I need to combine the filename onto the current path to determine core plus's installation path.
$path = $_SERVER['PWD'] . '/' . $_SERVER['SCRIPT_FILENAME'];
// If there is any "./" here, remove that... it's redundant.
$path = str_replace('./', '', $path);
// The path will contain /utilities/phpunit.phar... trim that off to reveal the root installation directory.
$path = substr($path, 0, -22);

define('ROOT_PDIR', $path);
define('ROOT_WDIR', '/');

require(ROOT_PDIR . 'core/bootstrap.php');