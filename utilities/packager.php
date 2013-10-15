#!/usr/bin/env php
<?php
/**
 * The purpose of this file is to archive up the core, components, and bundles.
 * and to set all the appropriate information.
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
define('ROOT_PDIR', realpath(dirname(__DIR__) . '/src/') . '/');
define('ROOT_WDIR', '/');

// Include the core bootstrap, this will get the system functional.
require_once(ROOT_PDIR . 'core/bootstrap.php');


/**
 * Just a simple usage tutorial.
 */
function print_help(){
	echo 'This utility will compile the component.xml metafile for any component,' . NL .
		'and optionally allow you to create a deployable package of the component.' .
		NL . NL .
		'Wizard Usage: simply run it without any arguments and follow the prompts.' . NL .
		'Advanced Options:' . NL .
		'  -c, --component=NAME   Operate on a component with the given name' . NL .
		'  --list-components      List out the available components along with  their versions and exit.' . NL .
		'  --list-themes          List out the available themes along with  their versions and exit.' . NL .
		'  -r, --repackage        Simply repackage a given component or theme.  Useful for updating a package while in development.' . NL .
		'  -t, --theme=NAME       Operate on a theme with the given name' . NL
		. NL . NL;
}


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
];

if($argc > 1){
	$arguments = $argv;
	// Drop the first, that is the filename.
	array_shift($arguments);

	// I'm using a for here instead of a foreach so I can increment $i artificially if an argument is two part,
	// ie: --option value_for_option --option2 value_for_option2
	for($i = 0; $i < sizeof($arguments); $i++){
		$arg = $arguments[$i];

		if($arg == '-h' || $arg == '--help' || $arg == '-?'){
			print_help();
			exit;
		}
		elseif($arg == '-c'){
			$opts['type'] = 'component';
			$opts['name'] = isset($arguments[$i+1]) ? $arguments[$i+1] : null;
			// And skip the name, (since that will be the next argument).
			++$i;
		}
		elseif(strpos($arg, '--component=') === 0){
			$opts['type'] = 'component';
			$opts['name'] = substr($arg, strpos($arg, '='));
		}
		elseif($arg == '--list-components'){
			$opts['type'] = 'component';
			$opts['listonly'] = true;
			$opts['listversions'] = true;
		}
		elseif($arg == '--list-themes'){
			$opts['type'] = 'theme';
			$opts['listonly'] = true;
			$opts['listversions'] = true;
		}
		elseif($arg == '-r' || $arg == '--repackage'){
			$opts['repackage'] = true;
		}
		elseif($arg == '-t'){
			$opts['type'] = 'theme';
			$opts['name'] = $arguments[$i+1];
			// And skip the name, (since that will be the next argument).
			++$i;
		}
		elseif(strpos($arg, '--theme=') === 0){
			$opts['type'] = 'theme';
			$opts['name'] = substr($arg, strpos($arg, '='));
		}
		else{
			echo "ERROR: unknown argument [" . $arg . "]" . NL;
			print_help();
			exit;
		}
	}
}



// I need a valid editor.
CLI::RequireEditor();

// Some cache variables.
$_cversions = null;


/**
 * Create Unique Arrays using an md5 hash
 *
 * @param array $array
 * @return array
 */
function arrayUnique($array, $preserveKeys = false){
	// Unique Array for return
	$arrayRewrite = array();
	// Array with the md5 hashes
	$arrayHashes = array();
	foreach($array as $key => $item) {
		// Serialize the current element and create a md5 hash
		$hash = md5(serialize($item));
		// If the md5 didn't come up yet, add the element to
		// to arrayRewrite, otherwise drop it
		if (!isset($arrayHashes[$hash])) {
			// Save the current element hash
			$arrayHashes[$hash] = $hash;
			// Add element to the unique Array
			if ($preserveKeys) {
				$arrayRewrite[$key] = $item;
			} else {
				$arrayRewrite[] = $item;
			}
		}
	}
	return $arrayRewrite;
}


/**
 * Simple function to get any license from a file context.
 *
 * @todo This may move to the CLI system if found useful enough...
 * @param string $file
 * @return array
 */
function get_file_licenses($file){
	$ret = array();

	$fh = fopen($file, 'r');
	// ** sigh... counldn't open the file... oh well, skip to the next.
	if(!$fh) return $ret;
	// This will make filetype be the extension of the file... useful for expanding to JS, HTML and CSS files.
	$filetype = strtolower(substr($file, strrpos($file, '.') + 1));

	$counter = 0;
	$inphpdoc = false;

	while(!feof($fh)){
		$counter++;
		$line = trim(fgets($fh, 1024));
		switch($filetype){
			case 'php':
				// Skip line 1... should be <?php
				if($counter == 1) continue;
				// start of a phpDoc comment.
				if($line == '/**'){
					$inphpdoc = true;
					continue;
				}
				// end of a phpDoc comment.  This indicates the end of the reading of the file...
				// Valid license tags must be in the FIRST phpDoc of the page, immediately after the <?php.
				if($file == '*/'){
					break(2);
				}
				// At line 5 and no phpDoc yet?!?  wtf?
				if($counter == 5 && !$inphpdoc){
					break(2);
				}
				// Recognize PHPDoc syntax... basically just [space]*[space]@license...
				if($inphpdoc && stripos($line, '@license') !== false){
					$lic = preg_replace('/\*[ ]*@license[ ]*/i', '', $line);
					if(substr_count($lic, ' ') == 0 && strpos($lic, '://') !== false){
						// lic is similar to @license http://www.gnu.org/licenses/agpl-3.0.txt
						// Take the entire string as both URL and title.
						$ret[] = array('title' => $lic, 'url' => $lic);
					}
					elseif(strpos($lic, '://') === false){
						// lic is similar to @license GNU Affero General Public License v3
						// There's no url at all... just take the entire string as a title, blank URL.
						$ret[] = array('title' => $lic, 'url' => null);
					}
					elseif(strpos($lic, '<') !== false && strpos($lic, '>') !== false){
						// lic is similar to @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
						// String has both.
						$title = preg_replace('/[ ]*<[^>]*>/', '', $lic);
						$url = preg_replace('/.*<([^>]*)>.*/', '$1', $lic);
						$ret[] = array('title' => $title, 'url' => $url);
					}
				}
				break; // type: 'php'
		}
	}
	fclose($fh);
	return $ret;
}


/**
 * Slightly more advanced function to parse for specific information from file headers.
 *
 * @todo Support additional filetypes other than just PHP.
 *
 * Will return an array containing any author, license
 *
 * @todo This may move to the CLI system if found useful enough...
 * @param string $file
 * @return array
 */
function parse_for_documentation($file){
	$ret = array(
		'authors' => array(),
		'licenses' => array()
	);

	$fh = fopen($file, 'r');
	// ** sigh... counldn't open the file... oh well, skip to the next.
	if(!$fh) return $ret;

	// This will make filetype be the extension of the file... useful for expanding to JS, HTML and CSS files.
	if(strpos(basename($file), '.') !== false){
		$filetype = strtolower(substr($file, strrpos($file, '.') + 1));
	}
	else{
		$filetype = null;
	}


	// This is the counter for non valid doc lines.
	$counter = 0;
	$inphpdoc = false;
	$incomment = false;

	while(!feof($fh) && $counter <= 10){
		// I want to limit the number of lines read so this doesn't continue on reading the entire file.

		// Remove any extra whitespace.
		$line = trim(fgets($fh, 1024));
		switch($filetype){
			case 'php':
				// This only support multi-line phpdocs.
				// start of a phpDoc comment.
				if($line == '/**'){
					$inphpdoc = true;
					break;
				}
				// end of a phpDoc comment.  This indicates the end of the reading of the file...
				if($line == '*/'){
					$inphpdoc = false;
					break;
				}
				// Not in phpdoc... ok
				if(!$inphpdoc){
					$counter++;
					break;
				}

				// Recognize PHPDoc syntax... basically just [space]*[space]@license...
				if($inphpdoc){
					// Is this an @license line?
					if(stripos($line, '@license') !== false){
						$lic = preg_replace('/\*[ ]*@license[ ]*/i', '', $line);
						if(substr_count($lic, ' ') == 0 && strpos($lic, '://') !== false){
							// lic is similar to @license http://www.gnu.org/licenses/agpl-3.0.txt
							// Take the entire string as both URL and title.
							$ret['licenses'][] = array('title' => $lic, 'url' => $lic);
						}
						elseif(strpos($lic, '://') === false){
							// lic is similar to @license GNU Affero General Public License v3
							// There's no url at all... just take the entire string as a title, blank URL.
							$ret['licenses'][] = array('title' => $lic, 'url' => null);
						}
						elseif(strpos($lic, '<') !== false && strpos($lic, '>') !== false){
							// lic is similar to @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
							// String has both.
							$title = preg_replace('/[ ]*<[^>]*>/', '', $lic);
							$url = preg_replace('/.*<([^>]*)>.*/', '$1', $lic);
							$ret['licenses'][] = array('title' => $title, 'url' => $url);
						}
					}
					// Is this an @author line?
					if(stripos($line, '@author') !== false){
						$aut = preg_replace('/\*[ ]*@author[ ]*/i', '', $line);
						$autdata = array();
						if(strpos($aut, '<') !== false && strpos($aut, '>') !== false && preg_match('/<[^>]*(@| at )[^>]*>/i', $aut)){
							// Resembles: @author user foo <email@domain.com>
							// or         @author user foo <email at domain dot com>
							preg_match('/(.*) <([^>]*)>/', $aut, $matches);
							$autdata = array('name' => $matches[1], 'email' => $matches[2]);
						}
						elseif(strpos($aut, '(') !== false && strpos($aut, ')') !== false && preg_match('/\([^\)]*(@| at )[^\)]*\)/i', $aut)){
							// Resembles: @author user foo (email@domain.com)
							// of         @author user foo (email at domain dot com)
							preg_match('/(.*) \(([^\)]*)\)/', $aut, $matches);
							$autdata = array('name' => $matches[1], 'email' => $matches[2]);
						}
						else{
							// Eh, must be something else...
							$autdata = array('name' => $aut, 'email' => null);
						}

						// Sometimes the @author line may consist of:
						// @author credit to someone <someone@somewhere.com>
						$autdata['name'] = preg_replace('/^credit[s]* to/i', '', $autdata['name']);
						$autdata['name'] = preg_replace('/^contribution[s]* from/i', '', $autdata['name']);
						$autdata['name'] = trim($autdata['name']);
						$ret['authors'][] = $autdata;
					}
				}
				break; // type: 'php'
			case 'js':
				// This only support multi-line phpdocs.
				// start of a multiline comment.
				if($line == '/*' || $line == '/*!' || $line == '/**'){
					$incomment = true;
					break;
				}
				// end of a phpDoc comment.  This indicates the end of the reading of the file...
				if($line == '*/'){
					$incomment = false;
					break;
				}
				// Not in phpdoc... ok
				if(!$incomment){
					$counter++;
					break;
				}

				// Recognize "* Author: Person Blah" syntax... basically just [space]*[space]license...
				if($incomment){
					// Is this line Author: ?
					if(stripos($line, 'author:') !== false){
						$aut = preg_replace('/\*[ ]*author:[ ]*/i', '', $line);
						$autdata = array();
						if(strpos($aut, '<') !== false && strpos($aut, '>') !== false && preg_match('/<[^>]*(@| at )[^>]*>/i', $aut)){
							// Resembles: @author user foo <email@domain.com>
							// or         @author user foo <email at domain dot com>
							preg_match('/(.*) <([^>]*)>/', $aut, $matches);
							$autdata = array('name' => $matches[1], 'email' => $matches[2]);
						}
						elseif(strpos($aut, '(') !== false && strpos($aut, ')') !== false && preg_match('/\([^\)]*(@| at )[^\)]*\)/i', $aut)){
							// Resembles: @author user foo (email@domain.com)
							// of         @author user foo (email at domain dot com)
							preg_match('/(.*) \(([^\)]*)\)/', $aut, $matches);
							$autdata = array('name' => $matches[1], 'email' => $matches[2]);
						}
						else{
							// Eh, must be something else...
							$autdata = array('name' => $aut, 'email' => null);
						}

						// Sometimes the @author line may consist of:
						// @author credit to someone <someone@somewhere.com>
						$autdata['name'] = preg_replace('/^credit[s]* to/i', '', $autdata['name']);
						$autdata['name'] = preg_replace('/^contribution[s]* from/i', '', $autdata['name']);
						$autdata['name'] = trim($autdata['name']);
						$ret['authors'][] = $autdata;
					}
				}
				break; // type: 'js'
			default:
				break(2);
		}
	}
	fclose($fh);

	// I don't want 5 million duplicates... so remove all the duplicate results.
	// I need to use arrayUnique because the arrays are multi-dimensional.
	$ret['licenses'] = arrayUnique($ret['licenses']);
	$ret['authors'] = arrayUnique($ret['authors']);
	return $ret;
}

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
function _increment_version($version, $original){
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
 * Try to intelligently merge duplicate authors, matching a variety of input names.
 *
 * @param array <<string>> $authors
 * @return array
 */
function get_unique_authors($authors){
	// This clusterfuck of a section will basicaly match up the name to its email,
	// use the email as a unique key for name grouping,
	// then try to figure out the canonical name of the author.
	$ea = array();
	foreach($authors as $a){
		// Remove any whitespace.
		$a['email'] = trim($a['email']);
		$a['name'] = trim($a['name']);

		// Group the names under the emails attached.
		if(!isset($ea[$a['email']])) $ea[$a['email']] = array($a['name']);
		else $ea[$a['email']][] = $a['name'];
	}
	// I now have a cross reference list of emails to names.

	// Handle the unset emails first.
	if(isset($ea[''])){
		array_unique($ea['']);
		foreach($ea[''] as $nk => $n){
			// Look up this name in the list of names that have emails to them.
			foreach($ea as $k => $v){
				if($k == '') continue;
				if(in_array($n, $v)){
					// This name is also under an email address... opt to use the email address one instead.
					unset($ea[''][$nk]);
					continue 2;
				}
			}
		}

		// If there are no more unset names, no need to keep this array laying about.
		if(!sizeof($ea[''])) unset($ea['']);
	}


	$authors = array();
	// Now handle every email.
	foreach($ea as $e => $na){
		$na = array_unique($na);
		if($e == ''){
			foreach($na as $name) $authors[] = array('name' => $name);
			continue;
		}


		// Match differences such as Tomas V.V.Cox and Tomas V. V. Cox
		$simsearch = array();
		foreach($na as $k => $name){
			$key = preg_replace('/[^a-z]/i', '', $name);
			if(in_array($key, $simsearch)) unset($na[$k]);
			else $simsearch[] = $key;
		}


		// There may be a pattern in the names, ie: Charlie Powell == cpowell == powellc == charlie.powell
		$aliases = array();
		// Try to get the first and last name.
		$ln = $fn = $funame = '';
		foreach($na as $name){
			if(preg_match('/([a-z]*)[ ]+([a-z]*)/i', $name, $matches)){
				$funame = $matches[1] . ' ' . $matches[2];
				$fn = strtolower($matches[1]);
				$ln = strtolower($matches[2]);
				break;
			}
		}
		if($ln && $fn){
			foreach($na as $name){
				switch(strtolower($name)){
					case $fn . ' ' . $ln:
					case $ln . $fn{0}:
					case $fn . $ln{0}:
					case $fn . '.' . $ln:
						// It matches the pattern, it'll just use the fullname.
						continue 2;
						break;
					default:
						$authors[] = array('email' => $e, 'name' => $name);
				}
			}
			$authors[] = array('email' => $e, 'name' => $funame);
		}
		else{
			foreach($na as $name){
				$authors[] = array('email' => $e, 'name' => $name);
			}
		}
	}

	return $authors;
}

function get_unique_licenses($licenses){
	// This behaves much similar to the unique_authors system above, but much simplier.
	$lics = array();
	foreach($licenses as $k => $v){
		$v['title'] = trim($v['title']);
		$v['url'] = trim($v['url']);

		if(!isset($lics[$v['title']])){
			$lics[$v['title']] = array($v['url']);
		}
		elseif(!in_array($v['url'], $lics[$v['title']])){
			$lics[$v['title']][] = $v['url'];
		}
	}
	// $lics should be unique-ified now.
	$licenses = array();
	foreach($lics as $l => $urls){
		foreach($urls as $url) $licenses[] = array('title' => $l, 'url' => $url);
	}

	return $licenses;
}



function process_component($component, $forcerelease = false){
	global $packagername, $packageremail, $opts;

	// Get that component, should be available via the component handler.
	$cfile = ComponentFactory::ResolveNameToFile($component);
	if(!$cfile){
		throw new Exception('Unable to locate component.xml file for component ' . $component);
	}

	// Resolve it to the full path
	$fullcfile = ROOT_PDIR . $cfile;

	// Get the XMLLoader object for this file.  This will allow me to have more fine-tune control over the file.
	$xml = new XMLLoader();
	$xml->setRootName('component');
	if(!$xml->loadFromFile($fullcfile)){
		throw new Exception('Unable to load XML file ' . $cfile);
	}

	// Get the current version, this will be used to autocomplete for the next version.
	$version = $xml->getRootDOM()->getAttribute("version");

	// Not a 2.1 component version?... well it needs to be!
	// This actually needs to work with a variety of versions.
	$componentapiversion = $xml->getDOM()->doctype->systemId;
	switch($componentapiversion){
		case 'http://corepl.us/api/2_1/component.dtd':
		case 'http://corepl.us/api/2_4/component.dtd':
			// Now I can load the component itself, now that I know that the metafile is a 2.1 compatible version.
			$comp = new Component_2_1($fullcfile);
			$comp->load();
			break;
		default:
			throw new Exception('Unsupported component version, please ensure that your doctype systemid is correct.');
	}


	// Get the licenses currently set.  (maybe there's one that's not in the code)
	$licenses = array();

	if($opts['repackage']){
		$scanlicenses = true;
	}
	else{
		//$scanlicenses = CLI::PromptUser('Retrieve current list of licenses and merge in from code?', 'boolean', true);
		$scanlicenses = true;
	}
	if($scanlicenses){
		foreach($xml->getElements('//component/licenses/license') as $el){
			$url = @$el->getAttribute('url');
			$licenses[] = array(
				'title' => $el->nodeValue,
				'url' => $url
			);
		}
	}


	// Get the authors currently set. (maybe there's one that's not in the code)
	$authors = array();
	if($opts['repackage']){
		$scanauthors = true;
	}
	else{
		//$scanauthors = CLI::PromptUser('Retrieve current list of authors and merge in from code?', 'boolean', true);
		$scanauthors = true;
	}
	if($scanauthors){
		foreach($xml->getElements('//component/authors/author') as $el){
			$authors[] = array(
				'name' => $el->getAttribute('name'),
				'email' => @$el->getAttribute('email'),
			);
		}
	}

	$it = new CAEDirectoryIterator();
	// Ignore the component metaxml, this will get added automatically via the installer.
	$it->addIgnore($cfile);

	// The core has a "few" extra ignores to it...
	if($component == 'core'){
		$it->addIgnores('components/', 'config/configuration.xml', 'dropins/', 'exports/', 'nbproject/', 'themes/', 'utilities/', '.htaccess', 'gnupg', 'core/bootstrap.compiled.php');
		if(CDN_LOCAL_ASSETDIR) $it->addIgnore(CDN_LOCAL_ASSETDIR);
		if(CDN_LOCAL_PUBLICDIR) $it->addIgnore(CDN_LOCAL_PUBLICDIR);
		if(strpos(TMP_DIR_WEB, ROOT_PDIR) === 0) $it->addIgnore(TMP_DIR_WEB);
		if(strpos(TMP_DIR_CLI, ROOT_PDIR) === 0) $it->addIgnore(TMP_DIR_CLI);
	}

	// Set a couple generic development-only utilities.
	$it->addIgnore(dirname($cfile) . '/dev/');
	$it->addIgnore(dirname($cfile) . '/tests/');

	// @todo Should I support ignored files in the component.xml file?
	// advantage, developers can have tools in their directories that are not meant to be packaged.
	// disadvantage, currently no component other than core requires this.....
	/*$list = $this->getElements('/ignorefiles/file');
	foreach($list as $el){
		$it->addIgnores($this->getBaseDir() . $el->getAttribute('filename'));
	}*/

	if($component == 'core'){
		$it->setPath(ROOT_PDIR);
	}
	else{
		$it->setPath(dirname($cfile));
	}

	echo "Loading root path...";
	$it->scan();
	echo "OK" . NL;

	$viewdir    = $comp->getViewSearchDir();
	$assetdir   = $comp->getAssetDir();
	$basestrlen = strlen($comp->getBaseDir());
	$assetfiles = array();
	$viewfiles  = array();
	$otherfiles = array();


	echo "Scanning files for documentation and metacode...";
	foreach($it as $file){
		// This will get an array of all licenses and authors in the file's phpdoc.
		$docelements = parse_for_documentation($file->getFilename());
		$licenses = array_merge($licenses, $docelements['licenses']);
		$authors = array_merge($authors, $docelements['authors']);

		// If this ends with a "~", skip it!
		// These are backup files used by gedit.
		if(substr($file->getFilename(), -1) == '~') continue;

		// And then, scan this file for code, ie: classes, controllers, etc.
		$fname = substr($file->getFilename(), $basestrlen);

		if($viewdir && $file->inDirectory($viewdir)){
			// It's a template! (view)
			$viewfiles[] = array('file' => $fname, 'md5' => $file->getHash());
		}
		elseif($assetdir && $file->inDirectory($assetdir)){
			// It's an asset!
			$assetfiles[] = array('file' => $fname, 'md5' => $file->getHash());
		}
		else{
			// It's a something..... it goes in the "files" array!
			// This will be slightly different though, as it needs to also check for classes.
			$filedat = array(
				'file' => $fname,
				'md5' => $file->getHash(),
				'controllers' => array(),
				'classes' => array(),
				'interfaces' => array()
			);

			// PHP files get checked.
			if(preg_match('/\.php$/i', $fname)){
				$fconts = file_get_contents($file->getFilename());

				// Trim out the comments to prevent false readings.

				// Will remove /* ... */ multi-line comments.
				$fconts = preg_replace(':/\*.*\*/:Us', '', $fconts);
				// Will remove // single-line comments.
				$fconts = preg_replace('://.*$:', '', $fconts);


				// If there is a namespace on this file, make sure to grab that and prefix any classes!
				if(preg_match('/^namespace ([a-z\\\\0-9]*);$/im', $fconts, $ret)){
					$namespace = $ret[1] . '\\';
				}
				else{
					$namespace = '';
				}


				// Well... get the classes!
				preg_match_all('/^\s*(abstract |final ){0,1}class[ ]*([a-z0-9_\-]*)[ ]*extends[ ]*controller_2_1/im', $fconts, $ret);
				foreach($ret[2] as $foundclass){
					$filedat['controllers'][] = $namespace . $foundclass;
				}

				// Add any class found in this file. (skipping the ones I already found)
				preg_match_all('/^\s*(abstract |final ){0,1}class[ ]*([a-z0-9_\-]*)/im', $fconts, $ret);
				foreach($ret[2] as $foundclass){
					if(in_array($foundclass, $filedat['controllers'])) continue;
					$filedat['classes'][] = $namespace . $foundclass;
				}

				// Allow interfaces to be associated as a provided element too.
				preg_match_all('/^\s*(interface)[ ]*([a-z0-9_\-]*)/im', $fconts, $ret);
				foreach($ret[2] as $foundclass){
					$filedat['interfaces'][] = $namespace . $foundclass;
				}
			}


			// Empty classes?
			if(!sizeof($filedat['controllers'])) unset($filedat['controllers']);
			if(!sizeof($filedat['classes'])) unset($filedat['classes']);
			if(!sizeof($filedat['interfaces'])) unset($filedat['interfaces']);

			$otherfiles[] = $filedat;
		}
		echo ".";
	}
	echo "OK" . NL;

	// Remove dupes
	$authors = get_unique_authors($authors);
	$licenses = get_unique_licenses($licenses);

	$comp->setAuthors($authors);
	$comp->setLicenses($licenses);
	$comp->setFiles($otherfiles);
	$comp->setViewFiles($viewfiles);
	$comp->setAssetFiles($assetfiles);


	$ans = false;
	// Lookup the changelog text of this current version.
	$changelogfile = $comp->getBaseDir();
	if($comp->getName() == 'core'){
		// Core's changelog is located in the core directory.
		$changelogfile .= 'core/CHANGELOG';
		$name = 'Core Plus';
		$gitpaths = [
			$comp->getBaseDir() . 'config',
			$comp->getBaseDir() . 'core',
			$comp->getBaseDir() . 'install',
			$comp->getBaseDir() . 'index.php'
		];
	}
	else{
		// Nope, no extension.
		$changelogfile .= 'CHANGELOG';
		$name = $comp->getName();
		$gitpaths = [
			$comp->getBaseDir()
		];
	}

	// If repackage is requested, simply save and exit.
	if($opts['repackage']) $ans = 'save';

	while($ans != 'save'){
		$opts = array(
			'editvers'   => '[ VERSION     ] Set version number',
			'editdesc'   => '[ DESCRIPTION ] Edit description',
			'viewgit'    => '[ GIT         ] View pending GIT commits between ' . $version . ' and HEAD',
			'importgit'  => '[ CHNGLOG/GIT ] Import GIT commit logs into version ' . $version,
			'editchange' => '[ CHNGLOG     ] Edit for version ' . $version,
			'viewchange' => '[ CHNGLOG     ] View for version ' . $version,
			//'dbtables' => 'Manage DB Tables',
			'printdebug' => '[ DEBUG       ] Print the XML',
			'save'       => '[ FINISH      ] Save it!',
			'exit'       => 'Abort and exit without saving changes',
		);
		$ans = CLI::PromptUser('What do you want to edit for component ' . $component . ' ' . $version, $opts);

		switch($ans){
			case 'editvers':
				// If the current version has a 3rd party signifier... keep it as such.
				if(!strpos($version, '~')) $original = true;
				else $original = false;

				// Try to explode the version by a ~ sign, this signifies not the original packager/source.
				// ie: ForeignComponent 3.2.4 may be versioned 3.2.4.thisproject5
				// if it's the 5th revision of the upstream version 3.2.4 for 'thisproject'.
				$previousversion = $version;
				$version = _increment_version($version, $original);

				$version = CLI::PromptUser('Please set the new version or', 'text', $version);
				$comp->setVersion($version);

				if($version != $previousversion){
					$importgit = CLI::PromptUser('Do you want to automatically import GIT commits performed after ' . $previousversion . ' into the ' . $version . ' changelog?', 'boolean', true);
					if($importgit){
						$parser = new Core\Utilities\Changelog\Parser($name, $changelogfile);
						try{
							$parser->parse();
							$previouschange = $parser->getSection($previousversion);
							$previousdate   = $previouschange->getReleasedDate();
							$thischange     = $parser->getSection($version);

						}
						catch(Exception $e){
							$previousdate = '01 Jan 2013 00:00:00 -0400';
							$thischange   = $parser->getSection($version);
						}

						$changes = get_git_changes_since($name, $previousversion, $previousdate, $gitpaths);
						$linecount = sizeof($thischange->getEntriesSorted());
						foreach($changes as $line){
							$thischange->addLine($line);
						}
						$linesadded = sizeof($thischange->getEntriesSorted()) - $linecount;

						// Don't forget to save this changelog back down to the original file!
						$parser->save();

						// And notify the user of what happened.
						if(!sizeof($changes)){
							print 'No GIT history found.' . NL;
						}
						elseif(!$linesadded){
							print 'No new GIT commits found.' . NL;
						}
						else{
							print 'Added ' . $linesadded . ($linesadded != 1 ? ' lines' : ' line') . ' to the changelog successfully!' . NL;
							print $thischange->fetchFormatted() . NL . NL;
						}
					}
				}
				break;
			case 'editdesc':
				$comp->setDescription(CLI::PromptUser('Enter a description.', 'textarea', $comp->getDescription()));
				break;
			case 'editchange':
				manage_changelog($changelogfile, $name, $version);
				break;
			case 'viewgit':
				try{
					$parser = new Core\Utilities\Changelog\Parser($name, $changelogfile);
					$parser->parse();

					/** @var $thisversion Core\Utilities\Changelog\Section */
					$thischange      = $parser->getSection($version);
					$thisdate        = $thischange->getReleasedDate();
					$versioncheck    = $version;
					if(!$thisdate){
						$previouschange = $parser->getPreviousSection($version);
						$thisdate       = $previouschange->getReleasedDate();
						$versioncheck   = $previouschange->getVersion();
					}
				}
				catch(Exception $e){
					$thisdate = '01 Jan 2013 00:00:00 -0400';
				}

				$changes = get_git_changes_since($name, $versioncheck, $thisdate, $gitpaths);
				print "GIT commits since $name $versioncheck was released on $thisdate:\n\n";
				foreach($changes as $line){
					print $line . "\n";
				}
				print "\n";
				break;

			case 'importgit':
				$parser = new Core\Utilities\Changelog\Parser($name, $changelogfile);
				$parser->parse();

				/** @var $thisversion Core\Utilities\Changelog\Section */
				$thischange      = $parser->getSection($version);
				$previouschange  = $parser->getPreviousSection($version);
				$previousversion = $previouschange ? $previouschange->getVersion() : '0.0.0';
				$previousdate    = $previouschange ? $previouschange->getReleasedDate() : '2000-01-01';

				// If this version is released, I can't import anything!
				if($thischange->getReleasedDate()){
					print 'Version ' . $version . ' is already marked as released!  Refusing to import GIT changes into a released version.' . NL;
				}
				else{
					$changes = get_git_changes_since($name, $previousversion, $previousdate, $gitpaths);
					$linecount = sizeof($thischange->getEntriesSorted());
					foreach($changes as $line){
						$thischange->addLine($line);
					}
					$linesadded = sizeof($thischange->getEntriesSorted()) - $linecount;

					// Don't forget to save this changelog back down to the original file!
					$parser->save();

					// And notify the user of what happened.
					if(!sizeof($changes)){
						print 'No GIT history found since version ' . $previousversion . ' was released on ' . $previousdate . '.' . NL;
					}
					elseif(!$linesadded){
						print 'No new GIT commits found since version ' . $previousversion . ' was released on ' . $previousdate . '.' . NL;
					}
					else{
						print 'Added ' . $linesadded . ($linesadded != 1 ? ' lines' : ' line') . ' to the changelog successfully!' . NL;
						print $thischange->fetchFormatted() . NL . NL;
					}
				}
				break;
			case 'viewchange':
				$parser = new Core\Utilities\Changelog\Parser($name, $changelogfile);
				$parser->parse();

				/** @var $thisversion Core\Utilities\Changelog\Section */
				$thisversion = $parser->getSection($version);

				// Read the current changelog.
				$changelog = $thisversion->fetchFormatted();
				print $changelog . NL . NL;
				break;
			//case 'dbtables':
			//	$comp->setDBSchemaTableNames(explode("\n", CLI::PromptUser('Enter the tables that are included in this component', 'textarea', implode("\n", $comp->getDBSchemaTableNames()))));
			//	break;
			case 'printdebug':
				echo $comp->getRawXML() . NL;
				break;
			case 'exit':
				echo "Aborting build" . NL;
				exit;
				break;
		}
	}

	// User must have selected 'save'...
	$comp->save();

	// Reload the XML file, since it probably changed.
	$xml->setRootName('component');
	if(!$xml->loadFromFile(ROOT_PDIR . $cfile)){
		//@todo The XML file didn't load.... would this be a good time to revert a saved state?
		throw new Exception('Unable to load XML file ' . $cfile);
	}


	// Update the version installed in the database, since the codebase *was* afterall running with whatever files
	// were currently available, (and thus were set as the next version).
	$cmodel = ComponentModel::Construct($name);
	$cmodel->set('version', $version);
	$cmodel->save();

	echo "Saved!" . NL;

	if($forcerelease){
		// if force release, don't give the user an option... just do it.
		$bundleyn = true;
	}
	elseif(isset($opts['repackage']) && $opts['repackage']){
		// Repackaging doesn't bundle it.
		$bundleyn = false;
	}
	else{
		$bundleyn = CLI::PromptUser('Package saved, do you want to bundle the changes into a package?', 'boolean');
	}

	if($bundleyn){


		// Update the changelog version first!
		// This is done here to signify that the version has actually been bundled up.
		// Lookup the changelog text of this current version.
		$file = $comp->getBaseDir();

		if($comp->getName() == 'core'){
			// Core's changelog is located in the core directory.
			$file .= 'core/CHANGELOG';
			$headerprefix = 'Core Plus';
			$header = 'Core Plus ' . $version . "\n";
		}
		else{
			// Nope, no extension.
			$file .= 'CHANGELOG';
			$headerprefix = $comp->getName();
			// The header line will be exactly [name] [version].
			$header = $comp->getName() . ' ' . $version . "\n";
		}

		add_release_date_to_changelog($file, $headerprefix, $version);

		// Create a temp directory to contain all these
		$dir = TMP_DIR . 'packager-' . $component . '/';

		// The destination depends on the type.
		switch($component){
			case 'core':
				$tgz = ROOT_PDIR . '../exports/core/' . $component . '-' . $version . '.tgz';
				break;
			default:
				$tgz = ROOT_PDIR . '../exports/components/' . $component . '-' . $version . '.tgz';
				break;
		}

		if(!is_dir(dirname($tgz)))  mkdir(dirname($tgz), 0777, true);
		if(!is_dir($dir))           mkdir($dir, 0777, true);
		if(!is_dir($dir . 'data/')) mkdir($dir . 'data/', 0777, true);

		// I already have a good iterator...just reuse it.
		$it->rewind();

		foreach($it as $file){
			$fname = substr($file->getFilename(), $basestrlen);
			$file->copyTo($dir . 'data/' . $fname);
		}

		// The core will have some additional files required to be created.
		if($component == 'core'){

			// I need the compiled version of the bootstrap.
			copy(ROOT_PDIR . 'core/bootstrap.compiled.php', $dir . 'data/core/bootstrap.compiled.php');

			// The deny hosts text will go into the various secure directories.
			$denytext = <<<EOD
# This is specifically created to prevent access to ANYTHING in this directory.
#  Under no situation should anything in this directory be readable
#  by anyone at any time.

<Files *>
	Order deny,allow
	Deny from All
</Files>
EOD;
			$corecreates = array(
				'components', 'gnupg', 'themes'
			);
			$coresecures = array(
				'components', 'config', 'gnupg', 'core'
			);
			foreach($corecreates as $createdir){
				mkdir($dir . 'data/' . $createdir);
			}
			foreach($coresecures as $securedir){
				file_put_contents($dir . 'data/' . $securedir . '/.htaccess', $denytext);
			}
		}

		// Because the destination is relative...
		$xmldest = 'data/' . substr(ROOT_PDIR . $cfile, $basestrlen);
		$xmloutput = \Core\Filestore\Factory::File($dir . $xmldest);
		$xmloutput->putContents($xml->asMinifiedXML());

		//$packager = 'Core Plus ' . ComponentHandler::GetComponent('core')->getVersion() . ' (http://corepl.us)';
		//$packagename = $c->getName();

		// Different component types require a different bundle type.
		//$bundletype = ($component == 'core')? 'core' : 'component';

		// Save the package.xml file.
		$comp->savePackageXML(true, $dir . 'package.xml');

		exec('tar -czf ' . $tgz . ' -C ' . $dir . ' --exclude-vcs --exclude=*~ --exclude=._* .');
		$bundle = $tgz;

		if(CLI::PromptUser('Package created, do you want to sign it?', 'boolean', true)){
			exec('gpg --homedir "' . GPG_HOMEDIR . '" --no-permission-warning -u "' . $packageremail . '" -a --sign "' . $tgz . '"');
			$bundle .= '.asc';

			// If the user signed it... give the option to automatically commit and tag all changes for the component.
			if(CLI::PromptUser('Package signed, GIT commit everything?', 'boolean', true)){
				// First, see if any new files need to be added to GIT in the directories.
				exec('git status -u -s "' . implode('" "', $gitpaths) . '" | egrep "^\?\?" | sed "s:?? ::"', $newfiles);
				foreach($newfiles as $nf){
					exec('git add "' . $nf . '"');
				}

				exec('git status -u -s "' . implode('" "', $gitpaths) . '" | egrep "(^A|^ M)" | sed "s:[A M]*::"', $modifiedfiles);
				foreach($modifiedfiles as $nf){
					exec('git add "' . $nf . '"');
				}

				exec('git status -u -s "' . implode('" "', $gitpaths) . '" | egrep "(^ D)" | sed "s:[A M]*::"', $deletedfiles);
				foreach($deletedfiles as $nf){
					exec('git del "' . $nf . '"');
				}

				exec('git commit -s -m "Release ' . $name . ' ' . $version . '" "' . implode('" "', $gitpaths) . '"');

				echo "Don't forget to do a git push ;)" . NL;
			}
		}

		// And remove the tmp directory.
		exec('rm -fr "' . $dir . '"');

		echo "Created package of " . $component . ' ' . $version . NL . " as " . $bundle . NL;
	}
} // function process_component($component)


function process_theme($theme, $forcerelease = false){

	// Since "Themes" are not enabled for CLI by default, I need to manually include that file.
	require_once(ROOT_PDIR . 'components/theme/libs/Theme/Theme.php');

	global $packagername, $packageremail, $opts;

	$t = new Theme\Theme($theme);
	$t->load();

	$version = $t->getVersion();

	echo "Loading root path...";
	$it = new CAEDirectoryIterator();
	$it->setPath($t->getBaseDir());
	$it->addIgnore($t->getBaseDir() . 'theme.xml');
	$it->scan();
	echo "OK" . NL;

	$assetdir   = $t->getAssetDir();
	$skindir    = $t->getSkinDir();
	$viewdir    = $t->getViewSearchDir();
	$basestrlen = strlen($t->getBaseDir());
	$assetfiles = array();
	$skinfiles  = array();
	$viewfiles  = array();
	$name       = $t->getName();

	echo "Scanning files for metacode...";
	foreach($it as $file){
		// And then, scan this file for code, ie: classes, controllers, etc.
		$fname = substr($file->getFilename(), $basestrlen);

		if($assetdir && $file->inDirectory($assetdir)){
			// It's an asset!
			$assetfiles[] = array('file' => $fname, 'md5' => $file->getHash());
		}
		if($skindir && $file->inDirectory($skindir)){
			// It's a skin!
			$skinfiles[] = array('file' => $fname, 'md5' => $file->getHash());
		}
		elseif($viewdir && $file->inDirectory($viewdir)){
			// It's a template! (view)
			$viewfiles[] = array('file' => $fname, 'md5' => $file->getHash());
		}
		else{
			// It's a something..... I don't care.
			//$otherfiles[] = array('file' => $fname, 'md5' => $file->getHash());
		}
		echo ".";
	}
	echo "OK" . NL;

	$t->setAssetFiles($assetfiles);
	$t->setSkinFiles($skinfiles);
	$t->setViewFiles($viewfiles);

	// Lookup the changelog text of this current version.
	$changelogfile = $t->getBaseDir() . 'CHANGELOG';
	$gitpaths = [
		$t->getBaseDir(),
	];

	$ans = false;

	while($ans != 'save'){
		$opts = array(
			'editvers'   => '[ VERSION     ] Set version number',
			'editdesc'   => '[ DESCRIPTION ] Edit description',
			'viewgit'    => '[ GIT         ] View pending GIT commits between ' . $version . ' and HEAD',
			'importgit'  => '[ CHNGLOG/GIT ] Import GIT commit logs into version ' . $version,
			'editchange' => '[ CHNGLOG     ] Edit for version ' . $version,
			'viewchange' => '[ CHNGLOG     ] View for version ' . $version,
			'printdebug' => '[ DEBUG       ] Print the XML',
			'save'       => '[ FINISH      ] Save it!',
			'exit'       => 'Abort and exit without saving changes',
		);
		$ans = CLI::PromptUser('What do you want to edit for theme ' . $name . ' ' . $version, $opts);

		switch($ans){
			case 'editvers':
				$previousversion = $t->getVersion();
				$version = _increment_version($t->getVersion(), true);
				$version = CLI::PromptUser('Please set the version of the new release', 'text', $version);
				$t->setVersion($version);

				if($version != $previousversion){
					$importgit = CLI::PromptUser('Do you want to automatically import GIT commits performed after ' . $previousversion . ' into the ' . $version . ' changelog?', 'boolean', true);
					if($importgit){
						$parser = new Core\Utilities\Changelog\Parser('Theme/' . $name, $changelogfile);
						$parser->parse();
						$previouschange = $parser->getSection($previousversion);
						$thischange     = $parser->getSection($version);

						$changes = get_git_changes_since('Theme/' . $name, $previousversion, $previouschange->getReleasedDate(), $gitpaths);
						$linecount = sizeof($thischange->getEntriesSorted());
						foreach($changes as $line){
							$thischange->addLine($line);
						}
						$linesadded = sizeof($thischange->getEntriesSorted()) - $linecount;

						// Don't forget to save this changelog back down to the original file!
						$parser->save();

						// And notify the user of what happened.
						if(!sizeof($changes)){
							print 'No GIT history found.' . NL;
						}
						elseif(!$linesadded){
							print 'No new GIT commits found.' . NL;
						}
						else{
							print 'Added ' . $linesadded . ($linesadded != 1 ? ' lines' : ' line') . ' to the changelog successfully!' . NL;
							print $thischange->fetchFormatted() . NL . NL;
						}
					}
				}

				break;
			case 'editdesc':
				$t->setDescription(CLI::PromptUser('Enter a description.', 'textarea', $t->getDescription()));
				break;
			case 'editchange':
				manage_changelog($changelogfile, 'Theme/' . $name, $version);
				break;

			case 'viewgit':
				try{
					$parser = new Core\Utilities\Changelog\Parser('Theme/' . $name, $changelogfile);
					$parser->parse();

					/** @var $thisversion Core\Utilities\Changelog\Section */
					$thischange      = $parser->getSection($version);
					$thisdate        = $thischange->getReleasedDate();
					$versioncheck    = $version;
					if(!$thisdate){
						$previouschange = $parser->getPreviousSection($version);
						$thisdate       = $previouschange->getReleasedDate();
						$versioncheck   = $previouschange->getVersion();
					}
				}
				catch(Exception $e){
					$thisdate = '01 Jan 2013 00:00:00 -0400';
				}

				$changes = get_git_changes_since('Theme ' . $name, $versioncheck, $thisdate, $gitpaths);
				print "GIT commits since $name $versioncheck was released on $thisdate:\n\n";
				foreach($changes as $line){
					print $line . "\n";
				}
				print "\n";
				break;

			case 'importgit':
				$parser = new Core\Utilities\Changelog\Parser('Theme/' . $name, $changelogfile);
				$parser->parse();

				/** @var $thisversion Core\Utilities\Changelog\Section */
				$thischange      = $parser->getSection($version);
				$previouschange  = $parser->getPreviousSection($version);
				$previousversion = $previouschange ? $previouschange->getVersion() : '0.0.0';
				$previousdate    = $previouschange ? $previouschange->getReleasedDate() : '2000-01-01';

				// If this version is released, I can't import anything!
				if($thischange->getReleasedDate()){
					print 'Version ' . $version . ' is already marked as released!  Refusing to import GIT changes into a released version.' . NL;
				}
				else{
					$changes = get_git_changes_since('Theme/' . $name, $previousversion, $previousdate, $gitpaths);
					$linecount = sizeof($thischange->getEntriesSorted());
					foreach($changes as $line){
						$thischange->addLine($line);
					}
					$linesadded = sizeof($thischange->getEntriesSorted()) - $linecount;

					// Don't forget to save this changelog back down to the original file!
					$parser->save();

					// And notify the user of what happened.
					if(!sizeof($changes)){
						print 'No GIT history found since version ' . $previousversion . ' was released on ' . $previousdate . '.' . NL;
					}
					elseif(!$linesadded){
						print 'No new GIT commits found since version ' . $previousversion . ' was released on ' . $previousdate . '.' . NL;
					}
					else{
						print 'Added ' . $linesadded . ($linesadded != 1 ? ' lines' : ' line') . ' to the changelog successfully!' . NL;
						print $thischange->fetchFormatted() . NL . NL;
					}
				}
				break;
			case 'viewchange':
				$parser = new Core\Utilities\Changelog\Parser('Theme/' . $name, $changelogfile);
				$parser->parse();

				/** @var $thisversion Core\Utilities\Changelog\Section */
				$thisversion = $parser->getSection($version);

				// Read the current changelog.
				$changelog = $thisversion->fetchFormatted();
				print $changelog . NL . NL;
				break;
			case 'printdebug':
				echo $t->getRawXML() . NL;
				break;
		}
	}



	// User must have selected 'finish'...
	$t->save();
	echo "Saved!" . NL;

	if($forcerelease){
		// if force release, don't give the user an option... just do it.
		$bundleyn = true;
	}
	else{
		$bundleyn = CLI::PromptUser('Theme saved, do you want to bundle the changes into a package?', 'boolean');
	}


	if($bundleyn){

		$file = $t->getBaseDir() . 'CHANGELOG';
		$headerprefix = 'Theme/' . $name;
		// The header line will be exactly [name] [version].
		$header = 'Theme/' . $name . ' ' . $version . "\n";

		add_release_date_to_changelog($file, $headerprefix, $version);


		// Create a temp directory to contain all these
		$dir = TMP_DIR . 'packager-' . $theme . '/';

		// Destination tarball
		$tgz = ROOT_PDIR . '../exports/themes/' . $theme . '-' . $version . '.tgz';

		// Ensure the export directory exists.
		if(!is_dir(dirname($tgz))) exec('mkdir -p "' . dirname($tgz) . '"');
		//mkdir(dirname($tgz));

		if(!is_dir($dir)) mkdir($dir);
		if(!is_dir($dir . 'data/')) mkdir($dir . 'data/');

		// I already have a good iterator...just reuse it.
		$it->rewind();

		foreach($it as $file){
			$fname = substr($file->getFilename(), $basestrlen);
			$file->copyTo($dir . 'data/' . $fname);
		}

		// Because the destination is relative...
		$xmldest = 'data/theme.xml' ;
		$xmloutput = \Core\Filestore\Factory::File($dir . $xmldest);
		$xmloutput->putContents($t->getRawXML(true));

		// Save the package.xml file.
		$t->savePackageXML(true, $dir . 'package.xml');

		exec('tar -czf ' . $tgz . ' -C ' . $dir . ' --exclude-vcs --exclude=*~ --exclude=._* .');
		$bundle = $tgz;

		if(CLI::PromptUser('Package created, do you want to sign it?', 'boolean', true)){
			exec('gpg --homedir "' . GPG_HOMEDIR . '" --no-permission-warning -u "' . $packageremail . '" -a --sign "' . $tgz . '"');
			$bundle .= '.asc';

			// If the user signed it... give the option to automatically commit and tag all changes for the component.
			if(CLI::PromptUser('Package signed, GIT commit everything?', 'boolean', true)){
				// First, see if any new files need to be added to GIT in the directories.
				exec('git status -u -s "' . implode('" "', $gitpaths) . '" | egrep "^\?\?" | sed "s:?? ::"', $newfiles);
				foreach($newfiles as $nf){
					exec('git add "' . $nf . '"');
				}

				exec('git status -u -s "' . implode('" "', $gitpaths) . '" | egrep "(^A|^ M)" | sed "s:[A M]*::"', $modifiedfiles);
				foreach($modifiedfiles as $nf){
					exec('git add "' . $nf . '"');
				}

				exec('git status -u -s "' . implode('" "', $gitpaths) . '" | egrep "(^ D)" | sed "s:[A M]*::"', $deletedfiles);
				foreach($deletedfiles as $nf){
					exec('git del "' . $nf . '"');
				}

				exec('git commit -s -m "Release Theme/' . $name . ' ' . $version . '" "' . implode('" "', $gitpaths) . '"');
			}
		}

		// And remove the tmp directory.
		exec('rm -fr "' . $dir . '"');

		echo "Created package of " . $theme . ' ' . $version . NL . " as " . $bundle . NL;
	}
} // function process_theme($theme)


/**
 * Will return an array with the newest exported versions of each component.
 */
function get_exported_components(){
	$c = array();
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

		$dash = strrpos($fbase, '-');
		$n = substr($fbase, 0, $dash);
		$v = substr($fbase, ($dash+1));
		// instead, I need to look for a dash followed by a number.  This should indicate the version number.
		//preg_match('/^(.*)\-([0-9]+.*)$/', $fbase, $matches);

		//$n = $matches[1];
		//$v = $matches[2];

		// Tack it on.
		if(!isset($c[$n])){
			$c[$n] = array('version' => $v, 'signed' => $signed, 'filename' => $dir . '/' . $file);
		}
		else{
			switch(Core::VersionCompare($c[$n]['version'], $v)){
				case -1:
					// Existing older, overwrite!
					$c[$n] = array('version' => $v, 'signed' => $signed, 'filename' => $dir . '/' . $file);
					break;
				case 0:
					// Same, check the signed status.
					if($signed) $c[$n] = array('version' => $v, 'signed' => $signed, 'filename' => $dir . '/' . $file);
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
	$c = array();
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
			$c[$n] = array('version' => $v, 'signed' => $signed, 'filename' => $dir . '/' . $file);
		}
		else{
			switch(version_compare($c[$n]['version'], $v)){
				case -1:
					// Existing older, overwrite!
					$c[$n] = array('version' => $v, 'signed' => $signed, 'filename' => $dir . '/' . $file);
					break;
				case 0:
					// Same, check the signed status.
					if($signed) $c[$n] = array('version' => $v, 'signed' => $signed, 'filename' => $dir . '/' . $file);
					break;
				default:
					// Do nothing, current is at a higher version.
			}
		}
	}
	closedir($dh);

	if(!isset($c['core'])) $c['core'] = array('version' => null, 'signed' => false);

	return $c['core'];
} // function get_exported_core()


function get_exported_component($component){
	global $_cversions;
	if(is_null($_cversions)){
		$_cversions = get_exported_components();
		// Tack on the core.
		$_cversions['core'] = get_exported_core();
	}

	if(!isset($_cversions[$component])) return array('version' => null, 'signed' => false);
	else return $_cversions[$component];
}

/**
 * Prompt the user for changes and write those changes back to a set changelog in the correct format.
 */
function manage_changelog($file, $name, $version){

	$parser = new Core\Utilities\Changelog\Parser($name, $file);

	if(file_exists($file)){
		$parser->parse();

		/** @var $thisversion Core\Utilities\Changelog\Section */
		$thisversion = $parser->getSection($version);

		// Read the current changelog.
		$changelog = $thisversion->fetch();
	}
	else{
		$thisversion = $parser->getSection($version);
		$changelog = '';
	}

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

	//echo $thisversion->fetchFormatted(); die('halting'); // DEBUG

	// Write this back out to that file :)
	$parser->save();
}

/**
 * Add the release date to the changelog for the current version.
 */
function add_release_date_to_changelog($file, $name, $version){
	// Update the changelog version first!
	// This is done here to signify that the version has actually been bundled up.
	// Lookup the changelog text of this current version.
	global $packagername, $packageremail;

	$parser = new Core\Utilities\Changelog\Parser($name, $file);
	$parser->parse();

	/** @var $thisversion Core\Utilities\Changelog\Section */
	$thisversion = $parser->getSection($version);

	$thisversion->parseLine("--$packagername <$packageremail>  " . Time::GetCurrent(Time::TIMEZONE_DEFAULT, Time::FORMAT_RFC2822));

	// Write this back out to that file :)
	$parser->save();
}


function get_git_changes_since($componentname, $sinceversion, $sincedate, $gitpaths){
	$paths = [];
	$changes = [];
	foreach($gitpaths as $path){
		$paths[] = '"' . trim($path) . '"';
	}
	exec('git log --no-merges --format="%s" --since="' . $sincedate . '" ' . implode(' ', $paths), $gitlogoutput);

	$linesadded = 0;
	foreach($gitlogoutput as $line){
		// If the line matches "[COMP NAME] [version]"... then assume that's simply a release commit and ignore it.
		if($line == $componentname . ' ' . $sinceversion) continue;

		// Or if it contains the name, since version, and "release"... same thing!
		if(stripos($line, $componentname . ' ' . $sinceversion) !== false && stripos($line, 'release') !== false) continue;

		// Otherwise, it's a change!
		$changes[] = $line;
	}

	return $changes;
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

CLI::SaveSettingsFile('packager', array('packagername', 'packageremail'));


// Before ANYTHING happens.... make sure that the system is compiled!
echo '# Compiling system...' . "\n";
exec(ROOT_PDIR . '../utilities/compiler.php');
echo "OK!\n";


if($opts['type']){
	$ans = $opts['type'];
}
else{
	$ans = CLI::PromptUser(
		"What operation do you want to do?",
		array(
			'component' => 'Manage a Component',
			'theme' => 'Manage a Theme',
			//	'bundle' => 'Installation Bundle',
			'exit' => 'Exit the script',
		),
		'component'
	);
}

switch($ans){
	case 'component':
		echo "Scanning existing components...\n";

		// Open the "component" directory and look for anything with a valid component.xml file.
		$files = array();
		$longestname = 4;
		// Tack on the core component.
		$core = Core::GetComponent('core');
		$files['core'] = [
			'title' => 'core' . ($opts['listversions'] ? (' ' . $core->getVersion()) : '' ),
			'name' => 'core',
			'version' => $core->getVersion(),
			'component' => $core,
			'dir' => ROOT_PDIR . 'core/',
		];
		$dir = ROOT_PDIR . 'components';
		$dh = opendir($dir);
		while(($file = readdir($dh)) !== false){
			if($file{0} == '.') continue;
			if(!is_dir($dir . '/' . $file)) continue;
			if(!is_readable($dir . '/' . $file . '/' . 'component.xml')) continue;

			$c = Core::GetComponent($file);

			// Is this not valid?
			if(!$c){
				echo 'Skipping invalid component ' . $file . NL;
				continue;
			}

			// What's this file's version?
			$xml = new XMLLoader();
			$xml->setRootName('component');
			if(!$xml->loadFromFile($dir .  '/' . $file . '/component.xml')){
				echo 'Skipping component ' . $file . ', unable to load XML file' . NL;
				continue;
			}

			// Get the current version, this will be used to autocomplete for the next version.
			//$version = $xml->getRootDOM()->getAttribute("version");
			$version = $c->getVersion();

			// If display versions is requested, tack on the version number too!
			if($opts['listversions']){
				$title = $file . ' ' . $version;
			}
			else{
				$title = $file;
			}

			$longestname = max($longestname, strlen($title));
			$files[$file] = [
				'title' => $title,
				'name' => $file,
				'version' => $version,
				'component' => $c,
				'dir' => ROOT_PDIR . $file . '/',
			];
		}
		closedir($dh);
		// They should be in alphabetical order...
		ksort($files);


		// Before prompting the user which one to choose, tack on the exported versions.
		$versionedfiles = array();
		foreach($files as $k => $f){

			$line = str_pad($f['title'], $longestname+1, ' ', STR_PAD_RIGHT);
			$c = $f['component'];
			$lineflags = array();

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

			// Change the changes
			if(
				sizeof($c->getChangedAssets()) ||
				sizeof($c->getChangedFiles()) ||
				sizeof($c->getChangedTemplates())
			){
				$lineflags[] = '** needs packaged **';
			}


			$versionedfiles[$k] = $line . ' ' . implode(' ', $lineflags);
		}

		if($opts['listonly']){
			echo implode(NL, $versionedfiles) . NL;
			exit;
		}

		if($opts['name']){
			process_component($opts['name']);
		}
		else{
			$ans = CLI::PromptUser("Which component do you want to package/manage?", $versionedfiles);
			process_component($files[$ans]['name']);
		}

		break; // case 'component'
	case 'theme':
		// Open the "themes" directory and look for anything with a valid theme.xml file.
		$files = array();
		$dir = ROOT_PDIR . 'themes';
		$dh = opendir($dir);
		while(($file = readdir($dh)) !== false){
			if($file{0} == '.') continue;
			if(!is_dir($dir . '/' . $file)) continue;
			if(!is_readable($dir . '/' . $file . '/' . 'theme.xml')) continue;

			$files[] = $file;
		}
		closedir($dh);
		// They should be in alphabetical order...
		sort($files);

		if($opts['listonly']){
			echo implode(NL, $files) . NL;
			exit;
		}

		if($opts['name']){
			process_theme($opts['name']);
		}
		else{
			$ans = CLI::PromptUser("Which theme do you want to package/manage?", $files);
			process_theme($files[$ans]);
		}

		break; // case 'component'
	case 'exit':
		echo 'Bye bye' . NL;
		break;
	default:
		echo "Unknown option..." . NL;
		break;
}


