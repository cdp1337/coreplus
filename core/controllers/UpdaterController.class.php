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

		$sitecount = UpdateSiteModel::Count('enabled = 1');
		$components = array();
		foreach(Core::GetComponents() as $k => $c){
			// Skip the core.
			if($k == 'core') continue;

			$components[$k] = $c;
		}

		$themes = ThemeHandler::GetAllThemes();

		$view->title = 'System Updater';
		$view->addControl('Manage Repos', '/updater/repos', 'settings');
		$view->assign('sitecount', $sitecount);
		$view->assign('components', $components);
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

		$view->contenttype = View::CTYPE_JSON;

		$components = UpdaterHelper::GetUpdates();

		$view->jsondata = $components;
	}

	/**
	 * Sites listing controller, displays all update sites and links to manage them.
	 *
	 */
	public function repos() {
		$view = $this->getView();
		// @todo List the sites currently installed/configured/etc.

		$sites = UpdateSiteModel::Find();

		$view->title = 'Repositories';
		$view->addControl('Add Repo', 'updater/repos/add', 'add');

		$view->assign('sites', $sites);

	}

	public function repos_edit() {
		$view    = $this->getView();
		$request = $this->getPageRequest();

		// Make sure the site exists.
		$siteid = $request->getParameter(0);
		if (!$siteid) {
			return View::ERROR_NOTFOUND;
		}

		$site = new UpdateSiteModel($siteid);
		if (!$site->exists()) {
			return View::ERROR_NOTFOUND;
		}

		$form = Form::BuildFromModel($site);
		$form->set('callsmethod', 'UpdaterController::_Sites_Update');
		$form->addElement('submit', array('value' => 'Update Repo'));

		$view->title = 'Edit Site';
		// Needed because dynamic pages do not record navigation.
		$view->addBreadcrumb('Repositories', 'Updater/Sites');

		$view->addControl('Add Repo', 'updater/repos/add', 'add');

		$view->assign('form', $form);
	}

	public function repos_add() {
		$view = $this->getView();

		$site = new UpdateSiteModel();

		$form = Form::BuildFromModel($site);
		$form->set('callsmethod', 'UpdaterController::_Sites_Update');
		$form->addElement('submit', array('value' => 'Add Repo'));

		$view->title = 'Add Repo';
		// Needed because dynamic pages do not record navigation.
		$view->addBreadcrumb('Repositories', 'updater/repos');

		$view->assign('form', $form);
	}

	public function component_install() {
		$view = $this->getView();
		$req = $this->getPageRequest();

		$components = UpdaterHelper::GetUpdates();

		$name    = $req->getParameter(0);
		$version = $req->getParameter(1);
		$dryrun  = $req->getParameter('dryrun');

		$status = UpdaterHelper::Install($name, $version, $dryrun);

		// This is a json-enabled page.
		if ($view->request['contenttype'] == View::CTYPE_JSON) {
			$view->jsondata = $status;
			return;
		}

		// Standard HTML page.
		if ($status['status']) {
			$type = 'success';
		}
		else {
			$type = 'error';
		}

		Core::SetMessage($status['message'], $type);
		Core::Redirect('/Updater/Check');
	}

	public function component_disable() {
		$view = $this->getView();
		$req = $this->getPageRequest();

		// This is a post-only page!
		/*if(!$req->isPost()){
			$view->error = View::ERROR_BADREQUEST;
			return;
		}*/

		$name    = $req->getParameter(0);
		$dryrun  = $req->getParameter('dryrun');
		$c = Core::GetComponent($name);

		if(!$c){
			$view->error = View::ERROR_NOTFOUND;
			return;
		}

		// Create a reverse map of what components are the basis of which components, this will make it easier
		// to do the necessary mapping.
		$reverse_requirements = array();

		foreach(Core::GetComponents() as $k => $ccheck){
			$requires = $ccheck->getRequires();
			if(!sizeof($requires)){
				unset($tocheck[$k]);
				continue;
			}
			var_dump($ccheck->getName(), $ccheck->getRequires());
		}

		// Run through every component and see if there's a conflicting requirement.
		// These must be disabled too!
		$provides = $c->getProvides();

		$tocheck = Core::GetComponents();

		foreach($tocheck as $k => $ccheck){
			$requires = $ccheck->getRequires();
			if(!sizeof($requires)){
				unset($tocheck[$k]);
				continue;
			}
			var_dump($ccheck->getName(), $ccheck->getRequires());
		}
		var_dump($provides); die();
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
