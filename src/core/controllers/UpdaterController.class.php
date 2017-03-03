<?php
/**
 *
 * @package Core
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


class UpdaterController extends Controller_2_1 {

	public function __construct() {
		// Only administrators can access these pages.
		$this->accessstring = 'g:admin';
	}


	/**
	 * Get the controls for the updater.
	 *
	 * @return array|null|void
	 */
	public function getControls(){
		$view = $this->getView();

		$view->addControl(
			[
				'title' => t('STRING_MANAGE_REPOSITORIES'), 
				'link' => '/updater/repos', 
				'icon' => 'cloud']
		);
		$view->addControl(
			[
				'title' => t('STRING_ADD_REPOSITORY_SITE'), 
				'link' => 'updater/repos/add', 
				'icon' => 'add']
		);

		$view->addControl(
			[
				'title' => t('STRING_MANAGE_GPG_KEYS'), 
				'link' => '/updater/keys', 
				'icon' => 'key']
		);
		$view->addControl(
			[
				'title' => t('STRING_IMPORT_GPG_KEY'), 
				'link' => '/updater/keys/import', 
				'icon' => 'add'
			]
		);

		$view->addControl(
			[
				'title' => t('STRING_FIND_NEW_PACKAGES'), 
				'link' => '/updater/browse', 
				'icon' => 'search']
		);
		$view->addControl(
			[
				'title' => t('STRING_MANUALLY_UPLOAD_PACKAGE'),
			    'link' => '/updater/upload',
			    'icon' => 'upload',
			]
		);
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


		$view->title = 't:STRING_SYSTEM_UPDATER';
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



		$updates = UpdaterHelper::GetUpdates();

		$view->contenttype = View::CTYPE_JSON;
		// Will get overwrote if found to be true.
		$view->jsondata = false;
		$view->record = false;


		if(isset($updates['core']) && $updates['core']['status'] == 'update'){
			$view->jsondata = true;
			return;
		}

		// Same for components and themes, (only a little more depth)
		foreach($updates['components'] as $up){
			if($up['status'] == 'update'){
				$view->jsondata = true;
				return;
			}
		}
		foreach($updates['themes'] as $up){
			if($up['status'] == 'update'){
				$view->jsondata = true;
				return;
			}
		}
	}


	/**
	 * Get the list of updates from remote repositories, (or session cache).
	 */
	public function getupdates() {
		$view = $this->getView();
		$req  = $this->getPageRequest();



		$components = UpdaterHelper::GetUpdates();

		// Allow filters to be set.
		if($req->getParameter('onlyupdates')){
			// Core not updatable? remove it.
			if($components['core']['status'] != 'update') unset($components['core']);

			// Check each component too.
			foreach($components['components'] as $c => $dat){
				if($dat['status'] != 'update') unset($components['components'][$c]);
			}

			// And the themes.
			foreach($components['themes'] as $c => $dat){
				if($dat['status'] != 'update') unset($components['themes'][$c]);
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


		$view->contenttype = View::CTYPE_JSON;
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
				\Core\set_message(GPG_HOMEDIR . ' does not exist and could not be created!  Please fix this before proceeding!', 'error');
			}
		}
		elseif(!is_writable(GPG_HOMEDIR)){
			\Core\set_message(GPG_HOMEDIR . ' is not writable!  Please fix this before proceeding!', 'error');
		}


		$sites = UpdateSiteModel::Find();

		$view->title = 't:STRING_MANAGE_REPOSITORIES';
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

		$form = \Core\Forms\Form::BuildFromModel($site);
		$form->set('action', \Core\resolve_link('/updater/repos/add'));
		$form->addElement('submit', array('value' => 'Next'));

		$view->title = 'Add Repo';
		// Needed because dynamic pages do not record navigation.
		$view->addBreadcrumb('Repositories', 'updater/repos');

		$view->assign('form', $form);

		if(!is_dir(GPG_HOMEDIR)){
			// Try to create it?
			if(is_writable(dirname(GPG_HOMEDIR))){
				// w00t
				mkdir(GPG_HOMEDIR);
			}
			else{
				\Core\set_message(GPG_HOMEDIR . ' does not exist and could not be created!  Please fix this before proceeding!', 'error');
				$form = null;
			}
		}
		elseif(!is_writable(GPG_HOMEDIR)){
			\Core\set_message(GPG_HOMEDIR . ' is not writable!  Please fix this before proceeding!', 'error');
			$form = null;
		}


		// This is the logic for step 2 (confirmation).
		// This is after all the template logic from step 1 because it will fallback to that form if necessary.
		if($request->isPost()){
			$url      = $request->getPost('model[url]');
			$username = $request->getPost('model[username]');
			$password = $request->getPost('model[password]');

			// Validate and standardize this repo url.
			// This is because most people will simply type corepl.us.
			if(strpos($url, '://') === false){
				$url = 'https://' . $url;
			}

			// Lookup that URL first!
			if(UpdateSiteModel::Count(array('url' => $url)) > 0){
				\Core\set_message($url . ' is already used!', 'error');
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
			$form = \Core\Forms\Form::BuildFromModel($model);
			$form->set('action', \Core\resolve_link('/updater/repos/add'));
			$form->addElement('submit', array('value' => 'Next'));
			$view->assign('form', $form);

			/** @var \Core\Filestore\Backends\FileRemote $remote */
			$remote = $model->getFile();

			if($remote->requiresAuthentication()){
				if(!$username){
					\Core\set_message($url . ' requires authentication!', 'error');
					return;
				}
				else{
					\Core\set_message('Invalid credentials for ' . $url, 'error');
					return;
				}
			}

			if(!$model->isValid()){
				\Core\set_message($url . ' does not appear to be a valid repository!', 'error');
				return;
			}

			$repo = new RepoXML();
			$repo->loadFromFile($remote);
			
			// Make sure the keys are good
			if(!$repo->validateKeys()){
				\Core\set_message('There were invalid/unpublished keys in the repo!  Refusing to import.', 'error');
				return;
			}

			// The very final bit of this logic is to look and see if there's a "confirm" present.
			// If there is, the user clicked accept on the second page and I need to go ahead and import the data.
			if($request->getPost('confirm')){
				$model->set('description', $repo->getDescription());
				$model->save();
				$keysimported = 0;
				$keycount     = sizeof($repo->getKeys());
				$gpg          = new \Core\GPG\GPG();

				foreach($repo->getKeys() as $keyData){
					if(!$keyData['installed']){
						try{
							if($keyData['contents']){
								// Local import! :)
								$gpg->importKey($keyData['contents']);
							}
							else{
								$gpg->importKey($keyData['key']);
							}
							++$keysimported;
						}
						catch(Exception $e){
							\Core\set_message('Unable to import key [' . $keyData['key'] . '] from keyserver!' . $e->getMessage(), 'error');
						}
					}
					else{
						// Flag already-imported keys as good.
						++$keysimported;
					}
				}

				if(!$keycount){
					\Core\set_message('Added repository site successfully!', 'success');
				}
				elseif($keycount != $keysimported){
					\Core\set_message('Added repository site, but unable to import ' . ($keycount-$keysimported) . ' key(s).', 'info');
				}
				else{
					\Core\set_message('Added repository site and imported ' . $keysimported . ' key(s) successfully!', 'success');
				}

				\core\redirect('/updater/repos');
			}

			$view->templatename = 'pages/updater/repos_add2.tpl';
			$view->assign('description', $repo->getDescription());
			$view->assign('keys', $repo->getKeys());
			$view->assign('url', $url);
			$view->assign('username', $username);
			$view->assign('password', $password);
		}
	}

	public function repos_edit() {
		$request = $this->getPageRequest();
		$view    = $this->getView();

		$site = UpdateSiteModel::Construct($request->getParameter(0));
		if(!$site->exists()){
			return View::ERROR_NOTFOUND;
		}

		$form = \Core\Forms\Form::BuildFromModel($site);
		$form->set('callsmethod', 'UpdaterController::_SaveRepo');
		$form->addElement('submit', array('value' => 'Update'));

		$view->title = 'Update Repo';
		// Needed because dynamic pages do not record navigation.
		$view->addBreadcrumb('Repositories', 'updater/repos');
		$view->assign('form', $form);
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
		\Core\set_message('Removed repository successfully', 'success');
		\core\redirect('/updater/repos');
	}

	/**
	 * Browse the repositories for a component, be it new or update.
	 *
	 * This is designed to give a syndicated list of ALL components in all available repos.
	 */
	public function browse() {
		$view = $this->getView();

		$sitecount = UpdateSiteModel::Count();

		if($sitecount == 0){
			\Core\set_message('Please add at least one repository before searching for new packages!', 'error');
			\core\redirect('/updater/repos/add');
		}

		$view->assign('sitecount', $sitecount);
		$view->title = 'Find New Packages';
	}

	/**
	 * View to manually upload a package to the system.
	 * 
	 * This shouldn't be used too often, but can be used for one-off packages that may not reside in a public repository.
	 */
	public function upload(){
		$request = $this->getPageRequest();
		$view = $this->getView();
		
		$form = new \Core\Forms\Form();
		$form->set('callsmethod', 'UpdaterController::_UploadHandler');
		$form->addElement(
			'file',
			[
				'name' => 'upload',
			    'title' => t('STRING_FILE'),
			    'description' => t('MESSAGE_UPLOAD_TGZ_TO_MANUALLY_INSTALL_PACKAGE'),
			    'required' => true,
			    //'accept' => ['application/pgp', 'application/gzip'],
			    'basedir' => '/tmp',
			]
		);
		$form->addElement('submit', ['value' => t('STRING_INSTALL')]);
		
		$view->title = 't:STRING_MANUALLY_UPLOAD_PACKAGE';
		$view->assign('form', $form);
	}

	public function keys() {
		$view = $this->getView();

		// Get the existing keys.
		$gpg = new \Core\GPG\GPG();
		$keys = $gpg->listKeys();
		
		$managerAvailable = Core::IsComponentAvailable('gpg-key-manager');

		$view->title = "GPG Keys";
		$view->assign('directory', GPG_HOMEDIR);
		$view->assign('keys', $keys);
		$view->assign('manager_available', $managerAvailable);
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
				exec('gpg --keyserver-options timeout=6 --homedir "' . GPG_HOMEDIR . '" --no-permission-warning --keyserver "hkp://pool.sks-keyservers.net" --recv-keys "' . $id . '"', $output, $result);
				if($result != 0){
					$error = 'Unable to lookup ' . $id . ' from keyserver.';
				}
				else{
					\core\redirect('/updater/keys');
				}
			}
			elseif($_POST['pubkey']){
				$tmp = \Core\Filestore\Factory::File('tmp/importkey-' . Core::RandomHex(2) . '.gpg');
				$tmp->putContents($_POST['pubkey']);
				exec('gpg --homedir "' . GPG_HOMEDIR . '" --no-permission-warning --import "' . $tmp->getFilename() . '"', $output, $result);
				$tmp->delete();
				if($result != 0){
					$error = 'Unable to import requested key.';
				}
				else{
					\core\redirect('/updater/keys');
				}
			}
			elseif($_FILES['pubkeyfile']){
				$tmp = \Core\Filestore\Factory::File($_FILES['pubkeyfile']['tmp_name']);
				$tmp->putContents($_POST['pubkey']);
				exec('gpg --homedir "' . GPG_HOMEDIR . '" --no-permission-warning --import "' . $tmp->getFilename() . '"', $output, $result);
				$tmp->delete();
				if($result != 0){
					$error = 'Unable to import requested key.';
				}
				else{
					\core\redirect('/updater/keys');
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
			\Core\set_message('Unable to remove key ' . $key, 'error');
		}
		\core\redirect('/updater/keys');
	}


	/**
	 * Page that is called to disable a given component.
	 *
	 * Performs all the necessary checks before disable, ie: dependencies from other components.
	 */
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
			// I want to record a list of actual changes performed.
			$changes = array();

			foreach($todisable as $c){
				$changes[] = 'Disabling component ' . $c;
				$change = Core::GetComponent($c)->disable();
				if(is_array($change)) $changes = array_merge($changes, $change);
			}

			$logmsg = implode("\n", $changes);
			SystemLogModel::LogSecurityEvent(
				'/updater/component/disabled',
				$logmsg
			);
		}

		// Yeah I know json "changes" isn't actually $changes.... STFU.
		$view->jsondata = array('changes' => $todisable, 'dryrun' => $dryrun);
	}

	/**
	 * Page that is called to enable a given component.
	 *
	 * Performs all the necessary checks before enable, ie: dependencies from other components.
	 */
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
			// I want to record a list of actual changes performed.
			$changes = array();

			$changes[] = 'Enabling component ' . $c->getName();
			$change = $c->enable();
			if(is_array($change)) $changes = array_merge($changes, $change);

			$logmsg = implode("\n", $changes);
			SystemLogModel::LogSecurityEvent(
				'/updater/component/enabled',
				$logmsg
			);
		}

		$view->jsondata = array('changes' => array($name), 'dryrun' => $dryrun);
	}

	/**
	 * Admin page to kick off the installation or upgrade of components.
	 */
	public function component_install() {
		$view = $this->getView();
		$req = $this->getPageRequest();

		$name    = $req->getParameter(0);
		$version = $req->getParameter('version');

		$this->_performInstall('components', $name, $version);
	}

	/**
	 * Admin page to kick off the installation or upgrade of themes.
	 */
	public function theme_install() {
		$view = $this->getView();
		$req  = $this->getPageRequest();

		$name    = $req->getParameter(0);
		$version = $req->getParameter('version');

		$this->_performInstall('themes', $name, $version);
	}

	/**
	 * Admin page to kick off the installation or upgrade of the core.
	 */
	public function core_install() {
		$view = $this->getView();
		$req  = $this->getPageRequest();

		$version = $req->getParameter('version');

		$this->_performInstall('core', 'core', $version);
	}

	/**
	 * Admin page to do exactly as it states; update everything possible.
	 */
	public function update_everything(){
		$view = $this->getView();
		$req  = $this->getPageRequest();

		if(!$req->isPost()){
			return View::ERROR_BADREQUEST;
		}

		$view->mode = View::MODE_NOOUTPUT;
		$view->render();

		\Core\CLI\CLI::PrintHeader('Loading Updates');
		Core\CLI\CLI::PrintProgressBar(2);
		$everything = UpdaterHelper::GetUpdates();
		$updates = [];

		// Core not updateable? remove them.
		if($everything['core']['status'] == 'update'){
			$updates[] = $everything['core'];
		}

		// Check each component too.
		foreach($everything['components'] as $c => $dat){
			if($dat['status'] == 'update'){
				$updates[] = $everything['components'][$c];
			}
		}

		// And the themes.
		foreach($everything['themes'] as $c => $dat){
			if($dat['status'] == 'update'){
				$updates[] = $everything['themes'][$c];
			}
		}
		Core\CLI\CLI::PrintProgressBar(5);

		// Calculate the progress amount for each iteration based on the number of total updates to apply.
		$progressEa = '+' . (95 / (sizeof($updates) * 2));

		\Core\CLI\CLI::PrintHeader('Installing ' . sizeof($updates) . ' Updates');
		foreach($updates as $thing){
			try{
				\Core\CLI\CLI::PrintLine('Installing ' . $thing['type'] . ' ' . $thing['name'] . ' ' . $thing['version']);

				$return = UpdaterHelper::PerformInstall($thing['type'], $thing['name'], $thing['version'], true, false);
				Core\CLI\CLI::PrintProgressBar($progressEa);

				if($return['status'] == 1){
					// Good to install!
					$return = UpdaterHelper::PerformInstall($thing['type'], $thing['name'], $thing['version'], false, false);
					Core\CLI\CLI::PrintProgressBar($progressEa);

					if($return['status'] != 1){
						\Core\CLI\CLI::PrintWarning($return['message']);
					}
				}
				else{
					\Core\CLI\CLI::PrintWarning($return['message']);
				}
			}
			catch(Exception $e){
				\Core\CLI\CLI::PrintError($e->getMessage());
			}
		}
	}


	/**
	 * Helper function called by the *_install views.
	 *
	 * @param $type
	 * @param $name
	 * @param $version
	 */
	private function _performInstall($type, $name, $version){
		$view = $this->getView();
		$req  = $this->getPageRequest();

		$dryrun  = $req->getParameter('dryrun');
		$verbose = $req->getParameter('verbose');
		$nl      = "<br/>\n";

		// For standard calls, this is a json-only page.
		// verbose runs are html however.
		if($verbose){
			$view->contenttype = View::CTYPE_HTML;
			$view->mode = View::MODE_NOOUTPUT;
		}
		else{
			$view->contenttype = View::CTYPE_JSON;
		}

		// This is a post-only page!
		if(!$req->isPost()){
			$view->error = View::ERROR_BADREQUEST;
			return;
		}

		$return = UpdaterHelper::PerformInstall($type, $name, $version, $dryrun, $verbose);


		// If it's not a dry run, record a log of this action!
		if(!$dryrun){
			if($return['status']){
				$logmsg = 'Installation of ' . $type . ' ' . $name . ' ' . $version . ' succeeded!' . "\n" . $return['message'];
				if(isset($return['changes'])){
					foreach($return['changes'] as $change){
						$logmsg .= "\n" . $change;
					}
				}
				$logstatus = 'success';
			}
			else{
				$logmsg = 'Installation of ' . $type . ' ' . $name . ' ' . $version . ' failed due to' . "\n" . $return['message'];
				$logstatus = 'fail';
			}

			SystemLogModel::LogSecurityEvent(
				'/updater/installation',
				$logmsg
			);
		}


		if($verbose){
			if(!$return['status']){
				echo $nl . '[===========  RESULTS  ===========]' . $nl;
				echo '[ERROR] - ' . $return['message'] . $nl;
			}
			else{
				echo $nl . '[===========  RESULTS  ===========]' . $nl;
				if(isset($return['changes'])){
					foreach($return['changes'] as $change){
						echo '[INFO] - ' . $change . $nl;
					}
				}
				echo '[SUCCESS] - Performed all operations successfully!' . $nl;
			}
			echo '<div id="results" style="display:none;" status="' . $return['status'] . '">' . $return['message'] . '</div>';
		}
		else{
			$view->jsondata = $return;
		}

	}

	public static function _SaveRepo(\Core\Forms\Form $form) {
		try{
			$model = $form->getModel();
			$model->save();
		}
		catch(Exception $e){
			\Core\set_message($e->getMessage(), 'error');
			return false;
		}

		\Core\set_message('Updated repository successfully', 'success');
		return '/updater/repos';
	}

	/**
	 * Call to check for updates as part of the health checking system in Core.
	 * 
	 * @return array
	 */
	public static function _HealthCheckHook(){
		// SERVER_ADMIN_EMAIL
		
		$checks      = [];
		$updateSites = UpdateSiteModel::Find();
		try{
			$updates = UpdaterHelper::GetUpdates();
		}
		catch (Exception $ex) {
			$checks[] = \Core\HealthCheckResult::ConstructError($ex->getMessage(), null, null);
			$updates = [
				'components' => [],
				'themes' => [],
			];
		}
		
		
		// Scan through the update sites and ensure that they are available and set.
		if(!sizeof($updateSites)){
			$checks[] = \Core\HealthCheckResult::ConstructWarn(
				t('STRING_CHECK_UPDATER_NO_UPDATE_SITES'),
				t('MESSAGE_WARNING_UPDATER_NO_UPDATE_SITES'),
				'/updater'
			);
		}
		else{
			foreach($updateSites as $site){
				/** @var UpdateSiteModel $site */
				if($site->isValid()){
					$checks[] = \Core\HealthCheckResult::ConstructGood(
						t('STRING_SUCCESS_UPDATER_SITE_S_OK', $site->get('url')),
						t('MESSAGE_SUCCESS_UPDATER_SITE_S_OK', $site->get('url'))
					);
				}
				else{
					$checks[] = \Core\HealthCheckResult::ConstructError(
						t('STRING_ERROR_UPDATER_SITE_S_OK', $site->get('url')),
						t('MESSAGE_ERROR_UPDATER_SITE_S_OK', $site->get('url')),
						'/updater'
					);
				}
			}
		}
		
		if(isset($updates['core'])){
			// This should always be set, but who knows...
			if($updates['core']['status'] == 'update'){
				$checks[] = \Core\HealthCheckResult::ConstructWarn(
					t('STRING_WARNING_UPDATER_CORE_OUTDATED'),
					t('MESSAGE_WARNING_UPDATER_CORE_OUTDATED_S_AVAILABLE', $updates['core']['version']),
					'/updater'
				);
			}
			elseif($updates['core']['status'] == 'installed'){
				$checks[] = \Core\HealthCheckResult::ConstructGood(
					t('STRING_SUCCESS_UPDATER_CORE_OUTDATED'),
					t('MESSAGE_SUCCESS_UPDATER_CORE_OUTDATED_S_AVAILABLE', $updates['core']['version'])
				);
			}
		}
		
		foreach($updates['components'] as $dat){
			if($dat['status'] == 'update'){
				$checks[] = \Core\HealthCheckResult::ConstructWarn(
					t('STRING_WARNING_UPDATER_COMPONENT_S_OUTDATED', $dat['title']),
					t('MESSAGE_WARNING_UPDATER_COMPONENT_S_OUTDATED_S_AVAILABLE', $dat['title'], $dat['version']),
					'/updater'
				);
			}
			elseif($dat['status'] == 'installed'){
				$checks[] = \Core\HealthCheckResult::ConstructGood(
					t('STRING_SUCCESS_UPDATER_COMPONENT_S_OUTDATED', $dat['title']),
					t('MESSAGE_SUCCESS_UPDATER_COMPONENT_S_OUTDATED_S_AVAILABLE', $dat['title'], $dat['version'])
				);
			}
		}

		foreach($updates['themes'] as $dat){
			if($dat['status'] == 'update'){
				$checks[] = \Core\HealthCheckResult::ConstructWarn(
					t('STRING_WARNING_UPDATER_THEME_S_OUTDATED', $dat['title']),
					t('MESSAGE_WARNING_UPDATER_THEME_S_OUTDATED_S_AVAILABLE', $dat['title'], $dat['version']),
					'/updater'
				);
			}
			elseif($dat['status'] == 'installed'){
				$checks[] = \Core\HealthCheckResult::ConstructGood(
					t('STRING_SUCCESS_UPDATER_THEME_S_OUTDATED', $dat['title']),
					t('MESSAGE_SUCCESS_UPDATER_THEME_S_OUTDATED_S_AVAILABLE', $dat['title'], $dat['version'])
				);
			}
		}
		
		return $checks;
	}

	public static function _UploadHandler(\Core\Forms\Form $form) {
		$localfile = \Core\Filestore\Factory::File($form->getElement('upload')->get('value'));
		$localobj = $localfile->getContentsObject();
		if(!$localobj instanceof Core\Filestore\Contents\ContentTGZ){
			$localfile->delete();
			\Core\set_message('Invalid file uploaded', 'error');
			return false;
		}
		
		$tmpdir = $localobj->extract('tmp/installer-' . Core::RandomHex(4));
		
		// There should now be a package.xml metafile inside that temporary directory.
		// Parse it to get the necessary information for this package.
		$metafile = \Core\Filestore\Factory::File($tmpdir->getPath() . 'package.xml');
		if(!$metafile->exists()){
			$localfile->delete();
			$tmpdir->delete();
			\Core\set_message('Invalid package, package does not contain a "package.xml" file.');
			return false;
		}
		
		$pkg     = new PackageXML($metafile->getFilename());
		$key     = str_replace(' ', '-', strtolower($pkg->getName()));
		$name    = $pkg->getName();
		$type    = $pkg->getType();
		$version = $pkg->getVersion();
		
		// Validate the contents of the package.
		if(!(
			$type == 'component' ||
			$type == 'theme' ||
			$type == 'core'
		)){
			$localfile->delete();
			$tmpdir->delete();
			\Core\set_message('Invalid package, package does not appear to be a valid Core package.');
			return false;
		}

		// Now that the data is extracted in a temporary directory, extract every file in the destination.
		/** @var $datadir \Core\Filestore\Directory */
		$datadir = $tmpdir->get('data/');
		if(!$datadir){
			\Core\set_message('Invalid package, package does not contain a "data" directory.');
			return false;
		}
		
		if($type == 'component'){
			$destdir = ROOT_PDIR . 'components/' . $key . '/';
		}
		elseif($type == 'theme'){
			$destdir = ROOT_PDIR . 'themes/' . $key . '/';
		}
		else{
			$destdir = ROOT_PDIR . '/';
		}

		try{
			// Will give me an array of Files in the data directory.
			$files = $datadir->ls(null, true);
			// Used to get the relative path for each contained file.
			$datalen = strlen($datadir->getPath());
			foreach($files as $file){
				if(!$file instanceof \Core\Filestore\Backends\FileLocal) continue;

				// It's a file, copy it over.
				// To do so, resolve the directory path inside the temp data dir.
				$dest = \Core\Filestore\Factory::File($destdir . substr($file->getFilename(), $datalen));
				/** @var $dest \Core\Filestore\Backends\FileLocal */
				$dest->copyFrom($file, true);
			}
		}
		catch(Exception $e){
			// OH NOES!
			$localfile->delete();
			$tmpdir->delete();
			\Core\set_message($e->getMessage(), 'error');
			return false;
		}
		
		
		// Cleanup everything
		$localfile->delete();
		$tmpdir->delete();

		// Clear the cache so the next pageload will pick up on the new components and goodies.
		\Core\Cache::Flush();
		\Core\Templates\Backends\Smarty::FlushCache();
		
		// Print a nice message to the user that it completed.
		\Core\set_message('Successfully installed ' . $name . ' ' . $version, 'success');
		return '/updater';
	}
}
