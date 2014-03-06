#!/usr/bin/env php
<?php
/**
 * Script to run the "next" upgrades queued up for components.
 *
 * This is useful for package development that runs for several commits or have multiple developers.
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20140305.1600
 * @package Core
 */

if(!isset($_SERVER['SHELL'])){
	die("Please run this script from the command line.");
}

// This is required to establish the root path of the system, (since it's always one directory up from "here"
define('ROOT_PDIR', realpath(dirname(__DIR__) . '/src/') . '/');
define('ROOT_WDIR', '/');
define('BASE_DIR', realpath(dirname(__DIR__)) . '/');

// Include the core bootstrap, this will get the system functional.
require_once(ROOT_PDIR . 'core/bootstrap.php');

foreach(Core::GetComponents() as $c){
	/** @var Component_2_1 $c */

	$r = $c->upgrade(true);

	if(is_array($r)){
		echo "Upgraded component " . $c->getName() . "\n";
		echo implode("\n", $r) . "\n\n";
	}
}