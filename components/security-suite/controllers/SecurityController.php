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
			//$ua = new UserAgent($entry->get('useragent'));
			//var_dump($ua); die();

			if($entry->get('user_id')){
				$userobject = User::Construct($entry->get('user_id'));
				$entry->set('user', $userobject->getDisplayName());
			}

			if($entry->get('affected_user_id')){
				$userobject = User::Construct($entry->get('affected_user_id'));
				$entry->set('affected_user', $userobject->getDisplayName());
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

		list($dt, $s) = explode('-', $request->getParameter(0));
		if(!$dt || !$s){
			return View::ERROR_NOTFOUND;
		}
		$log = SecurityLogModel::Construct($dt, $s);
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
}