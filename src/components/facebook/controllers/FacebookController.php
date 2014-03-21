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
	 *
	 * This will redirect to the registration page if the user doesn't exist,
	 * will throw an error and display a link to enable FB if it's not enabled already,
	 * or will simply log the user in via Facebook and sync his/her settings.
	 */
	public function login(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$view->ssl = true;
		$view->record = false;

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

		$facebook = new Facebook([
			'appId'  => FACEBOOK_APP_ID,
			'secret' => FACEBOOK_APP_SECRET,
		]);

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
			}
			catch(Exception $e){
				Core::SetMessage($e->getMessage(), 'error');
				\Core\go_back();
				return null;
			}

			/** @var \UserModel|null $user */
			$user = UserModel::Find(['email' => $user_profile['email']], 1);

			if(!$user){
				if(ConfigHandler::Get('/user/register/allowpublic')){
					// If public registration is enabled, then redirect the user to the registration page to complete their registration.
					$user = new UserModel();
					$user->set('email', $user_profile['email']);
					$user->enableAuthDriver('facebook');
					$user->disableAuthDriver('datastore');
					/** @var \Facebook\UserAuth $auth */
					$auth = $user->getAuthDriver('facebook');

					$auth->syncUser($_POST['access-token']);

					// Otherwise, w00t!  Record this user into a nonce and forward to step 2 of registration.
					$nonce = NonceModel::Generate('20 minutes', null, ['user' => $user]);
					\Core\redirect('/user/register2/' . $nonce);
				}
				else{
					// Log this as a login attempt!
					$logmsg = 'Failed Login (Facebook). Email not registered' . "\n" . 'Email: ' . $user_profile['email'] . "\n";
					\SystemLogModel::LogSecurityEvent('/user/login', $logmsg);
					Core::SetMessage('Your Facebook email (' . $user_profile['email'] . ') does not appear to be registered on this site.', 'error');
					\Core\go_back();
					return null;
				}
			}
			elseif(!$user->get('active')){
				// The model provides a quick cut-off for active/inactive users.
				// This is the control managed with in the admin.
				$logmsg = 'Failed Login. User tried to login before account activation' . "\n" . 'User: ' . $user->get('email') . "\n";
				\SystemLogModel::LogSecurityEvent('/user/login', $logmsg, null, $user->get('id'));
				Core::SetMessage('Your account is not active yet.', 'error');
				\Core\go_back();
				return null;
			}

			try{
				/** @var \Facebook\UserAuth $auth */
				$auth = $user->getAuthDriver('facebook');
			}
			catch(Exception $e){
				Core::SetMessage('Your account does not have Facebook logins enabled!  <a href="' . Core::ResolveLink('/facebook/enable') . '">Do you want to enable Facebook?</a>', 'error');
				\Core\go_back();
				return null;
			}

			if(!$user->isActive()){
				Core::SetMessage('Your account is not active!', 'error');
				\Core\go_back();
				return null;
			}

			// Well yay the user is available and authencation driver is ready!
			$auth->syncUser($_POST['access-token']);

			// If the user came from the registration page, get the page before that.
			if(REL_REQUEST_PATH == '/user/login') $url = \Core::GetHistory(2);
			elseif(REL_REQUEST_PATH == '/facebook/login') $url = \Core::GetHistory(2);
			// else the registration link is now on the same page as the 403 handler.
			else $url = REL_REQUEST_PATH;

			// Well, record this too!
			\SystemLogModel::LogSecurityEvent('/user/login', 'Login successful (via Facebook)', null, $user->get('id'));

			// yay...
			$user->set('last_login', \CoreDateTime::Now('U', \Time::TIMEZONE_GMT));
			$user->save();
			\Session::SetUser($user);

			// Allow an external script to override the redirecting URL.
			$overrideurl = \HookHandler::DispatchHook('/user/postlogin/getredirecturl');
			if($overrideurl){
				$url = $overrideurl;
			}

			\Core\redirect($url);
		}
		else{
			\Core\go_back();
		}
	}

	/**
	 * Page to enable Facebook logins for user accounts.
	 *
	 * @return int|null|string
	 */
	public function enable() {
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

		// If it was a POST, then it should be the first page.
		if($request->isPost()){
			$facebook = new Facebook([
				'appId'  => FACEBOOK_APP_ID,
				'secret' => FACEBOOK_APP_SECRET,
			]);

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
				}
				catch(Exception $e){
					Core::SetMessage($e->getMessage(), 'error');
					\Core\go_back();
					return null;
				}

				// If the user is logged in, then the verification logic is slightly different.
				if(\Core\user()->exists()){
					// Logged in users, the email must match.
					if(\Core\user()->get('email') != $user_profile['email']){
						Core::SetMessage('Your Facebook email is ' . $user_profile['email'] . ', which does not match your account email!  Unable to link accounts.', 'error');
						\Core\go_back();
						return null;
					}

					$user = \Core\user();
				}
				else{
					/** @var \UserModel|null $user */
					$user = UserModel::Find(['email' => $user_profile['email']], 1);

					if(!$user){
						Core::SetMessage('No local account found with the email ' . $user_profile['email'] . ', please <a href="' . Core::ResolveLink('/user/register') . '"create an account</a> instead.', 'error');
						\Core\go_back();
						return null;
					}
				}

				// Send an email with a nonce link that will do the actual activation.
				// This is a security feature so just anyone can't link another user's account.

				$nonce = NonceModel::Generate(
					'20 minutes',
					null,
					[
						'user' => $user,
						'access_token' => $_POST['access-token'],
					]
				);

				$email = new Email();
				$email->to($user->get('email'));
				$email->setSubject('Facebook Activation Request');
				$email->templatename = 'emails/facebook/enable_confirmation.tpl';
				$email->assign('link', Core::ResolveLink('/facebook/enable/' . $nonce));
				if($email->send()){
					Core::SetMessage('An email has been sent to your account with a link enclosed.  Please click on that to complete activation within twenty minutes.', 'success');
					\Core\go_back();
					return null;
				}
				else{
					Core::SetMessage('Unable to send a confirmation email, please try again later.', 'error');
					\Core\go_back();
					return null;
				}
			}
		}

		// If there is a nonce enclosed, then it should be the second confirmation page.
		// This is the one that actually performs the action.
		if($request->getParameter(0)){
			/** @var NonceModel $nonce */
			$nonce = NonceModel::Construct($request->getParameter(0));

			if(!$nonce->isValid()){
				Core::SetMessage('Invalid key requested.', 'error');
				\Core\redirect('/');
				return null;
			}

			$nonce->decryptData();
			$data = $nonce->get('data');

			/** @var UserModel $user */
			$user = $data['user'];

			try{
				$facebook = new Facebook([
					'appId'  => FACEBOOK_APP_ID,
					'secret' => FACEBOOK_APP_SECRET,
				]);
				$facebook->setAccessToken($data['access_token']);
				$facebook->getUser();
				$facebook->api('/me');
			}
			catch(Exception $e){
				Core::SetMessage($e->getMessage(), 'error');
				\Core\redirect('/');
				return null;
			}

			$user->enableAuthDriver('facebook');
			/** @var \Facebook\UserAuth $auth */
			$auth = $user->getAuthDriver('facebook');
			$auth->syncUser($data['access_token']);

			Core::SetMessage('Linked Facebook successfully!', 'success');

			// And log the user in!
			if(!\Core\user()->exists()){
				$user->set('last_login', \CoreDateTime::Now('U', \Time::TIMEZONE_GMT));
				$user->save();
				\Session::SetUser($user);
			}

			\Core\redirect('/');
			return null;
		}
	}

	/**
	 * POST-only view to disable a user's Facebook login ability.
	 * @return int
	 */
	public function disable(){
		$view    = $this->getView();
		$request = $this->getPageRequest();
		$userid  = $request->getParameter(0);
		$isadmin = \Core\user()->checkAccess('p:/user/users/manage'); // Current user an admin?
		$isself  = (\Core\user()->get('id') == $userid);

		if(!($isadmin || $isself)){
			return View::ERROR_ACCESSDENIED;
		}

		if(!$request->isPost()){
			return View::ERROR_BADREQUEST;
		}

		/** @var UserModel $user */
		$user = UserModel::Construct($userid);

		if(!$user->exists()){
			return View::ERROR_NOTFOUND;
		}

		if(sizeof($user->getEnabledAuthDrivers()) == 1){
			return View::ERROR_OTHER;
		}

		$user->disableAuthDriver('facebook');
		$user->save();

		Core::SetMessage('Disabled Facebook logins!', 'success');
		\Core\go_back();
	}

	/**
	 * Hook receiver for /core/controllinks/user/view
	 *
	 * @param int $userid
	 *
	 * @return array
	 */
	public static function GetUserControlLinks($userid){

		$enabled = User\Helper::GetEnabledAuthDrivers();
		if(!isset($enabled['facebook'])){
			// GPG isn't enabled at all, disable any control links from the system.
			return [];
		}

		/** @var UserModel $user */
		$user = UserModel::Construct($userid);

		if(!$user->exists()){
			// Invalid user.
			return [];
		}

		$isself = (\Core\user()->get('id') == $user->get('id'));
		$isadmin = \Core\user()->checkAccess('p:/user/users/manage');

		if(!($isself || $isadmin)){
			// Current user does not have access to manage the provided user's data.
			return [];
		}

		try{
			// If this throws an exception, then it's not enabled!
			$user->getAuthDriver('facebook');
		}
		catch(Exception $e){
			if($isself){
				return [
					[
						'link' => '/facebook/enable',
						'title' => 'Enable Facebook Login',
						'icon' => 'facebook',
					]
				];
			}
		}

		if(sizeof($user->getEnabledAuthDrivers()) > 1){
			return [
				[
					'link' => '/facebook/disable/' . $user->get('id'),
					'title' => 'Disable Facebook Login',
					'icon' => 'ban',
					'confirm' => 'Are you sure you want to disable Facebook-based logins?  (They can be re-enabled if requested.)',
				]
			];
		}

		return [];
	}
}
