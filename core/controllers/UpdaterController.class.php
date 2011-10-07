<?php
/**
 * Description of UpdaterController
 *
 * @author powellc
 */
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
		
		$view->title = 'Updater';
		$view->assign('sitecount', $sitecount);
	}
	
	/**
	 * Check for updates controller
	 * 
	 * @param View $view 
	 */
	public static function Check(View $view){
		
		$corevers = Core::GetComponent()->getVersion();
		
		// Build a list of components currently installed, this will act as a base.
		$components = array();
		foreach(ComponentHandler::GetAllComponents() as $c){
			$n = strtolower($c->getName());
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
			);
		}
		
		// Now, look up components from all the updates sites.
		$updatesites = UpdateSiteModel::Find('enabled = 1');
		foreach($updatesites as $site){
			
			$file = new File_remote_backend($site->get('url'));
			$file->username = $site->get('username');
			$file->password = $site->get('password');
			
			$repoxml = new RepoXML();
			$repoxml->loadFromFile($file);
			$rootpath = dirname($site->get('url')) . '/';
			foreach($repoxml->getPackages() as $pkg){
				// Already installed and is up to date, don't do anything.
				//if($pkg->isCurrent()) continue;
				
				$n = strtolower($pkg->getName());
				
				// Check and see if this version is already listed in the repo.
				if(!isset($components[$n][$pkg->getVersion()])){
					$components[$n][$pkg->getVersion()] = array(
						'name' => $n,
						'title' => $pkg->getName(),
						'version' => $pkg->getVersion(),
						'source' => 'repo-' . $site->get('id'),
						'description' => $pkg->getDescription(),
						'provides' => $pkg->getProvides(),
						'requires' => $pkg->getRequires(),
						'location' => $rootpath . $pkg->getFileLocation(),
					);
				}		
				
				//var_dump($pkg->asPrettyXML());
			}
		}
		
		var_dump($components['jquery-full']); die();
		
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
		$view->addBreadcrumb('Updater', 'Updater');
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
		$view->addBreadcrumb('Updater', 'Updater');
		$view->addBreadcrumb('Sites', 'Updater/Sites');
		
		$view->assign('form', $form);
	}
	
	
	
	public static function _Sites_Update(Form $form){
		$form->getModel()->save();
		
		return 'Updater/Sites';
	}
	
}
