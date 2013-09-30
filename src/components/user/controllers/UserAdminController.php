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
		$this->accessstring = 'p:/user/users/manage';
	}

	public function index(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$filters = new FilterForm();
		$filters->setName('user-admin');
		$filters->haspagination = true;
		$filters->hassort = true;
		$filters->setSortkeys(array('email', 'active', 'created'));
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

		$filters->load($request);
		$factory = new ModelFactory('UserModel');
		$filters->applyToFactory($factory);

		$users = $factory->get();
		//$users = UserModel::Find(null, null, 'email');


		$view->title = 'User Administration';
		$view->assign('enableavatar', (\ConfigHandler::Get('/user/enableavatar')));
		$view->assign('users', $users);
		$view->assign('filters', $filters);
		$view->addControl('Add User', '/user/register', 'add');
		$view->addControl('Import Users', '/useradmin/import', 'upload-alt');
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

	public function delete(){
		$view  = $this->getView();
		$req   = $this->getPageRequest();
		$id    = $req->getParameter(0);
		$model = User::Find(array('id' => $id));

		if(!$req->isPost()){
			return View::ERROR_BADREQUEST;
		}

		// Delete the user configs first.
		foreach($model->getConfigObjects() as $conf){
			$conf->delete();
		}

		$model->delete();
		Core::SetMessage('Removed user successfully', 'success');
		\Core\go_back();
	}

	/**
	 * Import a set of users from a CSV file.
	 */
	public function import(){
		$view = $this->getView();

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
		\core\redirect('/useradmin/import');
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


		$view->templatename = 'pages/useradmin/import1.tpl';
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
		$usergroups = UserGroupModel::Find();
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


		$view->templatename = 'pages/useradmin/import2.tpl';
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

		$view->templatename = 'pages/useradmin/import3.tpl';
		$view->assign('count', $_SESSION['user-import']['counts']);
		$view->assign('fails', $_SESSION['user-import']['fails']); // @todo Implement this

		unset($_SESSION['user-import']);
	}
}
