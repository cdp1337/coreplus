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


use Core\CLI\CLI;

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

// Build a list of all update sources to begin with.
// This is because a bundle can include files from other repositories!
CLI::PrintHeader('Loading Remote Repositories');
$sitecount = 0;
$remoteThemes = [];
$remoteComponents = [];
$updatesites = UpdateSiteModel::Find();
if(!sizeof($updatesites)){
	CLI::PrintActionStart('No repositories installed');
	CLI::PrintActionStatus('skip');
}
foreach($updatesites as $site){
	CLI::PrintActionStart('Scanning repository ' . $site->get('url'));
	/** @var UpdateSiteModel $site */
	if(!$site->isValid()){
		CLI::PrintActionStatus('failed');
		continue;
	}

	++$sitecount;
	$file = $site->getFile();

	$repoxml = new RepoXML();
	$repoxml->loadFromFile($file);
	$rootpath = dirname($file->getFilename()) . '/';

	CLI::PrintActionStatus('ok');

	foreach($repoxml->getPackages() as $pkg){
		/** @var PackageXML $pkg */

		$n = str_replace(' ', '-', strtolower($pkg->getName()));
		$type = $pkg->getType();

		switch($type){
			case 'component':
				$vers = $pkg->getVersion();

				// Only display the newest version available.
				if(isset($remoteComponents[$n]) && !Core::VersionCompare($vers, $remoteComponents[$n]['version'], 'gt')) {
					continue;
				}

				$parts = Core::VersionSplit($pkg->getVersion());

				$remoteComponents[$n] = [
					'name'            => $n,
					'title'           => $pkg->getName(),
					'version'         => $vers,
					'feature'         => $parts['major'] . '.' . $parts['minor'],
					'source'          => 'repo-' . $site->get('id'),
					'source_url'       => $site->get('url'),
					'source_username' => $site->get('username'),
					'source_password' => $site->get('password'),
					'description'     => $pkg->getDescription(),
					'provides'        => $pkg->getProvides(),
					'requires'        => $pkg->getRequires(),
					'location'        => $rootpath . $pkg->getFileLocation(),
					'type'            => 'component',
					'typetitle'       => 'Component ' . $pkg->getName(),
					'key'             => $pkg->getKey(),
				];
				break;
			case 'theme':
				$vers = $pkg->getVersion();
				$parts = Core::VersionSplit($pkg->getVersion());

				// I only want the newest version.
				if(isset($remoteThemes[$n]) && !Core::VersionCompare($vers, $remoteThemes[$n]['version'], 'gt')) {
					continue;
				}

				$remoteThemes[$n] = [
					'name'            => $n,
					'title'           => $pkg->getName(),
					'version'         => $vers,
					'feature'         => $parts['major'] . '.' . $parts['minor'],
					'source'          => 'repo-' . $site->get('id'),
					'source_url'       => $site->get('url'),
					'source_username' => $site->get('username'),
					'source_password' => $site->get('password'),
					'description'     => $pkg->getDescription(),
					'location'        => $rootpath . $pkg->getFileLocation(),
					'type'            => 'themes',
					'typetitle'       => 'Theme ' . $pkg->getName(),
					'key'             => $pkg->getKey(),
				];
		}
	}
}

// Get the bundler metafiles
CLI::PrintHeader('Scanning for Bundle Definitions');
$bundlefiles = [];
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
$version = CLI::PromptUser('Please set the bundled version or', 'text', $version);



foreach($bundles as $b){
	/** @var XMLLoader|null $xml */
	$xml = $b['xml'];
	$destdir = $dir . '/' . $b['sname'];

	// Check the dest directory for current versions.
	if(!is_dir($destdir)) mkdir($destdir);
	$desttgz = $b['sname'] . '-' . $version;
	if(!is_dir($destdir . '/' . $desttgz)) mkdir($destdir . '/' . $desttgz);

	// Get a list of packages to export.
	$export = [];
	$export[] = [
		'name'    => 'Core',
		'keyname' => 'core',
		'type'    => 'core',
		'src'     => BASE_DIR . 'exports/core',
		'dest'    => $destdir . '/' . $desttgz
	];

	// Add the components.
	if($xml){
		foreach($xml->getElements('//component') as $el){
			/** @var DOMElement $el */
			if($el->getAttribute('name')){
				$key = strtolower(str_replace(' ', '-', $el->getAttribute('name')));
				$name = $el->getAttribute('name');
			}
			elseif($el->getAttribute('key')){
				$key = $el->getAttribute('key');
				$name = str_replace('-', ' ', ucwords($el->getAttribute('key')));
			}
			else{
				CLI::PrintError('Invalid XML definition, all components in the bundle.xml MUST contain either a "name" or "key" attribute.');
				die();
			}

			$export[] = [
				'name'    => $name,
				'keyname' => $key,
				'type'    => 'component',
				'src'     => BASE_DIR . 'exports/components',
				'dest'    => $destdir . '/' . $desttgz . '/components/' . $key
			];
		}
	}
	else{
		foreach(Core::GetComponents() as $c){
			/** @var Component_2_1 $c */
			if($c->getKeyName() == 'core'){
				// Core is already included above.
				continue;
			}
			$export[] = [
				'name'    => $c->getName(),
				'keyname' => $c->getKeyName(),
				'type'    => 'component',
				'src'     => BASE_DIR . 'exports/components',
				'dest'    => $destdir . '/' . $desttgz . '/components/' . $c->getKeyName(),
			];
		}
	}

	if($xml){
		foreach($xml->getElements('//theme') as $el){
			/** @var DOMElement $el */
			if($el->getAttribute('name')){
				$key = strtolower(str_replace(' ', '-', $el->getAttribute('name')));
				$name = $el->getAttribute('name');
			}
			elseif($el->getAttribute('key')){
				$key = $el->getAttribute('key');
				$name = str_replace('-', ' ', ucwords($el->getAttribute('key')));
			}
			else{
				CLI::PrintError('Invalid XML definition, all themes in the bundle.xml MUST contain either a "name" or "key" attribute.');
				die();
			}

			$export[] = [
				'name'    => $name,
				'keyname' => $key,
				'type'    => 'theme',
				'src'     => BASE_DIR . 'exports/themes',
				'dest'    => $destdir . '/' . $desttgz . '/themes/' . $key
			];
		}
	}
	else{
		foreach(\ThemeHandler::GetAllThemes() as $t){
			/** @var Theme\Theme $t */
			$export[] = [
				'name'    => $t->getName(),
				'keyname' => $t->getKeyName(),
				'type'    => 'theme',
				'src'     => BASE_DIR . 'exports/themes',
				'dest'    => $destdir . '/' . $desttgz . '/themes/' . $t->getKeyName(),
			];
		}
	}


	CLI::PrintHeader('Assembling Packages for ' . $b['name'] . ' ' . $version);
	$changelog = '<h2>Packages included in ' . $b['name'] . ' ' . $version . '</h2>' . "\n\n";

	foreach($export as $dat){
		CLI::PrintActionStart("Searching for " . $dat['type'] . ' ' . $dat['name']);

		// Extract out the newest version of this component
		$compversion = '0.0.0';
		$dh = opendir($dat['src']);
		if($dh){
			while(($file = readdir($dh)) !== false){
				// Skip hidden files
				if($file{0} == '.') continue;
				// Skip directories
				if(is_dir($destdir . '/' . $file)) continue;

				// Skip non-tgz files
				if(strpos($file, $dat['keyname']) !== 0) continue;
				if(!preg_match('/\.tgz$/i', $file)) continue;

				// Yay, extract out the version from this filename.
				$filev = substr($file, strlen($dat['keyname']) + 1, -4);

				if($compversion == '0.0.0'){
					// If there's no version set yet...
					// This is because if the file version is < 1, the check will, (for some reason), return false.
					$compversion = $filev;
				}
				else{
					if(Core::VersionCompare($compversion, $filev, 'lt')){
						$compversion = $filev;
					}
				}

			}
			closedir($dh);
		}

		if($compversion == '0.0.0'){
			// Search online repositories, if available.
			if($dat['type'] == 'theme' && isset($remoteThemes[ $dat['keyname'] ])){
				$target =& $remoteThemes[ $dat['keyname'] ];
			}
			elseif($dat['type'] == 'component' && isset($remoteComponents[ $dat['keyname'] ])){
				$target =& $remoteComponents[ $dat['keyname'] ];
			}
			elseif($dat['type'] == 'core' && isset($remoteComponents[ $dat['keyname'] ])){
				$target =& $remoteComponents[ $dat['keyname'] ];
			}
			else{
				$target = null;
			}

			if($target){
				// Allow searching through remote repositories if the local file wasn't located.

				CLI::PrintActionStatus('ok');
				CLI::PrintLine('Found ' . $target['name'] . '-' . $target['version'] . ' in repo ' . $target['source_url']);
				$compversion = $target['version'];

				$output = [];
				exec('gpg --homedir "' . GPG_HOMEDIR . '" --list-public-keys "' . $target['key'] . '"', $output, $result);
				if($result > 0){
					// Key validation failed!
					CLI::PrintError(implode("\n", $output) . "\n" . 'Is the key ' . $target['key'] . ' installed?');
					die();
				}
				/*else{
					CLI::PrintLine($output);
				}*/

				// Setup the remote file that will be used to download from.
				$file = new \Core\Filestore\Backends\FileRemote($target['location']);
				$filesize = $file->getFilesize(true);
				CLI::PrintActionStart('Downloading ' . $target['location'] . ' (' . $filesize . ')');
				$file->username = $target['source_username'];
				$file->password = $target['source_password'];
				$obj = $file->getContentsObject();
				// Getting the object simply sets it up, it doesn't download the contents yet.
				$obj->getContents();
				CLI::PrintActionStatus('ok');

				if(!($obj instanceof \Core\Filestore\Contents\ContentASC)){
					CLI::PrintError($target['location'] . ' does not appear to be a valid GPG signed archive');
					die();
				}
				// The object's key must also match what's in the repo.
				if($obj->getKey() != $target['key']){
					CLI::PrintError('!!!WARNING!!!, Key for ' . $target['typetitle'] . ' is valid, but does not match what was expected form the repository data!  This could be a major risk!');
					die();
				}
				CLI::PrintLine('Decrypting signed package');
				/** @var $localfile \Core\Filestore\File */
				$localfile = $obj->decrypt('tmp/bundler/');
				/** @var $localobj \Core\Filestore\Contents\ContentTGZ */
				$localobj = $localfile->getContentsObject();
				$dat['tgz'] = $localfile->getFilename();
				CLI::PrintActionStatus('ok');
			}
			else{
				CLI::PrintActionStatus('fail');
				CLI::PrintError('Unable to find required component ' . $dat['name']);
				die();
			}
		}
		else{
			CLI::PrintActionStatus('ok');
			CLI::PrintLine('Found ' . $dat['name'] . '-' . $compversion . ' in a local package.');
			$dat['tgz'] = $dat['src'] . '/' . $dat['keyname'] . '-' . $compversion . '.tgz';
		}

		CLI::PrintActionStart("Extracting tarball");
		if(!is_dir($dat['dest'])){
			$d = \Core\Filestore\Factory::Directory($dat['dest']);
			$d->mkdir();
		}
		exec('tar -xzf ' . $dat['tgz'] . ' -C ' . $dat['dest'] . ' --transform "s:\./data::" ./data', $out, $result);
		if($result != 0){
			CLI::PrintActionStatus('fail');
			CLI::PrintLine($out);
			die();
		}
		CLI::PrintActionStatus('ok');

		CLI::PrintActionStart("Processing CHANGELOG");
		if($dat['keyname'] == 'core'){
			$parser = new Core\Utilities\Changelog\Parser($dat['name'], $dat['dest'] . '/core/CHANGELOG');
		}
		else{
			$parser = new Core\Utilities\Changelog\Parser($dat['name'], $dat['dest'] . '/CHANGELOG');
		}

		if(!$parser->exists()){
			CLI::PrintActionStatus('skip');
			$changelog .= '<h3>' . $dat['name'] . ' ' . $compversion . '</h3>';
		}
		else{
			try{
				$parser->parse();

				/** @var $thisversion Core\Utilities\Changelog\Section */
				$thisversion = $parser->getSection($compversion);

				// Read the current changelog.
				$changelog .= $thisversion->fetchAsHTML(3);

				CLI::PrintActionStatus('ok');
			}
			catch(Exception $e){
				CLI::PrintActionStatus('fail');
				CLI::PrintError($e->getMessage());
				$changelog .= '<h3>' . $dat['name'] . ' ' . $compversion . '</h3>';
			}
		}
	}

	// Keys
	if($xml){
		CLI::PrintHeader('Assembling GPG Keys for ' . $b['name'] . ' ' . $version);
		foreach($xml->getElements('//key') as $el){
			$id = $el->getAttribute('id');
			CLI::PrintActionStart('Exporting key ' . $id);
			exec('gpg -a --export ' . $id . ' > "' . $destdir . '/' . $id . '.gpg"');
			exec('gpg --homedir "' . $destdir . '/' . $desttgz . '/gnupg" --no-permission-warning --import "' . $destdir . '/' . $id . '.gpg"', $output, $result);
			unlink($destdir . '/' . $id . '.gpg');
			CLI::PrintActionStatus('ok');
		}
	}


	// Write out the changelog for the bundle.
	file_put_contents($destdir . '/' . $desttgz . '/packages.html', $changelog);

	CLI::PrintHeader('Finalizing Bundling for ' . $b['name'] . ' ' . $version);

	// create the tarballs!
	CLI::PrintActionStart("Creating tarball");
	exec('tar -czf "' . $destdir . '/' . $desttgz . '.tgz" -C "' . $destdir . '" --exclude-vcs --exclude=*~ --exclude=._* ' . $desttgz);
	CLI::PrintActionStatus('ok');

	CLI::PrintActionStart("Creating zip");
	exec('cd "' . $destdir . '/"; zip -rq "' . $desttgz . '.zip" "' . $desttgz . '"; cd -');
	CLI::PrintActionStatus('ok');

	CLI::PrintActionStart("Creating hashes");
	exec('md5sum "' . $destdir . '/' . $desttgz . '.tgz" > "' . $destdir . '/' . $desttgz . '.tgz.md5"');
	exec('md5sum "' . $destdir . '/' . $desttgz . '.zip" > "' . $destdir . '/' . $desttgz . '.zip.md5"');
	CLI::PrintActionStatus('ok');

	CLI::PrintActionStart("Cleaning up");
	exec('rm -fr "' . $destdir . '/' . $desttgz . '"');
	CLI::PrintActionStatus('ok');
}


// Give the option to automatically commit and tag all changes for the component.
if(CLI::PromptUser('Bundles created, GIT tag the release for ' . $version . '?', 'boolean', true)){
	exec('git tag -m "Release Version ' . $version . '" -f ' . 'v' . str_replace('~', '-', $version));
	exec('git push --tags');
}