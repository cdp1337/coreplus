<?php
/**
 * Main controller for the user system
 *
 * Provides both admin functions and front-end user functions.
 *
 * @package User
 * @since 1.9
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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
 * Main controller for the user system
 */
class UserController extends Controller_2_1{

	/**
	 * Admin listing of all the users
	 *
	 * @return int
	 */
	public function admin(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->checkAccess('p:/user/users/manage')){
			return View::ERROR_ACCESSDENIED;
		}

		$filters = new FilterForm();
		$filters->setName('user-admin');
		$filters->haspagination = true;
		$filters->hassort = true;
		$filters->setSortkeys(array('email', 'active', 'created','last_login'));
		$filters->addElement(
			'text',
			array(
				'title' => 'Email',
				'name' => 'email',
				'link' => FilterForm::LINK_TYPE_CONTAINS
			)
		);
		$filters->addElement(
			'select',
			array(
				'title' => 'Active',
				'name' => 'active',
				'options' => array('' => '-- All --', '0' => 'Inactive', '1' => 'Active'),
				'link' => FilterForm::LINK_TYPE_STANDARD,
			)
		);
		$filters->addElement(
			'select',
			array(
				'title' => 'Ever logged in?',
				'name' => 'last_login',
				'options' => array('' => 'Both', '1' => 'No', '2' => 'Yes'),
			)
		);

		$filters->load($request);
		$factory = new ModelFactory('UserModel');

		if($filters->get('last_login') == 1) {
			$factory->where('last_login = 0');
		}
		elseif($filters->get('last_login') == 2) {
			$factory->where('last_login > 0');
		}

		$filters->applyToFactory($factory);

		$users = $factory->get();

		$view->title = 'User Administration';
		$view->assign('enableavatar', (\ConfigHandler::Get('/user/enableavatar')));
		$view->assign('users', $users);
		$view->assign('filters', $filters);
		$view->addControl('Add User', '/user/register', 'add');
		$view->addControl('Import Users', '/user/import', 'upload-alt');
	}

	/**
	 * Show the current user's profile.
	 */
	public function me(){

		$view    = $this->getView();
		$req     = $this->getPageRequest();
		$user    = \Core\user();

		if(!$user->exists()){
			return View::ERROR_ACCESSDENIED;
		}

		$form = \Core\User\Helper::GetEditForm($user);


		$view->controls = ViewControls::Dispatch('/user/view', $user->get('id'));
		$view->controls->hovercontext = true;

		$view->assign('user', $user);
		$view->assign('form', $form);
		$view->title = 'My Profile';
	}

	/**
	 * View to set the user's password, both administratively and from the user's profile.
	 *
	 * @return int
	 */
	public function password(){

		$view    = $this->getView();
		$req     = $this->getPageRequest();
		$userid  = $req->getParameter(0);
		$manager = \Core\user()->checkAccess('p:/user/users/manage'); // Current user an admin?

		// Default to current user.
		if($userid === null){
			$ownpassword = true;
			$userid = \Core\user()->get('id');
		}
		else{
			$ownpassword = false;
		}

		// Only allow this if the user is either the same user or has the user manage permission.
		if(!($userid == \Core\user()->get('id') || $manager)){
			return View::ERROR_ACCESSDENIED;
		}

		/** @var UserModel $user */
		$user = UserModel::Construct($userid);

		if(!$user->exists()){
			Core::SetMessage('Unable to locate requested user', 'error');
			\Core\go_back(1);
		}

		$auth = $user->getAuthDriver();

		if(($canset = $auth->canSetPassword()) !== true){
			Core::SetMessage($canset);
			\Core\go_back(1);
		}

		if($req->isPost()){
			try{
				$p1val = $_POST['pass'];
				$p2val = $_POST['pass2'];
				// Check the passwords, (that they match).
				if($p1val != $p2val){
					throw new ModelValidationException('Passwords do not match');
				}

				$status = $auth->setPassword($p1val);
				if($status === false){
					// No change
					Core::SetMessage('No change detected');
				}
				elseif($status === true){
					$user->set('last_password', CoreDateTime::Now('U', Time::TIMEZONE_GMT));
					$user->save();
					Core::SetMessage('Updated Password Successfully', 'success');
				}
				else{
					throw new ModelValidationException($status);
				}

				if($ownpassword){
					\core\redirect('/user/me');
				}
				else{
					\core\redirect('/user/admin');
				}
			}
			catch(ModelValidationException $e){
				Core::SetMessage($e->getMessage(), 'error');
			}
			catch(Exception $e){
				if(DEVELOPMENT_MODE) Core::SetMessage($e->getMessage(), 'error');
				else Core::SetMessage('An unknown error occured', 'error');
				error_log($e->getMessage());
			}
		}

		$form = new Form();

		$form->addElement('password', array('name' => 'pass', 'title' => 'Password', 'required' => true));
		$form->addElement('password', array('name' => 'pass2', 'title' => 'Confirm', 'required' => true));

		$form->addElement('submit', array('value' => 'Update Password'));

		// Pull some info about the complexity requirements.
		$complexity = [
			'enabled'  => false,
			'length'   => 0,
			'symbols'  => 0,
			'capitals' => 0,
			'numbers'  => 0,
			'messages' => [],
		];
		if(ConfigHandler::Get('/user/password/minlength')){
			$complexity['enabled'] = true;
			$complexity['length'] = ConfigHandler::Get('/user/password/minlength');
			$complexity['messages'][] = 'The password is at least ' . $complexity['length'] . ' characters long.';
		}
		if(ConfigHandler::Get('/user/password/requiresymbols')){
			$complexity['enabled'] = true;
			$complexity['symbols'] = ConfigHandler::Get('/user/password/requiresymbols');
			$complexity['messages'][] = 'The password contains at least ' . $complexity['symbols'] . ' symbol(s).';
		}
		if(ConfigHandler::Get('/user/password/requirecapitals')){
			$complexity['enabled'] = true;
			$complexity['capitals'] = ConfigHandler::Get('/user/password/requirecapitals');
			$complexity['messages'][] = 'The password contains at least ' . $complexity['capitals'] . ' capital(s).';
		}
		if(ConfigHandler::Get('/user/password/requirenumbers')){
			$complexity['enabled'] = true;
			$complexity['numbers'] = ConfigHandler::Get('/user/password/requirenumbers');
			$complexity['messages'][] = 'The password contains at least ' . $complexity['numbers'] . ' number(s).';
		}

		$view->assign('complexity', $complexity);
		$view->assign('form', $form);
		$view->title = 'Password Management ';

		// Breadcrumbs! (based on access permissions)
		if(!$ownpassword){
			$view->addBreadcrumb('User Administration', '/user/admin');
			$view->addBreadcrumb($user->getDisplayName(), '/user/edit/' . $user->get('id'));
		}
		else{
			$view->addBreadcrumb('My Profile', '/user/me');
		}
	}

	/**
	 * View to edit the user account, both administratively and from within the user's profile.
	 */
	public function edit(){

		$view          = $this->getView();
		$req           = $this->getPageRequest();
		$userid        = $req->getParameter(0);
		$manager       = \Core\user()->checkAccess('p:/user/users/manage'); // Current user an admin?
		$groupmanager  = \Core\user()->checkAccess('p:/user/groups/manage');
		$contextnames  = [];
		$contexts      = [];
		$usecontexts   = false;

		if($userid === null) $userid = \Core\user()->get('id'); // Default to current user.

		// Only allow this if the user is either the same user or has the user manage permission.
		if(!($userid == \Core\user()->get('id') || $manager)){
			Core::SetMessage('Insufficient Permissions', 'error');
			\core\redirect('/');
		}

		/** @var UserModel $user */
		$user = UserModel::Construct($userid);
		if($user) {
			$form = \Core\User\Helper::GetEditForm($user);
		} else {
			Core::SetMessage('A user with this ID does not exist');
			\Core\go_back();
		}


		if($groupmanager){
			$contextgroups = UserGroupModel::Find(['context != '], null, 'name');
			foreach($contextgroups as $group){
				/** @var UserGroupModel $group */

				$ckey = $group->get('context');
				$gkey = $group->get('id');
				$contextnames[ $group->get('name') ] = $gkey;

				// I need to load *all* those models into the system so they're available to the UI.
				$fac = new ModelFactory($ckey . 'Model');
				$all = [];
				foreach($fac->get() as $m){
					/** @var Model $m */
					$all[$m->getPrimaryKeyString()] = $m->getLabel();
				}
				$contexts[$gkey] = $all;

				$usecontexts = true;
			}
		}



		$view->controls = ViewControls::Dispatch('/user/view', $user->get('id'));
		$view->title = 'Editing ' . $user->getDisplayName();
		$view->assign('form', $form);
		$view->assign('contextnames_json', json_encode($contextnames));
		$view->assign('contextnames', $contextnames);
		$view->assign('contexts_json', json_encode($contexts));
		$view->assign('use_contexts', $usecontexts);
		$view->assign('user', $user);

		// Breadcrumbs! (based on access permissions)
		if($manager){
			//$view->addBreadcrumb('User Administration', '/user/admin');
		}
	}

	/**
	 * Display the login page for whatever drivers may happen to be installed.
	 *
	 * @return View
	 */
	public function login(){
		$request = $this->getPageRequest();
		$view    = $this->getView();

		// Is the user already logged in?
		// Set the access permissions for this page as anonymous-only.
		if(\Core\user()->exists()){
			\core\redirect('/user/me');
		}

		$auths = \Core\User\Helper::GetEnabledAuthDrivers();

		$view->ssl = true;
		$view->assign('drivers', $auths);
		$view->assign('allowregister', ConfigHandler::Get('/user/register/allowpublic'));
		// Google has no business indexing user-action pages.
		$view->addMetaName('robots', 'noindex');
		return $view;
	}

	/**
	 * Ajax page to allow for quickly linking the current user to a facebook account from a strictly javascript interface.
	 *
	 */
	public function linkfacebook(){
		$request = $this->getPageRequest();
		$view    = $this->getView();

		$view->mode = View::MODE_AJAX;
		$view->contenttype = View::CTYPE_JSON;

		if(!$request->isAjax()){
			return View::ERROR_BADREQUEST;
		}
		if(!$request->isPost()){
			return View::ERROR_BADREQUEST;
		}

		$user = \Core\user();
		if(!$user->exists()){
			return View::ERROR_BADREQUEST;
		}

		// Necessary fields are id and token.
		$user->set('facebook_access_token', $_POST['token']);
		$user->set('facebook_id', $_POST['id']);
		$user->save();

		$view->jsondata = array(
			'id' => $user->get('id'),
			'access_token' => $user->get('facebook_access_token'),
			'facebook_id' => $user->get('facebook_id'),
		);
	}

	/**
	 * Display the register page for new users.
	 *
	 * @return int
	 */
	public function register(){

		$view          = $this->getView();
		$manager       = \Core\user()->checkAccess('p:/user/users/manage'); // Current user an admin?
		$groupmanager  = \Core\user()->checkAccess('p:/user/groups/manage');
		$contextnames  = [];
		$contexts      = [];
		$usecontexts   = false;

		// Anonymous users should have access to this if it's allow public.
		if(!\Core\user()->exists() && !ConfigHandler::Get('/user/register/allowpublic')){
			return View::ERROR_BADREQUEST;
		}

		// Authenticated users must check the permission to manage users.
		if(\Core\user()->exists() && !$manager){
			return View::ERROR_ACCESSDENIED;
		}

		$auths = \Core\User\Helper::GetEnabledAuthDrivers();

		$view->title = 'Register';
		$view->ssl = true;
		$view->assign('drivers', $auths);
		// Google has no business indexing user-action pages.
		$view->addMetaName('robots', 'noindex');

		// Breadcrumbs! (based on access permissions)
		if($manager){
			$view->addBreadcrumb('User Administration', '/user/admin');
		}

		return;




		if($groupmanager){
			$contextgroups = UserGroupModel::Find(['context != '], null, 'name');
			foreach($contextgroups as $group){
				/** @var UserGroupModel $group */

				$ckey = $group->get('context');
				$gkey = $group->get('id');
				$contextnames[ $group->get('name') ] = $gkey;

				// I need to load *all* those models into the system so they're available to the UI.
				$fac = new ModelFactory($ckey . 'Model');
				$all = [];
				foreach($fac->get() as $m){
					/** @var Model $m */
					$all[$m->getPrimaryKeyString()] = $m->getLabel();
				}
				$contexts[$gkey] = $all;

				$usecontexts = true;
			}
		}


		$view->ssl = true;
		$view->assign('form', $form);
		$view->assign('contextnames_json', json_encode($contextnames));
		$view->assign('contextnames', $contextnames);
		$view->assign('contexts_json', json_encode($contexts));
		$view->assign('use_contexts', $usecontexts);
		$view->assign('user', false);


	}

	public function logout(){
		$view = $this->getView();

		// Set the access permissions for this page as authenticated-only.
		if(!$view->setAccess('g:authenticated;g:!admin')){
			return View::ERROR_ACCESSDENIED;
		}

		Session::DestroySession();
		\core\redirect('/');
	}

	/**
	 * Front-end view to allow users to reset their password.
	 */
	public function forgotPassword(){
		$request = $this->getPageRequest();

		// If e and k are set as parameters... it's on step 2.
		if($request->getParameter('e') && $request->getParameter('n')){
			return $this->_forgotPassword2();
		}
		// Else, just step 1.
		else{
			return $this->_forgotPassword1();
		}
	}

	/**
	 * Simple controller to activate a user account.
	 * Meant to be called with json only.
	 */
	public function activate(){
		$req    = $this->getPageRequest();
		$view   = $this->getView();
		$userid = $req->getPost('user') ? $req->getPost('user') : $req->getParameter('user');
		$active = ($req->getPost('status') !== null) ? $req->getPost('status') : $req->getParameter('status');
		if($active === '') $active = 1; // default.


		if(!\Core\user()->checkAccess('p:/user/users/manage')){
			return View::ERROR_ACCESSDENIED;
		}

		if(!$req->isPost()){
			return View::ERROR_BADREQUEST;
		}

		if(!$userid){
			return View::ERROR_BADREQUEST;
		}


		$user = UserModel::Construct($userid);

		if(!$user->exists()){
			return View::ERROR_NOTFOUND;
		}

		$user->set('active', $active);
		$user->save();

		// Send an activation notice email to the user if the active flag is set to true.
		if($active){
			try{
				$email = new Email();

				if(!$user->get('password')){
					// Generate a Nonce for this user with the password reset.
					// Use the Nonce system to generate a one-time key with this user's data.
					$nonce = NonceModel::Generate(
						'1 week',
						['type' => 'password-reset', 'user' => $user->get('id')]
					);
					$setpasswordlink = Core::ResolveLink('/user/forgotpassword?e=' . urlencode($user->get('email')) . '&n=' . $nonce);
				}
				else{
					$setpasswordlink = null;
				}

				$email->assign('user', $user);
				$email->assign('sitename', SITENAME);
				$email->assign('rooturl', ROOT_URL);
				$email->assign('loginurl', Core::ResolveLink('/user/login'));
				$email->assign('setpasswordlink', $setpasswordlink);
				$email->setSubject('Welcome to ' . SITENAME);
				$email->templatename = 'emails/user/activation.tpl';
				$email->to($user->get('email'));

				// TESTING
				//error_log($email->renderBody());
				$email->send();
			}
			catch(\Exception $e){
				error_log($e->getMessage());
			}
		}

		if($req->isJSON()){
			$view->mode = View::MODE_AJAX;
			$view->contenttype = View::CTYPE_JSON;

			$view->jsondata = array(
				'userid' => $user->get('id'),
				'active' => $user->get('active'),
			);
		}
		else{
			\Core\go_back();
		}
	}

	/**
	 * Permanently delete a user account and all configuration options attached.
	 *
	 * @return int
	 */
	public function delete(){
		$view  = $this->getView();
		$req   = $this->getPageRequest();
		$id    = $req->getParameter(0);
		$model = UserModel::Construct($id);

		if(!\Core\user()->checkAccess('p:/user/users/manage')){
			return View::ERROR_ACCESSDENIED;
		}

		if(!$req->isPost()){
			return View::ERROR_BADREQUEST;
		}

		// Users are now a standard model, deleting a user account will automatically propagate down the stack.
		$model->delete();
		Core::SetMessage('Removed user successfully', 'success');
		\Core\go_back();
	}

	/**
	 * View to sudo as another user.
	 */
	public function sudo(){
		$view  = $this->getView();
		$req   = $this->getPageRequest();
		$id    = $req->getParameter(0);


		if($id){
			$model = UserModel::Construct($id);

			if(!\Core\user()->checkAccess('p:/user/users/sudo')){
				return View::ERROR_ACCESSDENIED;
			}

			if(!$req->isPost()){
				return View::ERROR_BADREQUEST;
			}

			if(!$model->exists()){
				return View::ERROR_NOTFOUND;
			}

			$_SESSION['user_sudo'] = $model;
		}
		elseif(isset($_SESSION['user_sudo'])){
			unset($_SESSION['user_sudo']);
		}

		\Core\redirect('/');
	}

	/**
	 * Import a set of users from a CSV file.
	 */
	public function import(){
		$view = $this->getView();

		if(!\Core\user()->checkAccess('p:/user/users/manage')){
			return View::ERROR_ACCESSDENIED;
		}

		$view->addBreadcrumb('User Administration', '/user/admin');
		$view->title = 'Import Users';

		if(!isset($_SESSION['user-import'])) $_SESSION['user-import'] = array();

		if(isset($_SESSION['user-import']['counts'])){
			// Counts array is present... show the results page.
			$this->_import3();
		}
		elseif(isset($_SESSION['user-import']['file']) && file_exists($_SESSION['user-import']['file'])){
			// The file is set, that's step two.
			$this->_import2();
		}
		else{
			$this->_import1();
		}
	}

	/**
	 * Link to abort the import process.
	 */
	public function import_cancel(){
		unset($_SESSION['user-import']);
		\core\redirect('/user/import');
	}

	/**
	 * Display the initial upload option that will kick off the rest of the import options.
	 */
	private function _import1(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$form = new Form();
		$form->set('callsmethod', 'User\\ImportHelper::FormHandler1');
		$form->addElement(
			'file',
			[
				'name' => 'file',
				'title' => 'File To Import',
				'basedir' => 'tmp/user-import',
				'required' => true,
				'accept' => '.csv',
			]
		);
		$form->addElement('submit', ['name' => 'submit', 'value' => 'Next']);


		$view->templatename = 'pages/user/import1.tpl';
		$view->assign('form', $form);
	}

	/**
	 * There has been a file selected; check that file for headers and what not to display something useful to the user.
	 */
	private function _import2(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$filename = $_SESSION['user-import']['file'];
		$file = \Core\Filestore\Factory::File($filename);
		$contents = $file->getContentsObject();

		if(!$contents instanceof \Core\Filestore\Contents\ContentCSV){
			Core::SetMessage($file->getBaseFilename() . ' does not appear to be a valid CSV file!', 'error');
			unset($_SESSION['user-import']['file']);
			\Core\reload();
		}

		$hasheader = $contents->hasHeader();
		$data = $contents->parse();
		$total = sizeof($data);

		// Since I don't want to display the entire dataset in the preview...
		if($hasheader){
			$header = $contents->getHeader();
		}
		else{
			$header = array();
			$i=0;
			foreach($data[0] as $k => $v){
				$header[$i] = 'Column ' . ($i+1);
				$i++;
			}
		}
		$colcount = sizeof($header);

		if($total > 11){
			$preview = array_splice($data, 0, 10);
		}
		else{
			$preview = $data;
		}

		$form = new Form();
		$form->set('callsmethod', 'User\\ImportHelper::FormHandler2');
		$form->addElement('system', ['name' => 'key', 'value' => $_SESSION['user-import']['key']]);
		$form->addElement(
			'checkbox',
			[
				'name' => 'has_header',
				'title' => 'Has Header',
				'value' => 1,
				'checked' => $hasheader,
				'description' => 'If this CSV has a header record on line 1, (as illustrated below), check this to ignore that line.'
			]
		);

		$form->addElement(
			'checkbox',
			[
				'name' => 'merge_duplicates',
				'title' => 'Merge Duplicate Records',
				'value' => 1,
				'checked' => true,
				'description' => 'Merge duplicate records that may be found in the import.'
			]
		);

		// Only display the user groups if the current user has access to manage user groups.
		$usergroups = UserGroupModel::Find(['context = ']);
		if(sizeof($usergroups) && \Core\user()->checkAccess('p:/user/groups/manage')){
			$usergroupopts = array();
			foreach($usergroups as $ug){
				$usergroupopts[$ug->get('id')] = $ug->get('name');
			}
			$form->addElement(
				'checkboxes',
				[
					'name' => 'groups[]',
					'title' => 'User Groups to Assign',
					'options' => $usergroupopts,
					'description' => 'Check which groups to set the imported users to.  If merge duplicate records is selected, any found users will be set to the checked groups, (and consequently unset from any unchecked groups).',
				]
			);
		}
		else{
			$form->addElement('hidden', ['name' => 'groups[]', 'value' => '']);
		}

		// Get the map-to options.
		$maptos = ['' => '-- Do Not Map --', 'email' => 'Email', 'password' => 'Password'];

		$configs = UserConfigModel::Find([], null, 'weight asc, name desc');
		foreach($configs as $c){
			$maptos[ $c->get('key') ] = $c->get('name');
		}

		$maptoselects = [];
		foreach($header as $key => $title){
			$value = '';
			if(isset($maptos[$key])) $value = $key;
			if(array_search($title, $maptos)) $value = array_search($title, $maptos);

			$form->addElement(
				'select',
				[
					'name' => 'mapto[' . $key . ']',
					'title' => $title,
					'options' => $maptos,
					'value' => $value
				]
			);
		}


		$view->templatename = 'pages/user/import2.tpl';
		$view->assign('has_header', $hasheader);
		$view->assign('header', $header);
		$view->assign('preview', $preview);
		$view->assign('form', $form);
		$view->assign('total', $total);
		$view->assign('col_count', $colcount);
	}

	private function _import3(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$view->templatename = 'pages/user/import3.tpl';
		$view->assign('count', $_SESSION['user-import']['counts']);
		$view->assign('fails', $_SESSION['user-import']['fails']); // @todo Implement this

		unset($_SESSION['user-import']);
	}

	private function _forgotPassword1(){
		$view = $this->getView();
		$request = $this->getPageRequest();


		// Create a simple form to render.  This is better than doing it in the template.
		$form = new Form();
		$form->set('method', 'POST');
		$form->addElement('text', ['name' => 'email', 'title' => 'Email', 'required' => true]);
		$form->addElement('submit', ['name' => 'submit', 'value' => 'Send Reset Instructions']);

		$view->title = 'Forgot Password';
		// This is step 1
		$view->assign('step', 1);
		$view->assign('form', $form);
		// Google has no business indexing user-action pages.
		$view->addMetaName('robots', 'noindex');

		// There's really nothing to do here except for check the email and send it.

		if($request->isPost()){

			/** @var UserModel $u */
			$u = UserModel::Find(array('email' => $_POST['email']), 1);
			if(!$u){
				Core::SetMessage('Invalid user account requested', 'error');
				SystemLogModel::LogSecurityEvent('/user/forgotpassword/send', 'Failed Forgot Password. Invalid email requested for reset: [' . $_POST['email'] . ']');
				return;
			}

			try{
				$auth = $u->getAuthDriver();
			}
			catch(Exception $e){
				Core::SetMessage('There was an error while retrieving your user account.  The administrator has been notified of this incident.  Please try again shortly.', 'error');
				SystemLogModel::LogSecurityEvent('/user/forgotpassword/send', $e->getMessage());
				return;
			}


			$str = $auth->canSetPassword();
			if($str === false){
				Core::SetMessage($auth->getAuthTitle() . ' user accounts do not support resetting the password via this method.', 'error');
				SystemLogModel::LogSecurityEvent('/user/forgotpassword/send', 'Failed Forgot Password. ' . $auth->getAuthTitle() . ' does not support password management locally: [' . $_POST['email'] . ']', null, $u->get('id'));
				return;
			}
			elseif($str !== true){
				Core::SetMessage($str, 'error');
				SystemLogModel::LogSecurityEvent('/user/forgotpassword/send', 'Failed Forgot Password. ' . $str . ': [' . $_POST['email'] . ']', null, $u->get('id'));
				return;
			}

			// Use the Nonce system to generate a one-time key with this user's data.
			$nonce = NonceModel::Generate(
				'20 minutes',
				['type' => 'password-reset', 'user' => $u->get('id')]
			);

			$link = '/user/forgotpassword?e=' . urlencode($u->get('email')) . '&n=' . $nonce;

			$e = new Email();
			$e->setSubject('Forgot Password Request');
			$e->to($u->get('email'));
			$e->assign('link', Core::ResolveLink($link));
			$e->assign('ip', REMOTE_IP);
			$e->templatename = 'emails/user/forgotpassword.tpl';
			try{
				$e->send();
				SystemLogModel::LogSecurityEvent('/user/forgotpassword/send', 'Forgot password request sent successfully', null, $u->get('id'));
			}
			catch(Exception $e){
				Core::SetMessage('Error sending the email, ' . $e->getMessage(), 'error');
				SystemLogModel::LogErrorEvent('/user/forgotpassword/send', $e->getMessage());
				return;
			}

			// Otherwise, it must have sent, (hopefully)...
			Core::SetMessage('Sent reset instructions via email.', 'success');
			\core\redirect('/');
		}
	}

	private function _forgotPassword2(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		// Create a simple form to render.  This is better than doing it in the template.
		$form = new Form();
		$form->set('method', 'POST');
		$form->addElement('password', ['name' => 'p1', 'title' => 'Password', 'required' => true]);
		$form->addElement('password', ['name' => 'p2', 'title' => 'Confirm', 'required' => true]);
		$form->addElement('submit', ['name' => 'submit', 'value' => 'Set New Password']);

		$view->title = 'Forgot Password';
		$view->assign('step', 2);
		$view->assign('form', $form);

		// Lookup and validate this information first.
		$e = urldecode($request->getParameter('e'));

		/** @var UserModel $u */
		$u = UserModel::Find(array('email' => $e), 1);
		if(!$u){
			SystemLogModel::LogSecurityEvent('/user/forgotpassword/confirm', 'Failed Forgot Password. Invalid user account requested: [' . $e . ']');
			Core::SetMessage('Invalid user account requested', 'error');
			\core\redirect('/');
			return;
		}

		$auth = $u->getAuthDriver();

		// Make sure that nonce hasn't expired yet and is still valid.
		$n = $request->getParameter('n');

		/** @var $nonce NonceModel */
		$nonce = NonceModel::Construct($n);
		// I can't invalidate it quite yet... the user still needs to set the new password.
		if(!$nonce->isValid(['type' => 'password-reset', 'user' => $u->get('id')])){
			SystemLogModel::LogSecurityEvent('/user/forgotpassword/confirm', 'Failed Forgot Password. Invalid key requested: [' . $n . ']', null, $u->get('id'));
			Core::SetMessage('Invalid key provided!', 'error');
			\core\redirect('/');
			return;
		}

		if(($str = $auth->canSetPassword()) !== true){
			Core::SetMessage($str, 'error');
			SystemLogModel::LogSecurityEvent('/user/forgotpassword/confirm', 'Failed Forgot Password. ' . $str, null, $u->get('id'));
			\core\redirect('/');
			return;
		}

		if($request->isPost()){
			// Validate the password.
			if($_POST['p1'] != $_POST['p2']){
				Core::SetMessage('Passwords do not match.', 'error');
				return;
			}

			// Else, try to set it... the user model will complain if it's invalid.
			try{
				$auth->setPassword($_POST['p1']);
				$u->set('last_password', CoreDateTime::Now('U', Time::TIMEZONE_GMT));
				$u->save();
				// NOW I can invalidate that nonce!
				$nonce->markUsed();
				SystemLogModel::LogSecurityEvent('/user/forgotpassword/confirm', 'Reset password successfully!', null, $u->get('id'));
				Core::SetMessage('Reset password successfully', 'success');
				if($u->get('active')){
					Session::SetUser($u);
				}
				\core\redirect('/');
			}
			catch(ModelValidationException $e){
				SystemLogModel::LogSecurityEvent('/user/forgotpassword/confirm', 'Failed Forgot Password. ' . $e->getMessage(), null, $u->get('id'));
				Core::SetMessage($e->getMessage(), 'error');
				return;
			}
			catch(Exception $e){
				SystemLogModel::LogSecurityEvent('/user/forgotpassword/confirm', 'Failed Forgot Password. ' . $e->getMessage(), null, $u->get('id'));
				if(DEVELOPMENT_MODE) Core::SetMessage($e->getMessage(), 'error');
				else Core::SetMessage('An unknown error occured', 'error');

				return;
			}
		}
	}

	/**
	 * This is a helper controller to expose server-side data to javascript.
	 *
	 * It's useful for currently logged in user and what not.
	 * Obviously nothing critical is exposed here, since it'll be sent to the useragent.
	 */
	public function jshelper(){
		$request = $this->getPageRequest();

		// This is a json-only page.
		if($request->ctype != View::CTYPE_JSON){
			\core\redirect('/');
		}

		// The data that will be returned.
		$data = array();

		$cu = Core::User();

		if(!$cu->exists()){
			$data['user'] = array(
				'id' => null,
				'displayname' => ConfigHandler::Get('/user/displayname/anonymous'),
				//'email' => null,
			);
			$data['accessstringtemplate'] = null;
		}
		else{
			$data['user'] = array(
				'id' => $cu->get('id'),
				'displayname' => $cu->getDisplayName(),
				//'email' => $cu->get('email'),
			);

			// Templated version of the access string form system, useful for dynamic permissions on the page.
			$templateel = new FormAccessStringInput(array(
				'title' => '##TITLE##',
				'name' => '##NAME##',
				'description' => '##DESCRIPTION##',
				'class' => '##CLASS##',
                'value' => 'none'
			));
			$data['accessstringtemplate'] = $templateel->render();
		}

		$this->getView()->jsondata = $data;
		$this->getView()->contenttype = View::CTYPE_JSON;
	}


	public static function _HookHandler403(View $view){

		if(\Core\user()->exists()){
		//if(Core::User()->exists()){
			// User is already logged in... I can't do anything.
			return true;
		}

		// I need to replace the current page with this one so that the previous controller never executes.
		$newcontroller = new self();
		$newcontroller->overwriteView($view);
		//$view->baseurl = '/user/login';

		$auths = \Core\User\Helper::GetEnabledAuthDrivers();

		$view->ssl = true;
		$view->error = View::ERROR_ACCESSDENIED;
		$view->allowerrors = true;
		$view->templatename = 'pages/user/guest403.tpl';
		$view->assign('drivers', $auths);
		$view->assign('allowregister', ConfigHandler::Get('/user/register/allowpublic'));
		// Google has no business indexing user-action pages.
		$view->addMetaName('robots', 'noindex');

		$loginform = new Form();
		$loginform->set('callsMethod', 'User\\Helper::LoginHandler');

		$loginform->addElement('text', array('name' => 'email', 'title' => 'Email', 'required' => true));
		$loginform->addElement('password', array('name' => 'pass', 'title' => 'Password', 'required' => true));
		$loginform->addElement('submit', array('value' => 'Login'));

		if(ConfigHandler::Get('/user/register/allowpublic')){
			$registerform = \Core\User\Helper::GetRegistrationForm();
		}
		else{
			$registerform = null;
		}


		$error = false;



		$view->assign('error', $error);
		$view->assign('backends', ConfigHandler::Get('/user/backends'));
		$view->assign('loginform', $loginform);
		$view->assign('registerform', $registerform);
		$view->assign('allowregister', ConfigHandler::Get('/user/register/allowpublic'));


		return $view;
	}

}
