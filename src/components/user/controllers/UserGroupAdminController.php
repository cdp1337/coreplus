<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 7/13/12
 * Time: 7:02 PM
 * To change this template use File | Settings | File Templates.
 */
class UserGroupAdminController extends Controller_2_1{
	public function __construct(){
		$this->accessstring = 'p:/user/groups/manage';
	}

	public function index(){

		$view = $this->getView();

		$permissionmanager = \Core\user()->checkAccess('p:/user/permissions/manage');

		$where = array();
		if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
			$where['site'] = MultiSiteHelper::GetCurrentSiteID();
		}

		$groups = UserGroupModel::Find($where, null, 'name');

		$view->title = 'User Group Administration';
		$view->assign('groups', $groups);
		$view->assign('permissionmanager', $permissionmanager);
		$view->addControl('Add Group', '/usergroupadmin/create', 'add');
	}

	public function create(){
		$view  = $this->getView();
		$req   = $this->getPageRequest();
		$model = new UserGroupModel();

		if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
			$model->set('site', MultiSiteHelper::GetCurrentSiteID());
		}

		$contextnames = [];
		$contexts     = [];
		$usecontexts  = false;

		$form  = Form::BuildFromModel($model);

		$form->set('callsmethod', 'UserGroupAdminController::_UpdateFormHandler');
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
		$form          = Form::BuildFromModel($model);
		$contextnames  = [];
		$contexts      = [];
		$usecontexts   = false;
		$contextlocked = (sizeof($model->getLink('UserUserGroup')) > 0);

		if(!$model->exists()){
			return View::ERROR_NOERROR;
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
					Core::SetMessage('This group has been added to a user account and therefore has been locked to the ' . $contextnames[$model->get('context')]);
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

		if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
			$where['site'] = MultiSiteHelper::GetCurrentSiteID();
		}

		$model->delete();
		Core::SetMessage('Removed group successfully', 'success');
		\core\redirect('/usergroupadmin');
	}

	/**
	 * Set the site permissions to a given Form object.
	 *
	 * Used by the create and update pages.
	 *
	 * @param Form $form
	 * @param UserGroupModel $model
	 */
	private function _setPermissionsToForm(Form $form, UserGroupModel $model){
		// I want to split up the permission set into a set of groups, based on the first key.
		$groups = [];
		foreach(Core::GetPermissions() as $key => $data){
			$description = $data['description'];

			if($key{0} == '/'){
				$group = substr($key, 1, strpos($key, '/', 1)-1);
			}
			else{
				$group = 'general';
			}

			if(!isset($groups[$group])){
				$groups[$group] = [];
			}

			$groups[$group][$key] = $description;
		}

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

	public static function _UpdateFormHandler(Form $form){

		try{
			/** @var UserGroupModel $model */
			$model = $form->getModel();

			if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
				if($model->exists() && $model->get('site') != MultiSiteHelper::GetCurrentSiteID()){
					Core::SetMessage('Invalid group specified', 'error');
					return false;
				}
			}

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
			Core::SetMessage($e->getMessage(), 'error');
			return false;
		}
		catch(Exception $e){
			Core::SetMessage($e->getMessage(), 'error');
			return false;
		}

		return '/usergroupadmin';
	}
}
