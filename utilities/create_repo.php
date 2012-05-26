#!/usr/bin/env php
<?php
/**
 * The purpose of this file is to create a repository that can be distributed 
 * pulled from remote servers.
 *
 * @package Core Plus\CLI Utilities
 * @since 1.9
 * @author Charlie Powell <powellc@powelltechs.com>
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

$destdir = ROOT_PDIR . 'exports/';
$tmpdir = ROOT_PDIR . 'exports/_tmp/';
// Ensure the export directory exists.
if(!is_dir($destdir)) exec('mkdir -p "' . $destdir . '"');
if(!is_dir($tmpdir)) exec('mkdir -p "' . $tmpdir . '"');

$repo = new RepoXML();
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
	)
);

// Load in all valid components in the exports/components directory.
foreach($directories as $dir){
	$dh = opendir($dir['directory']);
	$output = array();
	$ret = null;
	if($dh){
		while(($file = readdir($dh)) !== false){
			$fullpath = $dir['directory'] . $file;
			// Used in the XML file.
			$relpath = $dir['relpath'] . $file;

			// Skip hidden files
			if($file{0} == '.') continue;

			// Only package up ASC files.
			if(!preg_match('/\.asc$/i', $file)) continue;

			// Drop the .asc extension.
			$basename = substr($file, 0, -4);

			echo NL . NL . "Processing " . $dir['type'] . " " . $file . "..." . NL;

			// decode and untar it in a temp directory to get the package.xml file.
			exec('gpg -d "' . $fullpath . '" > "' . $tmpdir . $basename . '"', $output, $ret);
			if($ret){
				echo "FAILED to decrypt file!" . NL;
				$failedpackages++;
				continue;
			}

			exec('tar -xzf "' . $tmpdir . $basename . '" -C "' . $tmpdir . '" ./package.xml', $output, $ret);
			if($ret){
				echo "FAILED to extract package.xml!" . NL;
				unlink($tmpdir . $basename);
				$failedpackages++;
				continue;
			}

			// Read in that package file and append it to the repo xml.
			$package = new PackageXML($tmpdir . 'package.xml');
			$package->setFileLocation($relpath);
			$repo->addPackage($package);
			$addedpackages++;

			// Cleanup!
			unlink($tmpdir . 'package.xml');
			unlink($tmpdir . $basename);

			echo "Added package!" . NL;
		}
	}
}

file_put_contents($destdir . 'repo.xml', $repo->write());
// And gzip!
exec('gzip "' . $destdir . 'repo.xml' . '"');

rmdir($tmpdir);

echo NL . NL . "Created " . $destdir . 'repo.xml.gz successfully' . NL . 'Packages Added: ' . $addedpackages . NL . 'Packages Failed: ' . $failedpackages . NL;
