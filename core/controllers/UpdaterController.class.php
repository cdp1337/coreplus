<?php
/**
 * [PAGE DESCRIPTION HERE]
 *
 * @package Core Plus\Core
 * @author Charlie Powell <powellc@powelltechs.com>
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

// @todo Fix this page... it doesn't work with the new 2.1 api :/


class UpdaterController extends Controller {
	
	public static $AccessString = 'g:admin';
	
	/**
	 * Listing controller of the updater.
	 * 
	 * @param View $view 
	 */
	public static function Index(View $view){
		// @todo Display update statistics here, ie: "An update is available!"
		// or at very least a link to check for updates.
		
		$sitecount = UpdateSiteModel::Count('enabled = 1');
		
		$view->title = 'System Updater';
		$view->assign('sitecount', $sitecount);
	}
	
	/**
	 * Check for updates controller
	 * 
	 * @param View $view 
	 */
	public static function Check(View $view){
		$view->title = 'Check for Updates';
		
		$view->addBreadcrumb('System Updater', 'Updater');
	}
	
	
	/**
	 * Get the list of updates from remote repositories, (or session cache).
	 * 
	 * @param View $view 
	 */
	public static function Getupdates(View $view){
		
		// This is an ajax/json-only page.
		if($view->request['contenttype'] != View::CTYPE_JSON){
			Core::Redirect('/Updater/Check');
		}
		
		$components = UpdaterHelper::GetUpdates();
		
		$view->jsondata = $components;
	}
	
	/**
	 * Sites listing controller, displays all update sites and links to manage them.
	 * 
	 * @param View $view 
	 */
	public static function Sites(View $view){
		// @todo List the sites currently installed/configured/etc.
		
		$sites = UpdateSiteModel::Find();
		
		$view->title = 'Sites';
		$view->addBreadcrumb('Updater', 'Updater');
		
		$view->addControl('Add Site', 'Updater/Sites/Add', 'add');
		
		$view->assign('sites', $sites);
		
	}
	
	public static function Sites_Edit(View $view){
		// Make sure the site exists.
		$siteid = $view->getParameter(0);
		if(!$siteid){
			return View::ERROR_NOTFOUND;
		}
		
		$site = new UpdateSiteModel($siteid);
		if(!$site->exists()){
			return View::ERROR_NOTFOUND;
		}
		
		$form = Form::BuildFromModel($site);
		$form->set('callsmethod', 'UpdaterController::_Sites_Update');
		$form->addElement('submit', array('value' => 'Update Site'));
		
		$view->title = 'Edit Site';
		$view->addBreadcrumb('System Updater', 'Updater');
		$view->addBreadcrumb('Sites', 'Updater/Sites');
		
		$view->addControl('Add Site', 'Updater/Sites/Add', 'add');
		
		$view->assign('form', $form);
	}
	
	public static function Sites_Add(View $view){
		$site = new UpdateSiteModel();
		
		$form = Form::BuildFromModel($site);
		$form->set('callsmethod', 'UpdaterController::_Sites_Update');
		$form->addElement('submit', array('value' => 'Add Site'));
		
		$view->title = 'Add Site';
		$view->addBreadcrumb('System Updater', 'Updater');
		$view->addBreadcrumb('Sites', 'Updater/Sites');
		
		$view->assign('form', $form);
	}
	
	public static function Install(View $view){
		$components = UpdaterHelper::GetUpdates();
		
		$name = $view->getParameter(0);
		$version = $view->getParameter(1);
		$dryrun = $view->getParameter('dryrun');
		
		$status = UpdaterHelper::Install($name, $version, $dryrun);
		
		// This is a json-enabled page.
		if($view->request['contenttype'] == View::CTYPE_JSON){
			$view->jsondata = $status;
			return;
		}
		
		// Standard HTML page.
		if($status['status']){
			$type = 'success';
		}
		else{
			$type = 'error';
		}
		
		Core::SetMessage($status['message'], $type);
		Core::Redirect('/Updater/Check');
	}
	
	
	public static function _Sites_Update(Form $form){
		$form->getModel()->save();
		
		// Will be useful for importing new keys.
		// gpg --homedir . --no-permission-warning --keyserver x-hkp://pool.sks-keyservers.net --recv-keys B2BEDCCB
		
		return 'Updater/Sites';
	}
	
}
