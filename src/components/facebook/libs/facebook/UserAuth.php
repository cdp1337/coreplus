<?php
/**
 * File for class UserAuth definition in the coreplus project
 * 
 * @package Facebook
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20131204.2242
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
 * @author Charlie Powell <charlie@evalagency.com>
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
	 * Check if this user is active and can login.
	 *
	 * @return boolean|string
	 */
	public function isActive() {
		return $this->_usermodel->get('active');
	}

	/**
	 * Generate and print the rendered login markup to STDOUT.
	 *
	 * @param array $form_options
	 *
	 * @return void
	 */
	public function renderLogin($form_options = []) {

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

	/**
	 * Get the icon name for this Auth driver.
	 *
	 * @return string
	 */
	public function getAuthIcon(){
		return 'facebook';
	}

	/**
	 * Sync the user back to the linked Facebook account.
	 *
	 * <h3>Usage:</h3>
	 * <pre class="code">
	 * $auth->syncUser($_POST['access-token']);
	 * </pre>
	 *
	 * @param string $access_token A valid access token for the user to sync up.
	 *
	 * @return bool True or false on success.
	 */
	public function syncUser($access_token){
		try{
			$facebook = new \Facebook([
				'appId'  => FACEBOOK_APP_ID,
				'secret' => FACEBOOK_APP_SECRET,
			]);
			$facebook->setAccessToken($access_token);
			/** @var array $user_profile The array of user data from Facebook */
			$user_profile = $facebook->api('/me');
		}
		catch(\Exception $e){
			return false;
		}


		$user = $this->_usermodel;

		if(!$user->exists()){
			// Some config options for new accounts only.
			if($user->getConfigObject('json:profiles')){
				// This is a field from the user-social component.
				// Link facebook just because!
				$user->set(
					'json:profiles', json_encode(
						[
							[
								'type' => 'facebook',
								'url' => $user_profile['link'],
								'title' => 'Facebook Profile',
							]
						]
					)
				);
			}

			if($user->getConfigObject('username')){
				// Another component from the user-social component.
				// This needs to be unique, so do a little fudging if necessary.
				try{
					$user->set('username', $user_profile['username']);
				}
				catch(\ModelValidationException $e){
					$user->set('username', $user_profile['username'] . '-' . \Core\random_hex(3));
				}
			}

			// Sync the user avatar.
			$f = new \Core\Filestore\Backends\FileRemote('http://graph.facebook.com/' . $user_profile['id'] . '/picture?type=large');
			$dest = \Core\Filestore\Factory::File('public/user/avatar/' . $f->getBaseFilename());
			$f->copyTo($dest);
			$user->set('avatar', 'public/user/avatar/' . $dest->getBaseFilename());
		}

		// Get all user configs and load in anything possible.
		foreach($user->getConfigs() as $k => $v){
			// Facebook can import several configs...
			switch($k){
				case 'first_name':
				case 'last_name':
					$user->set($k, $user_profile[$k]);
					break;
				case 'gender':
					$user->set($k, ucwords($user_profile[$k]));
					break;
				case 'facebook_id':
					$user->set($k, $user_profile['id']);
					break;
				case 'facebook_link':
					$user->set($k, $user_profile['link']);
					break;
				case 'facebook_access_token':
					$user->set($k, $facebook->getAccessToken());
					break;
			}
		}
	}
}