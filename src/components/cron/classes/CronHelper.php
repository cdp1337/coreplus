<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 2/11/13
 * Time: 5:35 AM
 * To change this template use File | Settings | File Templates.
 */
abstract class CronHelper {

	/**
	 * The hook catch for the "/core/admin/view" hook.
	 */
	public static function AdminHook(){
		// If this user doesn't have access to manage crons, just continue.
		if(!\Core\user()->checkAccess('p:/cron/viewlog')){
			return;
		}

		$suffixtext = 'This could be a problem if you have scripts relying on it!  <a href="' . \Core\resolve_link('/cron/howto') . '">Read how to resolve this issue</a>.';

		// Lookup and make sure that the cron hooks have ran recently enough!
		$checks = [
			['cron' => 'hourly',  'modify' => '-1 hour',  'label' => 'hour'],
			['cron' => 'daily',   'modify' => '-1 day',   'label' => 'day'],
			['cron' => 'weekly',  'modify' => '-1 week',  'label' => 'week'],
			['cron' => 'monthly', 'modify' => '-1 month', 'label' => 'month'],
		];

		foreach($checks as $check){
			$time = new CoreDateTime();
			$cronfac = new ModelFactory('CronLogModel');
			$cronfac->where('cron = ' . $check['cron']);
			$time->modify($check['modify']);
			$cronfac->where('created >= ' . $time->getFormatted('U', Time::TIMEZONE_GMT));
			$count = $cronfac->count();

			if($count == 0){
				Core::SetMessage('Your ' . $check['cron'] . ' cron has not run in the last ' . $check['label'] . '!  ' . $suffixtext, 'error');
				// Only complain to the admin once per view.
				return;
			}
		}
	}
}
