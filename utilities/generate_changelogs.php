#!/usr/bin/env php
<?php
/**
 * Simple CLI script to build a list of changes from the core, components, and themes.
 *
 * The list of changelogs will be generated in BASEDIR/build/changelogs.
 *
 * @package Core\CLI Utilities
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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

// This is required to establish the root path of the system, (since it's always one directory up from "here"
define('ROOT_PDIR', realpath(dirname(__DIR__) . '/src/') . '/');
define('BASE_DIR', realpath(dirname(__DIR__)) . '/');

// Include the core bootstrap, this will get the system functional.
require_once(ROOT_PDIR . 'core/bootstrap.php');

// Make sure the directory exists.
if(!is_dir(BASE_DIR . 'build/changelogs')){
	mkdir(BASE_DIR . 'build/changelogs');
}


$dir = ROOT_PDIR . 'components';
$dh = opendir($dir);
while(($file = readdir($dh)) !== false){
	if($file{0} == '.') continue;
	if(!is_dir($dir . '/' . $file)) continue;
	if(!is_readable($dir . '/' . $file . '/' . 'component.xml')) continue;

	$src  = $dir . '/' . $file . '/' . 'CHANGELOG';
	$dest = BASE_DIR . 'build/changelogs/components/' . $file . '.html';

	if(!is_readable($src)) continue;

	$c = Core::GetComponent($file);

	echo 'Generating changelog for ' . $c->getName() . "...\n";
	$parser = new Core\Utilities\Changelog\Parser($c->getName(), $src);
	$parser->parse();
	$parser->saveHTML($dest);
}
closedir($dh);



$dir = ROOT_PDIR . 'themes';
$dh = opendir($dir);
while(($file = readdir($dh)) !== false){
	if($file{0} == '.') continue;
	if(!is_dir($dir . '/' . $file)) continue;
	if(!is_readable($dir . '/' . $file . '/' . 'theme.xml')) continue;

	$src  = $dir . '/' . $file . '/' . 'CHANGELOG';
	$dest = BASE_DIR . 'build/changelogs/themes/' . $file . '.html';

	if(!is_readable($src)) continue;

	$t = ThemeHandler::GetTheme($file);

	echo 'Generating changelog for Theme/' . $t->getName() . "...\n";
	$parser = new Core\Utilities\Changelog\Parser('Theme/' . $t->getName(), $src);
	$parser->parse();
	$parser->saveHTML($dest);
}
closedir($dh);



$dir = ROOT_PDIR;
$src  = $dir . '/core/' . 'CHANGELOG';
$dest = BASE_DIR . 'build/changelogs/core.html';

echo 'Generating changelog for Core' . "...\n";
$parser = new Core\Utilities\Changelog\Parser('Core Plus', $src);
$parser->parse();
$parser->saveHTML($dest);
