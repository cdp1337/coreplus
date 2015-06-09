<?php
/**
 * File for class WPHoneypotController definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130612.1732
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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
 * A short teaser of what WPHoneypotController does.
 *
 * More lengthy description of what WPHoneypotController does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for WPHoneypotController
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
class WPHoneypotController extends Controller_2_1 {
	public function wplogin() {
		$view = $this->getView();
		$request = $this->getPageRequest();

		if($request->isPost()){
			// Did they actually try to submit this form?......  silly bot ;)
			SystemLogModel::LogSecurityEvent('/wp-login Honeypot POST', 'POST submission to /wp-login detected!', print_r($_POST, true));
			$expireback = new CoreDateTime();
			$expireback->modify('+2 days');
			$block = IpBlacklistModel::Find(['ip_addr = ' . REMOTE_IP . '/32'], 1);
			if(!$block){
				$block = new IpBlacklistModel();
				$block->set('ip_addr', REMOTE_IP . '/32');
			}
			$block->setFromArray([
				'expires' => $expireback->getFormatted('U', Time::TIMEZONE_GMT),
				'message' => 'You tried to submit a wp-login page.... this is not a WP site!',
				'comment' => 'Bot or user submitted to wp-login'
			]);
			$block->save();
		}
		else{
			// Just record the hit.
			SystemLogModel::LogSecurityEvent('/wp-login Honeypot GET', 'GET request to /wp-login detected!');
		}


		$view->templatename = 'pages/wphoneypot/wplogin.phtml';
		$view->mastertemplate = false;
	}

	public function wpadmin() {
		$view = $this->getView();
		$request = $this->getPageRequest();

		if($request->isPost()){
			// Did they actually try to submit this form?......  silly bot ;)
			SystemLogModel::LogSecurityEvent('/wp-admin Honeypot POST', 'POST submission to /wp-admin detected!', print_r($_POST, true));
			$expireback = new CoreDateTime();
			$expireback->modify('+2 days');
			$block = IpBlacklistModel::Find(['ip_addr = ' . REMOTE_IP . '/32'], 1);
			if(!$block){
				$block = new IpBlacklistModel();
				$block->set('ip_addr', REMOTE_IP . '/32');
			}
			$block->setFromArray([
					'expires' => $expireback->getFormatted('U', Time::TIMEZONE_GMT),
					'message' => 'You tried to submit a wp-admin page.... this is not a WP site!',
					'comment' => 'Bot or user submitted to wp-admin'
				]);
			$block->save();
		}
		else{
			// Just record the hit.
			SystemLogModel::LogSecurityEvent('/wp-admin Honeypot GET', 'GET request to /wp-admin detected!');
		}

		$view->templatename = 'pages/wphoneypot/wpadmin.phtml';
		$view->mastertemplate = false;
	}
}