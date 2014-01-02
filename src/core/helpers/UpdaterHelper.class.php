<?php
/**
 * [PAGE DESCRIPTION HERE]
 *
 * @package Core Plus\Core
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


class UpdaterHelper {
	
	/**
	 * Perform a lookup on any repository sites installed and get a list of provided pacakges.
	 * 
	 * @return array
	 */
	public static function GetUpdates(){
		// Allow this to be cached for x amount of time.  This will save the number of remote requests.
		//if(false && isset($_SESSION['updaterhelper_getupdates']) && $_SESSION['updaterhelper_getupdates']['expire'] <= time()){
		//	return $_SESSION['updaterhelper_getupdates']['data'];
		//}
		
		// Build a list of components currently installed, this will act as a base.
		$components = array();
		$core       = array();
		$themes     = array();
		$sitecount  = 0;
		$pkgcount   = 0;
		$current    = Core::GetComponents();
		$froze      = \ConfigHandler::Get('/core/updater/versionfreeze');

		// If Core isn't installed yet, GetComponents will yield null.
		if($current === null) $current = array();

		foreach($current as $c){
			/** @var $c Component_2_1 */
			$n = $c->getKeyName();

			$parts = Core::VersionSplit($c->getVersion());

			if($n == 'core'){
				$core = array(
					'name' => $n,
					'title' => $c->getName(),
					'version' => $c->getVersion(),
					'feature' => $parts['major'] . '.' . $parts['minor'],
					'source' => 'installed',
					'description' => $c->getDescription(),
					'provides' => $c->getProvides(),
					'requires' => $c->getRequires(),
					'location' => null,
					'status' => 'installed',
					'type' => 'core',
					'typetitle' => 'Core',
					'key' => null,
					'destdir' => $c->getBaseDir(),
				);
			}
			else{
				$components[$n] = array(
					'name' => $n,
					'title' => $c->getName(),
					'version' => $c->getVersion(),
					'feature' => $parts['major'] . '.' . $parts['minor'],
					'source' => 'installed',
					'description' => $c->getDescription(),
					'provides' => $c->getProvides(),
					'requires' => $c->getRequires(),
					'location' => null,
					'status' => 'installed',
					'type' => 'components',
					'typetitle' => 'Component ' . $c->getName(),
					'key' => null,
					'destdir' => $c->getBaseDir(),
				);
			}
		}

		foreach(Core::GetDisabledComponents() as $c){
			/** @var $c Component_2_1 */
			$n = $c->getKeyName();

			$parts = Core::VersionSplit($c->getVersion());

			$components[$n] = array(
				'name' => $n,
				'title' => $c->getName(),
				'version' => $c->getVersion(),
				'feature' => $parts['major'] . '.' . $parts['minor'],
				'source' => 'installed',
				'description' => $c->getDescription(),
				'provides' => $c->getProvides(),
				'requires' => $c->getRequires(),
				'location' => null,
				'status' => 'disabled',
				'type' => 'components',
				'typetitle' => 'Component ' . $c->getName(),
				'key' => null,
				'destdir' => $c->getBaseDir(),
			);
		}

		// And repeat for the themes.
		// I need to do a check if they exist because if called from the installer, it may not.
		if(class_exists('ThemeHandler')){
			$currentthemes = ThemeHandler::GetAllThemes();
			if($currentthemes === null) $currentthemes = array();
		}
		else{
			$currentthemes = array();
		}

		foreach($currentthemes as $t){
			/** @var $t Theme */
			$n = $t->getKeyName();

			$parts = Core::VersionSplit($t->getVersion());

			$themes[$n] = array(
				'name' => $n,
				'title' => $t->getName(),
				'version' => $t->getVersion(),
				'feature' => $parts['major'] . '.' . $parts['minor'],
				'source' => 'installed',
				'description' => $t->getDescription(),
				'location' => null,
				'status' => 'installed',
				'type' => 'themes',
				'typetitle' => 'Theme ' . $t->getName(),
				'key' => null,
				'destdir' => $t->getBaseDir(),
			);
		}
		
		// Now, look up components from all the updates sites.
		// If the system isn't installed yet, then this will not be found.  Just use a blank array.
		if(class_exists('UpdateSiteModel')){
			$updatesites = UpdateSiteModel::Find();
		}
		else{
			$updatesites = array();
		}


		foreach($updatesites as $site){

			if(!$site->isValid()) continue;

			++$sitecount;
			$file = $site->getFile();

			$repoxml = new RepoXML();
			$repoxml->loadFromFile($file);
			$rootpath = dirname($file->getFilename()) . '/';

			foreach($repoxml->getPackages() as $pkg){
				/** @var $pkg PackageXML */
				// Already installed and is up to date, don't do anything.
				//if($pkg->isCurrent()) continue;

				$n = str_replace(' ', '-', strtolower($pkg->getName()));
				$type = $pkg->getType();
				if($n == 'core') $type = 'core'; // Override the core, even though it is a component...
				++$pkgcount;

				switch($type){
					case 'core':
						$vers = $pkg->getVersion();

						// Only display the newest version available.
						if(!Core::VersionCompare($vers, $core['version'], 'gt')) continue;

						// Only display new feature versions if it's not frozen.
						$parts = Core::VersionSplit($pkg->getVersion());
						if($froze && $core['feature'] != $parts['major'] . '.' . $parts['minor']) continue;

						$core = array(
							'name' => $n,
							'title' => $pkg->getName(),
							'version' => $vers,
							'feature' => $parts['major'] . '.' . $parts['minor'],
							'source' => 'repo-' . $site->get('id'),
							'sourceurl' => $site->get('url'),
							'description' => $pkg->getDescription(),
							'provides' => $pkg->getProvides(),
							'requires' => $pkg->getRequires(),
							'location' => $rootpath . $pkg->getFileLocation(),
							'status' =>'update',
							'type' => 'core',
							'typetitle' => 'Core ',
							'key' => $pkg->getKey(),
							'destdir' => ROOT_PDIR,
						);
						break;
					case 'component':
						$vers  = $pkg->getVersion();
						$parts = Core::VersionSplit($pkg->getVersion());

						// Is it already loaded in the list?
						if(isset($components[$n])){
							// I only want the newest version.
							if(!Core::VersionCompare($vers, $components[$n]['version'], 'gt')) continue;

							// Only display new feature versions if it's not frozen.
							if(
								$froze &&
								$components[$n]['status'] == 'installed' &&
								$components[$n]['feature'] != $parts['major'] . '.' . $parts['minor']
							){
								continue;
							}
						}

						// If it's available in the core, it's an update... otherwise it's new.
						$status = Core::GetComponent($n) ? 'update' : 'new';

						$components[$n] = array(
							'name' => $n,
							'title' => $pkg->getName(),
							'version' => $vers,
							'feature' => $parts['major'] . '.' . $parts['minor'],
							'source' => 'repo-' . $site->get('id'),
							'sourceurl' => $site->get('url'),
							'description' => $pkg->getDescription(),
							'provides' => $pkg->getProvides(),
							'requires' => $pkg->getRequires(),
							'location' => $rootpath . $pkg->getFileLocation(),
							'status' => $status,
							'type' => 'components',
							'typetitle' => 'Component ' . $pkg->getName(),
							'key' => $pkg->getKey(),
							'destdir' => ROOT_PDIR . 'components/' . $n . '/',
						);
						break;
					case 'theme':
						$vers = $pkg->getVersion();
						$parts = Core::VersionSplit($pkg->getVersion());

						// Is it already loaded in the list?
						if(isset($themes[$n])){
							// I only want the newest version.
							if(!Core::VersionCompare($vers, $themes[$n]['version'], 'gt')) continue;

							// Only display new feature versions if it's not frozen.
							if(
								$froze &&
								$themes[$n]['status'] == 'installed' &&
								$themes[$n]['feature'] != $parts['major'] . '.' . $parts['minor']
							){
								continue;
							}
						}

						$status = ThemeHandler::GetTheme($n) ? 'update' : 'new';

						$themes[$n] = array(
							'name' => $n,
							'title' => $pkg->getName(),
							'version' => $vers,
							'feature' => $parts['major'] . '.' . $parts['minor'],
							'source' => 'repo-' . $site->get('id'),
							'sourceurl' => $site->get('url'),
							'description' => $pkg->getDescription(),
							'location' => $rootpath . $pkg->getFileLocation(),
							'status' => $status,
							'type' => 'themes',
							'typetitle' => 'Theme ' . $pkg->getName(),
							'key' => $pkg->getKey(),
							'destdir' => ROOT_PDIR . 'themes/' . $n . '/',
						);
				}
				
				//var_dump($pkg->asPrettyXML()); die();
			}
		}
		
		// Give me the components in alphabetical order.
		ksort($components);
		ksort($themes);

		// Cache this for next pass.
		//$_SESSION['updaterhelper_getupdates'] = array();
		//$_SESSION['updaterhelper_getupdates']['data'] = array('core' => $core, 'components' => $components, 'themes' => $themes);
		//$_SESSION['updaterhelper_getupdates']['expire'] = time() + 60;
		
		return [
			'core'       => $core,
			'components' => $components,
			'themes'     => $themes,
			'sitecount'  => $sitecount,
			'pkgcount'   => $pkgcount,
		];
	}
	
	public static function InstallComponent($name, $version, $dryrun = false, $verbose = false){
		return self::PerformInstall('components', $name, $version, $dryrun, $verbose);
	}

	public static function InstallTheme($name, $version, $dryrun = false, $verbose = false){
		return self::PerformInstall('themes', $name, $version, $dryrun, $verbose);
	}

	public static function InstallCore($version, $dryrun = false, $verbose = false){
		return self::PerformInstall('core', 'core', $version, $dryrun, $verbose);
	}

	public static function PerformInstall($type, $name, $version, $dryrun = false, $verbose = false){

		if($verbose){
			// These are needed to force the output to be sent immediately.
			while ( @ob_end_flush() ); // even if there is no nested output buffer
			if(function_exists('apache_setenv')){
				// This function doesn't exist in CGI mode :/
				apache_setenv('no-gzip', '1');
			}
			ini_set('output_buffering','on');
			ini_set('zlib.output_compression', 0);
			ob_implicit_flush();

			// Give some basic styles for this output.
			echo '<html>
	<head>
	<!-- Yes, the following is 1024 spaces.  This is because some browsers have a 1Kb buffer before they start rendering text -->
	' . str_repeat(" ", 1024) . '
		<style>
			body {
				background: none repeat scroll 0 0 black;
				color: #22EE33;
				font-family: monospace;
			}
		</style>
	</head>
	<body>';
		}

		$timer = microtime(true);
		// Give this script a few more seconds to run.
		set_time_limit(max(90, ini_get('max_execution_time')));

		// This will get a list of all available updates and their sources :)
		if($verbose) self::_PrintHeader('Retrieving Updates');
		$updates = UpdaterHelper::GetUpdates();
		if($verbose){
			self::_PrintInfo('Found ' . $updates['sitecount'] . ' repository site(s)!', $timer);
			self::_PrintInfo('Found ' . $updates['pkgcount'] . ' packages!', $timer);
		}

		// A list of changes that are to be applied, (mainly for the dry run).
		$changes = array();

		// Target in on the specific object we're installing.  Useful for a shortcut.
		switch($type){
			case 'core':
				$initialtarget = &$updates['core'];
				break;
			case 'components':
				$initialtarget = &$updates['components'][$name];
				break;
			case 'themes':
				$initialtarget = &$updates['themes'][$name];
				break;
			default:
				return [
					'status' => 0,
					'message' => '[' . $type . '] is not a valid installation type!',
				];
		}

		// This is a special case for testing the installer UI.
		$test = ($type == 'core' && $version == '99.1337~(test)');

		if($test && $verbose){
			self::_PrintHeader('Performing a test installation!');
		}

		if($test){
			if($verbose){
				self::_PrintInfo('Sleeping for a few seconds... because servers are always slow when you don\'t want them to be!', $timer);
			}
			sleep(4);

			// Also overwrite some of the target's information.
			$repo = UpdateSiteModel::Find(null, 1);
			$initialtarget['source'] = 'repo-' . $repo->get('id');
			$initialtarget['location'] = 'http://corepl.us/api/2_4/tests/updater-test.tgz.asc';
			$initialtarget['destdir'] = ROOT_PDIR;
			$initialtarget['key'] = 'B2BEDCCB';
			$initialtarget['status'] = 'update';

			//if($verbose){
			//	echo '[DEBUG]' . $nl;
			//	var_dump($initialtarget);
			//}
		}

		// Make sure the name and version exist in the updates list.
		// In theory, the latest version of core is the only one displayed.
		if(!$test && $initialtarget['version'] != $version){
			return [
				'status' => 0,
				'message' => $initialtarget['typetitle'] . ' does not have the requested version available.',
				'debug' => [
					'versionrequested' => $version,
					'versionfound' => $initialtarget['version'],
				],
			];
		}

		// A queue of components to check.
		$pendingqueue = array($initialtarget);
		// A queue of components that will be installed that have satisfied dependencies.
		$checkedqueue = array();

		// This will assemble the list of required installs in the correct order.
		// If a given dependency can't be met, the installation will be aborted.
		if($verbose){
			self::_PrintHeader('CHECKING DEPENDENCIES');
		}
		do{
			$lastsizeofqueue = sizeof($pendingqueue);

			foreach($pendingqueue as $k => $c){
				$good = true;

				if(isset($c['requires'])){
					if($verbose){
						self::_PrintInfo('Checking dependencies for ' . $c['typetitle'], $timer);
					}

					foreach($c['requires'] as $r){

						// Sometimes there will be blank requirements in the metafile.
						if(!$r['name']) continue;

						$result = UpdaterHelper::CheckRequirement($r, $checkedqueue, $updates);

						if($result === false){
							// Dependency not met
							return [
								'status' => 0,
								'message' => $c['typetitle'] . ' requires ' . $r['name'] . ' ' . $r['version']
							];
						}
						elseif($result === true){
							// Dependency met via either installed components or new components
							// yay
							if($verbose){
								self::_PrintInfo('Dependency [' . $r['name'] . ' ' . $r['version'] . '] met with already-installed packages.', $timer);
							}
						}
						else{
							if($verbose){
								self::_PrintInfo('Additional package [' . $result['typetitle'] . '] required to meet dependency [' . $r['name'] . ' ' . $r['version'] . '], adding to queue and retrying!', $timer);
							}
							// It's an array of requirements that are needed to satisfy this installation.
							$pendingqueue = array_merge(array($result), $pendingqueue);
							$good = false;
						}
					}
				}
				else{
					if($verbose){
						self::_PrintInfo('Skipping dependency check for ' . $c['typetitle'] . ', no requirements present', $timer);
					}

					// The require key isn't present... OK!
					// This happens with themes, as they do not have any dependency logic.
				}

				if($good === true){
					$checkedqueue[] = $c;
					$changes[] = (($c['status'] == 'update') ? 'Update' : 'Install') .
						' ' . $c['typetitle'] . ' ' . $c['version'];
					unset($pendingqueue[$k]);
				}
			}
		}
		while(sizeof($pendingqueue) && sizeof($pendingqueue) != $lastsizeofqueue);

		// Do validation checks on all these changes.  I need to make sure I have the GPG key for each one.
		// This is done here to save having to download the files from the remote server first.
		foreach($checkedqueue as $target){
			// It'll be validated prior to installation anyway.
			if(!$target['key']) continue;

			$output = array();
			exec('gpg --homedir "' . GPG_HOMEDIR . '" --list-public-keys "' . $target['key'] . '"', $output, $result);
			if($result > 0){
				// Key validation failed!
				if($verbose){
					echo implode("<br/>\n", $output);
				}
				return [
					'status' => 0,
					'message' => $c['typetitle'] . ' failed GPG verification! Is the key ' . $target['key'] . ' installed?'
				];
			}
		}


		// Check that the queued packages have not been locally modified if installed.
		if($verbose){
			self::_PrintHeader('Checking for local modifications');
		}
		foreach($checkedqueue as $target){
			if($target['status'] == 'update'){
				switch($target['type']){
					case 'core':
						$c = Core::GetComponent('core');
						break;
					case 'components':
						$c = Core::GetComponent($target['name']);
						break;
					case 'themes':
						$c = null;
						break;
				}

				if($c){
					// Are there changes?
					if(sizeof($c->getChangedAssets())){
						foreach($c->getChangedAssets() as $change){
							$changes[] = 'Overwrite locally-modified asset ' . $change;
						}
					}
					if(sizeof($c->getChangedFiles())){
						foreach($c->getChangedFiles() as $change){
							$changes[] = 'Overwrite locally-modified file ' . $change;
						}
					}
					if(sizeof($c->getChangedTemplates())){
						foreach($c->getChangedTemplates() as $change){
							$changes[] = 'Overwrite locally-modified template ' . $change;
						}
					}
				}
			}
		}


		// If dry run is enabled, stop here.
		// After this stage, dragons be let loose from thar cages.
		if($dryrun){
			return [
				'status' => 1,
				'message' => 'All dependencies are met, ok to install',
				'changes' => $changes,
			];
		}


		// Reset changes, in this case it'll be what was installed.
		$changes = array();

		// By now, $checkedqueue will contain all the pending changes, theoretically with
		// the initially requested package at the end of the list.
		foreach($checkedqueue as $target){

			if($verbose){
				self::_PrintHeader('PERFORMING INSTALL (' . strtoupper($target['typetitle']) . ')');
			}

			// This package is already installed and up to date.
			if($target['source'] == 'installed'){
				return [
					'status' => 0,
					'message' => $target['typetitle'] . ' is already installed and at the newest version.',
				];
			}
			// If this package is coming from a repo, install it from that repo.
			elseif(strpos($target['source'], 'repo-') !== false){
				/** @var $repo UpdateSiteModel */
				$repo = new UpdateSiteModel(substr($target['source'], 5));
				if($verbose){
					self::_PrintInfo('Using repository ' . $repo->get('url') . ' for installation source', $timer);
				}

				// Setup the remote file that will be used to download from.
				$file = new \Core\Filestore\Backends\FileRemote($target['location']);
				$file->username = $repo->get('username');
				$file->password = $repo->get('password');

				// The initial HEAD request pulls the metadata for the file, and sees if it exists.
				if($verbose){
					self::_PrintInfo('Performing HEAD lookup on ' . $file->getFilename(), $timer);
				}
				if(!$file->exists()){
					return [
						'status' => 0,
						'message' => $target['location'] . ' does not seem to exist!'
					];
				}
				if($verbose){
					self::_PrintInfo('Found a(n) ' . $file->getMimetype() . ' file that returned a ' . $file->getStatus() . ' status.', $timer);
				}

				// Get file contents will download the file.
				if($verbose){
					self::_PrintInfo('Downloading ' . $file->getFilename(), $timer);
				}
				$downloadtimer = microtime(true);
				$obj = $file->getContentsObject();
				// Getting the object simply sets it up, it doesn't download the contents yet.
				$obj->getContents();
				// Now it has :p
				// How long did it take?
				if($verbose){
					self::_PrintInfo('Downloaded ' . $file->getFilesize(true) . ' in ' . (round(microtime(true) - $downloadtimer, 2) . ' seconds'), $timer);
				}

				if(!($obj instanceof \Core\Filestore\Contents\ContentASC)){
					return [
						'status' => 0,
						'message' => $target['location'] . ' does not appear to be a valid GPG signed archive'
					];
				}

				if(!$obj->verify()){
					// Maybe it can at least get the key....
					if($key = $obj->getKey()){
						return [
							'status' => 0,
							'message' => 'Unable to locate public key for ' . $key . '.  Is it installed?'
						];
					}
					return [
						'status' => 0,
						'message' => 'Invalid GPG signature for ' . $target['typetitle'],
					];
				}

				// The object's key must also match what's in the repo.
				if($obj->getKey() != $target['key']){
					return [
						'status' => 0,
						'message' => '!!!WARNING!!!, Key for ' . $target['typetitle'] . ' is valid, but does not match what was expected form the repository data!  This could be a major risk!',
						'debug' => [
							'detectedkey' => $obj->getKey(),
							'expectedkey' => $target['key'],
						],
					];
				}
				if($verbose){
					self::_PrintInfo('Found key ' . $target['key'] . ' for package maintainer, appears to be valid.', $timer);
					exec('gpg --homedir "' . GPG_HOMEDIR . '" --list-public-keys "' . $target['key'] . '"', $output, $result);
					foreach($output as $line){
						if(trim($line)) self::_PrintInfo(htmlentities($line), $timer);
					}
				}

				if($verbose) self::_PrintInfo('Checking write permissions', $timer);
				$dir = \Core\directory($target['destdir']);
				if(!$dir->isWritable()){
					return [
						'status' => 0,
						'message' => $target['destdir'] . ' is not writable!'
					];
				}
				if($verbose) self::_PrintInfo('OK!', $timer);


				// Decrypt the signed file.
				if($verbose) self::_PrintInfo('Decrypting signed file', $timer);

				/** @var $localfile \Core\Filestore\File */
				$localfile = $obj->decrypt('tmp/updater/');
				/** @var $localobj \Core\Filestore\Contents\ContentTGZ */
				$localobj = $localfile->getContentsObject();
				if($verbose) self::_PrintInfo('OK!', $timer);

				// This tarball will be extracted to a temporary directory, then copied from there.
				if($verbose){
					self::_PrintInfo('Extracting tarball ' . $localfile->getFilename(), $timer);
				}
				$tmpdir = $localobj->extract('tmp/installer-' . Core::RandomHex(4));

				// Now that the data is extracted in a temporary directory, extract every file in the destination.
				/** @var $datadir \Core\Filestore\Directory */
				$datadir = $tmpdir->get('data/');
				if(!$datadir){
					return [
						'status' => 0,
						'message' => 'Invalid package, ' . $target['typetitle'] . ', does not contain a "data" directory.'
					];
				}
				if($verbose) self::_PrintInfo('OK!', $timer);


				if($verbose){
					self::_PrintInfo('Installing files into ' . $target['destdir'], $timer);
				}

				// Will give me an array of Files in the data directory.
				$files = $datadir->ls(null, true);
				// Used to get the relative path for each contained file.
				$datalen = strlen($datadir->getPath());
				foreach($files as $file){
					if(!$file instanceof \Core\Filestore\Backends\FileLocal) continue;

					// It's a file, copy it over.
					// To do so, resolve the directory path inside the temp data dir.
					$dest = \Core\Filestore\Factory::File($target['destdir'] . substr($file->getFilename(), $datalen));
					/** @var $dest \Core\Filestore\Backends\FileLocal */
					if($verbose){
						self::_PrintInfo('...' . substr($dest->getFilename(''), 0, 67), $timer);
					}
					$dest->copyFrom($file, true);
				}
				if($verbose) self::_PrintInfo('OK!', $timer);


				// Cleanup the temp directory
				if($verbose){
					self::_PrintInfo('Cleaning up temporary directory', $timer);
				}
				$tmpdir->remove();
				if($verbose) self::_PrintInfo('OK!', $timer);

				$changes[] = 'Installed ' . $target['typetitle'] . ' ' . $target['version'];
			}
		}

		// Clear the cache so the next pageload will pick up on the new components and goodies.
		Core::Cache()->flush();
		Cache::GetSystemCache()->delete('core-components');

		// Yup, that's it.
		// Just extract the files and Core will autoinstall/autoupgrade everything on the next page view.


		// yay...
		return [
			'status' => 1,
			'message' => 'Performed all operations successfully!',
			'changes' => $changes,
		];
	}


	/**
	 * Simple function to scan through the components provided for one that
	 * satisfies the requirement.
	 *
	 * Returns true if requirement is met with current packages,
	 * Returns false if requirement cannot be met at all.
	 * Returns the component array of an available repository package if that will solve this requirement.
	 *
	 * @param array $requirement   Associative array [type, name, version, operation], of requirement to look for
	 * @param array $newcomponents Associatve array [core, components, themes], of currently installed components
	 * @param array $allavailable  Indexed array of all available components from the repositories
	 *
	 * @return array | false | true
	 */
	public static function CheckRequirement($requirement, $newcomponents = array(), $allavailable = array()){
		$rtype = $requirement['type'];
		$rname = $requirement['name'];
		$rvers = $requirement['version'];
		$rvrop = $requirement['operation'];

		// operation by default is ge.
		if(!$rvrop) $rvrop = 'ge';


		// This will check if the requirement is already met.
		switch($rtype){
			case 'library':
				if(Core::IsLibraryAvailable($rname, $rvers, $rvrop)){
					return true;
				}
				break;
			case 'jslibrary':
				if(Core::IsJSLibraryAvailable($rname, $rvers, $rvrop)){
					return true;
				}
				break;
			case 'component':
				if(Core::IsComponentAvailable($rname, $rvers, $rvrop)){
					return true;
				}
				break;
		}

		// Check the new components too.  Those are already queued up to be installed.
		// New components are squashed a little; all themes/components/core updates are lumped together.
		foreach($newcomponents as $data){
			// And provides is [type => "", name => "", version => ""].
			foreach($data['provides'] as $prov){
				if($prov['type'] == $rtype && $prov['name'] == $rname){
					if(Core::VersionCompare($prov['version'], $rvers, $rvrop)){
						// Yay, it's able to be provided by a package already set to be installed!
						return true;
					}
				}
			}
		}

		// Maybe it's in the set of available updates...
		// First array is [core => ..., components => ..., themes => ...].
		foreach($allavailable as $type => $availableset){
			// Core doesn't count here!
			if($type == 'core') continue;

			// Next inner array will be [componentname => {its data}, ... ].
			foreach($availableset as $data){
				// And provides is [type => "", name => "", version => ""].
				foreach($data['provides'] as $prov){
					if($prov['type'] == $rtype && $prov['name'] == $rname){
						if(Core::VersionCompare($prov['version'], $rvers, $rvrop)){
							// Yay, add this to the queue!
							return $data;
						}
					}
				}
			}
		}
		
		// Requirement not met... ok.  This needs to be conveyed to the calling script.
		return false;
	}

	/**
	 * A static function that can be tapped into the weekly cron hook.
	 *
	 * This ensures that the update cache is never more than a week old.
	 */
	public static function CheckWeekly(){
		self::GetUpdates();
		return true;
	}

	private static function _PrintHeader($header){
		echo
			"<br/>\n" .
			'[=' .
			strtoupper(str_pad(' ' . $header . ' ', 74, '=', STR_PAD_BOTH))
			. '=]'
			. "<br/>\n";
		flush();
	}

	private static function _PrintInfo($line, $timer){
		$t = microtime(true);
		// This will give me the timer at the beginning of the line.
		$out = '[' .
			str_pad(
				number_format(round($t - $timer, 2), 2),
				5,
				0,
				STR_PAD_LEFT
			) .
			'] - ' .
			$line;

		error_log('[DEBUG] - ' . $line, E_USER_NOTICE);

		echo wordwrap($out, 80, "<br/>\n") . "<br/>\n";
		flush();
	}
}
