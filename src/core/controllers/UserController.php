<?php
/**
 * Main controller for the user system
 *
 * Provides both admin functions and front-end user functions.
 *
 * @package Core\User
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
 *
 * This controller is only responsible for Core user functions.
 * Authentication-specific functions must be contained on the specific auth driver or its respective controller.
 *
 * @package Core\User
 */
class UserController extends Controller_2_1{

	/**
	 * Admin listing of all the users
	 *
	 * @return null|int
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

		return null;
	}

	/**
	 * Show the current user's profile.
	 *
	 * @return null|int
	 */
	public function me(){

		$view    = $this->getView();
		$user    = \Core\user();

		if(!$user->exists()){
			return View::ERROR_ACCESSDENIED;
		}

		if(!$user->isActive()){
			Core::SetMessage('Your account is not active!', 'error');
			return View::ERROR_ACCESSDENIED;
		}

		$form = \Core\User\Helper::GetEditForm($user);

		// Grab the login attempts for this user
		$logins = SystemLogModel::Find(
			//['affected_user_id = ' . $user->get('id'), 'code = /user/login'],
			['affected_user_id = ' . $user->get('id')],
			20,
			'datetime DESC'
		);

		$view->controls = ViewControls::Dispatch('/user/view', $user->get('id'));

		$view->mastertemplate = ConfigHandler::Get('/theme/siteskin/user');
		$view->title = 'My Profile';
		$view->assign('user', $user);
		$view->assign('form', $form);
		$view->assign('logins', $logins);
		$view->assign('profiles', json_decode($user->get('json:profiles'), true));

		return null;
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
		$view->mastertemplate = ConfigHandler::Get('/theme/siteskin/user');
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
	 * Function to edit the user's connected profiles.
	 */
	public function connectedprofiles(){
		$view    = $this->getView();
		$req     = $this->getPageRequest();
		$userid  = $req->getParameter(0);
		$manager = \Core\user()->checkAccess('p:/user/users/manage'); // Current user an admin?

		if($userid === null) $userid = \Core\user()->get('id'); // Default to current user.

		// Only allow this if the user is either the same user or has the user manage permission.
		if(!($userid == \Core\user()->get('id') || $manager)){
			return View::ERROR_ACCESSDENIED;
		}

		/** @var UserModel $user */
		$user = UserModel::Construct($userid);
		// I will be dealing with only one custom field...
		$jsonprofiles = $user->get('json:profiles');
		$profiles = json_decode($jsonprofiles, true);

		if($req->isPost()){
			// Update the new profiles.... yay
			$error = false;
			$profiles = array();
			foreach($_POST['type'] as $k => $v){
				// Check that this looks like a URL.
				if(!(
					// Links should be a valid URL (starting with HTTP or HTTPS).
					preg_match(Model::VALIDATION_URL_WEB, $_POST['url'][$k]) ||
					// Or twitter-style links using the '@' prefix.
					$_POST['url'][$k]{0} == '@'
				)){
					$error = true;
					Core::SetMessage($_POST['url'][$k] . ' does not appear to be a valid URL!  Please ensure that it starts with http:// or https://', 'error');
				}

				$profiles[] = array(
					'type' => $_POST['type'][$k],
					'url' => $_POST['url'][$k],
					'title' => $_POST['title'][$k],
				);
			}

			if(!$error){
				$user->set('json:profiles', json_encode($profiles));
				$user->save();
				Core::SetMessage('Updated profiles successfully', 'success');
				Core::GoBack();
			}
			else{
				$jsonprofiles = json_encode($profiles);
			}
		}

		//$view->addBreadcrumb($user->getDisplayName(), UserSocialHelper::ResolveProfileLinkById($user->get('id')));
		$view->title = 'Edit Connected Profiles';
		$view->assign('profiles_json', $jsonprofiles);
		$view->assign('profiles', $profiles);
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
	}

	/**
	 * The actual Core registration page.
	 *
	 * This renders all the user's configurable options at registration.
	 */
	public function register2(){
		$view    = $this->getView();
		$request = $this->getPageRequest();
		$manager = \Core\user()->checkAccess('p:/user/users/manage'); // Current user an admin?

		// Anonymous users should have access to this if it's allow public.
		if(!\Core\user()->exists() && !ConfigHandler::Get('/user/register/allowpublic')){
			return View::ERROR_BADREQUEST;
		}

		// Authenticated users must check the permission to manage users.
		if(\Core\user()->exists() && !$manager){
			return View::ERROR_ACCESSDENIED;
		}

		/** @var NonceModel $nonce */
		$nonce = NonceModel::Construct($request->getParameter(0));
		if(!$nonce->isValid()){
			Core::SetMessage('Invalid nonce token, please try again.', 'error');
			\Core\go_back();
		}
		$nonce->decryptData();
		$data = $nonce->get('data');

		if(!isset($data['user']) || !($data['user'] instanceof UserModel)){
			if(DEVELOPMENT_MODE){
				Core::SetMessage('Your nonce does not include a "user" key.  Please ensure that this is set to a non-existent UserModel object!', 'error');
			}
			else{
				Core::SetMessage('Invalid login type, please try again later.', 'error');
			}
			\Core\go_back();
		}

		/** @var UserModel $user */
		$user = $data['user'];

		$form = \User\Helper::GetForm($user);
		$form->addElement('hidden', ['name' => 'redirect', 'value' => $data['redirect']]);
		if(isset($data['password'])){
			// Password should ONLY set by a generate password system.
			// This is NOT the user's password they entered into the system.
			// The reason for this is so that the form handler has the original generated password available to send with the welcome email.
			$form->addElement('system', ['name' => 'password', 'value' => $data['password']]);
		}


		$view->title = 'Complete Registration';
		$view->assign('form', $form);
	}

	public function logout(){
		$view = $this->getView();

		Session::DestroySession();
		\core\redirect('/');
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
					$setpasswordlink = Core::ResolveLink('/datastoreauth/forgotpassword?e=' . urlencode($user->get('email')) . '&n=' . $nonce);
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
				\Core\ErrorManagement\exception_handler($e);
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
