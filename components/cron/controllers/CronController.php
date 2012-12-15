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
	 * Execute the daily cron
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
		$log->set('ip', REMOTE_IP);
		$log->save();
//var_dump($log); die();
		$start = microtime(true) * 1000;

		// Since these systems will just be writing to STDOUT, I'll need to capture that.
		ob_start();
		$result = HookHandler::DispatchHook('/cron/' . $cron);
		$contents = ob_get_clean();

		// Just in case the contents are returning html... (they should be plain text).
		$contents = str_replace(array('<br>', '<br/>', '<br />'), "\n", $contents);
		// And standardize line endings.
		$contents = str_replace(array("\r\n", "\r"), "\n", $contents);

		// Save the results.
		$log->set('completed', Time::GetCurrentGMT());
		$log->set('duration', ( (microtime(true) * 1000) - $start ) );
		$log->set('log', $contents);
		$log->set('status', ($result ? 'pass' : 'fail') );
		$log->save();

		// Just to notify the calling function.
		return $log;
	}
}