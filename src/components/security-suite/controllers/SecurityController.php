<?php
/**
 * Class file for the controller CronController
 *
 * @package Security-Suite
 */
class SecurityController extends Controller_2_1 {

	/**
	 * Provide a UI for the simple site password option.
	 */
	public function sitepassword(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->checkAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}

		require_once(ROOT_PDIR . 'core/libs/core/configs/functions.php');

		// Build a form to handle the config options themselves.
		// These will include password strength, whether or not captcha is enabled, etc.
		$configs = [
			'/security/site_password',
		];
		$configform = new Form();
		$configform->set('callsmethod', 'SecurityController::SitePasswordSave');

		foreach($configs as $key){
			$el = \Core\Configs\get_form_element_from_config(ConfigModel::Construct($key));
			// I don't need this, (Everything from this group will be on the root-level form).
			$el->set('group', null);
			$configform->addElement($el);
		}

		$configform->addElement('submit', ['name' => 'submit', 'value' => 'Save Password']);

		$view->title = 'Simple Site Password';
		$view->assign('form', $configform);
	}

	/**
	 * Display a list of cron jobs that have ran.
	 * @return int
	 */
	public function log(){

		// As of 3.0.0, this has been merged into Core.
		\Core\redirect('admin/log?filter[type]=security');

		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->checkAccess('p:/security/viewlog')){
			return View::ERROR_ACCESSDENIED;
		}

		$filters = new FilterForm();
		$filters->setName('security-log');
		$filters->hassort = true;
		$filters->haspagination = true;
		/*$filters->addElement(
			'select',
			array(
				'title' => 'Cron',
				'name' => 'cron',
				'options' => array(
					'' => '-- All --',
					'hourly' => 'hourly',
					'daily' => 'daily',
					'weekly' => 'weekly',
					'monthly' => 'monthly'
				),
				'link' => FilterForm::LINK_TYPE_STANDARD,
			)
		);
		$filters->addElement(
			'select',
			array(
				'title' => 'Status',
				'name' => 'status',
				'options' => array(
					'' => '-- All --',
					'pass' => 'pass',
					'fail' => 'fail'
				),
				'link' => FilterForm::LINK_TYPE_STANDARD,
			)
		);*/

		$filters->addElement(
			'hidden',
			array(
				'title' => 'Session',
				'name' => 'session_id',
				'link' => FilterForm::LINK_TYPE_STANDARD,
			)
		);
		$filters->addElement(
			'hidden',
			array(
				'title' => 'Affected User',
				'name' => 'affected_user_id',
				'link' => FilterForm::LINK_TYPE_STANDARD,
			)
		);
		$filters->setSortkeys(array('datetime', 'session_id', 'user_id', 'useragent', 'action', 'affected_user_id', 'status'));
		$filters->load($request);


		$factory = new ModelFactory('SecurityLogModel');
		$filters->applyToFactory($factory);
		$listings = $factory->get();

		foreach($listings as $k => $entry){
			/** @var $entry SecurityLogModel */
			// Look up the user agent
			//$ua = new \Core\UserAgent($entry->get('useragent'));
			//var_dump($ua); die();

			if($entry->get('user_id')){
				$userobject = UserModel::Construct($entry->get('user_id'));
				$entry->set('user', $userobject->getDisplayName());
			}

			if($entry->get('affected_user_id')){
				$userobject = UserModel::Construct($entry->get('affected_user_id'));
				if($userobject){
					$entry->set('affected_user', $userobject->getDisplayName());
				}
				else{
					$entry->set('affected_user', '[DELETED USER]');
				}

			}
		}

		$view->title = 'Security Log';
		$view->assign('filters', $filters);
		$view->assign('listings', $listings);
		$view->assign('sortkey', $filters->getSortKey());
		$view->assign('sortdir', $filters->getSortDirection());

		//var_dump($listings); die();
	}

	/**
	 * View a specific cron execution and its details.
	 */
	public function view(){
		$view = $this->getView();
		$request = $this->getPageRequest();
		$view->mode = View::MODE_PAGEORAJAX;

		if(!\Core\user()->checkAccess('p:/security/viewlog')){
			return View::ERROR_ACCESSDENIED;
		}

		$logid = $request->getParameter(0);
		$log = SecurityLogModel::Construct($logid);
		if(!$log->exists()){
			return View::ERROR_NOTFOUND;
		}

		if($log->get('user_id')){
			$userobject = UserModel::Construct($log->get('user_id'));
			$user = $userobject->getDisplayName();
		}
		else{
			$user = null;
		}

		if($log->get('affected_user_id')){
			$userobject = UserModel::Construct($log->get('affected_user_id'));
			$affected_user = $userobject->getDisplayName();
		}
		else{
			$affected_user = null;
		}

		$view->addBreadcrumb('Security Log', '/security/log');
		$view->title = 'Details';
		$view->assign('entry', $log);
		$view->assign('user', $user);
		$view->assign('affected_user', $affected_user);
	}

	/**
	 * Display a list of blacklisted IP addresses and subnets.
	 */
	public function blacklistip(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$listing = new Core\ListingTable\Table();
		$listing->setModelName('IpBlacklistModel');
		$listing->addColumn('IP or Network', 'ip_addr');
		$listing->addColumn('Comment');
		$listing->addColumn('Expires', 'expires');
		$listing->addColumn('Created', 'created');
		$listing->setDefaultSort('created', 'DESC');

		$listing->loadFiltersFromRequest($request);

		$view->addControl([
			'title' => 'Ban IP...',
			'icon' => 'add',
			'link' => '/security/blacklistip/add'
		]);
		$view->title = 'Blacklisted IP addresses';
		$view->assign('listings', $listing);
	}

	/**
	 * Quick-add an IP address to the blacklist.
	 */
	public function blacklistip_add(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$ban = new IpBlacklistModel();
		$ban->set('ip_addr', $request->getParameter('ip_addr'));
		$ban->set('message', 'Your IP address has been blocked from this site by the administrator!');

		$form = new Form();
		$form->set('callsmethod', 'SecurityController::SaveBlacklistIp');
		$form->addModel($ban, 'model');
		$form->addElement('submit', ['name' => 'submit', 'value' => 'Ban IP!']);

		$view->title = 'Ban IP';
		$view->assign('form', $form);
	}

	/**
	 * Quick-add an IP address to the blacklist.
	 */
	public function blacklistip_edit(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$ban = new IpBlacklistModel($request->getParameter(0));

		if(!$ban->exists()){
			return View::ERROR_NOTFOUND;
		}

		$form = new Form();
		$form->set('callsmethod', 'SecurityController::SaveBlacklistIp');
		$form->addModel($ban, 'model');
		$form->addElement('submit', ['name' => 'submit', 'value' => 'Edit Banned IP!']);

		$view->title = 'Edit Banned IP';
		$view->assign('form', $form);
	}

	public function blacklistip_delete(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$ban = new IpBlacklistModel($request->getParameter(0));

		if(!$ban->exists()){
			return View::ERROR_NOTFOUND;
		}

		if(!$request->isPost()){
			return View::ERROR_BADREQUEST;
		}

		$ban->delete();
		Core::SetMessage('Removed ban successfully', 'success');
		Core::GoBack();
	}

	/**
	 * Interface to display and manage the spam keywords on the site.
	 */
	public function spam_keywords(){
		$view = $this->getView();

		if(!\Core\user()->checkAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}

		$threshold = \ConfigHandler::Get('/security/spam_threshold');
		$table = new Core\ListingTable\Table();

		$table->setLimit(100);

		// Set the model that this table will be pulling data from.
		$table->setModelName('SpamHamKeywordModel');

		// Add in all the columns for this listing table.
		$table->addColumn('Keyword', 'keyword');
		$table->addColumn('Score', 'score');

		// This page will also feature a quick-edit feature.
		$table->setEditFormCaller('SecurityController::SpamKeywordsSave');

		$table->loadFiltersFromRequest();

		$view->addControl('Import Spam Training', '/security/spam/train', 'strikethrough');

		$view->mastertemplate = 'admin';
		$view->title = 'Spam Keywords';

		$view->assign('listing', $table);
		$view->assign('threshold', $threshold);
	}

	/**
	 * Interface to "train" the system to learn spam keywords.
	 *
	 * A block of content can be submitted to this page, where the user has the options to score phrases and words.
	 */
	public function spam_train() {
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->checkAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}

		$view->title = 'Spam Training';
		$view->mastertemplate = 'admin';

		if($request->isPost() && $request->getPost('keywords')){
			foreach($_POST['keywords'] as $w => $s){
				if($s == 0){
					// Populating the database with a bunch of neutral scores is pointless.
					continue;
				}
				$k = SpamHamKeywordModel::Construct($w);
				$k->set('score', $s);
				$k->save();
			}

			Core::SetMessage('Trained keywords successfully!', 'success');
			\Core\redirect('/security/spam/keywords');
		}
		elseif(!$request->isPost() || !$request->getPost('content')){
			// Step 1 for training with content, provide a text area to submit content!
			$view->templatename = 'pages/security/spam_train_1.tpl';

			$form = new Form();
			$form->addElement(
				'textarea',
				[
					'name' => 'content',
					'value' => '',
					'title' => 'Content',
					'description' => 'Paste in the content to parse for keywords.  You will have the ability to fine-tune specific keywords on the next page.',
					'rows' => 6,
				]
			);
			$form->addElement('submit', ['value' => 'Next']);

			$view->assign('form', $form);
		}
		else{
			// Step 2,
			$view->templatename = 'pages/security/spam_train_2.tpl';

			$check = new \SecuritySuite\SpamCan\SpamCheck($request->getPost('content'));
			$keywords = $check->getKeywords();

			$form = new Form();
			$form->set('orientation', 'grid');

			foreach($keywords as $dat){
				if($dat['score'] != 0){
					// Skip keywords that are already weighted.
					continue;
				}

				if(preg_match_all('# #', $dat['keyword']) == 1){
					// Skip keywords that only contain one space.
					// Here, we only want single words and 3-word phrases.
					continue;
				}

				$form->addElement(
					'text',
					[
						'name' => 'keywords[' . $dat['keyword'] . ']',
						'title' => $dat['keyword'],
						'value' => 0,
					]
				);
			}

			$form->addElement('submit', ['value' => 'Train!']);

			$view->assign('form', $form);
		}
	}

	/**
	 * Save the site password.
	 *
	 * @param Form $form
	 *
	 * @return bool
	 */
	public static function SitePasswordSave(Form $form){
		$pass = $form->getElement('config[/security/site_password]')->get('value');

		\ConfigHandler::Set('/security/site_password', $pass);

		return true;
	}

	/**
	 * @param Form $form
	 * @return false|string
	 */
	public static function SaveBlacklistIp(Form $form){
		try{
			$ban = $form->getModel('model');

			// First thing... check and make sure that this directive won't block out the current user!
			$longip = ip2long(REMOTE_IP);
			for($i=32; $i>0; $i--){
				$mask = ~((1 << (32 - $i)) - 1);
				$join = long2ip($longip & $mask) . '/' . $i;
				if($join == $ban->get('ip_addr')){
					Core::SetMessage('Corwardly refusing to ban an IP range that will blacklist your current connection!', 'error');
					return false;
				}
			}

			// The expires value will probably come in as a date string :/
			if($ban->get('expires')){
				$date = new CoreDateTime($ban->get('expires'));
				$ban->set('expires', $date->getFormatted('U', Time::TIMEZONE_GMT));
			}

			$ban->save();
			Core::SetMessage('Banned IP range ' . $ban->get('ip_addr'), 'success');
			return 'back';
		}
		catch(Exception $e){
			Core::SetMessage($e->getMessage());
			return false;
		}
	}

	public static function SpamKeywordsSave(Form $form) {
		ConfigHandler::Set('/security/spam_threshold', $form->getElementValue('threshold'));

		foreach($form->getElements() as $el){
			/** @var FormElement $el */
			$n = $el->get('name');
			if(strpos($n, 'score[') === 0){
				$n = substr($n, 6, -1);
				$s = $el->get('value');

				if($s == '') $s = 1;

				$model = SpamHamKeywordModel::Construct($n);
				$model->set('score', $s);
				$model->save();
			}
		}

		if($form->getElementValue('new_keyword')){
			$n = $form->getElementValue('new_keyword');
			$s = $form->getElementValue('new_score');

			if($s == '') $s = 1;

			$model = SpamHamKeywordModel::Construct($n);
			$model->set('score', $s);
			$model->save();
		}

		return true;
	}
}