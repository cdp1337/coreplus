<?php
/**
 * Class file for the controller CronController
 *
 * @package Security-Suite
 */
class SecurityController extends Controller_2_1 {

	/**
	 * Display a list of cron jobs that have ran.
	 * @return int
	 */
	public function log(){
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
				$userobject = User::Construct($entry->get('user_id'));
				$entry->set('user', $userobject->getDisplayName());
			}

			if($entry->get('affected_user_id')){
				$userobject = User::Construct($entry->get('affected_user_id'));
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
			$userobject = User::Construct($log->get('user_id'));
			$user = $userobject->getDisplayName();
		}
		else{
			$user = null;
		}

		if($log->get('affected_user_id')){
			$userobject = User::Construct($log->get('affected_user_id'));
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

		$factory = new ModelFactory('IpBlacklistModel');

		$view->addControl([
			'title' => 'Ban IP...',
			'icon' => 'add',
			'link' => '/security/blacklistip/add'
		]);
		$view->title = 'Blacklisted IP addresses';
		$view->assign('listings', $factory->get());
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
			return Core::GetHistory(2);
		}
		catch(Exception $e){
			Core::SetMessage($e->getMessage());
			return false;
		}
	}
}