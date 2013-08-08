<?php
/**
 * File for class IpBlacklistHelper definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130423.0245
 * @copyright Copyright (C) 2009-2013  Author
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
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
abstract class IpBlacklistHelper {
	/**
	 * Check the user's IP and see if it's blacklisted.
	 */
	public static function CheckIP() {

		$factory = new ModelFactory('IpBlacklistModel');
		$factory->whereGroup(
			'OR',
			[
				'expires > ' . CoreDateTime::Now('U', Time::TIMEZONE_GMT),
				'expires == 0'
			]
		);
		$where = new DatasetWhereClause();
		$where->setSeparator('or');

		$longip = ip2long(REMOTE_IP);
		for($i=32; $i>0; $i--){
			$mask = ~((1 << (32 - $i)) - 1);
			$where->addWhere('ip_addr = ' . long2ip($longip & $mask) . '/' . $i);
		}
		$factory->where($where);
		$factory->limit(1);

		$ban = $factory->get();

		if(!$ban){
			// Ok, you may pass.
			return;
		}
		// else... hehehe, happy happy fun time for you!
		SecurityLogModel::Log(
			'/security/blocked',
			null,
			null,
			'Blacklisted IP tried to access the site!<br/>Remote IP: ' . REMOTE_IP . '<br/>Matching Range: ' . $ban->get('ip_addr') . '<br/>Requested URL: ' . CUR_CALL
		);

		die($ban->get('message'));
	}

	/**
	 * Method to cleanup expired IP addresses from the database.
	 *
	 * @return bool
	 */
	public static function CleanupHook() {
		$factory = new ModelFactory('IpBlacklistModel');
		$factory->where('expires > 0'); // If they're set not to be deleted, don't purge them...
		$factory->where('expires <= ' . CoreDateTime::Now('U', Time::TIMEZONE_GMT));

		// DELETE!
		$count = $factory->count();
		if(!$count){
			echo 'No records purged.';
			return true;
		}


		foreach($factory->get() as $record){
			/** @var $record IpBlacklistModel */
			$record->delete();
		}

		echo "Purged " . $count . ' record'. ($count > 1 ? 's' : '') . ' successfully.';
		return true;
	}
}