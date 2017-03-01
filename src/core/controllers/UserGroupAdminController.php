<?php
/**
 * File for the class UserGroupAdminController
 *
 * @package Core\User
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

/**
 * Group admin controller for the User system.
 *
 * @package Core\User
 */
class UserGroupAdminController extends Controller_2_1{
	public function __construct(){
		$this->accessstring = 'p:/user/groups/manage';
	}

	public function index(){

		$view = $this->getView();

		$permissionmanager = \Core\user()->checkAccess('p:/user/permissions/manage');
		
		$factory = new ModelFactory('UserGroupModel');

		if(Core::IsComponentAvailable('multisite') && MultiSiteHelper::IsEnabled()){
			if(MultiSiteHelper::GetCurrentSiteID()){
				// Child site, only display global and site-specific sites.
				$w = new \Core\Datamodel\DatasetWhereClause();
				$w->setSeparator('or');
				$w->addWhere('site = ' . MultiSiteHelper::GetCurrentSiteID());
				$w->addWhere('site = -1');
				$factory->where($w);

				$displayglobal = true;
				$multisite = false;
			}
			else {
				// Root site, display all groups across all sites.
				$factory->where('site != -2');
				$displayglobal = false;
				$multisite = true;
			}
			$site = MultiSiteHelper::GetCurrentSiteID();
		}
		else{
			$displayglobal = false;
			$multisite = false;
			$site = null;
		}

		$factory->order('name');
		$groups = $factory->get();

		$view->title = 'User Group Administration';
		$view->assign('groups', $groups);
		$view->assign('permissionmanager', $permissionmanager);
		$view->assign('display_global', $displayglobal);
		$view->assign('site', $site);
		$view->assign('multisite', $multisite);
		$view->addControl('Add Group', '/usergroupadmin/create', 'add');
	}

	public function create(){
		$view  = $this->getView();
		$req   = $this->getPageRequest();
		$model = new UserGroupModel();



		$contextnames = [];
		$contexts     = [];
		$usecontexts  = false;
		$isadmin      = \Core\user()->checkAccess('g:admin');

		$form  = \Core\Forms\Form::BuildFromModel($model);
		$form->set('callsmethod', 'UserGroupAdminController::_UpdateFormHandler');

		if(Core::IsComponentAvailable('multisite') && MultiSiteHelper::IsEnabled()){
			$siteid = MultiSiteHelper::GetCurrentSiteID();

			if(!$isadmin || $siteid){
				// If the user is currently on a site, this group is locked to that site.
				// This also applies if the user is not a super admin.
				$model->set('site', $siteid);
			}
			else{
				// Otherwise, the user has a choice if this is a global or local group.
				$form->switchElementType('model[site]', 'select');
				$form->getElement('model[site]')->setFromArray(
					[
						'title' => 'Site Scope',
						'options' => MultiSiteModel::GetAllAsOptions(),
						//'options' => ['-1' => 'Global Scope', '0' => 'Local Scope'],
						'description' => 'A globally-scoped group is visible across every site.'
					]
				);
			}
		}

		if(\Core\user()->checkAccess('p:/user/permissions/manage')){

			foreach(Core::GetPermissions() as $key => $data){
				$ckey = $data['context'];
				$ctitle = ($ckey == '') ? '** Global Context Permissions **' : $ckey . ' Context Permissions';

				$contextnames[$ckey] = $ctitle;
				$contexts[$key] = $ckey;
			}

			if(sizeof($contextnames) > 1){
				$form->getElement('model[context]')->set('options', $contextnames);

				$usecontexts = true;
			}
			else{
				$form->removeElement('model[context]');
			}

			$this->_setPermissionsToForm($form, $model);
		}
		else{
			$form->removeElement('model[context]');
		}
		$form->addElement('submit', array('value' => 'Create'));

		$view->templatename = 'pages/usergroupadmin/create_update.tpl';
		$view->title = 'Create Group ';
		$view->assign('model', $model);
		$view->assign('form', $form);
		$view->assign('context_json', json_encode($contexts));
		$view->assign('use_contexts', $usecontexts);
	}

	public function update(){
		$view          = $this->getView();
		$req           = $this->getPageRequest();
		$id            = $req->getParameter(0);
		$model         = new UserGroupModel($id);
		$form          = \Core\Forms\Form::BuildFromModel($model);
		$contextnames  = [];
		$contexts      = [];
		$usecontexts   = false;
		$contextlocked = (sizeof($model->getLink('UserUserGroup')) > 0);
		$isadmin       = \Core\user()->checkAccess('g:admin');

		if(!$model->exists()){
			return View::ERROR_NOERROR;
		}

		if(Core::IsComponentAvailable('multisite') && MultiSiteHelper::IsEnabled()){
			$siteid = MultiSiteHelper::GetCurrentSiteID();

			if($isadmin){
				$form->switchElementType('model[site]', 'select');
				$form->getElement('model[site]')->setFromArray(
					[
						'title' => 'Site Scope',
						'options' => MultiSiteModel::GetAllAsOptions(),
						//'options' => ['-1' => 'Global Scope', '0' => 'Local Scope'],
						'description' => 'A globally-scoped group is visible across every site.'
					]
				);
			}
		}

		if(\Core\user()->checkAccess('p:/user/permissions/manage')){
			// The user has access to manage user permissions.... the enclosed is that full logic for edits.

			foreach(Core::GetPermissions() as $key => $data){
				$ckey = $data['context'];
				$ctitle = ($ckey == '') ? '** Global Context Permissions **' : $ckey . ' Context Permissions';

				$contextnames[$ckey] = $ctitle;
				$contexts[$key] = $ckey;
			}

			if(sizeof($contextnames) > 1){
				if(!$contextlocked){
					// There are contexts defined and this group has not yet been added to a user's profile,
					// editing of the context is still permitted.
					$form->getElement('model[context]')->set('options', $contextnames);
					$usecontexts = true;
				}
				else{
					// This group has been added to a user's profile, display a message that it's locked!
					\Core\set_message('This group has been added to a user account and therefore has been locked to the ' . $contextnames[$model->get('context')]);
					$form->switchElementType('model[context]', 'hidden');
					$usecontexts = true;
				}
			}
			else{
				$form->removeElement('model[context]');
			}

			$this->_setPermissionsToForm($form, $model);
		}
		else{
			// No permission edit access, just remove the context options too!
			$form->removeElement('model[context]');
		}

		// If this group is locked and not a global group, it cannot be defined as a default group!
		if($contextlocked && $model->get('context') != ''){
			$form->removeElement('model[default]');
		}

		$form->set('callsmethod', 'UserGroupAdminController::_UpdateFormHandler');
		$form->addElement('submit', array('value' => 'Update'));

		$view->templatename = 'pages/usergroupadmin/create_update.tpl';
		$view->title = 'Update Group ' . $model->get('name');
		$view->assign('model', $model);
		$view->assign('form', $form);
		$view->assign('context_json', json_encode($contexts));
		$view->assign('use_contexts', $usecontexts);
	}

	public function delete(){
		$view  = $this->getView();
		$req   = $this->getPageRequest();
		$id    = $req->getParameter(0);
		$model = new UserGroupModel($id);

		if(!$req->isPost()){
			return View::ERROR_BADREQUEST;
		}

		if(Core::IsComponentAvailable('multisite') && MultiSiteHelper::IsEnabled()){
			$where['site'] = MultiSiteHelper::GetCurrentSiteID();
		}

		$model->delete();
		\Core\set_message('Removed group successfully', 'success');
		\core\redirect('/usergroupadmin');
	}
	
	/**
	 * Admin page to render a report view of the current groups and all users attached to those groups.
	 */
	public function report(){
		$view = $this->getView();

		$permissionmanager = \Core\user()->checkAccess('p:/user/permissions/manage');
		
		$factory = new ModelFactory('UserGroupModel');

		if(Core::IsComponentAvailable('multisite') && MultiSiteHelper::IsEnabled()){
			if(MultiSiteHelper::GetCurrentSiteID()){
				// Child site, only display global and site-specific sites.
				$w = new \Core\Datamodel\DatasetWhereClause();
				$w->setSeparator('or');
				$w->addWhere('site = ' . MultiSiteHelper::GetCurrentSiteID());
				$w->addWhere('site = -1');
				$factory->where($w);

				$displayglobal = true;
				$multisite = false;
			}
			else {
				// Root site, display all groups across all sites.
				$factory->where('site != -2');
				$displayglobal = false;
				$multisite = true;
			}
			$site = MultiSiteHelper::GetCurrentSiteID();
		}
		else{
			$displayglobal = false;
			$multisite = false;
			$site = null;
		}

		$factory->order('name');
		$groups = $factory->get();
		$data = [];
		
		// For each group, give me all the users attached to that group.
		foreach($groups as $g){
			/** @var $g UserGroupModel */
			
			$uugs = $g->getLink('UserUserGroup');
			$users = [];
			foreach($uugs as $uug){
				$users[] = $uug->getLink('User');
			}
			
			$data[] = [
				'group' => $g,
				'users' => $users
			];
		}
		
		$view->title = 't:STRING_USER_GROUP_REPORT';
		$view->assign('data', $data);
	}

	/**
	 * Set the site permissions to a given Form object.
	 *
	 * Used by the create and update pages.
	 *
	 * @param Form $form
	 * @param UserGroupModel $model
	 */
	private function _setPermissionsToForm(\Core\Forms\Form $form, UserGroupModel $model){
		// Everything gets added to the 'Everything' group.
		$everything = new \Core\Forms\TabsGroup([
			'title' => t('STRING_PERMISSIONS'),
		]);
		
		$flatList = [];
		$elementList = [];
		
		// Flatten the permissions to a simple array
		$permissions = array_keys(Core::GetPermissions());
		
		foreach($permissions as $perm){
			$exploded = explode('/', substr($perm, 1));
			
			// Build the name for this permission group.
			$last = array_pop($exploded);
			
			// The name will be everything sans the last element.
			$title = 't:STRING_PERMISSION_' . strtoupper(implode('_', $exploded));
			$id = 'permission-' . implode('-', $exploded);

			if(!isset($elementList[$id])){
				$elementList[$id] = new \Core\Forms\CheckboxesInput([
					'title' => $title,
					'id' => $id,
					'name' => 'permissions',
					'options' => [],
					'value' => $model->getPermissions(),
				]);

				$everything->addElement($elementList[$id]);
			}

			// Append the title and key
			$title .= '_' . strtoupper($last);

			$opts = $elementList[$id]->get('options');
			$opts[$perm] = $title;
			$elementList[$id]->set('options', $opts);
		}
		
		$form->addElement($everything);
		return;
		
		foreach(Core::GetPermissions() as $key => $data){
			$last = null;
			if($key{0} == '/'){
				// Create nested groups for this permission.
				// This makes management easier as they'll be grouped by the parent permission.
				// eg: /blah/foo/thing will be grouped under Blah -> Foo
				$exploded = explode('/', substr($key, 1));
				$len = sizeof($exploded);
				$title = 't:STRING_PERMISSION';
				$keyname = '';
				for($i = 0; $i+2<$len; $i++){
					
					// Shortcut to this element
					$k = $exploded[$i];
					
					// Append the title and key
					$title .= '_' . strtoupper($k);
					$keyname .= '/' . $k;
					
					if(!isset($everything[$keyname])){
						$everything[$keyname] = new \Core\Forms\FormGroup([
							'title' => $title,
						]);
					}
					
					if($last === null){
						$grouped[] = $everything[$keyname];
					}
					else{
						$last->addElement($everything[$keyname]);
					}
					
					$last =& $everything[$keyname];
				}
				
				var_dump($last);
				
				
				/*// Add this element to the last group
				$k = $e[ $l-1 ];
				$g['elements'][ $key ] = $t . '_' . strtoupper($k);
				
				$group = substr($key, 1, strpos($key, '/', 1)-1);*/
			}
			else{
				$group = 'general';
			}
			
			
			
			

			/*if(!isset($groups[$group])){
				$groups[$group] = [];
			}

			// NEW i18n support for config options!
			$i18nKey = \Core\i18n\I18NLoader::KeyifyString($key);
			//$opts['description'] = t('MESSAGE_PERM__' . $i18nKey);
			$groups[$group][$key] = t('STRING_PERMISSION_' . $i18nKey);*/
		}
		
		var_dump($everything); die();

		// Now, I can add these groups to the form.
		foreach($groups as $gkey => $options){
			// Make the title a little more friendly.
			$gtitle = ucwords($gkey) . ' Permissions to Assign';

			$form->addElement(
				'checkboxes',
				array(
					'group' => 'Permissions',
					'id' => 'permissions-' . $gkey,
					'name' => 'permissions',
					'title' => $gtitle,
					'options' => $options,
					'value' => $model->getPermissions(),
				)
			);
		}
	}

	public static function _UpdateFormHandler(\Core\Forms\Form $form){

		try{
			/** @var UserGroupModel $model */
			$model = $form->getModel();

			if(\Core\user()->checkAccess('p:/user/permissions/manage')){
				// hehe... this is kind of a hack that works.
				// it's a hack because "getElement" returns only 1 element, but it works
				// because all those elements share the same POST name.
				// As such, the value from all permission[] checkboxes actually get transposed to all
				// form elements with that same base name.
				$model->setPermissions($form->getElement('permissions[]')->get('value'));
			}

			if($model->get('context') != ''){
				// Non-global context groups can never be default!
				$model->set('default', 0);
			}

			$model->save();
		}
		catch(ModelValidationException $e){
			\Core\set_message($e->getMessage(), 'error');
			return false;
		}
		catch(Exception $e){
			\Core\set_message($e->getMessage(), 'error');
			return false;
		}

		return '/usergroupadmin';
	}
}
