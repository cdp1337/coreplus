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
		if(false && isset($_SESSION['updaterhelper_getupdates']) && $_SESSION['updaterhelper_getupdates']['expire'] <= time()){
			return $_SESSION['updaterhelper_getupdates']['data'];
		}
		
		$corevers = Core::GetComponent()->getVersion();
		
		// Build a list of components currently installed, this will act as a base.
		$components = array();
		$core       = array();
		$themes     = array();

		foreach(Core::GetComponents() as $c){
			$n = strtolower($c->getName());
			if($n == 'core'){
				$core[$c->getVersion()] = array(
					'name' => $n,
					'title' => $c->getName(),
					'version' => $c->getVersion(),
					'source' => 'installed',
					'description' => $c->getDescription(),
					'provides' => $c->getProvides(),
					'requires' => $c->getRequires(),
					'location' => null,
					'status' => 'installed',
				);
			}
			else{
				if(!isset($components[$n])) $components[$n] = array();
				$components[$n][$c->getVersion()] = array(
					'name' => $n,
					'title' => $c->getName(),
					'version' => $c->getVersion(),
					'source' => 'installed',
					'description' => $c->getDescription(),
					'provides' => $c->getProvides(),
					'requires' => $c->getRequires(),
					'location' => null,
					'status' => 'installed',
				);
			}
		}

		foreach(ThemeHandler::GetAllThemes() as $t){
			$n = strtolower($t->getName());
			if(!isset($themes[$n])) $themes[$n] = array();
			$themes[$n][$t->getVersion()] = array(
				'name' => $n,
				'title' => $n,
				'version' => $c->getVersion(),
				'source' => 'installed',
				'description' => $c->getDescription(),
				'location' => null,
				'status' => 'installed',
			);
		}
		
		// Now, look up components from all the updates sites.
		$updatesites = UpdateSiteModel::Find('enabled = 1');
		foreach($updatesites as $site){

			if(!$site->isValid()) continue;
			$file = $site->getFile();
			
			$repoxml = new RepoXML();
			$repoxml->loadFromFile($file);
			$rootpath = dirname($file->getFilename()) . '/';
			foreach($repoxml->getPackages() as $pkg){
				// Already installed and is up to date, don't do anything.
				if($pkg->isCurrent()) continue;

				$n = strtolower($pkg->getName());
				$type = $pkg->getType();
				if($n == 'core') $type = 'core'; // Override the core, even though it is a component...

				switch($type){
					case 'core':
						$vers = $pkg->getVersion();
						// Check and see if this version is already listed in the repo.
						if(!isset($core[$vers])){
							$core[$vers] = array(
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
							);
						}
						break;
					case 'component':
						$vers  = $pkg->getVersion();
						$status = 'new';
						if(Core::GetComponent($n)){
							if(Core::VersionCompare($vers, Core::GetComponent($n)->getVersion(), 'gt')) $status = 'update';
							else $status = 'downgrade';
						}

						// Check and see if this version is already listed in the repo.
						if(!isset($components[$n][$vers])){
							$components[$n][$vers] = array(
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
							);
						}
						break;
					case 'theme':
						$vers = $pkg->getVersion();
						$status = 'new';
						if(ThemeHandler::GetTheme($n)){
							if(Core::VersionCompare($vers, ThemeHandler::GetTheme($n)->getVersion(), '>')) $status = 'update';
							else $status = 'downgrade';
						}

						// Check and see if this version is already listed in the repo.
						if(!isset($themes[$n][$vers])){
							$themes[$n][$vers] = array(
								'name' => $n,
								'title' => $n,
								'version' => $vers,
								'source' => 'repo-' . $site->get('id'),
								'sourceurl' => $site->get('url'),
								'description' => $pkg->getDescription(),
								'location' => $rootpath . $pkg->getFileLocation(),
								'status' => $status,
							);
						}
				}
				
				//var_dump($pkg->asPrettyXML()); die();
			}
		}
		
		// Give me the components in alphabetical order.
		ksort($components);
		ksort($themes);
		
		// And sort the versions.
		foreach($components as $k => $v){
			ksort($components[$k], SORT_NUMERIC);
		}
		foreach($themes as $k => $v){
			ksort($themes[$k], SORT_NUMERIC);
		}
		
		// Cache this for next pass.
		$_SESSION['updaterhelper_getupdates'] = array();
		$_SESSION['updaterhelper_getupdates']['data'] = array('core' => $core, 'components' => $components, 'themes' => $themes);
		$_SESSION['updaterhelper_getupdates']['expire'] = time() + 3600;
		
		return array('core' => $core, 'components' => $components, 'themes' => $themes);
	}
	
	public static function InstallComponent($name, $version, $dryrun = false){
		$updates = UpdaterHelper::GetUpdates();

		// I just need the component array itself.
		$components = $updates['components'];
		
		// Make sure the name and version exist in the updates list.
		if(!isset($components[$name])){
			return array('status' => 0, 'message' => 'Component ' . $name . ' does not appear to be valid.');
		}
		if(!isset($components[$name][$version])){
			return array('status' => 0, 'message' => 'Component ' . $name . ' does not appear to have requested version.');
		}
		
		// A queue of components to check.
		$pendingqueue = array($components[$name][$version]);
		// A queue of components that will be installed that have satisfied dependencies.
		$checkedqueue = array();
		$lastsizeofqueue = 99;
		
		do{
			foreach($pendingqueue as $k => $c){
				$good = true;
				foreach($c['requires'] as $r){

					// Sometimes there will be blank requirements in the metafile.
					if(!$r['name']) continue;

					$result = UpdaterHelper::CheckRequirement($r);
					if($result === false){
						return array('status' => 0, 'message' => 'Component ' . $name . ' requires ' . $r['name'] . ' ' . $r['version']);
					}
					elseif($result === true){
						// yay
						continue;
					}
					else{
						die('Yeah... finish this part.');
					}
				}
				
				if($good === true){
					$checkedqueue[] = $c;
					unset($pendingqueue[$k]);
				}
			}
			
			$lastsizeofqueue = sizeof($pendingqueue);
		}
		while(sizeof($pendingqueue) && sizeof($pendingqueue) != $lastsizeofqueue);


		$repos = array();
		$remotefiles = array();
		$names = array();
		// Check the signatures for the packages first.
		foreach($checkedqueue as $component){
			if(strpos($component['source'], 'repo-') !== false){
				// Look up that repo's connection information, since username and password may be required.
				if(!isset($repos[$component['source']])){
					$repos[$component['source']] = new UpdateSiteModel(substr($component['source'], 5));
				}
				$remotefiles[$component['name']] = new File_remote_backend($component['location']);
				$remotefiles[$component['name']]->username = $repos[$component['source']]->get('username');
				$remotefiles[$component['name']]->password = $repos[$component['source']]->get('password');

				if(!$remotefiles[$component['name']]->exists()){
					return array('status' => 0, 'message' => $component['location'] . ' does not seem to exist!');
				}

				$obj = $remotefiles[$component['name']]->getContentsObject();
				if(!$obj->verify()){
					// Maybe it can at least get the key....
					if($key = $obj->getKey()){
						return array('status' => 0, 'message' => 'Unable to locate public key for ' . $key);
					}
					return array('status' => 0, 'message' => 'Invalid GPG signature for ' . $component['title']);
				}

				$dir = Core::Directory('components/' . $component['name']);
				if(!$dir->isWritable()){
					return array('status' => 0, 'message' => ROOT_PDIR . 'components/' . $component['name'] . '/ is not writable!');
				}
			}
			// else, it is already locally installed.

			$names[] = $component['name'];
		}
		
		
		// If dryrun only was requested, just return the status here.
		if($dryrun){
			return array('status' => 1, 'message' => 'All dependencies are met, ok to install', 'changes' => $names);
		}
		
		// and do the actual installation.
		foreach($checkedqueue as $component){
			if(strpos($component['source'], 'repo-') !== false){

				// Don't need to verify this again, was done above.
				$obj = $remotefiles[$component['name']]->getContentsObject();

				// Decrypt the signed file.
				$localfile = $obj->decrypt('tmp/updater/');
				$localobj = $localfile->getContentsObject();
				
				// This tarball will be extracted to a temporary directory, then copied from there.
				$tmpdir = $localobj->extract('tmp/installer-' . Core::RandomHex(4));
				
				// Destination directory it will be installed to.
				$destbase = ROOT_PDIR . 'components/' . $component['name'] . '/';
				
				// Now that the data is extracted in a temporary directory, extract every file in the destination.
				$datadir = $tmpdir->get('data/');
				if(!$datadir){
					return array('status' => 0, 'message' => 'Invalid component ' . $component['title'] . ', does not contain a data directory.');
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
							$dest = $destbase . substr($q->getFilename(), strlen($datadir->getPath()));
							$newfile = $q->copyTo($dest, true);
							
							unset($queue[$k]);
						}
					}
				}
				while(sizeof($queue) > 0 && $x < 15);
				
				// Cleanup the temp directory
				$tmpdir->remove();
			}
			// else, it must be locally installed already, just not set to be installed.
			// This happens if a component was copied in manually and the site is not in development mode.
			// If this is the case... there's nothing to do extraction-wise.

			// and w00t, the files should be extracted.  Do the actual installation.
			$c = new Component($component['name']);
			$c->load();
			// if it's installed, switch to that version and upgrade.
			if($c->isInstalled()){
				$c = Core::GetComponent($component['name']);
				// Make sure I get the new XML
				$c->load();
				// And upgrade
				$c->upgrade();
			}
			else{
				// It's a new installation.
				$c->install();
			}
		}

		// yay...
		return array('status' => 1, 'message' => 'Performed all operations successfully');
	}


	public static function InstallTheme($name, $version, $dryrun = false){
		$updates = UpdaterHelper::GetUpdates();

		// I just need the component array itself.
		$themes = $updates['themes'];

		// Make sure the name and version exist in the updates list.
		if(!isset($themes[$name])){
			return array('status' => 0, 'message' => 'Theme ' . $name . ' does not appear to be valid.');
		}
		if(!isset($themes[$name][$version])){
			return array('status' => 0, 'message' => 'Theme ' . $name . ' does not appear to have requested version.');
		}

		// This is the theme that will be installed.
		// Since themes don't (currently) have dependencies, the logic is much simplier.
		$theme = $themes[$name][$version];


		$repos = array();
		$remotefiles = array();
		$names = array();
		// Check the signatures for the package first.
		if(strpos($theme['source'], 'repo-') !== false){
			// Look up that repo's connection information, since username and password may be required.
			if(!isset($repos[$theme['source']])){
				$repos[$theme['source']] = new UpdateSiteModel(substr($theme['source'], 5));
			}
			$remotefiles[$theme['name']] = new File_remote_backend($theme['location']);
			$remotefiles[$theme['name']]->username = $repos[$theme['source']]->get('username');
			$remotefiles[$theme['name']]->password = $repos[$theme['source']]->get('password');

			if(!$remotefiles[$theme['name']]->exists()){
				return array('status' => 0, 'message' => $theme['location'] . ' does not seem to exist!');
			}

			$obj = $remotefiles[$theme['name']]->getContentsObject();
			if(!$obj->verify()){
				// Maybe it can at least get the key....
				if($key = $obj->getKey()){
					return array('status' => 0, 'message' => 'Unable to locate public key for ' . $key);
				}
				return array('status' => 0, 'message' => 'Invalid GPG signature for ' . $theme['title']);
			}

			$dir = Core::Directory('themes/' . $theme['name']);
			if(!$dir->isWritable()){
				return array('status' => 0, 'message' => ROOT_PDIR . 'themes/' . $theme['name'] . '/ is not writable!');
			}
		}

		$names[] = $theme['name'];


		// If dryrun only was requested, just return the status here.
		if($dryrun){
			return array('status' => 1, 'message' => 'All dependencies are met, ok to install', 'changes' => $names);
		}

		// and do the actual installation.
		if(strpos($theme['source'], 'repo-') !== false){

			// Don't need to verify this again, was done above.
			$obj = $remotefiles[$theme['name']]->getContentsObject();

			// Decrypt the signed file.
			$localfile = $obj->decrypt('tmp/updater/');
			$localobj = $localfile->getContentsObject();

			// This tarball will be extracted to a temporary directory, then copied from there.
			$tmpdir = $localobj->extract('tmp/installer-' . Core::RandomHex(4));

			// Destination directory it will be installed to.
			$destbase = ROOT_PDIR . 'themes/' . $theme['name'] . '/';

			// Now that the data is extracted in a temporary directory, extract every file in the destination.
			$datadir = $tmpdir->get('data/');
			if(!$datadir){
				return array('status' => 0, 'message' => 'Invalid theme ' . $theme['title'] . ', does not contain a data directory.');
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
						$dest = $destbase . substr($q->getFilename(), strlen($datadir->getPath()));
						$newfile = $q->copyTo($dest, true);

						unset($queue[$k]);
					}
				}
			}
			while(sizeof($queue) > 0 && $x < 15);

			// Cleanup the temp directory
			$tmpdir->remove();

			// and w00t, the files should be extracted.  Do the actual installation.
			$t = new Theme($theme['name']);
			$t->load();
			// if it's installed, switch to that version and upgrade.
			if($t->isInstalled()){
				if(($t = ThemeHandler::GetTheme($theme['name'])) !== false){
					// Make sure I get the new XML
					$t->load();
					// And upgrade
					$t->upgrade();
				}
			}
			else{
				// It's a new installation.
				$t->install();
			}
		}

		// yay...
		return array('status' => 1, 'message' => 'Performed all operations successfully');
	}

	public static function InstallCore($version, $dryrun = false){
		$updates = UpdaterHelper::GetUpdates();

		// I just need the component array itself.
		$cores = $updates['core'];

		// Make sure the name and version exist in the updates list.
		if(!isset($cores[$version])){
			return array('status' => 0, 'message' => 'Core does not appear to have requested version.');
		}

		// This is the theme that will be installed.
		// Since themes don't (currently) have dependencies, the logic is much simplier.
		$core = $cores[$version];


		$repos = array();
		$remotefiles = array();
		$names = array();
		// Check the signatures for the package first.
		if(strpos($core['source'], 'repo-') !== false){
			// Look up that repo's connection information, since username and password may be required.
			if(!isset($repos[$core['source']])){
				$repos[$core['source']] = new UpdateSiteModel(substr($core['source'], 5));
			}
			$remotefiles['core'] = new File_remote_backend($core['location']);
			$remotefiles['core']->username = $repos[$core['source']]->get('username');
			$remotefiles['core']->password = $repos[$core['source']]->get('password');

			if(!$remotefiles['core']->exists()){
				return array('status' => 0, 'message' => $core['location'] . ' does not seem to exist!');
			}

			$obj = $remotefiles['core']->getContentsObject();

			if(!($obj instanceof File_asc_contents)){
				return array(
					'status' => 0,
					'message' => $remotefiles['core']->getFilename() . ' does not appear to be a valid GPG signed archive'
				);
			}

			if(!$obj->verify()){
				// Maybe it can at least get the key....
				if($key = $obj->getKey()){
					return array('status' => 0, 'message' => 'Unable to locate public key for ' . $key);
				}
				return array('status' => 0, 'message' => 'Invalid GPG signature for Core');
			}

			$dir = Core::Directory(ROOT_PDIR);
			if(!$dir->isWritable()){
				return array('status' => 0, 'message' => ROOT_PDIR . ' is not writable!');
			}
		}

		$names[] = 'core';


		// If dryrun only was requested, just return the status here.
		if($dryrun){
			return array('status' => 1, 'message' => 'All dependencies are met, ok to install', 'changes' => $names);
		}

		// and do the actual installation.
		if(strpos($core['source'], 'repo-') !== false){

			// Don't need to verify this again, was done above.
			$obj = $remotefiles['core']->getContentsObject();

			// Decrypt the signed file.
			$localfile = $obj->decrypt('tmp/updater/');
			$localobj = $localfile->getContentsObject();

			// This tarball will be extracted to a temporary directory, then copied from there.
			$tmpdir = $localobj->extract('tmp/installer-' . Core::RandomHex(4));

			// Destination directory it will be installed to.
			$destbase = ROOT_PDIR;

			// Now that the data is extracted in a temporary directory, extract every file in the destination.
			$datadir = $tmpdir->get('data/');
			if(!$datadir){
				return array('status' => 0, 'message' => 'Invalid theme ' . $core['title'] . ', does not contain a data directory.');
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
						$dest = $destbase . substr($q->getFilename(), strlen($datadir->getPath()));
						$newfile = $q->copyTo($dest, true);

						unset($queue[$k]);
					}
				}
			}
			while(sizeof($queue) > 0 && $x < 15);

			// Cleanup the temp directory
			$tmpdir->remove();

			// and w00t, the files should be extracted.  Do the actual installation.
			$t = new Theme('core');
			$t->load();
			// if it's installed, switch to that version and upgrade.
			if($t->isInstalled()){
				if(($t = ThemeHandler::GetTheme('core')) !== false){
					// Make sure I get the new XML
					$t->load();
					// And upgrade
					$t->upgrade();
				}
			}
			else{
				// It's a new installation.
				$t->install();
			}
		}

		// yay...
		return array('status' => 1, 'message' => 'Performed all operations successfully');
	}

	
	/**
	 * Simple function to scan through the components provided for one that
	 * satisfies the requirement.
	 * 
	 * @param array $requirement
	 * @return array | false
	 */
	public static function CheckRequirement($requirement){
		
		// This will check if the requirement is already met.
		switch($requirement['type']){
			case 'library':
				if(Core::IsLibraryAvailable($requirement['name'], $requirement['version'], $requirement['operation'])){
					return true;
				}
				break;
			case 'jslibrary':
				if(Core::IsJSLibraryAvailable($requirement['name'], $requirement['version'], $requirement['operation'])){
					return true;
				}
				break;
			case 'component':
				if(Core::IsComponentAvailable($requirement['name'], $requirement['version'], $requirement['operation'])){
					return true;
				}
				break;
		}
		
		// @todo Run through the components that are available via an update.
		
		// Requirement not met... ok.  This needs to be conveyed to the calling script.
		return false;
	}
}
