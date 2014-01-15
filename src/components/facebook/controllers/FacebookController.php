<?php
/**
 * Contains the controller that intercepts and handles Facebook logins.
 *
 * @package Facebook
 * @author Charlie Powell <charlie@eval.bz>
 *
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

/**
 * Facebook Controller, handles any Facebook-related views and pages.
 *
 * Current, there is only one, login.
 */
class FacebookController extends Controller_2_1{
	/**
	 * View to accept and process the FB login post.
	 */
	public function login(){
		$request = $this->getPageRequest();

		$auths = \Core\User\Helper::GetEnabledAuthDrivers();

		if(!isset($auths['facebook'])){
			// Facebook isn't enabled, simply redirect to the home page.
			\Core\redirect('/');
		}

		if(!FACEBOOK_APP_ID){
			\Core\redirect('/');
		}

		if(!FACEBOOK_APP_SECRET){
			\Core\redirect('/');
		}

		if(!$request->isPost()){
			return View::ERROR_BADREQUEST;
		}

		$facebook = new Facebook(array(
			'appId'  => FACEBOOK_APP_ID,
			'secret' => FACEBOOK_APP_SECRET,
		));

		// Did the user submit the facebook login request?
		if(
			isset($_POST['login-method']) &&
			$_POST['login-method'] == 'facebook' &&
			$_POST['access-token']
		){
			try{
				$facebook->setAccessToken($_POST['access-token']);
				/** @var int $fbid The user ID from facebook */
				$fbid = $facebook->getUser();
				/** @var array $user_profile The array of user data from Facebook */
				$user_profile = $facebook->api('/me');

				$user = UserModel::Find(['email' => $user_profile['email']], 1);
				if(!$user){
					// Find with a limit of 1 will return null if it doesn't exist.
					// Facebook supports auto-creation!

					$user = new UserModel();
					$user->set('email', $user_profile['email']);
					$user->set('backend', 'facebook');

					// Some admins may require administrative approval for all accounts.
					if(\ConfigHandler::Get('/user/register/requireapproval')){
						$user->set('active', false);
					}
					else{
						$user->set('active', true);
					}

					// Facebook uses native groups for its users.
					// Set the default group on new accounts, if a default is set.
					$defaultgroups = \UserGroupModel::Find(array("default = 1"));
					$gs = [];
					foreach($defaultgroups as $g){
						/** @var \UserGroupModel $g */
						$gs[] = $g->get('id');
					}
					$user->setGroups($gs);

					// Record some more meta information about this user.
					$user->set('registration_ip', REMOTE_IP);
					$user->set('registration_source', 'self');
					$user->set('registration_invitee', 0);

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

					$user->save();

					// User created... make a log of this!
					\SystemLogModel::LogInfoEvent('/user/register', 'User registration of ' . $user->get('email') . ' successful via Facebook');
				}
				elseif($user->get('backend') != 'facebook'){
					// The user exists, but is not a facebook user.  Prevent that user from logging in with facebook!
					Core::SetMessage('Your account already exists, however it is not linked with Facebook.', 'error');
					\Core\go_back();
				}
				// No else needed, it exists and is linked to facebook already.  Go ahead and sync the metadata.

				// Sync the user avatar.
				$f = new \Core\Filestore\Backends\FileRemote('http://graph.facebook.com/' . $user_profile['id'] . '/picture?type=large');
				$dest = \Core\Filestore\Factory::File('public/user/avatar/' . $f->getBaseFilename());
				$f->copyTo($dest);
				$user->set('avatar', 'public/user/avatar/' . $dest->getBaseFilename());

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

				// Save any configs that may have changed :)
				$user->save();


				// If the user came from the registration page, get the page before that.
				switch(REL_REQUEST_PATH){
					case '/user/login':
					case '/facebook/login':
					case '/user/register':
						$url = \Core::GetHistory(1);
						break;
					default:
						$url = REL_REQUEST_PATH;
				}


				//$url = Core::GetHistory(2);
				if($user->get('active')){
					$user->set('last_login', \CoreDateTime::Now('U', \Time::TIMEZONE_GMT));
					$user->save();
					\Session::SetUser($user);
				}

				// Allow an external script to override the redirecting URL.
				$overrideurl = \HookHandler::DispatchHook('/user/postlogin/getredirecturl');
				if($overrideurl){
					$url = $overrideurl;
				}
				elseif($url == \Core::ResolveLink('/user/register')){
					$url = '/';
				}

				\Core\redirect($url);
			}
			catch(Exception $e){
				Core::SetMessage($e->getMessage(), 'error');
				\Core\go_back();
			}
		}
		else{
			\Core\go_back();
		}
	}
}
