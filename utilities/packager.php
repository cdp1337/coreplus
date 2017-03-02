#!/usr/bin/env php
<?php
/**
 * The purpose of this file is to archive up the core, components, and bundles.
 * and to set all the appropriate information.
 *
 * @package Core\CLI Utilities
 * @since 1.9
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2016  Charlie Powell
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

// This is required to establish the root path of the system, (since it's always one directory up from "here"
define('ROOT_PDIR', realpath(dirname(__DIR__) . '/src/') . '/');
define('ROOT_WDIR', '/');
define('BASE_DIR', realpath(dirname(__DIR__)) . '/');
// It is unsafe to run the packager when not in development mode due to caching issues.
define('DEVELOPMENT_MODE', true);

// Include the core bootstrap, this will get the system functional.
require_once(ROOT_PDIR . 'core/bootstrap.php');


require_once(ROOT_PDIR . 'core/libs/core/cli/Arguments.php');
require_once(ROOT_PDIR . 'core/libs/core/cli/Argument.php');

$arguments = new \Core\CLI\Arguments([
	'help' => [
		'description' => 'Display help and exit.',
		'value' => false,
		'shorthand' => ['?', 'h'],
	],
	'component' => [
		'description' => 'Operate on a component with the given name.',
		'value' => true,
		'shorthand' => ['c'],
	],
	'list-components' => [
		'description' => '(DEPRECATED) Alias of --list',
		'value' => false,
		'shorthand' => [],
	],
	'list-themes' => [
		'description' => '(DEPRECATED) Alias of --list',
		'value' => false,
		'shorthand' => [],
	],
	'list' => [
		'description' => 'List all available components and themes, along with their versions and flags',
	    'value' => false,
	    'shorthand' => 'l',
	],
	'repackage' => [
		'description' => 'Simply repackage a given component or theme.  Useful for updating a package while in development.',
		'value' => false,
		'shorthand' => ['r'],
	],
	'theme' => [
		'description' => 'Operate on a theme with the given name.',
		'value' => true,
		'shorthand' => ['t'],
	],
]);
$arguments->usageHeader = 'This utility will compile the component.xml metafile for any component,
and optionally allow you to create a deployable package of the component.

Wizard Usage:
  Simply run it without any arguments and follow the prompts.

Advanced Options:';
$arguments->processArguments();


// Allow for inline arguments.
$opts = [
	// Repackage the given [whatever] at the current version and exit, no bundling.
	'repackage'    => false,
	// Only list the given [type] and their versions and exit, no saving or bundling.
	'listonly'     => false,
	// component|theme
	'type'         => null,
	// The name of the [type]
	'name'         => null,
	// Display versions along with the listing?
	'listversions' => false,
	// Quiet
    'quiet'        => false,
];


// Process and validate those arguments now.
if($arguments->getArgumentValue('help')){
	$arguments->printUsage();
	exit;
}

if($arguments->getArgumentValue('component')){
	$opts['type'] = 'component';
	$opts['name'] = $arguments->getArgumentValue('component');
}

if($arguments->getArgumentValue('theme')){
	$opts['type'] = 'theme';
	$opts['name'] = $arguments->getArgumentValue('theme');
}

if($arguments->getArgumentValue('list-components')){
	CLI::PrintError('--list-components is subject for removal, please use --list instead.');
	$opts['type'] = 'component';
	$opts['listonly'] = true;
	$opts['listversions'] = true;
	$opts['quiet'] = true;
}

if($arguments->getArgumentValue('list-themes')){
	CLI::PrintError('--list-themes is subject for removal, please use --list instead.');
	$opts['type'] = 'theme';
	$opts['listonly'] = true;
	$opts['listversions'] = true;
	$opts['quiet'] = true;
}

if($arguments->getArgumentValue('repackage')){
	$opts['repackage'] = true;
}

if($arguments->getArgumentValue('list')){
	$opts['listonly'] = true;
	$opts['listversions'] = true;
	$opts['quiet'] = true;
}



// I need a valid editor.
CLI::RequireEditor();

// Some cache variables.
$_cversions = null;


/**
 * Simple function to intelligently "up" the version number.
 * Supports Ubuntu-style versioning for non-original maintainers (~extraversionnum)
 *
 * Will try to utilize the versioning names, ie: dev to alpha to beta, etc.
 *
 * @param string $version
 * @param boolean $original
 * @return string
 */
function increment_version($version, $original){
	if($original){

		// It's an official package, increment the regular number and drop anything after the ~...
		if(strpos($version, '~') !== false){
			$version = substr($version, 0, strpos($version, '~'));
		}

		// if there's a -dev, -b, -rc[0-9], -beta, -a, -alpha, etc... just step up to the next one.
		// dev < alpha = a < beta = b < RC = rc < # <  pl = p
		if(preg_match('/\-(dev|a|alpha|b|beta|rc[0-9]|p|pl)$/i', $version, $match)){
			// Step the development stage up instead of the version number.
			$basev = substr($version, 0, -1-strlen($match[1]));
			switch(strtolower($match[1])){
				case 'dev':
					return $basev . '-alpha';
				case 'a':
				case 'alpha':
					return $basev . '-beta';
				case 'b':
				case 'beta':
					return $basev . '-rc1';
				case 'p':
				case 'pl':
					return $basev;
			}
			// still here, might be 'rc#'.
			if(preg_match('/rc([0-9]*)/i', $match[1], $rcnum)){
				return $basev . '-rc' . ($rcnum[1] + 1);
			}

			// still no?  I give up...
			$version = $basev;
		}

		// Increment the version number by 0.0.1.
		@list($vmaj, $vmin, $vrev) = explode('.', $version);
		// They need to at least be 0....
		if(is_null($vmaj)) $vmaj = 1;
		if(is_null($vmin)) $vmin = 0;
		if(is_null($vrev)) $vrev = 0;

		$vrev++;
		$version = "$vmaj.$vmin.$vrev";
	}
	else{
		// This is a release, but not the original packager.
		// Therefore, all versions should be after the ~ to signify this.
		if(strpos($version, '~') === false){
			$version .= '~1';
		}
		else{
			preg_match('/([^~]*)~([^0-9]*)([0-9]*)/', $version, $matches);
			$version = $matches[1];
			$vname = $matches[2];
			$vnum = $matches[3];
			$vnum++;
			$version .= '~' . $vname . $vnum;
		}
	}

	return $version;
}


/**
 * Will return an array with the newest exported versions of each component.
 */
function get_exported_components(){
	$c = [];
	$dir = ROOT_PDIR . '../exports/components';
	if(!is_dir($dir)){
		// Doesn't exist?  Don't even try opening it.
		return $c;
	}
	$dh = opendir($dir);
	if(!$dh){
		// Easy enough, just return a blank array!
		return $c;
	}
	while(($file = readdir($dh)) !== false){
		if($file{0} == '.') continue;
		if(is_dir($dir . '/' . $file)) continue;
		// Get the extension type.

		if(preg_match('/\.tgz$/', $file)){
			$signed = false;
			$fbase = substr($file, 0, -4);
		}
		elseif(preg_match('/\.tgz\.asc$/', $file)){
			$signed = true;
			$fbase = substr($file, 0, -8);
		}
		else{
			continue;
		}

		// Split up the name and the version.
		// This is a little touchy because a dash in the package name is perfectly valid.
		// instead, grab the last dash in the string.
		// Additionally, versions such as:
		// name-1.2.3
		// name-blah-1.2.3
		// name-blah-1.2.3-4
		// must all be supported.
		preg_match('/^([a-z\.\-]*)-(r[0-9]|[0-9][0-9abrc\.\-]*)(~.*)?$/', $fbase, $fileparts);
		switch(sizeof($fileparts)){
			case 3:
				$n = $fileparts[1];
				$v = $fileparts[2];
				break;
			case 4:
				$n = $fileparts[1];
				$v = $fileparts[2] . $fileparts[3];
				break;
			default:
				CLI::PrintWarning('Unsupported file version string: [' . $fbase . '], please submit this.');
				continue;
		}
		
		//$dash = strrpos($fbase, '-');
		//$n = substr($fbase, 0, $dash);
		//$v = substr($fbase, ($dash+1));
		// instead, I need to look for a dash followed by a number.  This should indicate the version number.
		//preg_match('/^(.*)\-([0-9]+.*)$/', $fbase, $matches);

		//$n = $matches[1];
		//$v = $matches[2];

		// Tack it on.
		if(!isset($c[$n])){
			$c[$n] = ['version' => $v, 'signed' => $signed, 'filename' => $dir . '/' . $file];
		}
		else{
			switch(Core::VersionCompare($c[$n]['version'], $v)){
				case -1:
					// Existing older, overwrite!
					$c[$n] = ['version' => $v, 'signed' => $signed, 'filename' => $dir . '/' . $file];
					break;
				case 0:
					// Same, check the signed status.
					if($signed) $c[$n] = ['version' => $v, 'signed' => $signed, 'filename' => $dir . '/' . $file];
					break;
				default:
					// Do nothing, current is at a higher version.
			}
		}
	}
	closedir($dh);

	return $c;
} // function get_exported_components()

/**
 * Will return an array with the newest exported version of the core.
 */
function get_exported_core(){
	$c = [];
	$dir = ROOT_PDIR . '../exports/core';
	if(!is_dir($dir)){
		// Doesn't exist?  Don't even try opening it.
		return $c;
	}
	$dh = opendir($dir);
	if(!$dh){
		// Easy enough, just return a blank array!
		return $c;
	}
	while(($file = readdir($dh)) !== false){
		if($file{0} == '.') continue;
		if(is_dir($dir . '/' . $file)) continue;
		// Get the extension type.

		if(preg_match('/\.tgz$/', $file)){
			$signed = false;
			$fbase = substr($file, 0, -4);
		}
		elseif(preg_match('/\.tgz\.asc$/', $file)){
			$signed = true;
			$fbase = substr($file, 0, -8);
		}
		else{
			continue;
		}

		// Split up the name and the version.
		preg_match('/([^-]*)\-(.*)/', $fbase, $matches);
		$n = $matches[1];
		$v = $matches[2];

		// Tack it on.
		if(!isset($c[$n])){
			$c[$n] = ['version' => $v, 'signed' => $signed, 'filename' => $dir . '/' . $file];
		}
		else{
			switch(version_compare($c[$n]['version'], $v)){
				case -1:
					// Existing older, overwrite!
					$c[$n] = ['version' => $v, 'signed' => $signed, 'filename' => $dir . '/' . $file];
					break;
				case 0:
					// Same, check the signed status.
					if($signed) $c[$n] = ['version' => $v, 'signed' => $signed, 'filename' => $dir . '/' . $file];
					break;
				default:
					// Do nothing, current is at a higher version.
			}
		}
	}
	closedir($dh);

	if(!isset($c['core'])) $c['core'] = ['version' => null, 'signed' => false];

	return $c['core'];
} // function get_exported_core()


function get_exported_component($component){
	global $_cversions;
	if(is_null($_cversions)){
		$_cversions = get_exported_components();
		// Tack on the core.
		$_cversions['core'] = get_exported_core();
	}

	if(!isset($_cversions[$component])) return ['version' => null, 'signed' => false];
	else return $_cversions[$component];
}


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

CLI::SaveSettingsFile('packager', ['packagername', 'packageremail']);


// Before ANYTHING happens.... make sure that the system is compiled!
// echo '# Compiling system...' . "\n";
// exec(escapeshellarg(BASE_DIR . 'utilities/compiler.php'));
//echo "OK!\n";


while(true){
	if(!($opts['quiet'] || $opts['name'])){
		CLI::PrintHeader('Building Main Menu');
	}
	// Purge the cache of exported versions.
	$_cversions = null;

	/** @var array $files Master array containing all top-level menu options and their targets */
	$files = [];

	// Tack on the core component.
	$core = Core::GetComponent('core');
	$version = $core->getVersion();

	// If display versions is requested, tack on the version number too!
	if($opts['listversions']){
		$title = 'Core' . ' ' . $version;
	}
	else{
		$title = '  ------  Core';
	}

	$longestname = strlen($title);

	$files['a-core'] = [
		'title' => $title,
		'type'  => 'core',
		'name'  => 'core',
		'version' => $core->getVersion(),
		'component' => $core,
		'dir' => ROOT_PDIR . 'core/',
	];
	unset($core);


	// Load in all components currently on the system
	// Open the "component" directory and look for anything with a valid component.xml file.
	$dir = ROOT_PDIR . 'components';
	$dh = opendir($dir);
	while(($file = readdir($dh)) !== false){
		if($file{0} == '.') continue;
		if(!is_dir($dir . '/' . $file)) continue;
		if(!is_readable($dir . '/' . $file . '/' . 'component.xml')) continue;

		$c = Core::GetComponent($file);

		// Is this not valid?
		if(!$c){
			CLI::PrintLine('Skipping invalid component ' . $file);
			continue;
		}

		// What's this file's version?
		$xml = new XMLLoader();
		$xml->setRootName('component');
		if(!$xml->loadFromFile($dir .  '/' . $file . '/component.xml')){
			CLI::PrintLine('Skipping component ' . $file . ', unable to load XML file');
			continue;
		}

		// Get the current version, this will be used to autocomplete for the next version.
		//$version = $xml->getRootDOM()->getAttribute("version");
		$version = $c->getVersion();

		// If display versions is requested, tack on the version number too!
		if($opts['listversions']){
			$title = $c->getName() . ' ' . $version;
		}
		else{
			$title = 'Component ' . $c->getName();
		}

		$longestname = max($longestname, strlen($title));
		$files['b-' . $file] = [
			'title'     => $title,
			'type'      => 'component',
			'name'      => $file,
			'version'   => $version,
			'component' => $c,
			'dir'       => ROOT_PDIR . $file . '/',
		];
	}
	closedir($dh);
	unset($file, $version, $title, $c, $dh);

	// Load in all themes currently on the system
	$dir = ROOT_PDIR . 'themes';
	$dh = opendir($dir);
	while(($file = readdir($dh)) !== false){
		if($file{0} == '.') continue;
		if(!is_dir($dir . '/' . $file)) continue;
		if(!is_readable($dir . '/' . $file . '/' . 'theme.xml')) continue;

		$t = ThemeHandler::GetTheme($file);

		// What's this file's version?
		$xml = new XMLLoader();
		$xml->setRootName('theme');
		if(!$xml->loadFromFile($dir .  '/' . $file . '/theme.xml')){
			CLI::PrintLine('Skipping theme ' . $file . ', unable to load XML file');
			continue;
		}

		// Get the current version, this will be used to autocomplete for the next version.
		//$version = $xml->getRootDOM()->getAttribute("version");
		$version = $t->getVersion();

		// If display versions is requested, tack on the version number too!
		if($opts['listversions']){
			$title = 'Theme/' . $t->getName() . ' ' . $version;
		}
		else{
			$title = '    Theme ' . $t->getName();
		}

		$longestname = max($longestname, strlen($title));
		$files['c-' . $file] = [
			'title'   => $title,
			'type'    => 'theme',
			'name'    => $file,
			'version' => $version,
			'theme'   => $t,
			'dir'     => ROOT_PDIR . $file . '/',
		];
	}
	closedir($dh);
	unset($file, $version, $title, $t, $dh);

	// They should be in alphabetical order...
	ksort($files);

	// Before prompting the user which one to choose, tack on the exported versions.
	$versionedfiles = [];
	foreach($files as $k => $f){

		if($f['type'] == 'component' || $f['type'] == 'core'){
			$line = str_pad($f['title'], $longestname+1, ' ', STR_PAD_RIGHT);

			/** @var Component_2_1 $c */
			$c = $f['component'];
			$lineflags = [];

			// Now give me the exported version.
			$lookup = get_exported_component($f['name']);

			if(!isset($lookup['version'])){
				// Is it even set?
				$lineflags[] = '** needs exported **';
			}
			elseif($lookup['version'] != $f['version']){
				// yeah, it's set... but it's not the right version.
				$lineflags[] = '** needs exported **';
			}

			// Change the local changes
			if(
				sizeof($c->getChangedAssets()) ||
				sizeof($c->getChangedFiles()) ||
				sizeof($c->getChangedTemplates())
			){
				$lineflags[] = '** needs packaged **';
			}

			// Scan GIT changes since released
			$packager = new \Core\Utilities\Packager('component', $f['name']);
			$release_info = $packager->getLatestReleaseInfo();
			$changes = $packager->getGitChangesSince($release_info['version'], $release_info['date']);
			if(sizeof($changes) > 0){
				$lineflags[] = '** needs packaged **';
			}


			// Strip any duplicate flags.
			$lineflags = array_unique($lineflags);

			$versionedfiles[$k] = $line . ' ' . implode(' ', $lineflags);
		}
		elseif($f['type'] == 'theme'){
			$line = str_pad($f['title'], $longestname+1, ' ', STR_PAD_RIGHT);

			/** @var Theme\Theme $t */
			$t = $f['theme'];
			$lineflags = [];

			// Now give me the exported version.
			//$lookup = get_exported_component($f['name']);

			/*if(!isset($lookup['version'])){
				// Is it even set?
				$lineflags[] = '** needs exported **';
			}
			elseif($lookup['version'] != $f['version']){
				// yeah, it's set... but it's not the right version.
				$lineflags[] = '** needs exported **';
			}*/

			// Change the changes
			/*if(
				sizeof($t->getChangedAssets()) ||
				sizeof($t->getChangedFiles()) ||
				sizeof($t->getChangedTemplates())
			){
				$lineflags[] = '** needs packaged **';
			}*/

			// Scan GIT changes since released
			$packager = new \Core\Utilities\Packager('theme', $f['name']);
			$release_info = $packager->getLatestReleaseInfo();
			$changes = $packager->getGitChangesSince($release_info['version'], $release_info['date']);
			if(sizeof($changes) > 0){
				$lineflags[] = '** needs packaged **';
			}


			// Strip any duplicate flags.
			$lineflags = array_unique($lineflags);


			$versionedfiles[$k] = $line . ' ' . implode(' ', $lineflags);
		}
	}

	if($opts['listonly']){
		echo implode(NL, $versionedfiles) . NL;
		exit;
	}

	$versionedfiles['quit'] = 'Quit';
	if($opts['name'] && $opts['type']){
		if($opts['name'] == 'core'){
			$ans = 'a-core';
		}
		elseif($opts['type'] == 'component'){
			$ans = 'b-' . $opts['name'];
		}
		elseif($opts['type'] == 'theme'){
			$ans = 'c-' . $opts['name'];
		}
		else{
			CLI::PrintError('Invalid argument requested');
			exit;
		}
	}
	else{
		$ans = CLI::PromptUser("What do you want to package/manage?", $versionedfiles);
	}

	if($ans == 'quit'){
		echo 'Bye!' . NL;
		exit;
	}

	$r = $files[$ans];

	CLI::PrintActionStart('Loading package data for ' . $r['name']);
	$packager = new \Core\Utilities\Packager($r['type'], $r['name']);
	CLI::PrintActionStatus('ok');

	CLI::PrintActionStart('Scanning Documentation');
	$packager->scanInlineDocumentation();
	CLI::PrintActionStatus('ok');

	// Build a stack of operations to do from the arguments in the order that they would make sense.
	$autostack = [];
	// This should really be at the tail end!
	if($opts['repackage']){
		$autostack = ['save', 'quit'];
	}
	$ans   = null;
	$saved = false;

	while(true){

		$answer_opts = [
			'editvers'   => '[ VERSION     ] Set version number',
			'editdesc'   => '[ DESCRIPTION ] Edit description',
			'viewgit'    => '[ GIT         ] View pending GIT commits between ' . $packager->getVersion() . ' and HEAD',
			'importgit'  => '[ CHNGLOG/GIT ] Import GIT commit logs into version ' . $packager->getVersion(),
			'editchange' => '[ CHNGLOG     ] Edit for version ' . $packager->getVersion(),
			'viewchange' => '[ CHNGLOG     ] View for version ' . $packager->getVersion(),
			//'dbtables' => 'Manage DB Tables',
			//'printdebug' => '[ DEBUG       ] Print the XML',
			'save'       => '[ FINISH      ] Save it!',
			'menu'       => 'Back to menu',
			'quit'       => 'Abort and exit without saving changes',
		];

		if($packager->isVersionReleased()){
			$answer_opts['viewgit']    = '[ GIT         ] View GIT commits since ' . $packager->getVersion();
			$answer_opts['importgit']  = '[ CHNGLOG/GIT ] *DISABLED* Import GIT commit logs into version ' . $packager->getVersion();
			$answer_opts['editchange'] = '[ CHNGLOG     ] *DISABLED* Edit for version ' . $packager->getVersion();
		}

		if(sizeof($autostack)){
			// Do this instead!
			$ans = array_shift($autostack);
		}
		else{
			$ans = CLI::PromptUser('What do you want to edit for ' . $packager->getLabel() . ' ' . $packager->getVersion() . ' on branch ' . $packager->getGitBranch(), $answer_opts);
		}


		switch($ans){
			case 'editvers':
				// If the current version has a 3rd party signifier... keep it as such.
				if(!strpos($packager->getVersion(), '~')) $original = true;
				else $original = false;

				// Try to explode the version by a ~ sign, this signifies not the original packager/source.
				// ie: ForeignComponent 3.2.4 may be versioned 3.2.4.thisproject5
				// if it's the 5th revision of the upstream version 3.2.4 for 'thisproject'.
				$previousversion = $packager->getVersion();
				$version = increment_version($packager->getVersion(), $original);

				$version = CLI::PromptUser('Please set the new version on branch ' . $packager->getGitBranch() . ' or', 'text', $version);
				$packager->setVersion($version);

				if($version != $previousversion){
					$importgit = CLI::PromptUser('Do you want to automatically import GIT commits performed after ' . $previousversion . ' into the ' . $version . ' changelog?', 'boolean', true);
					if($importgit){
						// Append this operation onto the stack, to have it executed next.
						$autostack = array_merge(['importgit'], $autostack);
					}
				}

				// Cleanup
				unset($original, $previouschange, $previousdate, $previousversion, $version, $importgit, $answer_opts);
				break;
			case 'editdesc':
				$packager->setDescription(CLI::PromptUser('Enter a description for ' . $packager->getLabel(), 'textarea', $packager->getDescription()));
				break;
			case 'editchange':
				if($packager->isVersionReleased()){
					CLI::PrintError('Version ' . $packager->getVersion() . ' is already marked as released!  Refusing to allow editing of the CHANGELOG.');
				}
				else{
					$thisversion = $packager->getChangelogSection();

					// Read the current changelog.
					$changelog = $thisversion->fetch();

					// Prompt the user with the ability to change them.
					$changelog = CLI::PromptUser(
						'Enter the changelog for this release.  Separate each different bullet point on a new line with no dashes or asterisks.',
						'textarea',
						$changelog
					);

					// And write them back in.
					$thisversion->clearEntries();
					foreach(explode("\n", $changelog) as $line){
						$thisversion->addLine($line);
					}

					// Cleanup
					unset($thisversion, $changelog, $line);
				}
				break;
			case 'viewgit':
				$release_info = $packager->getLatestReleaseInfo();

				if($release_info['version'] == '0.0.0') {
					$title = 'All GIT commits for ' . $release_info['label'];
				}
				else {
					$title =
						'GIT commits since ' . $release_info['label'] . ' ' . $release_info['version'] .
						' was released on ' . $release_info['date'];
				}

				$changes = $packager->getGitChangesSince($release_info['version'], $release_info['date']);

				CLI::PrintHeader($title);
				if(!sizeof($changes)){
					CLI::PrintError('No GIT commits found.');
				}
				else{
					CLI::PrintLine($changes);
				}

				// Cleanup
				unset($release_info, $changes);
				break;

			case 'importgit':
				if($packager->isVersionReleased()){
					CLI::PrintError('Version ' . $packager->getVersion() . ' is already marked as released!  Refusing to import GIT changes into a released version.');
				}
				else{
					$thischange      = $packager->getChangelogSection();
					$versioncheck    = $packager->getVersion();
					$previouschange  = $packager->getChangelog()->getPreviousSection($versioncheck);
					$previousdate    = $previouschange->getReleasedDate() ? $previouschange->getReleasedDate() : '01 Jan 2013 00:00:00 -0400';
					$previousversion = $previouschange->getVersion();
					$changes         = $packager->getGitChangesSince($previousversion, $previousdate);
					$linecount       = sizeof($thischange->getEntriesSorted());

					foreach($changes as $line){
						$thischange->addLine($line);
					}
					$linesadded = sizeof($thischange->getEntriesSorted()) - $linecount;

					// And notify the user of what happened.
					if(!sizeof($changes)){
						CLI::PrintLine('No GIT history found.');
					}
					elseif(!$linesadded){
						CLI::PrintLine('No new GIT commits found.');
					}
					else{
						CLI::PrintLine('Added ' . $linesadded . ($linesadded != 1 ? ' lines' : ' line') . ' to the changelog successfully!');
						print $thischange->fetchFormatted() . NL . NL;
					}

					// Cleanup
					unset($thischange, $versioncheck, $previouschange, $previousdate, $changes, $linecount, $linesadded, $line);
				}
				break;
			case 'viewchange':
				// Just print the current CHANGELOG section, easy now that everything is compartmentalized.
				CLI::PrintHeader('CHANGELOG for ' . $packager->getLabel() . ' ' . $packager->getVersion());
				CLI::PrintLine($packager->getChangelogSection()->fetchFormatted());
				break;
			case 'save':
				CLI::PrintActionStart('Saving ' . $packager->getLabel());
				$packager->save();
				CLI::PrintActionStatus('ok');
				$saved = true;

				$saveopts = [
				    'package-sign-commit' => 'Create signed package and GIT commit any changes',
				    'package-sign'        => 'Create signed package',
				    'package'             => 'Create unsigned package',
				    'commit'              => 'GIT commit any changes',
				    'menu'                => 'Do nothing else, back to menu',
				    'quit'                => 'Do nothing else and exit script',
				];

				if(!sizeof($autostack)){
					$saveans = CLI::PromptUser(ucfirst($packager->getLabel()) . ' saved, do you want to bundle the changes into a package?', $saveopts, 'menu');
					// Append this operation onto the stack, to have it executed next.
					$autostack = array_merge([$saveans], $autostack);
				}
				break;
			case 'package':
				$bundle = $packager->package($packagername, $packageremail, false);
				echo "Created unsigned package of " . $packager->getLabel() . ' ' . $packager->getVersion() . NL . " as " . $bundle . NL;
				break 2; // Exit back to the menu.
			case 'package-sign':
				$bundle = $packager->package($packagername, $packageremail, true);
				echo "Created signed package of " . $packager->getLabel() . ' ' . $packager->getVersion() . NL . " as " . $bundle . NL;
				break 2; // Exit back to the menu.
			case 'package-sign-commit':
				$bundle = $packager->package($packagername, $packageremail, true);
				echo "Created signed package of " . $packager->getLabel() . ' ' . $packager->getVersion() . NL . " as " . $bundle . NL;
				$packager->gitCommit();
				break 2; // Exit back to the menu.
			case 'commit':
				$packager->gitCommit();
				break 2; // Exit back to the menu.
			case 'menu':
				break 2; // Exit back to the menu.
			case 'quit':
				if($saved){
					echo 'Bye!' . NL;
				}
				else{
					echo "Aborting build" . NL;
				}
				exit;
				break;
			default:
				CLI::PrintError('Unknown option');
				break;
		}
	}
}
