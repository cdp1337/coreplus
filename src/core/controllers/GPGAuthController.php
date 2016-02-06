<?php
/**
 * File for class GPGAuthController definition in the coreplus project
 *
 * @package Core\User
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140319.1608
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
 * A short teaser of what GPGAuthController does.
 *
 * More lengthy description of what GPGAuthController does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
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
 * @todo Write documentation for GPGAuthController
 * @package Core\User
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class GPGAuthController extends Controller_2_1 {

	/**
	 * Public view to set or reset the GPG key.
	 *
	 * This can work for users that are not currently set to use GPG, as it will perform an email confirmation.
	 */
	public function reset(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(\Core\user()->exists()){
			// Already logged in!
			// They simply get forwarded to the configure page.
			\Core\redirect('/gpgauth/configure/' . \Core\user()->get('id'));
		}

		$form = new Form();
		$form->set('callsMethod', 'GPGAuthController::ResetHandler');

		$form->addElement('text', array('name' => 'email', 'title' => 'Email', 'required' => true));
		$form->addElement('submit', ['name' => 'submit', 'value' => 'Next']);

		$view->assign('form', $form);
	}

	/**
	 * View to login a user, this is actually a supplemental view for /user/login, as it requires an additional step.
	 */
	public function login2(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(\Core\user()->exists()){
			// Already logged in!

			return View::ERROR_ACCESSDENIED;
		}

		/** @var NonceModel $nonce */
		$nonce = NonceModel::Construct($request->getParameter(0));

		if(!$nonce->isValid()){
			\Core\set_message('Invalid nonce provided!', 'error');
			\Core\go_back();
		}

		$nonce->decryptData();
		$data = $nonce->get('data');

		$form = new Form();
		$form->set('orientation', 'vertical');
		$form->set('callsmethod', 'GPGAuthController::Login2Handler');
		$form->addElement('system', ['name' => 'nonce', 'value' => $nonce->get('key')]);
		$form->addElement(
			'textarea',
			[
				'name' => 'message',
				'required' => true,
				'title' => 'Signed Message',
				'description' => 'Paste the result of the GPG command.',
			]
		);
		$form->addElement('submit', ['name' => 'submit', 'value' => 'Verify and Login']);

		$view->assign('sentence', $data['sentence']);
		$view->assign('form', $form);
	}

	/**
	 * Method to be expected to be called from the command line to upload a key.
	 */
	public function rawUpload(){
		$view = $this->getView();
		$view->mode = View::MODE_NOOUTPUT;
		$view->templatename = false;
		$view->contenttype = 'text/plain';

		$input = file_get_contents('php://input');

		if(!$input){
			echo "No key found!  Do you have one generated yet?\n";
			return;
		}

		$user = \Core\user();

		if(!$user->exists()){
			echo "Invalid user requested!\n";
			return;
		}

		try{
			$gpg = new Core\GPG\GPG();
			$key = $gpg->importKey($input);

			if(\Core\User\AuthDrivers\gpg::SendVerificationEmail($user, $key->fingerprint)){
				echo "Step 1 of 2 complete!  Please check your email for futher instructions to verify this key!\n";
				return;
			}
		}
		catch(\Exception $e){
			echo "Invalid input provided :(\n";
			return;
		}
	}

	public function rawVerify(){
		$view = $this->getView();
		$view->mode = View::MODE_NOOUTPUT;
		$view->templatename = false;
		$view->contenttype = 'text/plain';

		$input = file_get_contents('php://input');

		$nonce = isset($_SERVER['HTTP_X_CORE_NONCE_KEY']) ? $_SERVER['HTTP_X_CORE_NONCE_KEY'] : null;

		if(!$nonce){
			echo "Invalid nonce provided!\n";
			return;
		}

		/** @var \NonceModel $nonce */
		$nonce = \NonceModel::Construct($nonce);

		if(!$nonce->isValid()){
			echo "Invalid nonce provided!\n";
			return;
		}

		if(!$input){
			echo "No key found!  Do you have one generated yet?\n";
			return;
		}

		$user = \Core\user();

		if(!$user->exists()){
			echo "Invalid user requested!\n";
			return;
		}

		// Verify that this user was the one provided by the nonce.
		$nonce->decryptData();
		$data = $nonce->get('data');

		if($data['user'] != $user->get('id')){
			echo "Invalid user requested!\n";
			return;
		}

		$result = \Core\User\AuthDrivers\gpg::ValidateVerificationResponse($nonce, $input);

		if($result !== true){
			echo $result . "\n";
			return;
		}
		else{
			echo "Public key successfully registered!\n";
			return;
		}
	}

	/**
	 * The public configure method for each user.
	 *
	 * This helps the user set his/her public key that the system will use to authenticate with.
	 */
	public function configure(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->exists()){
			// This system can work off reset key as well, but requires a nonce first to do so.
			$nonce = NonceModel::Construct($request->getParameter(0));
			$nonce->decryptData();
			$data = $nonce->get('data');
			/** @var UserModel $user */
			$user = UserModel::Construct($data['user']);
			$isManager = false;
		}
		elseif(\Core\user()->checkAccess('p:/user/users/manage') && $request->getParameter(0)){
			/** @var UserModel $user */
			$user = UserModel::Construct($request->getParameter(0));
			$isManager = true;
		}
		else{
			$user = \Core\user();
			$isManager = false;
		}

		if(!$user->exists()){
			// Current user does not have access to manage the provided user's data.
			return View::ERROR_ACCESSDENIED;
		}

		$currentkey = $user->get('/user/gpgauth/pubkey');

		$eml = $user->get('email');
		$key = $user->get('apikey');
		$url = \Core\resolve_link('/gpgauth/rawupload');
		$cmd = <<<EOD
gpg --export -a $eml 2>/dev/null | curl --data-binary @- \\
--header "X-Core-Auth-Key: $key" \\
$url
EOD;
		$form = new Form();
		$form->set('callsmethod', 'GPGAuthController::ConfigureHandler');
		$form->addElement('system', ['name' => 'userid', 'value' => $user->get('id')]);
		$form->addElement(
			'textarea',
			[
				'name' => 'key',
				'title' => 'GPG Public Key',
			]
		);
		$form->addElement('submit', ['name' => 'submit', 'value' => 'Submit Public Key']);

		if($isManager){
			$view->mastertemplate = 'admin';
			$view->addBreadcrumb('Administration', '/admin');
			$view->addBreadcrumb('User Administration', '/user/admin');
			$view->addBreadcrumb($user->getDisplayName() . ' Profile', '/user/view/' . $user->get('id'));
		}
		$view->title = 'Configure GPG Public Key';
		$view->assign('form', $form);
		$view->assign('current_key', $currentkey);
		$view->assign('cmd', $cmd);
	}

	/**
	 * The second page for setting or updating GPG keys for a given user.
	 *
	 * This is required because the command is sent to the user's email,
	 * and this page will display the text area to submit the signed content.
	 */
	public function configure2(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		/** @var NonceModel $nonce */
		$nonce = NonceModel::Construct($request->getParameter(0));

		if(!$nonce->isValid()){
			\Core\set_message('Invalid nonce provided!', 'error');
			\Core\go_back();
		}

		$form = new Form();
		$form->set('orientation', 'vertical');
		$form->set('callsmethod', 'GPGAuthController::Configure2Handler');
		$form->addElement('system', ['name' => 'nonce', 'value' => $nonce->get('key')]);
		$form->addElement(
			'textarea',
			[
				'name' => 'message',
				'required' => true,
				'title' => 'Signed Message',
				'description' => 'Paste the result of the GPG command that was sent to your email.',
			]
		);
		$form->addElement('submit', ['name' => 'submit', 'value' => 'Set Key']);

		$view->assign('form', $form);
	}

	/**
	 * Form submission page for anonymous-based set or reset attempts.
	 * This requires an additional step because I don't want to expose the user ID based on their email.
	 *
	 * @param Form $form
	 *
	 * @return bool|string
	 */
	public static function ResetHandler(Form $form) {
		$email = $form->getElement('email');

		/** @var \UserModel $u */
		$u = \UserModel::Find(array('email' => $email->get('value')), 1);

		if(!$u){
			// Log this as a login attempt!
			$logmsg = 'Failed Login. Email not registered' . "\n" . 'Email: ' . $email->get('value') . "\n";
			\SystemLogModel::LogSecurityEvent('/user/login', $logmsg);
			$email->setError('Requested email is not registered.');
			return false;
		}

		if($u->get('active') == 0){
			// The model provides a quick cut-off for active/inactive users.
			// This is the control managed with in the admin.
			$logmsg = 'Failed Login. User tried to login before account activation' . "\n" . 'User: ' . $u->get('email') . "\n";
			\SystemLogModel::LogSecurityEvent('/user/login', $logmsg, null, $u->get('id'));
			$email->setError('Your account is not active yet.');
			return false;
		}

		$nonce = NonceModel::Generate(
			'5 minutes',
			null,
			[
				'user' => $u->get('id')
			]
		);

		return '/gpgauth/configure/' . $nonce;
	}

	/**
	 * Form handler for the initial key set or reset request.
	 *
	 * This sends out the email with the signing command.
	 *
	 * @param Form $form
	 *
	 * @return bool|string
	 */
	public static function ConfigureHandler(Form $form){
		$key  = $form->getElementValue('key');
		/** @var UserModel $user */
		$user = UserModel::Construct($form->getElement('userid')->get('value'));

		try{
			$gpg = new Core\GPG\GPG();
			$key = $gpg->importKey($key);

			if(($nonce = \Core\User\AuthDrivers\gpg::SendVerificationEmail($user, $key->fingerprint, false))){
				\Core\set_message('Instructions have been sent to your email.', 'success');
				return '/gpgauth/configure2/' . $nonce;
			}
		}
		catch(\Exception $e){
			\Core\set_message('Invalid key provided!', 'error');
			return false;
		}
	}

	/**
	 * Form handler for the final submit to set or reset a GPG key.
	 *
	 * This performs the actual key change in the database.
	 *
	 * @param Form $form
	 *
	 * @return bool|string
	 */
	public static function Configure2Handler(Form $form){
		/** @var NonceModel $nonce */
		$nonceKey = NonceModel::Construct($form->getElement('nonce')->get('value'));
		$sig      = $form->getElement('message')->get('value');

		$nonce = \NonceModel::Construct($nonceKey);

		if(!$nonce->isValid()){
			return 'Invalid nonce provided!';
		}

		// Now is where the real fun begins.

		$nonce->decryptData();

		$data = $nonce->get('data');

		$result = \Core\User\AuthDrivers\gpg::ValidateVerificationResponse($nonceKey, $sig);

		if($result !== true){
			\Core\set_message($result, 'error');
			return false;
		}
		else{
			\Core\set_message('Set/Updated GPG key successfully!', 'success');

			if(!\Core\user()->exists()){
				$user = UserModel::Construct($data['user']);
				// Not logged in yet, this process can log the user in.
				\SystemLogModel::LogSecurityEvent('/user/login', 'Login successful (via GPG Key)', null, $user->get('id'));

				// yay...
				$user->set('last_login', \CoreDateTime::Now('U', \Time::TIMEZONE_GMT));
				$user->save();
				\Core\Session::SetUser($user);
			}

			return '/user/me';
		}
	}

	/**
	 * Form handler for login requests.
	 *
	 * This looks up the user's email and ensures that it's linked to a key.
	 *
	 * @param Form $form
	 *
	 * @return bool|string
	 */
	public static function LoginHandler(Form $form){
		$email = $form->getElement('email');

		/** @var \UserModel $u */
		$u = \UserModel::Find(array('email' => $email->get('value')), 1);

		if(!$u){
			// Log this as a login attempt!
			$logmsg = 'Failed Login. Email not registered' . "\n" . 'Email: ' . $email->get('value') . "\n";
			\SystemLogModel::LogSecurityEvent('/user/login', $logmsg);
			$email->setError('Requested email is not registered.');
			return false;
		}

		if($u->get('active') == 0){
			// The model provides a quick cut-off for active/inactive users.
			// This is the control managed with in the admin.
			$logmsg = 'Failed Login. User tried to login before account activation' . "\n" . 'User: ' . $u->get('email') . "\n";
			\SystemLogModel::LogSecurityEvent('/user/login', $logmsg, null, $u->get('id'));
			$email->setError('Your account is not active yet.');
			return false;
		}
		elseif($u->get('active') == -1){
			// The model provides a quick cut-off for active/inactive users.
			// This is the control managed with in the admin.
			$logmsg = 'Failed Login. User tried to login after account deactivation.' . "\n" . 'User: ' . $u->get('email') . "\n";
			\SystemLogModel::LogSecurityEvent('/user/login', $logmsg, null, $u->get('id'));
			$email->setError('Your account has been deactivated.');
			return false;
		}

		try{
			$auth = $u->getAuthDriver('gpg');
		}
		catch(\Exception $ex){
			$email->setError($ex->getMessage());
			return false;
		}

		$authactive = $auth->isActive();
		if($authactive === false){
			// Auth systems may have their own is-active check.
			$logmsg = 'Failed Login. User tried to login before account activation' . "\n" . 'User: ' . $u->get('email') . "\n";
			\SystemLogModel::LogSecurityEvent('/user/login', $logmsg, null, $u->get('id'));
			$email->setError('Your account is not active yet.');
			return false;
		}
		elseif($authactive !== true){
			// Auth systems may have their own is-active check.
			$logmsg = 'Failed Login due to Authentication driver ' . $auth->getAuthTitle() . "\n" . 'User: ' . $u->get('email') . "\n" . $authactive;
			\SystemLogModel::LogSecurityEvent('/user/login', $logmsg, null, $u->get('id'));
			$email->setError($authactive);
			return false;
		}

		// Everything checked out as good, display the signing logic.
		$sentence = trim(BaconIpsumGenerator::Make_a_Sentence());

		$nonce = NonceModel::Generate(
			'5 minutes',
			null,
			[
				'sentence' => $sentence,
				'user' => $u->get('id'),
				'redirect' => $form->getElementValue('redirect'),
			]
		);

		// This will be part 2 of the 2-factor auth.
		return '/gpgauth/login2/' . $nonce;
	}

	/**
	 * Form handler for the login page.
	 *
	 * This will read the signed content and ensure that it was signed with
	 * 1) The user's exact key that they have previously registered
	 * 2) That the key has not been revoked
	 * 3) That the key has not expired
	 * 4) That the signed content matches the original content submitted for the challange/response.
	 *
	 * @param Form $form
	 *
	 * @return bool|mixed|string
	 */
	public static function Login2Handler(Form $form){
		/** @var NonceModel $nonce */
		$nonce = NonceModel::Construct($form->getElement('nonce')->get('value'));

		if(!$nonce->isValid()){
			\Core\set_message('Invalid nonce provided!', 'error');
			return false;
		}

		// Now is where the real fun begins.

		$nonce->decryptData();

		$data = $nonce->get('data');

		/** @var UserModel $user */
		$user = UserModel::Construct($data['user']);
		$keyid = $user->get('/user/gpgauth/pubkey');

		//var_dump($data, $form->getElement('message')->get('value')); die();

		$gpg = new \Core\GPG\GPG();
		// I can skip the import here, as it was just checked for validity on the first step of login.
		$key = $gpg->getKey($keyid);

		if(!$key){
			\Core\set_message('That key could not be loaded from local!', 'error');
			return false;
		}

		if(!$key->isValid()){
			\Core\set_message('Your GPG key is not valid anymore, is it revoked or expired?', 'error');
		}

		if(!$key->isValid($user->get('email'))){
			\Core\set_message('Your GPG subkey containing your email address is not valid anymore, is it revoked or expired?', 'error');
		}

		// Lastly, verify that the signature is correct.
		if(!$gpg->verifyDetachedSignature($form->getElement('message')->get('value'), $data['sentence'], $keyid)){
			\Core\set_message('Invalid signature!', 'error');
			return false;
		}

		// Otherwise?
		if($data['redirect']){
			// The page was set via client-side javascript on the login page.
			// This is the most reliable option.
			$url = $data['redirect'];
		}
		else{
			// If the user came from the registration page, get the page before that.
			$url = $form->referrer;
		}

		// Well, record this too!
		\SystemLogModel::LogSecurityEvent('/user/login', 'Login successful (via GPG Key)', null, $user->get('id'));

		// yay...
		$user->set('last_login', \CoreDateTime::Now('U', \Time::TIMEZONE_GMT));
		$user->save();
		\Core\Session::SetUser($user);

		// Allow an external script to override the redirecting URL.
		$overrideurl = \HookHandler::DispatchHook('/user/postlogin/getredirecturl');
		if($overrideurl){
			$url = $overrideurl;
		}

		$nonce->markUsed();

		return $url;
	}

	/**
	 * Handle the registration request for GPG-based accounts and do some preliminary checking.
	 *
	 * @param Form $form
	 *
	 * @return bool|string
	 */
	public static function RegisterHandler(Form $form) {
		$keyid = $form->getElement('keyid');
		$email = $form->getElement('email');

		// Search for that email address on the remote servers.
		$gpg = new \Core\GPG\GPG();
		$keys = $gpg->searchRemoteKeys($email->get('value'));
		if(!sizeof($keys)){
			$email->setError('No public keys were found with this email address, have you uploaded your key to ' . $gpg->keyserver . '?');
			return false;
		}

		if(!in_array($keyid->get('value'), $keys)){
			$keyid->setError('Email address has keys associated on ' . $gpg->keyserver . ', but the key you provided does not match any of them!');
			return false;
		}

		// Ok, there was a key on the keyserver with that email, time to actually load in the key so I can examine it closer.
		$key = $gpg->importKey($keyid->get('value'));

		if(!$key){
			$keyid->setError('Unable to load key from keyserver ' . $gpg->keyserver . ', hmmm.');
			return false;
		}

		if(!$key->isValid()){
			$keyid->setError('Key is not valid, is it revoked or expired?');
			return false;
		}

		if(!$key->isValid($email->get('value'))){
			$email->setError('UID subkey containing that email address is not valid, is it revoked or expired?');
			return false;
		}

		// All the GPG checks went through, tme to actually load the user object.
		try{
			$user = new \UserModel();
			$user->set('email', $email->get('value'));
			$user->enableAuthDriver('gpg');
			$user->set('/user/gpgauth/pubkey', $keyid->get('value'));
		}
		catch(\ModelValidationException $e){
			// Make a note of this!
			\SystemLogModel::LogSecurityEvent('/user/register', $e->getMessage());

			\Core\set_message($e->getMessage(), 'error');
			return false;
		}
		catch(\Exception $e){
			// Make a note of this!
			\SystemLogModel::LogSecurityEvent('/user/register', $e->getMessage());

			\Core\set_message(DEVELOPMENT_MODE ? $e->getMessage() : 'An unknown error occurred', 'error');

			return false;
		}

		// Otherwise, w00t!  Record this user into a nonce and forward to step 2 of registration.
		$nonce = NonceModel::Generate(
			'20 minutes',
			null,
			[
				'user' => $user,
				'redirect' => $form->getElementValue('redirect'),
			]
		);
		return '/user/register2/' . $nonce;
	}

	/**
	 * Hook receiver for /core/controllinks/user/view
	 *
	 * @param int $userid
	 *
	 * @return array
	 */
	public static function GetUserControlLinks($userid){

		$enabled = \Core\User\Helper::GetEnabledAuthDrivers();
		//if(!isset($enabled['gpg'])){
		// GPG isn't enabled at all, disable any control links from the system.
		//	return [];
		//}

		if($userid instanceof UserModel){
			$user = $userid;
		}
		else{
			/** @var UserModel $user */
			$user = UserModel::Construct($userid);
		}

		if(!$user->exists()){
			// Invalid user.
			return [];
		}

		if(!(
			\Core\user()->checkAccess('p:/user/users/manage') || \Core\user()->get('id') == $user->get('id')
		)){
			// Current user does not have access to manage the provided user's data.
			return [];
		}
		if($user->get('/user/gpgauth/pubkey')){
			$text = 'Change GPG Key';
		}
		else{
			$text = 'Upload GPG Key';
		}

		return [
			[
				'link' => '/gpgauth/configure/' . $user->get('id'),
				'title' => $text,
				'icon' => 'lock',
			]
		];
		/*
				try{
					// If this throws an exception, then it's not enabled!
					$user->getAuthDriver('gpg');
				}
				catch(Exception $e){
					return [
						[
							'link' => '/gpgauth/configure/' . $user->get('id'),
							'title' => 'Enable GPG Authentication',
							'icon' => 'lock',
						]
					];
				}
		*/

		// Otherwise?


	}
} 