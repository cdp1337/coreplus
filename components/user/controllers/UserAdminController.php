<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 7/12/12
 * Time: 1:06 AM
 * To change this template use File | Settings | File Templates.
 */
class UserAdminController extends Controller_2_1{
	public function __construct(){
		$this->accessstring = 'p:user_manage';
	}

	public function index(){
		$view = $this->getView();

		$users = UserModel::Find(null, null, 'email');

		$view->title = 'User Administration';
		$view->assignVariable('users', $users);
		$view->addControl('Add User', '/user/register', 'add');
	}

	/**
	 * Simple controller to activate a user account.
	 * Meant to be called with json only.
	 */
	public function activate(){
		$req    = $this->getPageRequest();
		$view   = $this->getView();
		$userid = $req->getPost('user');
		$active = $req->getPost('status');
		if($active === '') $active = 1; // default.

		$view->mode = View::MODE_AJAX;
		$view->contenttype = View::CTYPE_JSON;

		if(!$req->isJSON()){
			return View::ERROR_BADREQUEST;
		}

		if(!$req->isPost()){
			return View::ERROR_BADREQUEST;
		}

		if(!$userid){
			return View::ERROR_BADREQUEST;
		}

		$user = new UserModel($userid);

		if(!$user->exists()){
			return View::ERROR_NOTFOUND;
		}

		$user->set('active', $active);
		$user->save();

		$view->jsondata = array(
			'userid' => $user->get('id'),
			'active' => $user->get('active'),
		);
	}
}
