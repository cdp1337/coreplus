<?php
/**
 * File for class UserAuth definition in the coreplus project
 * 
 * @package Facebook
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20131204.2242
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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

namespace Facebook;
use Core\User\AuthDriverInterface;


/**
 * A short teaser of what UserAuth does.
 *
 * More lengthy description of what UserAuth does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for UserAuth
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
 * @package Facebook
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class UserAuth implements AuthDriverInterface {

	/**
	 * @var \UserModel The parent model object for this user.
	 */
	protected $_usermodel;

	public function __construct(\UserModel $usermodel = null) {
		$this->_usermodel = $usermodel;
	}

	/**
	 * Check that the supplied password or key is valid for this user.
	 *
	 * @param string $password The password to verify
	 *
	 * @return boolean
	 */
	public function checkPassword($password) {
		// TODO: Implement checkPassword() method.
	}

	/**
	 * Set the user's password using the necessary hashing
	 *
	 * @param $password
	 *
	 * @return bool|string True/False on success or failure, a string if on error.
	 */
	public function setPassword($password) {
		return 'Please go to facebook.com to reset your password.';
	}

	/**
	 * Check if this user is active and can login.
	 *
	 * @return boolean
	 */
	public function isActive() {
		return $this->_usermodel->get('active');
	}

	/**
	 * Get if this user can set their password via the site.
	 *
	 * @return bool|string True if backend allows for password management, a string if cannot.
	 */
	public function canSetPassword() {
		return 'Please go to facebook.com to reset your password.';
	}

	/**
	 * Get if this user can login via a password on the traditional login interface.
	 *
	 * @return boolean
	 */
	public function canLoginWithPassword() {
		return false;
	}

	/**
	 * Generate and print the rendered login markup to STDOUT.
	 *
	 * @return void
	 */
	public function renderLogin() {

		if(!FACEBOOK_APP_ID){
			echo 'Please configure Facebook with your APP_ID.';
			return;
		}

		if(!FACEBOOK_APP_SECRET){
			echo 'Please configure Facebook with your APP_SECRET.';
			return;
		}

		$facebook = new \Facebook([
			'appId'  => FACEBOOK_APP_ID,
			'secret' => FACEBOOK_APP_SECRET,
		]);



		// User was already logged in.
		try{
			$user = $facebook->getUser();
			if($user){
				$user_profile = $facebook->api('/me');
				$facebooklink = false;
			}
			else{
				$facebooklink = $facebook->getLoginUrl();
			}

		}
		catch(\Exception $c){
			$facebooklink = $facebook->getLoginUrl();
		}

		// $logoutUrl = $facebook->getLogoutUrl();

		$tpl = \Core\Templates\Template::Factory('includes/user/facebook_login.tpl');
		$tpl->assign('facebooklink', $facebooklink);
		$tpl->render();
	}

	/**
	 * Generate and print the rendered registration markup to STDOUT.
	 *
	 * @return void
	 */
	public function renderRegister() {
		// This is identical to the login, so just use the same function.
		$this->renderLogin();
	}

	/**
	 * Get the title for this Auth driver.  Used in some automatic messages.
	 *
	 * @return string
	 */
	public function getAuthTitle() {
		return 'Facebook';
	}
}