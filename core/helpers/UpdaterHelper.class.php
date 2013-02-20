<?php
/**
 * [PAGE DESCRIPTION HERE]
 *
 * @package Core Plus\Core
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

		foreach(Core::GetComponents() as $c){
			/** @var $c Component_2_1 */
			$n = strtolower($c->getName());
			if($n == 'core'){
				$core = array(
					'name' => $n,
					'title' => $c->getName(),
					'version' => $c->getVersion(),
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
			$n = strtolower($c->getName());

			$components[$n] = array(
				'name' => $n,
				'title' => $c->getName(),
				'version' => $c->getVersion(),
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

		foreach(ThemeHandler::GetAllThemes() as $t){
			/** @var $t Theme */
			$n = strtolower($t->getName());
			$themes[$n] = array(
				'name' => $n,
				'title' => $t->getName(),
				'version' => $t->getVersion(),
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
		$updatesites = UpdateSiteModel::Find();
		foreach($updatesites as $site){

			if(!$site->isValid()) continue;
			$file = $site->getFile();

			$repoxml = new RepoXML();
			$repoxml->loadFromFile($file);
			$rootpath = dirname($file->getFilename()) . '/';

			foreach($repoxml->getPackages() as $pkg){
				/** @var $pkg PackageXML */
				// Already installed and is up to date, don't do anything.
				//if($pkg->isCurrent()) continue;

				$n = strtolower($pkg->getName());
				$type = $pkg->getType();
				if($n == 'core') $type = 'core'; // Override the core, even though it is a component...

				switch($type){
					case 'core':
						$vers = $pkg->getVersion();

						// Only display the newest version available.
						if(Core::VersionCompare($vers, $core['version'], 'gt')){
							$core = array(
								'name' => $n,
								'title' => $pkg->getName(),
								'version' => $vers,
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
						}
						break;
					case 'component':
						$vers  = $pkg->getVersion();

						// Is it already loaded in the list?
						if(isset($components[$n])){
							// I only want the newest version.
							if(!Core::VersionCompare($vers, $components[$n]['version'], 'gt')) continue;
						}

						// If it's available in the core, it's an update... otherwise it's new.
						$status = Core::GetComponent($n) ? 'update' : 'new';

						$components[$n] = array(
							'name' => $n,
							'title' => $pkg->getName(),
							'version' => $vers,
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

						// Is it already loaded in the list?
						if(isset($themes[$n])){
							// I only want the newest version.
							if(!Core::VersionCompare($vers, $themes[$n]['version'], 'gt')) continue;
						}

						$status = ThemeHandler::GetTheme($n) ? 'update' : 'new';

						$themes[$n] = array(
							'name' => $n,
							'title' => $pkg->getName(),
							'version' => $vers,
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
		
		return array('core' => $core, 'components' => $components, 'themes' => $themes);
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

		// This will get a list of all available updates and their sources :)
		$updates = UpdaterHelper::GetUpdates();

		// Fewer keystrokes ;P
		$nl = "<br/>\n";

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

		if($verbose){
			// These are needed to force the output to be sent immediately.
			while ( @ob_end_flush() ); // even if there is no nested output buffer
			apache_setenv('no-gzip', '1');
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


		if($test && $verbose){
			echo '[INFO] - Performing a test installation!' . $nl;
			flush();
		}

		if($test){
			if($verbose){
				echo '[INFO] - Sleeping for a few seconds... because servers are always slow when you don\'t want them to be!' . $nl;
				flush();
			}
			sleep(4);

			// Also overwrite some of the target's information.
			$repo = UpdateSiteModel::Find(null, 1);
			$initialtarget['source'] = 'repo-' . $repo->get('id');
			$initialtarget['location'] = 'http://corepl.us';

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
			echo '[===========  CHECKING DEPENDENCIES  ===========]' . $nl;
			flush();
		}
		do{
			$lastsizeofqueue = sizeof($pendingqueue);

			foreach($pendingqueue as $k => $c){
				$good = true;

				if(isset($c['requires'])){
					if($verbose){
						echo '[INFO] - Checking dependencies for ' . $c['typetitle'] . $nl;
						flush();
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
								echo '[INFO] - Dependency [' . $r['name'] . ' ' . $r['version'] . '] met with already-installed packages.' . $nl;
								flush();
							}
						}
						else{
							if($verbose){
								echo '[INFO] - Additional package [' . $result['typetitle'] . '] required to meet dependency [' . $r['name'] . ' ' . $r['version'] . '], adding to queue and retrying!' . $nl;
								flush();
							}
							// It's an array of requirements that are needed to satisfy this installation.
							$pendingqueue = array_merge(array($result), $pendingqueue);
							$good = false;
						}
					}
				}
				else{
					if($verbose){
						echo '[INFO] - Skipping dependency check for ' . $c['typetitle'] . ', no requirements present' . $nl;
						flush();
					}

					// The require key isn't present... OK!
					// This happens with themes, as they do not have any dependency logic.
				}

				if($good === true){
					$checkedqueue[] = $c;
					$changes[] = $c['typetitle'];
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
					echo implode($nl, $output);
				}
				return [
					'status' => 0,
					'message' => $c['typetitle'] . ' failed GPG verification! Is the key ' . $target['key'] . ' installed?'
				];
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
				echo $nl . '[===========  PERFORMING INSTALL (' . strtoupper($target['typetitle']) . ')  ===========]' . $nl;
				flush();
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
					echo '[INFO] - Using repository ' . $repo->get('url') . ' for installation source' . $nl;
					flush();
				}

				// Setup the remote file that will be used to download from.
				$file = new File_remote_backend($target['location']);
				$file->username = $repo->get('username');
				$file->password = $repo->get('password');

				// The initial HEAD request pulls the metadata for the file, and sees if it exists.
				if($verbose){
					echo '[INFO] - Performing HEAD lookup on ' . $file->getFilename() . $nl;
					flush();
				}
				if(!$file->exists()){
					return [
						'status' => 0,
						'message' => $target['location'] . ' does not seem to exist!'
					];
				}
				if($verbose){
					echo '[INFO] - Found a(n) ' . $file->getMimetype() . ' file that returned a ' . $file->getStatus() . ' status.' . $nl;
					flush();
				}

				// Get file contents will download the file.
				if($verbose){
					echo '[INFO] - Downloading ' . $file->getFilename() . $nl;
					flush();
				}
				$downloadtimer = microtime(true);
				$obj = $file->getContentsObject();
				// Getting the object simply sets it up, it doesn't download the contents yet.
				$obj->getContents();
				// Now it has :p
				// How long did it take?
				if($verbose){
					echo '[INFO] - Downloaded ' . $file->getFilesize(true) . ' in ' . (round(microtime(true) - $downloadtimer, 2) . ' seconds') . $nl;
					flush();
				}

				if(!($obj instanceof File_asc_contents)){
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
					echo '[INFO] - Found key ' . $target['key'] . ' for package maintainer, appears to be valid.' . $nl;
					$output = array();
					exec('gpg --homedir "' . GPG_HOMEDIR . '" --list-public-keys "' . $target['key'] . '"', $output, $result);
					foreach($output as $line){
						if(trim($line)) echo '[INFO] - ' . htmlentities($line) . $nl;
					}
				}

				$dir = \Core\directory($target['destdir']);
				if(!$dir->isWritable()){
					return [
						'status' => 0,
						'message' => $target['destdir'] . ' is not writable!'
					];
				}

				if($test){
					// Well, shy of reinstalling... what else can I do in a test?
					continue;
				}

				// Decrypt the signed file.
				if($verbose){
					echo '[INFO] - Decrypting signed file' . $nl;
					flush();
				}
				/** @var $localfile File_Backend */
				$localfile = $obj->decrypt('tmp/updater/');
				$localobj = $localfile->getContentsObject();

				// This tarball will be extracted to a temporary directory, then copied from there.
				if($verbose){
					echo '[INFO] - Extracting tarball ' . $localfile->getFilename() . $nl;
					flush();
				}
				$tmpdir = $localobj->extract('tmp/installer-' . Core::RandomHex(4));

				// Now that the data is extracted in a temporary directory, extract every file in the destination.
				$datadir = $tmpdir->get('data/');
				if(!$datadir){
					return [
						'status' => 0,
						'message' => 'Invalid package, ' . $target['typetitle'] . ', does not contain a "data" directory.'
					];
				}


				if($verbose){
					echo '[INFO] - Installing files into ' . $target['destdir'] . $nl;
					flush();
				}
				$queue = array($datadir);//$datadir->ls();
				$x = 0;
				do{
					++$x;
					$queue = array_values($queue);
					foreach($queue as $k => $q){
						if($q instanceof Directory_local_backend){
							unset($queue[$k]);
							// Just queue directories up to be scanned.
							// (don't do array merge, because I'm inside a foreach loop)
							foreach($q->ls() as $subq) $queue[] = $subq;
						}
						else{
							// It's a file, copy it over.
							// To do so, resolve the directory path inside the temp data dir.
							$dest = $target['destdir'] . substr($q->getFilename(), strlen($datadir->getPath()));
							$newfile = $q->copyTo($dest, true);

							unset($queue[$k]);
						}
					}
				}
				while(sizeof($queue) > 0 && $x < 15);

				// Cleanup the temp directory
				if($verbose){
					echo '[INFO] - Cleaning up temporary directory' . $nl;
					flush();
				}
				$tmpdir->remove();

				$changes[] = 'Installed ' . $target['typetitle'] . ' ' . $target['version'];
			}
		}

		// Clear the cache so the next pageload will pick up on the new components and goodies.
		Core::Cache()->flush();

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
	 * @param array $requirement
	 * @return array | false
	 */
	public static function CheckRequirement($requirement, $newcomponents = array(), $allavailable = array()){
		$rtype = $requirement['type'];
		$rname = $requirement['name'];
		$rvers = $requirement['version'];
		$rvrop = $requirement['operation'];


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
}
