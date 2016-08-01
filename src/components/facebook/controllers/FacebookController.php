<?php
/**
 * Contains the controller that intercepts and handles Facebook logins.
 *
 * @package Facebook
 * @author Charlie Powell <charlie@evalagency.com>
 *
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

		$fb = new Facebook\Facebook([
			'app_id'  => \FACEBOOK_APP_ID,
			'app_secret' => \FACEBOOK_APP_SECRET,
		]);

		$helper = $fb->getRedirectLoginHelper();

		try {
			$accessToken = $helper->getAccessToken();
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			\Core\set_message('Graph returned an error: ' . $e->getMessage(), 'error');
			\Core\redirect('/');
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			\Core\set_message('Facebook SDK returned an error: ' . $e->getMessage(), 'error');
			\Core\redirect('/');
		}

		// The OAuth 2.0 client handler helps us manage access tokens
		$oAuth2Client = $fb->getOAuth2Client();

		// Get the access token metadata from /debug_token
		$tokenMetadata = $oAuth2Client->debugToken($accessToken);

		// Validation (these will throw FacebookSDKException's when they fail)
		$tokenMetadata->validateAppId(FACEBOOK_APP_ID); // Replace {app-id} with your app id
		// If you know the user ID this access token belongs to, you can validate it here
		//$tokenMetadata->validateUserId('123');
		$tokenMetadata->validateExpiration();

		if (! $accessToken->isLongLived()) {
			// Exchanges a short-lived access token for a long-lived one
			try {
				$accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
			}
			catch (Facebook\Exceptions\FacebookSDKException $e) {
				\Core\set_message('Error getting long-lived access token: ' . $helper->getMessage(), 'error');
				\Core\redirect('/');
			}
		}
		
		// Record this token in case I need to use it again.
		\Core\Session::Set('fb_access_token', (string) $accessToken);
		
		try{
			/** @var \Facebook\FacebookResponse $response The raw response from FB */
			$response = $fb->get('/me?fields=id,first_name,last_name,gender,email,about,bio,picture,link,public_key,website', $accessToken);
			/** @var \Facebook\FacebookResponse $user_profile The decoded array of user data from Facebook */
			$user_profile = $response->getDecodedBody();
		}
		catch(Exception $e){
			\Core\set_message($e->getMessage(), 'error');
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

				$auth->syncUser($user_profile);
				
				$user->setDefaultActiveStatuses();
				$user->setDefaultGroups();
				$user->setDefaultMetaFields();
				$user->generateNewApiKey();
				$user->set('last_login', \CoreDateTime::Now('U', \Time::TIMEZONE_GMT));
				$user->save();
				
				\Core\Session::SetUser($user);
				\Core\redirect('/');
			}
			else{
				// Log this as a login attempt!
				$logmsg = 'Failed Login (Facebook). Email not registered' . "\n" . 'Email: ' . $user_profile['email'] . "\n";
				\SystemLogModel::LogSecurityEvent('/user/login', $logmsg);
				\Core\set_message('Your Facebook email (' . $user_profile['email'] . ') does not appear to be registered on this site.', 'error');
				\Core\redirect('/');
				return null;
			}
		}
		elseif(!$user->get('active')){
			// The model provides a quick cut-off for active/inactive users.
			// This is the control managed with in the admin.
			$logmsg = 'Failed Login. User tried to login before account activation' . "\n" . 'User: ' . $user->get('email') . "\n";
			\SystemLogModel::LogSecurityEvent('/user/login', $logmsg, null, $user->get('id'));
			\Core\set_message('Your account is not active yet.', 'error');
			\Core\redirect('/');
			return null;
		}
		else{
			// user exists and is active!

			$user->enableAuthDriver('facebook');
			/** @var \Facebook\UserAuth $auth */
			$auth = $user->getAuthDriver('facebook');
			$auth->syncUser($user_profile);

			// Well, record this too!
			\SystemLogModel::LogSecurityEvent('/user/login', 'Login successful (via Facebook)', null, $user->get('id'));

			// yay...
			$user->set('last_login', \CoreDateTime::Now('U', \Time::TIMEZONE_GMT));
			$user->save();
			\Core\Session::SetUser($user);

			\Core\redirect('/');
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
					\Core\set_message($e->getMessage(), 'error');
					\Core\go_back();
					return null;
				}

				// If the user is logged in, then the verification logic is slightly different.
				if(\Core\user()->exists()){
					// Logged in users, the email must match.
					if(\Core\user()->get('email') != $user_profile['email']){
						\Core\set_message('Your Facebook email is ' . $user_profile['email'] . ', which does not match your account email!  Unable to link accounts.', 'error');
						\Core\go_back();
						return null;
					}

					$user = \Core\user();
				}
				else{
					/** @var \UserModel|null $user */
					$user = UserModel::Find(['email' => $user_profile['email']], 1);

					if(!$user){
						\Core\set_message('No local account found with the email ' . $user_profile['email'] . ', please <a href="' . \Core\resolve_link('/user/register') . '"create an account</a> instead.', 'error');
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
				$email->assign('link', \Core\resolve_link('/facebook/enable/' . $nonce));
				if($email->send()){
					\Core\set_message('An email has been sent to your account with a link enclosed.  Please click on that to complete activation within twenty minutes.', 'success');
					\Core\go_back();
					return null;
				}
				else{
					\Core\set_message('Unable to send a confirmation email, please try again later.', 'error');
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
				\Core\set_message('Invalid key requested.', 'error');
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
				\Core\set_message($e->getMessage(), 'error');
				\Core\redirect('/');
				return null;
			}

			$user->enableAuthDriver('facebook');
			/** @var \Facebook\UserAuth $auth */
			$auth = $user->getAuthDriver('facebook');
			$auth->syncUser($data['access_token']);

			\Core\set_message('Linked Facebook successfully!', 'success');

			// And log the user in!
			if(!\Core\user()->exists()){
				$user->set('last_login', \CoreDateTime::Now('U', \Time::TIMEZONE_GMT));
				$user->save();
				\Core\Session::SetUser($user);
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

		\Core\set_message('Disabled Facebook logins!', 'success');
		\Core\go_back();
	}

	/**
	 * Hook receiver for /core/controllinks/usermodel
	 *
	 * @param int $userid
	 *
	 * @return array
	 */
	public static function GetUserControlLinks($userid){

		$enabled = \Core\User\Helper::GetEnabledAuthDrivers();
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
