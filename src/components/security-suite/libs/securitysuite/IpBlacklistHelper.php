<?php
/**
 * File for class IpBlacklistHelper definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130423.0245
 * @copyright Copyright (C) 2009-2017  Charlie Powell
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

namespace SecuritySuite;
use Core\Datamodel\Dataset;
use Core\Date\DateTime;

/**
 * A short teaser of what IpBlacklistHelper does.
 *
 * More lengthy description of what IpBlacklistHelper does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for IpBlacklistHelper
 * <h4>Example 1</h4>
 * <p>Description 1</p>
 * <code>
 * // Some code for example 1
 * $a = $b;
 * </code>
 *
 *
 * <h4>Example 2</h4>
 * <p>Description 2</p>
 * <code>
 * // Some code for example 2
 * $b = $a;
 * </code>
 *
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
abstract class IpBlacklistHelper {
	/**
	 * Check the user's IP and see if it's blacklisted.
	 */
	public static function CheckIP() {

		$factory = new \ModelFactory('IpBlacklistModel');
		/*$factory->whereGroup(
			'OR',
			[
				'expires > ' . \CoreDateTime::Now('U', \Time::TIMEZONE_GMT),
				'expires = 0'
			]
		);*/
		$where = new \Core\Datamodel\DatasetWhereClause();

		$ips = [];
		$longip = ip2long(REMOTE_IP);
		for($i=32; $i>0; $i--){
			if($i < 16){
				// Skip anything smaller than a /16.
				break;
			}
			$mask = ~((1 << (32 - $i)) - 1);
			$ips[] = long2ip($longip & $mask) . '/' . $i;
			//$where->addWhere('ip_addr = ' . long2ip($longip & $mask) . '/' . $i);
		}
		$factory->where('ip_addr IN ' . implode(',', $ips));
		$factory->limit(1);

		$ban = $factory->get();

		if(!$ban){
			// Ok, you may pass.
			return;
		}
		// Check the date
		if($ban->get('expires') != 0 && $ban->get('expires') < DateTime::NowGMT()){
			// Well it has one, but it's already expired.
			// Go ahead and clean it up.
			$ban->delete();
			return;
		}

		// else... hehehe, happy happy fun time for you!
		\SystemLogModel::LogSecurityEvent(
			'/security/blocked',
			'Blacklisted IP tried to access the site (' . REMOTE_IP . ')',
			'Blacklisted IP tried to access the site!<br/>Remote IP: ' . REMOTE_IP . '<br/>Matching Range: ' . $ban->get('ip_addr') . '<br/>Requested URL: ' . CUR_CALL
		);

		header('HTTP/1.0 420 Enhance Your Calm');
		die($ban->get('message'));
	}

	/**
	 * This will check and see how many 404 requests there have been recently.
	 *
	 * @return bool
	 */
	public static function Check404Pages() {
		// How long back do I want to check the logs?
		$time = new DateTime();
		$time->modify('-30 seconds');

		$ds = Dataset::Init()
			->table('user_activity')
			->where(
				[
					'status = 404',
					'ip_addr = ' . REMOTE_IP,
					'datetime > ' . $time->format('U')
				]
			)
			->count()
			->execute();

		if($ds->num_rows > 30){
			// CHILL THAR FUCKER!

			$time->modify('+6 minutes');

			$blacklist = new \IpBlacklistModel();
			$blacklist->setFromArray(
				[
					'ip_addr' => REMOTE_IP . '/24',
					'expires' => $time->format('U'),
					'message' => 'You have requested too many "404" pages recently, please go get some coffee and wait for a short bit.  If you are a bot and/or spammer, please bugger off.',
					'comment' => '5-minute auto-ban for too many 404 requests in 30 seconds',
				]
			);
			$blacklist->save();

			\SystemLogModel::LogSecurityEvent('/security/blocked', 'Blocking IP due to too many 404 requests in 30 seconds.');

			die($blacklist->get('message'));
		}
	}

	/**
	 * Method to cleanup expired IP addresses from the database.
	 *
	 * @return bool
	 */
	public static function CleanupHook() {
		$factory = new \ModelFactory('IpBlacklistModel');
		$factory->where('expires > 0'); // If they're set not to be deleted, don't purge them...
		$factory->where('expires <= ' . \CoreDateTime::Now('U', \Time::TIMEZONE_GMT));

		// DELETE!
		$count = $factory->count();
		if(!$count){
			echo 'No records purged.';
			return true;
		}


		foreach($factory->get() as $record){
			/** @var $record \IpBlacklistModel */
			$record->delete();
		}

		echo "Purged " . $count . ' record'. ($count > 1 ? 's' : '') . ' successfully.';
		return true;
	}
}