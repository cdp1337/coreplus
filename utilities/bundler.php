#!/usr/bin/env php
<?php
/**
 * The purpose of this file is to archive up bundles from existing packages.
 *
 * @package Core\CLI Utilities
 * @since 1.9
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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
define('BASE_DIR', realpath(dirname(__DIR__)) . '/');

// Include the core bootstrap, this will get the system functional.
require_once(ROOT_PDIR . 'core/bootstrap.php');

$args = new \Core\CLI\Arguments(
	[
		'help' => [
			'description' => 'Display help and exit.',
			'value' => false,
			'shorthand' => ['?', 'h'],
		],
	]
);

$args->processArguments();

if($args->getArgumentValue('help')){
	$args->printUsage();
	exit;
}


$dir = BASE_DIR . 'exports/bundles';
$bundles = [];


// Get the bundler metafiles
$bundlefiles = array();
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

// Transpose them to the keyname => human readable name.
foreach($bundlefiles as $b){
	// Open it up
	$xml = new XMLLoader();
	$xml->setRootName('bundler');
	$xml->loadFromFile($dir . '/' . $b);

	$name  = $xml->getRootDOM()->getAttribute('name');
	$sname = preg_replace('/[^a-z0-9-\.\+]*/i', '', str_replace(' ', '-', $name));
	$bundles[] = [
		'file'  => $b,
		'name'  => $name,
		'sname' => $sname,
		'xml'   => $xml,
	];
}


if(!sizeof($bundles)){
	echo "No bundles found, exporting will bundle the entire site." . NL;
	$bundles[] = [
		'file'  => '',
		'name'  => SITENAME,
		'sname' =>  preg_replace('/[^a-z0-9-\.\+]*/i', '', str_replace(' ', '-', SITENAME)),
		'xml'   => null,
	];
}
else{
	echo "Found the following bundles:" . NL;
	foreach($bundles as $b){
		echo $b['name'] . ' (' . $b['file'] . ')' . NL;
	}
}
sleep(1);

// Prompt the user with what version the new bundles will be.
$version = Core::GetComponent('core')->getVersion();
$version = \Core\CLI\CLI::PromptUser('Please set the bundled version or', 'text', $version);



foreach($bundles as $b){
	/** @var XMLLoader|null $xml */
	$xml = $b['xml'];
	$destdir = $dir . '/' . $b['sname'];

	// Check the dest directory for current versions.
	if(!is_dir($destdir)) mkdir($destdir);
	$desttgz = $b['sname'] . '-' . $version;
	if(!is_dir($destdir . '/' . $desttgz)) mkdir($destdir . '/' . $desttgz);

	// Get a list of packages to export.
	$export = array();
	$export[] = array(
		'name' => 'core',
		'type' => 'core',
		'src'  => BASE_DIR . 'exports/core',
		'dest' => $destdir . '/' . $desttgz
	);

	// Add the components.
	if($xml){
		foreach($xml->getElements('//component') as $el){
			$export[] = array(
				'name' => strtolower($el->getAttribute('name')),
				'type' => 'component',
				'src'  => BASE_DIR . 'exports/components',
				'dest' => $destdir . '/' . $desttgz . '/components/' . strtolower($el->getAttribute('name'))
			);
		}
	}
	else{
		foreach(Core::GetComponents() as $c){
			/** @var Component_2_1 $c */
			if($c->getKeyName() == 'core'){
				// Core is already included above.
				continue;
			}
			$export[] = array(
				'name' => strtolower($c->getName()),
				'type' => 'component',
				'src'  => BASE_DIR . 'exports/components',
				'dest' => $destdir . '/' . $desttgz . '/components/' . $c->getKeyName(),
			);
		}
	}

	if($xml){
		foreach($xml->getElements('//theme') as $el){
			$export[] = array(
				'name' => strtolower($el->getAttribute('name')),
				'type' => 'theme',
				'src'  => BASE_DIR . 'exports/themes',
				'dest' => $destdir . '/' . $desttgz . '/themes/' . strtolower($el->getAttribute('name'))
			);
		}
	}
	else{
		foreach(\ThemeHandler::GetAllThemes() as $t){
			/** @var Theme\Theme $t */
			$export[] = array(
				'name' => strtolower($t->getName()),
				'type' => 'theme',
				'src'  => BASE_DIR . 'exports/themes',
				'dest' => $destdir . '/' . $desttgz . '/themes/' . $t->getKeyName(),
			);
		}
	}


	$changelog = '<h2>Packages included in ' . $b['name'] . ' ' . $version . '</h2>' . "\n\n";


	foreach($export as $dat){
		echo "\n";
		echo "--------------------------------\n";
		echo "Searching for " . $dat['type'] . ' ' . $dat['name'] . "...\n";

		// Extract out the newest version of this component
		$compversion = '0.0.0';
		$dh = opendir($dat['src']);
		if(!$dh){
			die('Unable to open required directory for the bundler [' . $dat['src'] . ']' . "\n");
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

			if($compversion == '0.0.0'){
				// If there's no version set yet...
				// This is because if the file version is < 1, the check will, (for some reason), return false.
				$compversion = $filev;
			}
			else{
				if(Core::VersionCompare($compversion, $filev, 'lt')) $compversion = $filev;
			}

		}
		closedir($dh);
		if($compversion == '0.0.0') die('Unable to find required component ' . $dat['name'] . "\n");
		echo "Found version " . $compversion . '!' . "\n";

		echo "Extracting tarball... ";
		if(!is_dir($dat['dest'])) mkdir($dat['dest']);
		exec('tar -xzf ' . $dat['src'] . '/' . $dat['name'] . '-' . $compversion . '.tgz' . ' -C ' . $dat['dest'] . ' --transform "s:\./data::" ./data', $out, $result);
		if($result != 0) die(":( \n");
		echo "OK!\n";

		echo "Processing CHANGELOG... ";
		if($dat['name'] == 'core'){
			$parser = new Core\Utilities\Changelog\Parser($dat['name'], $dat['dest'] . '/core/CHANGELOG');
		}
		else{
			$parser = new Core\Utilities\Changelog\Parser($dat['name'], $dat['dest'] . '/CHANGELOG');
		}


		if(!$parser->exists()){
			echo "Failed!\n";
			$changelog .= '<h3>' . $dat['name'] . ' ' . $compversion . '</h3>';
		}
		else{
			try{
				$parser->parse();

				/** @var $thisversion Core\Utilities\Changelog\Section */
				$thisversion = $parser->getSection($compversion);

				// Read the current changelog.
				$changelog .= $thisversion->fetchAsHTML(3);

				echo "OK!\n";
			}
			catch(Exception $e){
				echo "Failed!\n";
				$changelog .= '<h3>' . $dat['name'] . ' ' . $compversion . '</h3>';
			}
		}
	}

	// Keys
	if($xml){
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
	}


	// Write out the changelog for the bundle.
	file_put_contents($destdir . '/' . $desttgz . '/packages.html', $changelog);


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
}


// Give the option to automatically commit and tag all changes for the component.
if(\Core\CLI\CLI::PromptUser('Bundles created, GIT tag the release for ' . $version . '?', 'boolean', true)){
	exec('git tag -m "Release Version ' . $version . '" -f ' . 'v' . str_replace('~', '-', $version));
	exec('git push --tags');
}