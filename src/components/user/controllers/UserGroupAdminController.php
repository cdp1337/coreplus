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
		$this->accessstring = 'p:user_manage';
	}

	public function index(){
		$view = $this->getView();

		$where = array();
		if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
			$where['site'] = MultiSiteHelper::GetCurrentSiteID();
		}

		$groups = UserGroupModel::Find($where, null, 'name');

		$view->title = 'User Group Administration';
		$view->assignVariable('groups', $groups);
		$view->addControl('Add Group', '/usergroupadmin/create', 'add');
	}

	public function create(){
		$view  = $this->getView();
		$req   = $this->getPageRequest();
		$model = new UserGroupModel();

		if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
			$model->set('site', MultiSiteHelper::GetCurrentSiteID());
		}

		$form  = Form::BuildFromModel($model);

		$form->set('callsmethod', 'UserGroupAdminController::_UpdateFormHandler');

		// Add in the permissions to set to this group.
		$form->addElement(
			'checkboxes',
			array(
				'name' => 'permissions',
				'title' => 'Permissions to assign',
				'options' => Core::GetPermissions(),
			)
		);

		$form->addElement('submit', array('value' => 'Create'));

		$view->assign('model', $model);
		$view->assign('form', $form);
		$view->title = 'Create Group ';
	}

	public function update(){
		$view  = $this->getView();
		$req   = $this->getPageRequest();
		$id    = $req->getParameter(0);
		$model = new UserGroupModel($id);
		$form  = Form::BuildFromModel($model);

		if(!$model->exists()){
			return View::ERROR_NOERROR;
		}

		$form->set('callsmethod', 'UserGroupAdminController::_UpdateFormHandler');

		// Add in the permissions to set to this group.
		$form->addElement(
			'checkboxes',
			array(
				'name' => 'permissions',
				'title' => 'Permissions to assign',
				'options' => Core::GetPermissions(),
				'value' => $model->getPermissions(),
			)
		);

		$form->addElement('submit', array('value' => 'Update'));

		$view->assign('model', $model);
		$view->assign('form', $form);
		$view->title = 'Update Group ' . $model->get('name');
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

	public static function _UpdateFormHandler(Form $form){

		try{
			$model = $form->getModel();

			if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
				if($model->exists() && $model->get('site') != MultiSiteHelper::GetCurrentSiteID()){
					Core::SetMessage('Invalid group specified', 'error');
					return false;
				}
			}

			$model->setPermissions($form->getElement('permissions[]')->get('value'));
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
