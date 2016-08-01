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

		if(!\FACEBOOK_APP_ID){
			echo 'Please configure Facebook with your APP_ID.';
			return;
		}

		if(!\FACEBOOK_APP_SECRET){
			echo 'Please configure Facebook with your APP_SECRET.';
			return;
		}

		$fb = new Facebook([
			'app_id'  => \FACEBOOK_APP_ID,
			'app_secret' => \FACEBOOK_APP_SECRET,
		]);


		$helper = $fb->getRedirectLoginHelper();
		$perms = [
			'public_profile',
			'email',
			'user_about_me',
		];
		$loginUrl = $helper->getLoginUrl(\Core\resolve_link('/facebook/login'), $perms);

		// User was already logged in.
		/*try{
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
		}*/

		// $logoutUrl = $facebook->getLogoutUrl();

		$tpl = \Core\Templates\Template::Factory('includes/user/facebook_login.tpl');
		$tpl->assign('facebooklink', $loginUrl);
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
	 *
	 * @param array $data All the data from Facebook
	 *
	 * @return bool True or false on success.
	 */
	public function syncUser($data){
		
		$user = $this->_usermodel;
		
		$profiles = $user->get('external_profiles');
		if(!is_array($profiles)){
			$profiles = [];
		}
		
		if(isset($data['link'])){
			$new = true;
			foreach($profiles as $p){
				if($p['type'] == 'facebook'){
					$new = false;
					break;
				}
			}
			if($new){
				$profiles[] = [
					'type' => 'facebook',
					'url' => $data['link'],
					'title' => 'Facebook Profile',
				];
			}
		}
		
		if(isset($data['website'])){
			// Ensure it's resolved.
			if(strpos($data['website'], '://') === false){
				$data['website'] = 'http://' . $data['website'];
			}
			
			$new = true;
			foreach($profiles as $p){
				if($p['type'] == 'link'){
					$new = false;
					break;
				}
			}
			if($new){
				$profiles[] = [
					'type' => 'link',
					'url' => $data['website'],
					'title' => $data['website'],
				];
			}
		}
		
		$user->set('external_profiles', $profiles);
		
		if(isset($data['picture'])){
			// Sync the user avatar.
			$f = new \Core\Filestore\Backends\FileRemote($data['picture']['data']['url']);
			$dest = \Core\Filestore\Factory::File('public/user/avatar/' . $f->getBaseFilename());
			$f->copyTo($dest);
			$user->set('avatar', 'public/user/avatar/' . $dest->getBaseFilename());
		}

		if(isset($data['first_name'])){
			$user->set('first_name', $data['first_name']);	
		}

		if(isset($data['last_name'])) {
			$user->set('last_name', $data['last_name']);
		}
		if(isset($data['gender'])) {
			$user->set('gender', ucwords($data['gender']));
		}
		if(isset($data['id'])) {
			$user->set('facebook_id', $data['id']);
		}
		if(isset($data['bio'])) {
			$user->set('bio', $data['bio']);
		}
		
		if(isset($data['public_key'])){
			$gpg = new \Core\GPG\GPG();
			$key = $gpg->importKey($data['public_key']);
			
			$user->enableAuthDriver('gpg');
			$user->set('gpgauth_pubkey', $key->fingerprint);
		}
	}
}