<?php
/**
 * File for class Packager definition in the Core Plus project
 *
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20141125.2112
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

namespace Core\Utilities;
use Core\Filestore\Backends\DirectoryLocal;
use Core\Filestore\Backends\FileLocal;
use Core\Filestore\Directory;
use Core\Filestore\DirectoryIterator;
use Core\Filestore\File;


/**
 * A short teaser of what Packager does.
 *
 * More lengthy description of what Packager does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for Packager
 * <h4>Example 1</h4>
 * <p>Description 1</p>
 * <code>
 * // Some code for example 1
 * $a = $b;
 * </code>
 *
 *
 * <h4>Example 2</h4>
 * <p>Description 2</p>
 * <code>
 * // Some code for example 2
 * $b = $a;
 * </code>
 *
 *
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class Packager {
	/** @var string Type of the package to manage, set from __construct */
	private $_type;
	/** @var string Name of the package to manage, usually pulled from metafile */
	private $_name;
	/** @var string Keyname of the package to manage, set from __construct */
	private $_keyname;
	/** @var string Filename of the component or theme XML metafile. */
	private $_xmlFile;
	/** @var Directory Base directory of this package source */
	private $_base;
	/** @var DirectoryIterator Directory object containing all of the package source */
	private $_iterator;
	/** @var \XMLLoader Raw XML loader object for the source component/theme */
	private $_xmlLoader;
	/** @var array Array of licenses from this package source */
	private $_licenses;
	/** @var array Array of authors from this package source */
	private $_authors;
	/** @var Changelog\Parser Changelog parser for this package */
	private $_changelog;
	/** @var string Version according to the XML metafile, (or set by the developer) */
	private $_version;
	/** @var array Paths of files tracked by GIT for this package */
	private $_gitPaths;

	private static $Denytext = <<<EOD
# This is specifically created to prevent access to EVERYTHING in this directory.
# Under no situation should anything in this directory be readable
# by any remote user at any time.

<Files *>
	Order deny,allow
	Deny from All
</Files>
EOD;

	public function __construct($type, $name){

		$this->_keyname = $name;

		switch($type){
			case 'theme':
				$this->_type = $type;
				$this->_setupTheme();
				break;
			case 'component':
				$this->_type = $type;
				$this->_setupComponent();
				break;
			case 'core':
				$this->_type = $type;
				$this->_setupCore();
				break;
			default:
				throw new \Exception('Invalid type requested, please set only "theme", "component", or "core".  Provided type: [' . $type . ']');
		}

		$this->_version = $this->_xmlLoader->getRootDOM()->getAttribute("version");

		if(!$this->_changelog->exists()){
			$this->_changelog->createInitial($this->_version);
		}
		else{
			$this->_changelog->parse();
		}
	}

	/**
	 * Get the current GIT branch of the directory the user is CD'd into, (presumably the application directory).
	 *
	 * @return string
	 */
	public function getGitBranch(){
		// Use the CLI to execute the command, since we have no GIT wrapper.
		$branch = exec("git branch -q | egrep '^\*' | sed 's:^\* ::'");

		return $branch;
	}

	/**
	 * Get the current version of the package,
	 * set from either the developer or pulled from the metafile.
	 *
	 * @return string
	 */
	public function getVersion(){
		return $this->_version;
	}

	/**
	 * Set the version for this package
	 *
	 * @param string $version
	 */
	public function setVersion($version){
		if($version == $this->_version){
			return;
		}

		$this->_getObject()->setVersion($version);
		$this->_version = $version;
	}

	/**
	 * Get the description for this Package
	 * @return string
	 */
	public function getDescription(){
		return $this->_getObject()->getDescription();
	}

	/**
	 * Set the description for this package
	 *
	 * @param string $description
	 */
	public function setDescription($description){
		$this->_getObject()->setDescription($description);
	}

	/**
	 * Get a human-friendly string for this package.
	 *
	 * Will be Core, component [blah], or theme [blah].
	 *
	 * @return string
	 */
	public function getLabel(){
		switch($this->_type){
			case 'core':
				return 'Core';
			case 'component':
				return 'component ' . $this->_name;
			case 'theme':
				return 'theme ' . $this->_name;
		}
	}

	/**
	 * Get the type of package, "core", "component", "theme".
	 *
	 * @return string
	 */
	public function getType(){
		return $this->_type;
	}

	/**
	 * Get the keyname of packager
	 * @return string
	 */
	public function getKeyname(){
		return $this->_keyname;
	}

	/**
	 * Scan this source package for inline documentation.
	 *
	 * This will populate the license and author fields automatically.
	 */
	public function scanInlineDocumentation(){
		foreach($this->_iterator as $file){
			/** @var FileLocal $file */

			// This will get an array of all licenses and authors in the file's phpdoc.
			$docelements = self::ParseForDocumentation($file->getFilename());
			$this->_licenses = array_merge($this->_licenses, $docelements['licenses']);
			$this->_authors = array_merge($this->_authors, $docelements['authors']);
			// No else needed, else is it's a directory, we don't care about those here.
		}
		
		// If there is a LICENSE, LICENSE.[txt|TXT], or LICENSE.md, then read that and try to determine the best license from there.
		$licenseFile = null;
		if(file_exists($this->_base->getPath() . '/LICENSE')){
			$licenseFile = $this->_base->getPath() . '/LICENSE';
		}
		elseif(file_exists($this->_base->getPath() . '/LICENSE.txt')){
			$licenseFile = $this->_base->getPath() . '/LICENSE.txt';
		}
		elseif(file_exists($this->_base->getPath() . '/LICENSE.TXT')){
			$licenseFile = $this->_base->getPath() . '/LICENSE.TXT';
		}
		elseif(file_exists($this->_base->getPath() . '/LICENSE.md')){
			$licenseFile = $this->_base->getPath() . '/LICENSE.md';
		}
		
		if($licenseFile){
			$contents = file_get_contents($licenseFile);
			
			$autoDetect = \Core\Licenses\Factory::DetectLicense($contents);
			if($autoDetect){
				$this->_licenses[ $autoDetect['key'] ] = $autoDetect;
			}
			else{
				trigger_error('Unknown license in ' . $licenseFile . ', please contribute this upstream!', E_USER_NOTICE);
			}
		}

		// Remove dupes
		$this->_authors = self::GetUniqueAuthors($this->_authors);
	}

	/**
	 * Get the raw CHANGELOG object
	 *
	 * @return Changelog\Parser
	 */
	public function getChangelog(){
		return $this->_changelog;
	}

	/**
	 * Get the requested CHANGELOG section by version
	 *
	 * @param null|string $version
	 *
	 * @return Changelog\Section
	 */
	public function getChangelogSection($version = null){
		if(!$version){
			$version = $this->_version;
		}

		return $this->_changelog->getSection($version);
	}

	/**
	 * Check if the requested version has been marked as released yet
	 *
	 * @param null|string $version
	 *
	 * @return bool
	 */
	public function isVersionReleased($version = null){
		return ($this->getChangelogSection($version)->getReleasedDate());
	}

	/**
	 * Get all GIT changes since version z.y.x and date xxxx.yy.dd for this package
	 *
	 * @param string $sinceversion Version to ignore, (used to ignore release statements).
	 * @param string $sincedate    Date to pull changes since
	 *
	 * @return array
	 */
	public function getGitChangesSince($sinceversion, $sincedate){
		$paths = [];
		$changes = [];
		foreach($this->_gitPaths as $path){
			$paths[] = '"' . trim($path) . '"';
		}
		exec('git log --no-merges --format="%s" --since="' . $sincedate . '" ' . implode(' ', $paths), $gitlogoutput);

		foreach($gitlogoutput as $line){
			if(
				// If the line matches "[COMP NAME] [version]"...
				$line == $this->_name . ' ' . $sinceversion ||
				// Or if it contains the name, since version, and "release"...
				$line == $this->_keyname . ' ' . $sinceversion ||
				(
					stripos($line, $this->_name . ' ' . $sinceversion) !== false && stripos($line, 'release') !== false
				) ||
				// OR, if it contains "Update [name] to [version]
				(
					strpos($line, 'Update') === 0 &&
					stripos($line, $this->_name) !== false &&
					stripos($line, $sinceversion) !== false
				) ||
				// OR support "Update Theme/(anything)", as there seems to be a minor issue where Themes don't record their name.
				(
					strpos($line, 'Update Theme/') === 0 &&
					$this->_type == 'theme' &&
					stripos($line, $sinceversion) !== false
				)
			){
				continue;
			}
			
			// Otherwise, it's a change!
			$changes[] = $line;
		}

		return $changes;
	}

	/**
	 * Get the release date and version of the last released version on this component.
	 *
	 * If this current version has been released, it'll be that date.
	 * Otherwise, it will be the previous version that has been released.
	 *
	 * @return array
	 */
	public function getLatestReleaseInfo(){
		$label = $this->getLabel();

		if($this->isVersionReleased()){
			// Checking version against HEAD, easy enough!
			$thischange   = $this->getChangelogSection();
			$thisdate     = $thischange->getReleasedDate();
			$versioncheck = $this->getVersion();
			$type         = 'current';
		}
		else{
			$thischange   = $this->getChangelogSection();
			$thisdate     = $thischange->getReleasedDate();
			$versioncheck = $this->getVersion();
			$type         = 'previous';

			if(!$thisdate) {
				$previouschange = $this->getChangelog()->getPreviousSection($versioncheck);
				if($previouschange){
					$thisdate       = $previouschange->getReleasedDate();
					$versioncheck   = $previouschange->getVersion();
				}
				else{
					$thisdate     = '01 Jan 2013 00:00:00 -0400';
					$versioncheck = '0.0.0';
					$type         = 'none';
				}
			}
		}

		return [
			'label'   => $label,
			'date'    => $thisdate,
			'version' => $versioncheck,
			'type'    => $type,
		];
	}

	/**
	 * Save this package along with all changes back down to the disk and database.
	 *
	 */
	public function save(){
		$object     = $this->_getObject();
		$viewdir    = $object->getViewSearchDir();
		$assetdir   = $object->getAssetDir();
		$skindir    = ($this->_type == 'theme') ? $object->getSkinDir() : null;
		$basestrlen = strlen($this->_base->getPath());
		$assetfiles = [];
		$viewfiles  = [];
		$otherfiles = [];
		$skinfiles  = []; // Only themes have skins, but this is the new unified save handler, so it needs to be present.

		// Save out the CHANGELOG before anything happens, that way its updated MD5 will be present in the metafile.
		$this->_changelog->save();

		foreach($this->_iterator as $file){
			/** @var FileLocal|DirectoryLocal $file */

			// If this ends with a "~", skip it!
			// These are backup files used by gedit.
			if(substr($file->getFilename(), -1) == '~'){
				continue;
			}

			// And then, scan this file for code, ie: classes, controllers, etc.
			$fname = substr($file->getFilename(), $basestrlen);
			/*
						// Skip the component.xml file itself
						if($fname == 'component.xml'){
							continue;
						}
						// Applies to themes too
						if($fname == 'theme.xml'){
							continue;
						}*/

			if($viewdir && $file->inDirectory($viewdir)){
				// It's a template! (view)
				$viewfiles[] = ['file' => $fname, 'md5' => $file->getHash()];
			}
			elseif($assetdir && $file->inDirectory($assetdir) && $file->getExtension() == 'scss'){
				// SASS/SCSS Files are NOT assets, but other files.
				$otherfiles[] = ['file' => $fname, 'md5' => $file->getHash()];
			}
			elseif($assetdir && $file->inDirectory($assetdir)){
				// It's an asset!
				$assetfiles[] = ['file' => $fname, 'md5' => $file->getHash()];
			}
			elseif($skindir && $file->inDirectory($skindir)){
				// It's a skin!
				$skinfiles[] = ['file' => $fname, 'md5' => $file->getHash()];
			}
			else{
				// It's a something..... it goes in the "files" array!
				// This will be slightly different though, as it needs to also check for classes.
				$filedat = [
					'file'        => $fname,
					'md5'         => $file->getHash(),
					'controllers' => [],
					'classes'     => [],
					'interfaces'  => [],
					'traits'      => [],
				];

				// PHP files get checked.
				if(preg_match('/\.(php|inc)$/i', $fname)){
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
					preg_match_all('/^\s*(abstract |final ){0,1}class [ ]*([a-z0-9_\-]*)[ ]*extends[ ]*controller_2_1/im', $fconts, $ret);
					foreach($ret[2] as $foundclass){
						$filedat['controllers'][] = $namespace . $foundclass;
					}

					// Add any class found in this file. (skipping the ones I already found)
					preg_match_all('/^\s*(abstract |final ){0,1}class [ ]*([a-z0-9_\-]*)/im', $fconts, $ret);
					foreach($ret[2] as $foundclass){
						if(in_array($foundclass, $filedat['controllers'])) continue;
						$filedat['classes'][] = $namespace . $foundclass;
					}

					// Allow interfaces to be associated as a provided element too.
					preg_match_all('/^\s*(interface)[ ]*([a-z0-9_\-]*)/im', $fconts, $ret);
					foreach($ret[2] as $foundclass){
						$filedat['interfaces'][] = $namespace . $foundclass;
					}

					// Allow traits to be associated as a provided element too.
					preg_match_all('/^\s*(trait)[ ]*([a-z0-9_\-]*)/im', $fconts, $ret);
					foreach($ret[2] as $foundclass){
						$filedat['traits'][] = $namespace . $foundclass;
					}
				}

				// Empty classes?
				if(!sizeof($filedat['controllers'])) unset($filedat['controllers']);
				if(!sizeof($filedat['classes'])) unset($filedat['classes']);
				if(!sizeof($filedat['interfaces'])) unset($filedat['interfaces']);
				if(!sizeof($filedat['traits'])) unset($filedat['traits']);

				$otherfiles[] = $filedat;
			}
		}



		$object->setAuthors($this->_authors);
		$object->setLicenses($this->_licenses);
		$object->setFiles($otherfiles);
		$object->setViewFiles($viewfiles);
		$object->setAssetFiles($assetfiles);
		if($this->_type == 'theme'){
			$object->setSkinFiles($skinfiles);
		}


		// If this is not Core, set the required version to the current Core version.
		if($this->_type == 'component'){
			$vers = \Core::GetComponent('core')->getVersion();
			// Split it up into pieces
			$versParts = new \Core\VersionString($vers);
			$object->setRequires('core', 'component', $versParts->major . '.' . $versParts->minor . '.0', 'ge');
		}

		$object->save();

		// Update the version installed in the database, since the codebase *was* afterall running with whatever files
		// were currently available, (and thus were set as the next version).
		if($this->_type == 'core' || $this->_type == 'component') {
			$name = $this->_name;
		}
		else {
			$name = 'Theme/' . $this->_name;
		}
		$cmodel = \ComponentModel::Construct($name);
		$cmodel->set('version', $this->_version);
		$cmodel->save();


		// Reload the data from disk,
		// this is to ensure that subsequent operations on the component are synced up with the disk's data and not just running from memory.
		switch($this->_type){
			case 'theme':
				$this->_setupTheme();
				break;
			case 'component':
				$this->_setupComponent();
				break;
			case 'core':
				$this->_setupCore();
				break;
		}

		// Don't forget to re-parse the CHANGELOG, otherwise it's lost :/
		if(!$this->_changelog->exists()){
			$this->_changelog->createInitial($this->_version);
		}
		else{
			$this->_changelog->parse();
		}
	}

	/**
	 * Create a valid Core package for this source component or theme, optionally signed
	 *
	 * @param string $packager_name  Name of packager for this package
	 * @param string $packager_email Email of packager for this package
	 * @param bool   $signed         True/False if this will be a GPG signed package
	 *
	 * @return string The full filename of the packaged component/theme
	 *
	 * @throws \Exception
	 */
	public function package($packager_name, $packager_email, $signed){
		// Update the changelog version first!
		// This is done here to signify that the version has actually been bundled up.
		if(!$this->getChangelogSection()->getReleasedDate()){
			$this->getChangelogSection()->markReleased($packager_name, $packager_email);
		}

		// The packager needs to be resaved since the CHANGELOG file changed.
		$this->save();

		// Create a temp directory to contain all these
		$dir = TMP_DIR . 'packager-' . $this->_keyname . '/';

		// The destination depends on the type.
		switch($this->_type){
			case 'core':
				$tgz = ROOT_PDIR . '../exports/core/' . $this->_keyname . '-' . $this->_version . '.tgz';
				break;
			case 'component':
				$tgz = ROOT_PDIR . '../exports/components/' . $this->_keyname . '-' . $this->_version . '.tgz';
				break;
			case 'theme':
				$tgz = ROOT_PDIR . '../exports/themes/' . $this->_keyname . '-' . $this->_version . '.tgz';
				break;
		}

		if(!is_dir(dirname($tgz)))  mkdir(dirname($tgz), 0777, true);
		if(!is_dir($dir))           mkdir($dir, 0777, true);
		if(!is_dir($dir . 'data/')) mkdir($dir . 'data/', 0777, true);

		$basepath   = $this->_base->getPath();
		$basestrlen = strlen($basepath);
		foreach($this->_iterator as $file){
			/** @var FileLocal $file */
			$fname = substr($file->getFilename(), $basestrlen);
			$file->copyTo($dir . 'data/' . $fname);
		}

		// The core will have some additional files required to be created.
		if($this->_type == 'core'){

			// I need the compiled version of the bootstrap.
			copy(ROOT_PDIR . 'core/bootstrap.compiled.php', $dir . 'data/core/bootstrap.compiled.php');

			// The deny hosts text will go into the various secure directories.

			$corecreates = [
				'components', 'gnupg', 'themes'
			];
			$coresecures = [
				'components', 'config', 'gnupg', 'core'
			];
			foreach($corecreates as $createdir){
				mkdir($dir . 'data/' . $createdir);
			}
			foreach($coresecures as $securedir){
				file_put_contents($dir . 'data/' . $securedir . '/.htaccess', self::$Denytext);
			}
		}

		// Because the destination is relative...
		if(strpos($this->_xmlFile, $basepath) === 0){
			$xmldest = 'data/' . substr($this->_xmlFile, $basestrlen);
		}
		else{
			$xmldest = 'data/' . $this->_xmlFile;
		}

		$xmloutput = \Core\Filestore\Factory::File($dir . $xmldest);
		$xmloutput->putContents($this->_xmlLoader->asMinifiedXML());

		//$packager = 'Core Plus ' . ComponentHandler::GetComponent('core')->getVersion() . ' (http://corepl.us)';
		//$packagename = $c->getName();

		// Different component types require a different bundle type.
		//$bundletype = ($component == 'core')? 'core' : 'component';

		// Save the package.xml file.
		$this->_getObject()->savePackageXML(true, $dir . 'package.xml');

		exec('tar -czf "' . $tgz . '" -C "' . $dir . '" --exclude-vcs --exclude=*~ --exclude=._* .');
		$bundle = $tgz;

		if($signed) {
			// If the file already exists, remove it beforehand.
			// This is required for re-building packages of the same version.
			if(file_exists($tgz . '.asc')){
				unlink($tgz . '.asc');
			}
			//exec('gpg --homedir "' . GPG_HOMEDIR . '" --no-permission-warning -u "' . $packager_email . '" -a --sign "' . $tgz . '"');
			// Use the user's current home directory instead of GPG_HOMEDIR!
			// This is because the private key is required for signing of packages.
			exec('gpg --homedir ' . escapeshellarg($_SERVER['HOME'] . '/.gnupg') . ' --no-permission-warning -u "' . $packager_email . '" -a --sign "' . $tgz . '"');
			$bundle .= '.asc';
		}

		// And remove the tmp directory.
		exec('rm -fr "' . $dir . '"');

		return $bundle;
	}

	/**
	 * Perform a GIT commit on all changed resources in this package.
	 */
	public function gitCommit(){
		// First, see if any new files need to be added to GIT in the directories.
		exec('git status -u -s "' . implode('" "', $this->_gitPaths) . '" | egrep "^\?\?" | sed "s:?? ::"', $newfiles);
		foreach($newfiles as $nf){
			exec('git add "' . $nf . '"');
		}

		exec('git status -u -s "' . implode('" "', $this->_gitPaths) . '" | egrep "(^A|^ M)" | sed "s:[A M]*::"', $modifiedfiles);
		foreach($modifiedfiles as $nf){
			exec('git add "' . $nf . '"');
		}

		exec('git status -u -s "' . implode('" "', $this->_gitPaths) . '" | egrep "(^ D)" | sed "s:[A M]*::"', $deletedfiles);
		foreach($deletedfiles as $nf){
			exec('git del "' . $nf . '"');
		}

		// Does git automatically create tags for anything "Release [name] [version]???"
		switch($this->_type){
			case 'core':
				$label = 'Core Plus';
				break;
			case 'component':
				$label = $this->_name;
				break;
			case 'theme':
				$label = 'Theme/' . $this->_name;
				break;
		}

		exec('git commit -s -m "Update ' . $label . ' to ' . $this->_version . '" "' . implode('" "', $this->_gitPaths) . '"');
	}

	/**
	 * Get the Core object for this package, be it a Component or Theme.
	 *
	 * @return \Component_2_1|\Theme\Theme
	 */
	private function _getObject(){
		switch($this->_type){
			case 'core':
				return \Core::GetComponent('core');
			case 'component':
				return \Core::GetComponent($this->_keyname);
			case 'theme':
				return \ThemeHandler::GetTheme($this->_keyname);
		}
	}

	private function _setupComponent(){
		$this->_xmlFile = \ComponentFactory::ResolveNameToFile($this->_keyname);
		if(!$this->_xmlFile){
			throw new \Exception('XML file for requested component not found. [' . $this->_keyname . ']');
		}

		// Resolve it to the full path
		$this->_xmlFile = ROOT_PDIR . $this->_xmlFile;

		$this->_base = new DirectoryLocal(dirname($this->_xmlFile));
		$this->_iterator = new DirectoryIterator($this->_base);
		$baseDir = $this->_base->getPath();

		// Get the XMLLoader object for this file.  This will allow me to have more fine-tune control over the file.
		$this->_xmlLoader = new \XMLLoader();
		$this->_xmlLoader->setRootName('component');
		if(!$this->_xmlLoader->loadFromFile($this->_xmlFile)){
			throw new \Exception('Unable to load XML file ' . $this->_xmlFile);
		}


		$this->_iterator->findDirectories = false;
		$this->_iterator->recursive = true;
		$this->_iterator->ignores = [
			'component.xml',
			'dev/',
			'tests/',
		];

		// @todo Should I support ignored files in the component.xml file?
		// advantage, developers can have tools in their directories that are not meant to be packaged.
		// disadvantage, currently no component other than core requires this.....
		/*$list = $this->getElements('/ignorefiles/file');
		foreach($list as $el){
			$it->addIgnores($this->getBaseDir() . $el->getAttribute('filename'));
		}*/

		// Not a 2.1 component version?... well it needs to be!
		// This actually needs to work with a variety of versions.
		$componentapiversion = $this->_xmlLoader->getDOM()->doctype->systemId;
		switch($componentapiversion){
			case 'http://corepl.us/api/2_1/component.dtd':
			case 'http://corepl.us/api/2_4/component.dtd':
				// Now I can load the component itself, now that I know that the metafile is a 2.1 compatible version.
				$comp = \Core::GetComponent($this->_keyname);

				// Because the editor may request a component by key name, translate that to the pretty name.
				$this->_name = $comp->getName();

				//$comp = new \Component_2_1($this->_xmlFile);
				//$comp->load();
				break;
			default:
				throw new \Exception('Unsupported component version, please ensure that your doctype systemid is correct.');
		}

		$this->_licenses = [];
		foreach($this->_xmlLoader->getElements('//component/licenses/license') as $el){
			/** @var \DOMElement $el */
			$url = @$el->getAttribute('url');
			$this->_licenses[] = [
				'title' => $el->nodeValue,
				'url' => $url
			];
		}

		$this->_authors = [];
		foreach($this->_xmlLoader->getElements('//component/authors/author') as $el){
			$this->_authors[] = [
				'name' => $el->getAttribute('name'),
				'email' => @$el->getAttribute('email'),
			];
		}

		$this->_changelog = new Changelog\Parser($this->_name, $baseDir . 'CHANGELOG');

		$this->_gitPaths = [
			$baseDir,
		];
	}

	private function _setupCore(){
		$this->_xmlFile = \ComponentFactory::ResolveNameToFile($this->_keyname);
		if(!$this->_xmlFile){
			throw new \Exception('XML file for Core not found??? [' . $this->_keyname . ']');
		}

		$this->_base = new DirectoryLocal(ROOT_PDIR);
		$this->_iterator = new DirectoryIterator($this->_base);

		// Get the XMLLoader object for this file.  This will allow me to have more fine-tune control over the file.
		$this->_xmlLoader = new \XMLLoader();
		$this->_xmlLoader->setRootName('component');
		if(!$this->_xmlLoader->loadFromFile($this->_xmlFile)){
			throw new \Exception('Unable to load XML file ' . $this->_xmlFile);
		}

		$this->_iterator->findDirectories = false;
		$this->_iterator->recursive = true;
		$this->_iterator->ignores = [
			'core/component.xml',
			'components/',
			'config/configuration.xml',
			'dropins/',
			'exports/',
			'nbproject/',
			'.idea/',
			'themes/',
			'.htaccess',
			'gnupg',
			'core/bootstrap.compiled.php',
			'logs/',
			'core/dev/',
			'core/tests/',
		];

		if(CDN_LOCAL_ASSETDIR)   $this->_iterator->ignores[] = CDN_LOCAL_ASSETDIR;
		if(CDN_LOCAL_PUBLICDIR)  $this->_iterator->ignores[] = CDN_LOCAL_PUBLICDIR;
		if(CDN_LOCAL_PRIVATEDIR) $this->_iterator->ignores[] = CDN_LOCAL_PRIVATEDIR;
		if(strpos(TMP_DIR_WEB, ROOT_PDIR) === 0) $this->_iterator->ignores[] = TMP_DIR_WEB;
		if(strpos(TMP_DIR_CLI, ROOT_PDIR) === 0) $this->_iterator->ignores[] = TMP_DIR_CLI;

		$this->_name = 'Core';

		$this->_licenses = [];
		foreach($this->_xmlLoader->getElements('//component/licenses/license') as $el){
			/** @var \DOMElement $el */
			$url = @$el->getAttribute('url');
			$this->_licenses[] = [
				'title' => $el->nodeValue,
				'url' => $url
			];
		}

		$this->_authors = [];
		foreach($this->_xmlLoader->getElements('//component/authors/author') as $el){
			$this->_authors[] = [
				'name' => $el->getAttribute('name'),
				'email' => @$el->getAttribute('email'),
			];
		}

		$this->_changelog = new Changelog\Parser('Core Plus', ROOT_PDIR . 'core/CHANGELOG');

		$this->_gitPaths = [
			ROOT_PDIR . 'config/',
			ROOT_PDIR . 'core/',
			ROOT_PDIR . 'install/',
			ROOT_PDIR . 'utilities/',
			ROOT_PDIR . 'index.php'
		];
	}

	private function _setupTheme(){
		$this->_xmlFile = ROOT_PDIR . 'themes/' . $this->_keyname . '/theme.xml';

		if(!file_exists($this->_xmlFile)){
			throw new \Exception('XML file for requested theme not found. [' . $this->_keyname . ']');
		}

		$this->_base = new DirectoryLocal(dirname($this->_xmlFile));
		$this->_iterator = new DirectoryIterator($this->_base);
		$baseDir = $this->_base->getPath();


		// Get the XMLLoader object for this file.  This will allow me to have more fine-tune control over the file.
		$this->_xmlLoader = new \XMLLoader();
		$this->_xmlLoader->setRootName('theme');
		if(!$this->_xmlLoader->loadFromFile($this->_xmlFile)){
			throw new \Exception('Unable to load XML file ' . $this->_xmlFile);
		}

		$this->_iterator->findDirectories = false;
		$this->_iterator->recursive = true;
		$this->_iterator->ignores = [
			'theme.xml',
		];

		$this->_name = $this->_xmlLoader->getElement('//theme')->getAttribute('name');

		$this->_licenses = [];
		foreach($this->_xmlLoader->getElements('//theme/licenses/license') as $el){
			/** @var \DOMElement $el */
			$url = @$el->getAttribute('url');
			$this->_licenses[] = [
				'title' => $el->nodeValue,
				'url' => $url
			];
		}

		$this->_authors = [];
		foreach($this->_xmlLoader->getElements('//theme/authors/author') as $el){
			$this->_authors[] = [
				'name' => $el->getAttribute('name'),
				'email' => @$el->getAttribute('email'),
			];
		}

		$this->_changelog = new Changelog\Parser('Theme/' . $this->_name, $baseDir . 'CHANGELOG');

		$this->_gitPaths = [
			$baseDir,
		];
	}


	/**
	 * Slightly more advanced function to parse for specific information from file headers.
	 *
	 * Will return an array containing any author, license
	 *
	 * @param string $file
	 * @return array
	 */
	public static function ParseForDocumentation($file) {
		$ret = [
			'authors' => [],
			'licenses' => []
		];

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
								$m = \Core\Licenses\Factory::GetLicense($lic);
								if($m){
									$ret['licenses'][ $m['key'] ] =	$m;
								}
								else{
									$ret['licenses'][] = [
										'url' => $lic,
										'title' => null,
									];
								}
							}
							elseif(strpos($lic, '://') === false){
								// lic is similar to @license GNU Affero General Public License v3
								$m = \Core\Licenses\Factory::GetLicense($lic);
								if($m){
									$ret['licenses'][ $m['key'] ] =	$m;
								}
								else{
									$ret['licenses'][] = [
										'url' => null,
										'title' => $lic,
									];
								}
							}
							elseif(strpos($lic, '<') !== false && strpos($lic, '>') !== false){
								// lic is similar to @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
								// String has both.
								$title = preg_replace('/[ ]*<[^>]*>/', '', $lic);
								$url = preg_replace('/.*<([^>]*)>.*/', '$1', $lic);
								if(($m = \Core\Licenses\Factory::GetLicense($url))){
									$ret['licenses'][ $m['key'] ] = $m;
								}
								elseif(($m = \Core\Licenses\Factory::GetLicense($title))){
									$ret['licenses'][ $m['key'] ] = $m;
								}
								else{
									$ret['licenses'][] = [
										'url' => $url,
										'title' => $title,
									];
								}
							}
						}
						// Is this an @author line?
						if(stripos($line, '@author') !== false){
							$aut = preg_replace('/\*[ ]*@author[ ]*/i', '', $line);
							if(strpos($aut, '<') !== false && strpos($aut, '>') !== false && preg_match('/<[^>]*(@| at )[^>]*>/i', $aut)){
								// Resembles: @author user foo <email@domain.com>
								// or         @author user foo <email at domain dot com>
								preg_match('/(.*) <([^>]*)>/', $aut, $matches);
								$autdata = ['name' => $matches[1], 'email' => $matches[2]];
							}
							elseif(strpos($aut, '(') !== false && strpos($aut, ')') !== false && preg_match('/\([^\)]*(@| at )[^\)]*\)/i', $aut)){
								// Resembles: @author user foo (email@domain.com)
								// of         @author user foo (email at domain dot com)
								preg_match('/(.*) \(([^\)]*)\)/', $aut, $matches);
								$autdata = ['name' => $matches[1], 'email' => $matches[2]];
							}
							else{
								// Eh, must be something else...
								$autdata = ['name' => $aut, 'email' => null];
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
							if(strpos($aut, '<') !== false && strpos($aut, '>') !== false && preg_match('/<[^>]*(@| at )[^>]*>/i', $aut)){
								// Resembles: @author user foo <email@domain.com>
								// or         @author user foo <email at domain dot com>
								preg_match('/(.*) <([^>]*)>/', $aut, $matches);
								$autdata = ['name' => $matches[1], 'email' => $matches[2]];
							}
							elseif(strpos($aut, '(') !== false && strpos($aut, ')') !== false && preg_match('/\([^\)]*(@| at )[^\)]*\)/i', $aut)){
								// Resembles: @author user foo (email@domain.com)
								// of         @author user foo (email at domain dot com)
								preg_match('/(.*) \(([^\)]*)\)/', $aut, $matches);
								$autdata = ['name' => $matches[1], 'email' => $matches[2]];
							}
							else{
								// Eh, must be something else...
								$autdata = ['name' => $aut, 'email' => null];
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
		//$ret['licenses'] = arrayUnique($ret['licenses']);
		//$ret['authors'] = arrayUnique($ret['authors']);
		return $ret;
	}

	/**
	 * Try to intelligently merge duplicate authors, matching a variety of input names.
	 *
	 * @param array <<string>> $authors
	 * @return array
	 */
	public static function GetUniqueAuthors($authors){
		// This clusterfuck of a section will basicaly match up the name to its email,
		// use the email as a unique key for name grouping,
		// then try to figure out the canonical name of the author.
		$ea = [];
		foreach($authors as $a){
			// Remove any whitespace.
			$a['email'] = trim($a['email']);
			$a['name'] = trim($a['name']);

			// Group the names under the emails attached.
			if(!isset($ea[$a['email']])) $ea[$a['email']] = [$a['name']];
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


		$authors = [];
		// Now handle every email.
		foreach($ea as $e => $na){
			$na = array_unique($na);
			if($e == ''){
				foreach($na as $name) $authors[] = ['name' => $name];
				continue;
			}


			// Match differences such as Tomas V.V.Cox and Tomas V. V. Cox
			$simsearch = [];
			foreach($na as $k => $name){
				$key = preg_replace('/[^a-z]/i', '', $name);
				if(in_array($key, $simsearch)) unset($na[$k]);
				else $simsearch[] = $key;
			}

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
							$authors[] = ['email' => $e, 'name' => $name];
					}
				}
				$authors[] = ['email' => $e, 'name' => $funame];
			}
			else{
				foreach($na as $name){
					$authors[] = ['email' => $e, 'name' => $name];
				}
			}
		}

		return $authors;
	}
} 