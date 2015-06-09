<?php
/**
 * File for class GPGAuthController definition in the coreplus project
 *
 * @package Core\User
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140319.1608
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
			Core::SetMessage('Invalid nonce provided!', 'error');
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
			$userid = $data['user'];
		}
		else{
			$userid = $request->getParameter(0);
		}


		/** @var UserModel $user */
		$user = UserModel::Construct($userid);

		if(!(
			\Core\user()->checkAccess('p:/user/users/manage') ||
			\Core\user()->get('id') == $user->get('id') ||
			!\Core\user()->exists()
		)){
			// Current user does not have access to manage the provided user's data.
			return View::ERROR_ACCESSDENIED;
		}

		$currentkey = $user->get('/user/gpgauth/pubkey');

		// Lookup the keys that match to this user.
		$gpg = new \Core\GPG\GPG();
		$keys = $gpg->searchRemoteKeys($user->get('email'));

		// I'm going to throw in a few extra keys into the mix to require the user to choose.
		$keys[] = Core::RandomHex(8);
		$keys[] = Core::RandomHex(8);

		// And shuffle them.
		shuffle($keys);

		$form = new Form();
		$form->set('callsmethod', 'GPGAuthController::ConfigureHandler');
		$form->addElement('system', ['name' => 'userid', 'value' => $user->get('id')]);
		$form->addElement(
			'radio',
			[
				'required' => true,
				'name' => 'key',
				'title' => 'Your Public Key',
				'options' => $keys
			]
		);
		$form->addElement('submit', ['name' => 'submit', 'value' => 'Next (email verification)']);

		$view->assign('current_key', $currentkey);
		$view->assign('keys', $keys);
		$view->assign('form', $form);
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
			Core::SetMessage('Invalid nonce provided!', 'error');
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
		// Generate a random sentence to be decoded with the given nonce.
		$sentence = trim(BaconIpsumGenerator::Make_a_Sentence());

		$nonce = NonceModel::Generate(
			'5 minutes',
			null,
			[
				'sentence' => $sentence,
				'key' => $form->getElement('key')->get('value'),
				'user' => $form->getElement('userid')->get('value')
			]
		);

		$user = UserModel::Construct($form->getElement('userid')->get('value'));

		$email = new Email();
		$email->setSubject('GPG Key Change Request');
		$email->assign('key', $form->getElement('key')->get('value'));
		$email->assign('sentence', $sentence);
		$email->templatename = 'emails/user/gpgauth_key_verification.tpl';
		$email->to($user->get('email'));

		if($email->send()){
			Core::SetMessage('Instructions have been sent to your email.  Please complete them within 5 minutes.', 'success');
			return '/gpgauth/configure2/' . $nonce;
		}
		else{
			Core::SetMessage('Unable to send verification email, please contact the administrator about this issue.', 'error');
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
		$nonce = NonceModel::Construct($form->getElement('nonce')->get('value'));

		if(!$nonce->isValid()){
			Core::SetMessage('Invalid nonce provided!', 'error');
			return false;
		}

		// Now is where the real fun begins.

		$nonce->decryptData();

		$data = $nonce->get('data');

		/** @var UserModel $user */
		$user = UserModel::Construct($data['user']);

		//var_dump($data, $form->getElement('message')->get('value')); die();

		$gpg = new \Core\GPG\GPG();
		$key = $gpg->importKey($data['key']);

		if(!$key){
			Core::SetMessage('That key could not be loaded from the keyservers!', 'error');
			return false;
		}

		if(!$key->isValid()){
			Core::SetMessage('That key is not valid!  Is it expired or revoked?', 'error');
			return false;
		}

		if(!$key->isValid($user->get('email'))){
			Core::SetMessage('That email subkey is not valid!  Is it expired or revoked?', 'error');
			return false;
		}

		// Lastly, verify that the signature is correct.
		if(!$gpg->verifyDetachedSignature($form->getElement('message')->get('value'), $data['sentence'], $data['key'])){
			Core::SetMessage('Invalid signature!', 'error');
			return false;
		}

		// Otherwise?
		$user->enableAuthDriver('gpg');
		$user->set('/user/gpgauth/pubkey', $data['key']);
		$user->save();

		$nonce->markUsed();

		Core::SetMessage('Enabled/Updated GPG key successfully!', 'success');

		if(!\Core\user()->exists()){
			// Not logged in yet, this process can log the user in.
			\SystemLogModel::LogSecurityEvent('/user/login', 'Login successful (via GPG Key)', null, $user->get('id'));

			// yay...
			$user->set('last_login', \CoreDateTime::Now('U', \Time::TIMEZONE_GMT));
			$user->save();
			\Session::SetUser($user);
		}

		return '/user/me';
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
			Core::SetMessage('Invalid nonce provided!', 'error');
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
			Core::SetMessage('That key could not be loaded from local!', 'error');
			return false;
		}

		if(!$key->isValid()){
			Core::SetMessage('Your GPG key is not valid anymore, is it revoked or expired?', 'error');
		}

		if(!$key->isValid($user->get('email'))){
			Core::SetMessage('Your GPG subkey containing your email address is not valid anymore, is it revoked or expired?', 'error');
		}

		// Lastly, verify that the signature is correct.
		if(!$gpg->verifyDetachedSignature($form->getElement('message')->get('value'), $data['sentence'], $keyid)){
			Core::SetMessage('Invalid signature!', 'error');
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
			$url = \Core::GetHistory(2);
		}

		// Well, record this too!
		\SystemLogModel::LogSecurityEvent('/user/login', 'Login successful (via GPG Key)', null, $user->get('id'));

		// yay...
		$user->set('last_login', \CoreDateTime::Now('U', \Time::TIMEZONE_GMT));
		$user->save();
		\Session::SetUser($user);

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

			\Core::SetMessage($e->getMessage(), 'error');
			return false;
		}
		catch(\Exception $e){
			// Make a note of this!
			\SystemLogModel::LogSecurityEvent('/user/register', $e->getMessage());

			if(DEVELOPMENT_MODE) \Core::SetMessage($e->getMessage(), 'error');
			else \Core::SetMessage('An unknown error occurred', 'error');

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

		$enabled = User\Helper::GetEnabledAuthDrivers();
		if(!isset($enabled['gpg'])){
			// GPG isn't enabled at all, disable any control links from the system.
			return [];
		}

		/** @var UserModel $user */
		$user = UserModel::Construct($userid);

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

		// Otherwise?
		return [
			[
				'link' => '/gpgauth/configure/' . $user->get('id'),
				'title' => 'Change GPG Key',
				'icon' => 'lock',
			]
		];

	}
} 