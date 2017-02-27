<?php
/**
 * Provides the main widgets for user functions.
 *
 * @package User
 * @since 2.0
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2016  Charlie Powell
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

class UserWidget extends \Core\Widget {

	/**
	 * The small link-only widget.
	 */
	public function login() {
		$v = $this->getView();

		$user = \Core\user();

		$v->assign('user', $user);
		$v->assign('loggedin', $user->exists());
		$v->assign('allowregister', ConfigHandler::Get('/user/register/allowpublic'));
	}

	/**
	 * Display a login widget with the actual login fields present.
	 */
	public function loginfull(){
		$view = $this->getView();

		// Is the user already logged in?
		if(\Core\user()->exists()){
			$view->assign('user', \Core\user());
		}
		else{
			// Display the standard login forms.

			// Set the page to use SSL if possible, since this login form has sensitive information, (username/pass).
			\Core\view()->ssl = true;

			$auths = \Core\User\Helper::GetEnabledAuthDrivers();

			$view->assign('user', false);
			$view->assign('drivers', $auths);
			$view->assign('allowregister', ConfigHandler::Get('/user/register/allowpublic'));
		}
	}

	public function register() {

		$view = $this->getView();
		$user = \Core\user();

		// Set the access permissions for this page as anonymous-only.
		if(!$user->checkAccess('g:anonymous;g:!admin')){
			return '';
		}

		// Also disallow access to this page if the configuration option is disabled.
		if(!ConfigHandler::Get('/user/register/allowpublic')){
			return '';
		}

		$auths = \Core\User\Helper::GetEnabledAuthDrivers();
		$view->assign('drivers', $auths);
	}

	/**
	 * Get the path for the preview image for this widget.
	 *
	 * Should be an image of size 210x70, 210x140, or 210x210.
	 *
	 * @return string
	 */
	public function getPreviewImage(){
		$instance = $this->getWidgetInstanceModel();
		$baseurl = $instance ? $instance->get('baseurl') : null;
		$base = 'assets/images/previews/templates/widgets/user/';

		switch($baseurl){
			case '/user/login':
				return $base . 'user-login-link.png';
			case '/user/loginfull':
				return $base . 'user-login-form.png';
			case '/user/register':
				return $base . 'user-registration-form.png';
			default:
				//var_dump($baseurl);
				return '';
		}
	}
}
