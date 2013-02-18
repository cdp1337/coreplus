<?php
/**
 * [PAGE DESCRIPTION HERE]
 *
 * @package Core Plus\Core
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


class UpdaterController extends Controller_2_1 {

	public function __construct() {
		// Only administrators can access these pages.
		$this->accessstring = 'g:admin';
	}

	/**
	 * Listing controller of the updater.
	 *
	 */
	public function index() {
		$view = $this->getView();

		$sitecount = UpdateSiteModel::Count();
		$components = array();
		foreach(Core::GetComponents() as $k => $c){
			// Skip the core.
			if($k == 'core') continue;

			$components[$k] = $c;
		}

		// These should really be sorted by name
		ksort($components);

		// Merge in the disabled ones too!
		$components = array_merge($components, Core::GetDisabledComponents());

		// If the theme is disabled, this won't be available.
		if(class_exists('ThemeHandler')){
			$themes = ThemeHandler::GetAllThemes();
		}
		else{
			$themes = array();
		}


		$view->title = 'System Updater';
		$view->addControl(array('title' => 'Manage Repos', 'link' => '/updater/repos', 'icon' => 'cloud'));
		$view->addControl(array('title' => 'Manage GPG Keys', 'link' => '/updater/keys', 'icon' => 'key'));
		$view->addControl(array('title' => 'Browse Packages', 'link' => '/updater/browse', 'icon' => 'search'));
		$view->assign('sitecount', $sitecount);
		$view->assign('components', $components);
		$view->assign('core', Core::GetComponent('core'));
		$view->assign('themes', $themes);
	}

	/**
	 * Check for updates controller
	 *
	 * This is just a very simple json function to return true or false on if there are updates for currently installed components.
	 *
	 * This is so simple because its sole purpose is to just notify the user if there is an update available.
	 * For more full-featured update scripts, look at the getupdates page; that actually returns the updates.
	 */
	public function check() {
		$view = $this->getView();

		$view->contenttype = View::CTYPE_JSON;

		$updates = UpdaterHelper::GetUpdates();
		$updatesavailable = false;

		// Since I'm not sure which version of core is going to be returned...
		foreach($updates['core'] as $up){
			if($up['status'] == 'update') $updatesavailable = true;
		}

		// Same for components and themes, (only a little more depth)
		foreach($updates['components'] as $c){
			foreach($c as $up){
				if($up['status'] == 'update') $updatesavailable = true;
			}
		}
		foreach($updates['themes'] as $c){
			foreach($c as $up){
				if($up['status'] == 'update') $updatesavailable = true;
			}
		}

		$view->jsondata = $updatesavailable;
	}


	/**
	 * Get the list of updates from remote repositories, (or session cache).
	 */
	public function getupdates() {
		$view = $this->getView();
		$req  = $this->getPageRequest();

		$view->contenttype = View::CTYPE_JSON;

		$components = UpdaterHelper::GetUpdates();

		// Allow filters to be set.
		if($req->getParameter('onlyupdates')){
			foreach($components['core'] as $v => $dat){
				if($dat['status'] != 'update') unset($components['core'][$v]);
			}

			foreach($components['components'] as $c => $arr){
				foreach($arr as $v => $dat){
					if($dat['status'] != 'update') unset($components['components'][$c][$v]);
				}
				if(!sizeof($components['components'][$c])) unset($components['components'][$c]);
			}

			foreach($components['themes'] as $c => $arr){
				foreach($arr as $v => $dat){
					if($dat['status'] != 'update') unset($components['themes'][$c][$v]);
				}
				if(!sizeof($components['themes'][$c])) unset($components['themes'][$c]);
			}
		}

		if($req->getParameter('onlycore')){
			unset($components['components']);
			unset($components['themes']);
		}
		elseif($req->getParameter('onlycomponents')){
			unset($components['core']);
			unset($components['themes']);
		}

		$view->jsondata = $components;
	}

	/**
	 * Sites listing controller, displays all update sites and links to manage them.
	 *
	 */
	public function repos() {
		$view = $this->getView();

		if(!is_dir(GPG_HOMEDIR)){
			// Try to create it?
			if(is_writable(dirname(GPG_HOMEDIR))){
				// w00t
				mkdir(GPG_HOMEDIR);
			}
			else{
				Core::SetMessage(GPG_HOMEDIR . ' does not exist and could not be created!  Please fix this before proceeding!', 'error');
			}
		}
		elseif(!is_writable(GPG_HOMEDIR)){
			Core::SetMessage(GPG_HOMEDIR . ' is not writable!  Please fix this before proceeding!', 'error');
		}


		$sites = UpdateSiteModel::Find();

		$view->title = 'Repositories';
		$view->addControl('Add Repo', 'updater/repos/add', 'add');
		//$view->addControl(array('title' => 'Manage Repos', 'link' => '/updater/repos', 'icon' => 'cloud'));
		$view->addControl(array('title' => 'Manage GPG Keys', 'link' => '/updater/keys', 'icon' => 'key'));
		$view->addControl(array('title' => 'Browse Packages', 'link' => '/updater/browse', 'icon' => 'search'));

		$view->assign('sites', $sites);

	}

	/**
	 * Add a repository to the site.
	 * This will also handle the embedded keys, (as of 2.4.5).
	 *
	 * This contains the first step and second steps.
	 */
	public function repos_add() {
		$request = $this->getPageRequest();
		$view    = $this->getView();

		$site = new UpdateSiteModel();

		$form = Form::BuildFromModel($site);
		$form->set('action', Core::ResolveLink('/updater/repos/add'));
		$form->addElement('submit', array('value' => 'Next'));

		$view->title = 'Add Repo';
		// Needed because dynamic pages do not record navigation.
		$view->addBreadcrumb('Repositories', 'updater/repos');

		$view->assign('form', $form);


		// This is the logic for step 2 (confirmation).
		// This is after all the template logic from step 1 because it will fallback to that form if necessary.
		if($request->isPost()){
			$url      = $request->getPost('model[url]');
			$username = $request->getPost('model[username]');
			$password = $request->getPost('model[password]');

			// Validate and standardize this repo url.
			// This is because most people will simply type repo.corepl.us.
			if(strpos($url, '://') === false){
				$url = 'http://' . $url;
			}

			// Lookup that URL first!
			if(UpdateSiteModel::Count(array('url' => $url)) > 0){
				Core::SetMessage($url . ' is already used!', 'error');
				return;
			}

			// Load up a new Model, that's the easiest way to pull the repo data.
			$model = new UpdateSiteModel();
			$model->setFromArray([
				'url' => $url,
				'username' => $username,
				'password' => $password,
			]);

			// From here on out, populate the previous form with this new model.
			$form = Form::BuildFromModel($model);
			$form->set('action', Core::ResolveLink('/updater/repos/add'));
			$form->addElement('submit', array('value' => 'Next'));
			$view->assign('form', $form);

			if(!$model->isValid()){
				Core::SetMessage($url . ' does not appear to be a valid repository!', 'error');
				return;
			}

			$repo = new RepoXML();
			$repo->loadFromFile($model->getFile());

			// Make sure the keys are good
			if(!$repo->validateKeys()){
				Core::SetMessage('There were invalid/unpublished keys in the repo!  Refusing to import.', 'error');
				return;
			}

			// The very final bit of this logic is to look and see if there's a "confirm" present.
			// If there is, the user clicked accept on the second page and I need to go ahead and import the data.
			if($request->getPost('confirm')){
				$model->set('description', $repo->getDescription());
				$model->save();
				$keysimported = 0;
				$keycount = sizeof($repo->getKeys());

				foreach($repo->getKeys() as $key){
					$id = strtoupper(preg_replace('/[^a-zA-Z0-9]*/', '', $key['id']));
					$output = array();
					exec('gpg --homedir "' . GPG_HOMEDIR . '" --no-permission-warning --keyserver "hkp://pool.sks-keyservers.net" --recv-keys "' . $id . '"', $output, $result);
					if($result != 0){
						Core::SetMessage('Unable to import key [' . $id . '] from keyserver!', 'error');
					}
					else{
						++$keysimported;
					}
				}

				if(!$keycount){
					Core::SetMessage('Added repository site successfully!', 'success');
				}
				elseif($keycount != $keysimported){
					Core::SetMessage('Added repository site, but unable to import ' . ($keycount-$keysimported) . ' key(s).', 'info');
				}
				else{
					Core::SetMessage('Added repository site and imported ' . $keysimported . ' key(s) successfully!', 'success');
				}

				Core::Redirect('/updater/repos');
			}

			$view->templatename = 'pages/updater/repos_add2.tpl';
			$view->assign('description', $repo->getDescription());
			$view->assign('keys', $repo->getKeys());
			$view->assign('url', $url);
			$view->assign('username', $username);
			$view->assign('password', $password);
		}
	}

	/**
	 * Page to remove a repository.
	 */
	public function repos_delete(){
		$request = $this->getPageRequest();
		if(!$request->isPost()){
			return View::ERROR_BADREQUEST;
		}

		$model = new UpdateSiteModel($request->getParameter(0));
		if(!$model->exists()){
			return View::ERROR_NOTFOUND;
		}

		$model->delete();
		Core::SetMessage('Removed repository successfully', 'success');
		Core::Redirect('/updater/repos');
	}

	/**
	 * Browse the repositories for a component, be it new or update.
	 *
	 * This is designed to give a syndicated list of ALL components in all enabled repos.
	 */
	public function browse() {
		$view = $this->getView();

		$view->title = 'Browse Packages';
		$view->addControl(array('title' => 'Manage Repos', 'link' => '/updater/repos', 'icon' => 'cloud'));
		$view->addControl(array('title' => 'Manage GPG Keys', 'link' => '/updater/keys', 'icon' => 'key'));
		//$view->addControl(array('title' => 'Browse Packages', 'link' => '/updater/browse', 'icon' => 'search'));
	}

	public function keys() {
		$view = $this->getView();

		// Get the existing keys.
		exec('gpg --homedir "' . GPG_HOMEDIR . '" --no-permission-warning --list-public-keys', $output, $result);
		$keys = array();

		if(sizeof($output) > 3){
			// Drop the first two lines, these are useless headers.
			array_shift($output);
			array_shift($output);
			$k = null;
			foreach($output as $line){
				if(strpos($line, 'pub') === 0){
					// This is a new key.
					if($k !== null){
						// Save the previous one.
						$keys[] = $k;
					}

					// And start the new key.
					$k = array(
						'key' => preg_replace('#^pub[ ]*[0-9]{4}[A-Z]/([A-Z0-9]*) .*#', '$1', $line),
						'date' => preg_replace('#^pub[ ]*[0-9]{4}[A-Z]/[A-Z0-9]* ([0-9\-]*).*#', '$1', $line),
						'names' => array()
					);
				}
				elseif(strpos($line, 'uid') === 0){
					// No key started yet?... hmm
					if($k === null) continue;

					$k['names'][] = preg_replace('#^uid[ ]*(.*)$#', '$1', $line);
				}
			}
			// Save the last one.
			$keys[] = $k;
		}


		$view->title = "GPG Keys";
		$view->addControl('Import Key', '/updater/keys/import', 'add');
		$view->addControl(array('title' => 'Manage Repos', 'link' => '/updater/repos', 'icon' => 'cloud'));
		//$view->addControl(array('title' => 'Manage GPG Keys', 'link' => '/updater/keys', 'icon' => 'key'));
		$view->addControl(array('title' => 'Browse Packages', 'link' => '/updater/browse', 'icon' => 'search'));
		$view->assign('directory', GPG_HOMEDIR);
		$view->assign('keys', $keys);
	}

	public function keys_import() {
		$view  = $this->getView();
		$req   = $this->getPageRequest();
		$error = null;

		if(!is_writable(GPG_HOMEDIR)){
			$error = 'Please ensure that ' . GPG_HOMEDIR . ' is writable!';
		}

		if($req->isPost()){
			// Receive public key from a keyserver.
			if($_POST['pubkeyid']){
				$id = strtoupper(preg_replace('/[^a-zA-Z0-9]*/', '', $_POST['pubkeyid']));
				exec('gpg --homedir "' . GPG_HOMEDIR . '" --no-permission-warning --keyserver "hkp://pool.sks-keyservers.net" --recv-keys "' . $id . '"', $output, $result);
				if($result != 0){
					$error = 'Unable to lookup ' . $id . ' from keyserver.';
				}
				else{
					Core::Redirect('/updater/keys');
				}
			}
			elseif($_POST['pubkey']){
				$tmp = new File_local_backend('tmp/importkey-' . Core::RandomHex(2) . '.gpg');
				$tmp->putContents($_POST['pubkey']);
				exec('gpg --homedir "' . GPG_HOMEDIR . '" --no-permission-warning --import "' . $tmp->getFilename() . '"', $output, $result);
				$tmp->delete();
				if($result != 0){
					$error = 'Unable to import requested key.';
				}
				else{
					Core::Redirect('/updater/keys');
				}
			}
			elseif($_FILES['pubkeyfile']){
				$tmp = new File_local_backend($_FILES['pubkeyfile']['tmp_name']);
				$tmp->putContents($_POST['pubkey']);
				exec('gpg --homedir "' . GPG_HOMEDIR . '" --no-permission-warning --import "' . $tmp->getFilename() . '"', $output, $result);
				$tmp->delete();
				if($result != 0){
					$error = 'Unable to import requested key.';
				}
				else{
					Core::Redirect('/updater/keys');
				}
			}
		}

		$view->addBreadcrumb('GPG Keys', 'updater/keys');
		$view->title = 'Import Key';
		$view->assign('error', $error);
	}

	public function keys_delete() {
		$view = $this->getView();
		$req = $this->getPageRequest();

		// This is a post-only page!
		if(!$req->isPost()){
			$view->error = View::ERROR_BADREQUEST;
			return;
		}

		$key = $req->getParameter(0);
		if(!$key){
			$view->error = View::ERROR_BADREQUEST;
			return;
		}

		$key = strtoupper(preg_replace('/[^a-zA-Z0-9]*/', '', $key));

		exec('gpg --homedir "' . GPG_HOMEDIR . '" --no-permission-warning --batch --yes --delete-key "' . $key . '"', $output, $result);
		if($result != 0){
			Core::SetMessage('Unable to remove key ' . $key, 'error');
		}
		Core::Redirect('/updater/keys');
	}

	public function component_install() {
		$view = $this->getView();
		$req = $this->getPageRequest();

		// This is a json-only page.
		$view->contenttype = View::CTYPE_JSON;

		// This is a post-only page!
		if(!$req->isPost()){
			$view->error = View::ERROR_BADREQUEST;
			return;
		}

		$name    = strtolower($req->getParameter(0));
		$version = $req->getParameter(1);
		$dryrun  = $req->getParameter('dryrun');

		$status = UpdaterHelper::InstallComponent($name, $version, $dryrun);

		$view->jsondata = $status;
	}

	public function component_disable() {
		$view = $this->getView();
		$req = $this->getPageRequest();

		// This is a json-only page.
		$view->contenttype = View::CTYPE_JSON;

		// This is a post-only page!
		if(!$req->isPost()){
			$view->error = View::ERROR_BADREQUEST;
			return;
		}

		$name    = strtolower($req->getParameter(0));
		$dryrun  = $req->getParameter('dryrun');
		$c = Core::GetComponent($name);

		if(!$c){
			$view->jsondata = array('message' => 'Requested component not found');
			//$view->error = View::ERROR_NOTFOUND;
			return;
		}

		if($c instanceof Component){
			//$view->error = View::ERROR_SERVERERROR;
			$view->jsondata = array('message' => 'Requested component is not a valid 2.1-based component, please upgrade manually ' . $name);
			return;
		}

		// Create a reverse map of what components are the basis of which components, this will make it easier
		// to do the necessary mapping.
		$reverse_requirements = array();

		foreach(Core::GetComponents() as $k => $ccheck){

			// I only want to look at enabled components.
			if(!$ccheck->isEnabled()) continue;

			$requires = $ccheck->getRequires();
			foreach($requires as $r){
				$n = strtolower($r['name']);

				// This is a dependency of everything, I know....
				if($n == 'core') continue;

				if(!isset($reverse_requirements[$n])) $reverse_requirements[$n] = array();
				$reverse_requirements[$n][] = $ccheck->getName();
			}
			//var_dump($ccheck->getName(), $ccheck->getRequires());
		}

		// Now I can quickly see if any of the "provides" of this component will conflict with other systems.
		// These must be disabled too!
		$provides = $c->getProvides();

		$todisable = array($name);

		foreach($provides as $p){
			if(isset($reverse_requirements[$p['name']])){
				$todisable = array_merge($todisable, $reverse_requirements[$p['name']]);
			}
		}
		// And again!
		// (I could just use a simple recursive function here, but a level of two should be adequate)
		foreach($todisable as $n){
			if(isset($reverse_requirements[$n])){
				$todisable = array_merge($todisable, $reverse_requirements[$n]);
			}
		}

		$todisable = array_unique($todisable);

		if(!$dryrun){
			foreach($todisable as $c){
				Core::GetComponent($c)->disable();
			}
		}

		$view->jsondata = array('changes' => $todisable, 'dryrun' => $dryrun);
	}

	public function component_enable() {
		$view = $this->getView();
		$req = $this->getPageRequest();

		// This is a json-only page.
		$view->contenttype = View::CTYPE_JSON;

		// This is a post-only page!
		/*if(!$req->isPost()){
			$view->error = View::ERROR_BADREQUEST;
			return;
		}*/

		$name    = strtolower($req->getParameter(0));
		$dryrun  = $req->getParameter('dryrun');
		$c = Core::GetComponent($name);

		if(!$c){
			$view->error = View::ERROR_NOTFOUND;
			return;
		}

		if($c instanceof Component){
			$view->error = View::ERROR_SERVERERROR;
			$view->jsondata = array('message' => 'Requested component is not a valid 2.1 version component, please upgrade ' . $name);
			return;
		}

		// Create a reverse map of what components are the basis of which components, this will make it easier
		// to do the necessary mapping.
		$provides = array('library' => array(), 'component' => array());
		foreach(Core::GetComponents() as $ccheck){
			// I only want to look at enabled components.
			if(!$ccheck->isEnabled()) continue;

			foreach($ccheck->getProvides() as $p){
				$provides[$p['type']][$p['name']] = $p['version'];
			}
		}

		// And check this component's requirements.
		$requires = $c->getRequires();
		foreach($requires as $r){
			if(!isset($provides[$r['type']][$r['name']])){
				$view->jsondata = array('message' => 'Unable to locate requirement ' . $r['type'] . ' ' . $r['name']);
				return;
			}

			$op = ($r['operation']) ? $r['operation'] : 'ge';

			if(!Core::VersionCompare($provides[$r['type']][$r['name']], $r['version'], $op)){
				$view->jsondata = array('message' => 'Dependency version for ' . $r['type'] . ' ' . $r['name'] . ' ' . $op . ' ' . $r['version'] . ' not met');
				return;
			}
		}

		if(!$dryrun){
			$c->enable();
		}

		$view->jsondata = array('changes' => array($name), 'dryrun' => $dryrun);
	}


	public function theme_install() {
		$view = $this->getView();
		$req  = $this->getPageRequest();

		// This is a json-only page.
		$view->contenttype = View::CTYPE_JSON;

		// This is a post-only page!
		if(!$req->isPost()){
			$view->error = View::ERROR_BADREQUEST;
			return;
		}

		$name    = strtolower($req->getParameter(0));
		$version = $req->getParameter(1);
		$dryrun  = $req->getParameter('dryrun');

		$status = UpdaterHelper::InstallTheme($name, $version, $dryrun);

		$view->jsondata = $status;
	}

	public function core_install() {
		$view = $this->getView();
		$req  = $this->getPageRequest();

		// This is a json-only page.
		$view->contenttype = View::CTYPE_JSON;

		// This is a post-only page!
		if(!$req->isPost()){
			$view->error = View::ERROR_BADREQUEST;
			return;
		}

		$version = $req->getParameter(0);
		$dryrun  = $req->getParameter('dryrun');

		$status = UpdaterHelper::InstallCore($version, $dryrun);

		// This page cannot continue execution with the core, as it was extracted.  Simply return raw json.
		if($status['status'] == 1){
			die(json_encode($status));
		}

		$view->jsondata = $status;
	}


	public function _Sites_Update(Form $form) {

		$m = $form->getModel();

		// Test it first if it's enabled!
		if ($m->get('enabled')) {
			if (!$m->isValid()) {
				Core::SetMessage('Requested update site does not appear to be valid!', 'error');
				return false;
			}
		}

		$form->getModel()->save();

		// Will be useful for importing new keys.
		// gpg --homedir . --no-permission-warning --keyserver x-hkp://pool.sks-keyservers.net --recv-keys B2BEDCCB

		return 'updater/repos';
	}

}
