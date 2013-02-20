#!/usr/bin/env php
<?php
/**
 * The purpose of this file is to create a repository that can be distributed 
 * pulled from remote servers.
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

// This is required to establish the root path of the system, (since it's always one directory up from "here"
$dir = realpath(dirname($_SERVER['PWD'] . '/' . $_SERVER['SCRIPT_FILENAME']) . '/..') . '/';

// Inlude the core bootstrap, this will get the system functional.
require_once($dir . 'core/bootstrap.php');


// I need a valid editor.
CLI::RequireEditor();

// And some info about the user.
// I need a few variables first about the user...
$packagername = '';
$packageremail = '';

CLI::LoadSettingsFile('packager');

if(!$packagername){
	$packagername = CLI::PromptUser('Please provide your name you wish to use for packaging', 'text-required');
}
if(!$packageremail){
	$packageremail = CLI::Promptuser('Please provide your email you wish to use for packaging.', 'text-required');
}

CLI::SaveSettingsFile('packager', array('packagername', 'packageremail'));


$destdir = ROOT_PDIR . 'exports/';
$tmpdir = ROOT_PDIR . 'exports/_tmp/';
// Ensure the export directory exists.
if(!is_dir($destdir)) exec('mkdir -p "' . $destdir . '"');
if(!is_dir($tmpdir)) exec('mkdir -p "' . $tmpdir . '"');


if(file_exists($destdir . 'repo.xml')){
	// w00t, load it up and import the info!
	$repo = new RepoXML($destdir . 'repo.xml');
	// Don't forget to remove any previous components!
	$repo->clearPackages();
}
else{
	// Just a new one works...
	$repo = new RepoXML();
}


// Prompt the user if there's not information already set on the necessary ones.
if(!$repo->getDescription()){
	$desc = CLI::PromptUser('Please enter a short description for this repo.', 'textarea');
	$repo->setDescription($desc);
}

// Load in the keys if not set.
if(!sizeof($repo->getKeys())){
	// Find and use the package maintainer's key.
	$out = array();
	exec('gpg --homedir "' . GPG_HOMEDIR . '" --no-permission-warning --list-secret-keys', $out);
	$key = null;
	$currentkey = null;
	foreach($out as $line){
		if(strpos($line, 'sec') === 0){
			// Remember this ID for the next line, it may be the email.
			$currentkey = preg_replace('#^.*/([A-F0-9]*).*$#', '$1', $line);
		}
		elseif($currentkey && strpos($line, 'uid') === 0 && strpos($line, $packageremail) !== false){
			// WOOT, a key was found and the email matches.
			$key = $currentkey;
			break;
		}
	}

	if($key){
		// Key was found, save that!
		$repo->addKey($key, $packagername, $packageremail);
	}
}


$addedpackages = 0;
$failedpackages = 0;


// An array of all the source directories to scan, (and their types).
$directories = array(
	array(
		'directory' => $destdir . 'core/',
		'relpath' => 'core/',
		'type' => 'core'
	),
	array(
		'directory' => $destdir . 'components/',
		'relpath' => 'components/',
		'type' => 'component'
	),
	array(
		'directory' => $destdir . 'themes/',
		'relpath' => 'themes/',
		'type' => 'theme'
	)
);

// Load in all valid components in the exports/components directory.
foreach($directories as $dir){
	echo NL . NL . '####### Processing ' . $dir['type'] . '... #######' . NL;
	$dh = opendir($dir['directory']);
	$output = array();
	$ret = null;
	if($dh){
		// Because after a LOT of results... this can get a little tedious to manage
		$sorted = array();
		$maxlen = 0;

		while(($file = readdir($dh)) !== false){
			// Skip hidden files
			if($file{0} == '.'){
				echo '.';
				continue;
			}

			// Only package up ASC files.
			if(!preg_match('/\.asc$/i', $file)){
				echo '!';
				continue;
			}

			// Yay, throw onto the sorted array and continue.  I'll come back in a sec.
			$sorted[$file] = $file;
			$maxlen = max($maxlen, strlen($file));
			echo '+';
		}
		echo NL . NL;

		// Now I can do the sorting!
		ksort($sorted);

		// And loop over each of those.
		foreach($sorted as $file){

			$fullpath = $dir['directory'] . $file;
			// Used in the XML file.
			$relpath = $dir['relpath'] . $file;

			// Drop the .asc extension.
			$basename = substr($file, 0, -4);

			echo str_pad("Processing " . $file . "...", $maxlen + 15, '.', STR_PAD_RIGHT);

			// decode and untar it in a temp directory to get the package.xml file.
			exec('gpg -q -d "' . $fullpath . '" > "' . $tmpdir . $basename . '" 2>/dev/null', $output, $ret);
			if($ret){
				echo 'FAILED!' . NL . 'Decryption of signed file failed!' . NL;
				$failedpackages++;
				continue;
			}

			exec('tar -xzf "' . $tmpdir . $basename . '" -C "' . $tmpdir . '" ./package.xml', $output, $ret);
			if($ret){
				echo 'FAILED!' . NL . 'Unable to extract package.xml from the tarball!' . NL;
				unlink($tmpdir . $basename);
				$failedpackages++;
				continue;
			}

			$output = array();
			// I also need the GPG key for this specific package.
			exec('gpg --verify "' . $fullpath . '" 2>&1 | grep "key ID" | sed \'s:.*key ID \([A-Z0-9]*\)$:\1:\'', $output, $result);
			$key = $output[0];

			// Read in that package file and append it to the repo xml.
			$package = new PackageXML($tmpdir . 'package.xml');
			$package->getRootDOM()->setAttribute('key', $key);
			$package->setFileLocation($relpath);
			$repo->addPackage($package);
			$addedpackages++;

			// But I can still cleanup!
			unlink($tmpdir . 'package.xml');
			unlink($tmpdir . $basename);

			echo "OK!" . NL;
		}


	}
	else{
		echo '!!WARNING!! - Unable to open ' . $dir['directory'] . ' for scanning!' . NL;
	}
}

file_put_contents($destdir . 'repo.xml', $repo->asPrettyXML());

// And gzip!
if(file_exists($destdir . 'repo.xml.gz')) unlink($destdir . 'repo.xml.gz');

exec('gzip "' . $destdir . 'repo.xml' . '" -c > "' . $destdir . 'repo.xml.gz' . '"');

rmdir($tmpdir);

echo NL . NL . "Created " . $destdir . 'repo.xml.gz successfully' . NL . 'Packages Added: ' . $addedpackages . NL . 'Packages Failed: ' . $failedpackages . NL;
