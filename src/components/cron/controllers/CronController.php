<?php
/**
 * Class file for the controller CronController
 *
 * @package Cron
 */
class CronController extends Controller_2_1 {
	// Each controller can have many views, each defined by a different method.
	// These methods should be regular public functions that DO NOT begin with an underscore (_).
	// Any method that begins with an underscore or is static will be assumed as an internal method
	// and cannot be called externally via a url.

	/**
	 * Execute the hourly cron
	 */
	public function hourly(){
		$request = $this->getPageRequest();
		$view    = $this->getView();
		$view->mode = View::MODE_NOOUTPUT;

		// Check and see if I need to run this cron already, ie: don't run an hourly log twice in the same hour.
		$last = CronLogModel::Find(array('cron' => 'hourly'), 1, 'created DESC');
		if($last && (Time::GetCurrentGMT() - $last->get('created') < 3540) ){
			// No run needed, already ran within the past 59 minutes.

			// Report this.
			$logmsg = "Not re-running, already executed hourly cron " . floor((Time::GetCurrentGMT() - $last->get('created')) / 60) . ' minutes ago';
			error_log('[CRON] - ' . $logmsg);
			return;
		}

		$log = $this->_performcron('hourly');
		if($log->get('status') != 'pass'){
			echo 'failed, check the logs';
		}
	}

	/**
	 * Execute the daily cron
	 */
	public function daily(){
		$request = $this->getPageRequest();
		$view    = $this->getView();
		$view->mode = View::MODE_NOOUTPUT;

		// Check and see if I need to run this cron already, ie: don't run an hourly log twice in the same hour.
		$last = CronLogModel::Find(array('cron' => 'daily'), 1, 'created DESC');
		if($last && (Time::GetCurrentGMT('Ymd') == Time::FormatGMT($last->get('created'), Time::TIMEZONE_GMT, 'Ymd')) ){
			// No run needed, already ran today.

			// Report this.
			$timelast = floor((Time::GetCurrentGMT() - $last->get('created')) / 60);
			if($timelast >= 120) $timelast = floor((Time::GetCurrentGMT() - $last->get('created')) / 3600) . ' hours';
			else $timelast .= ' minutes';

			$logmsg = "Not re-running, already executed daily cron " . $timelast . ' ago';
			error_log('[CRON] - ' . $logmsg);
			return;
		}

		$log = $this->_performcron('daily');
		if($log->get('status') != 'pass'){
			echo 'failed, check the logs';
		}
	}

	/**
	 * Execute the weekly cron
	 */
	public function weekly(){
		$request = $this->getPageRequest();
		$view    = $this->getView();
		$view->mode = View::MODE_NOOUTPUT;

		// Check and see if I need to run this cron already, ie: don't run an hourly log twice in the same hour.
		$last = CronLogModel::Find(array('cron' => 'weekly'), 1, 'created DESC');
		// 3600 seconds in an hour * 24 hours in a day * 7 days in a week - 10 seconds.
		if($last && (Time::GetCurrentGMT() - $last->get('created') < (3600 * 24 * 7 - 10) ) ){
			// No run needed, already ran recently.

			// Report this.
			$timelast = floor((Time::GetCurrentGMT() - $last->get('created')) / 60);
			if($timelast >= (3600*48)) $timelast = floor((Time::GetCurrentGMT() - $last->get('created')) / 3600*24) . ' days';
			elseif($timelast >= 120) $timelast = floor((Time::GetCurrentGMT() - $last->get('created')) / 3600) . ' hours';
			else $timelast .= ' minutes';

			$logmsg = "Not re-running, already executed weekly cron " . $timelast . ' ago';
			error_log('[CRON] - ' . $logmsg);
			return;
		}

		$log = $this->_performcron('weekly');
		if($log->get('status') != 'pass'){
			echo 'failed, check the logs';
		}
	}

	/**
	 * Execute the monthly cron
	 */
	public function monthly(){
		$request = $this->getPageRequest();
		$view    = $this->getView();
		$view->mode = View::MODE_NOOUTPUT;

		// Check and see if I need to run this cron already, ie: don't run an hourly log twice in the same hour.
		$last = CronLogModel::Find(array('cron' => 'monthly'), 1, 'created DESC');
		if($last && (Time::GetCurrentGMT('Ym') == Time::FormatGMT($last->get('created'), Time::TIMEZONE_GMT, 'Ym')) ){
			// No run needed, already ran this month.

			// Report this.
			$timelast = floor((Time::GetCurrentGMT() - $last->get('created')) / 60);
			if($timelast >= (3600*48)) $timelast = floor((Time::GetCurrentGMT() - $last->get('created')) / 3600*24) . ' days';
			elseif($timelast >= 120) $timelast = floor((Time::GetCurrentGMT() - $last->get('created')) / 3600) . ' hours';
			else $timelast .= ' minutes';

			$logmsg = "Not re-running, already executed monthly cron " . $timelast . ' ago';
			error_log('[CRON] - ' . $logmsg);
			return;
		}

		$log = $this->_performcron('monthly');
		if($log->get('status') != 'pass'){
			echo 'failed, check the logs';
		}
	}


	/**
	 * Display a list of cron jobs that have ran.
	 * @return int
	 */
	public function admin(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->checkAccess('p:/cron/viewlog')){
			return View::ERROR_ACCESSDENIED;
		}

		$filters = new FilterForm();
		$filters->setName('cron-admin');
		$filters->hassort = true;
		$filters->haspagination = true;
		$filters->addElement(
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
		);
		$filters->setSortkeys(array('cron', 'created', 'duration', 'status'));
		$filters->load($request);


		$cronfac = new ModelFactory('CronLogModel');
		$filters->applyToFactory($cronfac);
		$listings = $cronfac->get();

		$view->mastertemplate = 'admin';
		$view->title = 'Cron Results';
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

		if(!\Core\user()->checkAccess('p:/cron/viewlog')){
			return View::ERROR_ACCESSDENIED;
		}

		$logid = $request->getParameter(0);
		$log = CronLogModel::Construct($logid);
		if(!$log->exists()){
			return View::ERROR_NOTFOUND;
		}

		$view->mastertemplate = 'admin';
		$view->addBreadcrumb('Cron Results', '/cron/admin');
		$view->title = 'Log Execution Details';
		$view->assign('entry', $log);
	}

	/**
	 * Provide the admins with a general "howto" page for setting up cron jobs.
	 *
	 * This is linked to from the "your cron hasn't run recently" error.
	 */
	public function howto(){
		$view = $this->getView();
		$request = $this->getPageRequest();
		$view->mode = View::MODE_PAGEORAJAX;

		if(!\Core\user()->checkAccess('p:/cron/viewlog')){
			return View::ERROR_ACCESSDENIED;
		}

		$view->mastertemplate = 'admin';
		$view->title = 'Cron Howto';
		$view->assign('url', ROOT_URL_NOSSL);
		$view->assign('sitename', SITENAME);
	}


	/**
	 * @param $cron
	 *
	 * @return CronLogModel
	 * @throws Exception
	 */
	private function _performcron($cron){
		switch($cron){
			case 'hourly':
			case 'daily':
			case 'weekly':
			case 'monthly':
				break;
			default:
				throw new Exception('Unsupported cron type: [' . $cron . ']');
		}

		// First, check and see if there's one that's still running.
		$runninglogs = CronLogModel::Find(array('cron' => $cron, 'status' => 'running'));
		if(sizeof($runninglogs)){
			foreach($runninglogs as $log){
				/** @var $log CronLogModel */
				$log->set('status', 'fail');
				$log->set('log', $log->get('log') . "\n------------\nTIMED OUT!");
				$log->save();
			}
		}



		// Start recording.
		$log = new CronLogModel();
		$log->set('cron', $cron);
		$log->set('status', 'running');
		$log->set('memory', memory_get_usage());
		$log->set('ip', REMOTE_IP);
		$log->save();
//var_dump($log); die();
		$start = microtime(true) * 1000;

		$sep = '==========================================' . "\n";
		$contents = "Starting cron execution for $cron\n$sep";

		// This uses the hook system, but will be slightly different than most things.
		$overallresult = true;
		$hook = HookHandler::GetHook('/cron/' . $cron);
		if($hook){
			if($hook->getBindingCount()){
				$bindings = $hook->getBindings();
				foreach($bindings as $b){
					$contents .= sprintf(
						"\nExecuting Binding %s...\n",
						$b['call']
					);
					// Since these systems will just be writing to STDOUT, I'll need to capture that.
					ob_start();
					$execution = $hook->callBinding($b, array());
					$executiondata = ob_get_clean();

					if($executiondata == '' && $execution){
						$contents .= "Cron executed successfully with no output\n";
					}
					elseif($execution){
						$contents .= $executiondata . "\n";
					}
					else{
						$contents .= $executiondata . "\n!!FAILED\n";
						$overallresult = false;
					}
				}
			}
			else{
				$contents = 'No bindings located for requested cron';
				$overallresult = true;
			}
		}
		else{
			$contents = 'Invalid hook requested: ' . $cron;
			$overallresult = false;
		}


		// Just in case the contents are returning html... (they should be plain text).
		$contents = str_replace(array('<br>', '<br/>', '<br />'), "\n", $contents);
		// And standardize line endings.
		$contents = str_replace(array("\r\n", "\r"), "\n", $contents);

		// Save the results.
		$log->set('completed', Time::GetCurrentGMT());
		$log->set('duration', ( (microtime(true) * 1000) - $start ) );
		$log->set('log', $contents);
		$log->set('status', ($overallresult ? 'pass' : 'fail') );
		$log->save();

		// Just to notify the calling function.
		return $log;
	}
}