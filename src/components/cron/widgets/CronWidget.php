<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 2/11/13
 * Time: 5:17 AM
 * To change this template use File | Settings | File Templates.
 */
class CronWidget extends \Core\Widget {
	/**
	 * The view for the admin dashboard.  Gets the last executed crons and displays that to the admin.
	 */
	public function dashboard(){
		// This dashboard has no effect if the user can't view crons.
		if(!\Core\user()->checkAccess('p:/cron/viewlog')){
			return '';
		}

		$view = $this->getView();

		// Get the latest cron and its execution information and display that to the dashboard.
		$checks = [
			['cron' => 'hourly',  'modify' => '-1 hour',  'label' => 'hour'],
			['cron' => 'daily',   'modify' => '-1 day',   'label' => 'day'],
			['cron' => 'weekly',  'modify' => '-1 week',  'label' => 'week'],
			['cron' => 'monthly', 'modify' => '-1 month', 'label' => 'month'],
		];

		$crons = array();
		foreach($checks as $k => $check){
			$time = new CoreDateTime();
			$cronfac = new ModelFactory('CronLogModel');
			$cronfac->limit(1);
			$cronfac->where('cron = ' . $check['cron']);
			$cronfac->order('created desc');

			$c = $cronfac->get();
			if($c){
				$crons[] = $c;
			}
		}

		$view->title = 't:STRING_LATEST_CRON_RESULTS';
		$view->assign('crons', $crons);
	}
}
