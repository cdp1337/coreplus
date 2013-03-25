#!/usr/bin/env php
<?php
/**
 * The purpose of this file is to archive up bundles from existing packages.
 *
 * @package Core Plus\CLI Utilities
 * @since 1.9
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
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

// Include the core bootstrap, this will get the system functional.
require_once(ROOT_PDIR . 'core/bootstrap.php');

// @todo
// scan the bundles directory for <bundler> definition files.
// then, check if there is currently one that exists and ask the user
// what version to make the new one
// and copy the files from the respective export location.
// this should use just the newest versions, since that's probably what the user wants anyways.


// Get the bundler metafiles
$bundlefiles = array();
$dir = ROOT_PDIR . 'exports/bundles';
if(is_dir($dir)){
	$dh = opendir($dir);
	if($dh){
		while(($file = readdir($dh)) !== false){
			// Skip hidden files
			if($file{0} == '.') continue;
			// Skip directories
			if(is_dir($dir . '/' . $file)) continue;

			// Skip non-xml files
			if(!preg_match('/\.xml$/i', $file)) continue;

			$bundlefiles[] = $file;
		}
		closedir($dh);
	}
}

// They should be in alphabetical order...
sort($bundlefiles);

if(count($bundlefiles) == 1){
	$ans = 0;
	echo "Using bundle " . $bundlefiles[0] . NL;
}
else{
	$ans = CLI::PromptUser("Process which bundle?", $bundlefiles);
}
$b = $bundlefiles[$ans];


// Open it up
$xml = new XMLLoader();
$xml->setRootName('bundler');
$xml->loadFromFile($dir . '/' . $b);

$name  = $xml->getRootDOM()->getAttribute('name');
$sname = preg_replace('/[^a-z0-9-\.\+]*/i', '', str_replace(' ', '-', $name));

$destdir = $dir . '/' . $sname;

// Check the dest directory for current versions.
if(!is_dir($destdir)) mkdir($destdir);
$dh = opendir($destdir);
$version = '0.0.0';
if($dh){
	while(($file = readdir($dh)) !== false){
		// Skip hidden files
		if($file{0} == '.') continue;
		// Skip directories
		if(is_dir($destdir . '/' . $file)) continue;

		// Skip non-tgz files
		if(!preg_match('/^' . $sname . '.*\.tgz$/i', $file)) continue;

		// Yay, extract out the version from this filename.
		$filev = substr($file, strlen($sname) + 1, -4);

		if(Core::VersionCompare($version, $filev, 'lt')) $version = $filev;
	}
	closedir($dh);
}

if($version == '0.0.0'){
	$version = '1.0.0';
}
else{
	$versionc = Core::VersionSplit($version);
	// Increment the revision version for the default.
	if($versionc['point'] >= 9){
		++$versionc['minor'];
		$versionc['point'] = 0;
	}
	else{
		++$versionc['point'];
	}

	$version = $versionc['major'] . '.' . $versionc['minor'] . '.' . $versionc['point'];
}

// Prompt the user just in case.
$version = CLI::PromptUser('Please set the new version or', 'text', $version);


// GO!
if(!is_dir($destdir)) mkdir($destdir);
$desttgz = $sname . '-' . $version;
if(!is_dir($destdir . '/' . $desttgz)) mkdir($destdir . '/' . $desttgz);


// Get a list of packages to export.
$export = array();
$export[] = array(
	'name' => 'core',
	'type' => 'core',
	'src' => ROOT_PDIR . 'exports/core',
	'dest' => $destdir . '/' . $desttgz
);
foreach($xml->getElements('//component') as $el){
	$export[] = array(
		'name' => strtolower($el->getAttribute('name')),
		'type' => 'component',
		'src' => ROOT_PDIR . 'exports/components',
		'dest' => $destdir . '/' . $desttgz . '/components/' . strtolower($el->getAttribute('name'))
	);
}
foreach($xml->getElements('//theme') as $el){
	$export[] = array(
		'name' => strtolower($el->getAttribute('name')),
		'type' => 'theme',
		'src' => ROOT_PDIR . 'exports/themes',
		'dest' => $destdir . '/' . $desttgz . '/themes/' . strtolower($el->getAttribute('name'))
	);
}


foreach($export as $dat){
	echo "\n";
	echo "--------------------------------\n";
	echo "Searching for " . $dat['type'] . ' ' . $dat['name'] . "...\n";

	// Extract out the newest version of this component
	$compversion = '0.0.0';
	$dh = opendir($dat['src']);
	if(!$dh){
		die('Unable to open required directory for the bundler' . "\n");
	}
	while(($file = readdir($dh)) !== false){
		// Skip hidden files
		if($file{0} == '.') continue;
		// Skip directories
		if(is_dir($destdir . '/' . $file)) continue;

		// Skip non-tgz files
		if(strpos($file, $dat['name']) !== 0) continue;
		if(!preg_match('/\.tgz$/i', $file)) continue;

		// Yay, extract out the version from this filename.
		$filev = substr($file, strlen($dat['name']) + 1, -4);

		if(Core::VersionCompare($compversion, $filev, 'lt')) $compversion = $filev;
	}
	closedir($dh);
	if($compversion == '0.0.0') die('Unable to find required component ' . $dat['name'] . "\n");
	echo "Found version " . $compversion . '!' . "\n";

	echo "Extracting tarball...\n";
	if(!is_dir($dat['dest'])) mkdir($dat['dest']);
	exec('tar -xzf ' . $dat['src'] . '/' . $dat['name'] . '-' . $compversion . '.tgz' . ' -C ' . $dat['dest'] . ' --transform "s:\./data::" ./data', $out, $result);
	if($result != 0) die(":( \n");
	echo "OK!\n";
}

// Keys
foreach($xml->getElements('//key') as $el){
	$id = $el->getAttribute('id');
	echo "\n";
	echo "--------------------------------\n";
	echo "Exporting key " . $id . "...\n";
	exec('gpg -a --export ' . $id . ' > "' . $destdir . '/' . $id . '.gpg"');
	exec('gpg --homedir "' . $destdir . '/' . $desttgz . '/gnupg" --no-permission-warning --import "' . $destdir . '/' . $id . '.gpg"', $output, $result);
	unlink($destdir . '/' . $id . '.gpg');
	echo "OK!\n";
}


// create the tarballs!
echo "Creating tarball...\n";
exec('tar -czf "' . $destdir . '/' . $desttgz . '.tgz" -C "' . $destdir . '" --exclude-vcs --exclude=*~ --exclude=._* ' . $desttgz);

echo "Creating zip..\n";
exec('cd "' . $destdir . '/"; zip -rq "' . $desttgz . '.zip" "' . $desttgz . '"; cd -');

echo "Creating hashes...\n";
exec('md5sum "' . $destdir . '/' . $desttgz . '.tgz" > "' . $destdir . '/' . $desttgz . '.tgz.md5"');
exec('md5sum "' . $destdir . '/' . $desttgz . '.zip" > "' . $destdir . '/' . $desttgz . '.zip.md5"');

echo "Cleaning up...\n";
exec('rm -fr "' . $destdir . '/' . $desttgz . '"');