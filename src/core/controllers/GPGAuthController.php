<?php
/**
 * File for class GPGAuthController definition in the coreplus project
 *
 * @package Core\User
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140319.1608
 * @copyright Copyright (C) 2009-2017  Charlie Powell
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

		$form = new \Core\Forms\Form();
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
		$nonce->decryptData();
		$data = $nonce->get('data');
		
		if($nonce->isUsed()){
			// It's used!  yay!
			// This is needed here because the CLI will submit to one page and that session,
			// but the user is browsing with another session.
			// If it's marked as used, then it was a successful login.
			$user = \UserModel::Construct($data['user']);
			\Core\Session::SetUser($user);

			// Allow an external script to override the redirecting URL.
			$overrideurl = \HookHandler::DispatchHook('/user/postlogin/getredirecturl');
			$url = $overrideurl ? $overrideurl : $data['redirect'];
			
			\Core\redirect($url);
		}

		if(!$nonce->isValid()){
			\Core\set_message('Invalid nonce provided!', 'error');
			\Core\go_back();
		}
		
		$user = UserModel::Construct($data['user']);
		$sentence = $data['sentence'];
		$email = $user->get('email'); // use this instead of fingerprint to not reveal which key the user used for signing their logins!
		$url = \Core\resolve_link('/gpgauth/rawlogin');

		$cmd = <<<EOD
echo -n "{$sentence}" \\
| gpg -b -a --default-key "$email" \\
| curl --data-binary @- \\
--header "X-Core-Nonce-Key: $nonce" \\
$url

EOD;

		$form = new \Core\Forms\Form();
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

		$view->assign('cmd', $cmd);
		$view->assign('sentence', $data['sentence']);
		$view->assign('form', $form);
		$view->assign('nonce', $nonce->get('key'));
	}

	/**
	 * Second page for GPG registration; should be called automatically.
	 * 
	 * This happens after the user uploads the GPG public key and is meant to allow the user to select the email to use based on the key's data.
	 */
	public function register2(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		/** @var NonceModel $nonce */
		$nonce = NonceModel::Construct($request->getParameter(0));
		$nonce->decryptData();
		
		if($nonce->isUsed()){
			$data = $nonce->get('data');
			
			$form = new \Core\Forms\Form();
			$form->set('callsmethod', 'GPGAuthController::RegisterHandler');
			$form->addElement('system', ['name' => 'redirect', 'value' => $data['original_redirect']]);
			$form->addElement('system', ['name' => 'keyid', 'value' => $data['key']]);
			
			// Extract out the emails on this key and present the user with the option to select the correct one.
			$gpg = new \Core\GPG\GPG();
			$key = $gpg->getKey($data['key']);
			
			$emailOpts = [];
			
			foreach($key->uids as $uid){
				/** @var \Core\GPG\UID $uid */
				if($uid->isValid() && $uid->email){
					$emailOpts[] = $uid->email;
				}
			}
			
			if(!sizeof($emailOpts)){
				\Core\set_message('No valid emails found on the uploaded key!', 'error');
				\Core\go_back();
			}
			
			$form->addElement('radio', [
				'name' => 'email', 
				'required' => true, 
				'title' => 'Select Your Email', 
				'options' => $emailOpts, 
				'description' => 'Select the email address you would like to use to login and which you would like to receive notifications to.'
			]);
			$form->addElement('submit', ['value' => 'Continue With GPG']);
			
			$view->assign('form', $form);
		}
		else{
			\Core\set_message('Invalid nonce requested!', 'error');
			\Core\go_back();
		}
	}

	/**
	 * Method to be expected to be called from the command line to upload a key.
	 * 
	 * This is used from the reset+configure page.
	 */
	public function rawUpload(){
		$view = $this->getView();
		$view->mode = View::MODE_NOOUTPUT;
		$view->templatename = false;
		$view->contenttype = 'text/plain';

		$nonce = isset($_SERVER['HTTP_X_CORE_NONCE_KEY']) ? $_SERVER['HTTP_X_CORE_NONCE_KEY'] : null;

		if(!$nonce){
			echo "Invalid nonce provided!\n";
			return;
		}

		/** @var \NonceModel $nonce */
		$nonce = \NonceModel::Construct($nonce);

		if($nonce->isUsed()){
			echo 'Step already complete!';
			return;	
		}

		if(!$nonce->isValid()){
			echo "Invalid nonce provided!\n";
			return;
		}
		$nonce->decryptData();
		$data = $nonce->get('data');

		$input = file_get_contents('php://input');

		if(!$input){
			echo "No key found!  Do you have one generated yet?\n";
			return;
		}

		if(isset($data['user']) && $data['user']){
			$user = UserModel::Construct($data['user']);

			if(!$user->exists()){
				echo "Invalid user requested!\n";
				return;
			}
		}
		else{
			// It's a new user!
			$user = null;
		}
		
		try{
			$gpg = new Core\GPG\GPG();
			$key = $gpg->importKey($input);

			if($user && ($newnonce = \Core\User\AuthDrivers\gpg::SendVerificationEmail($user, $key->fingerprint))){
				// Record the new nonce so that the other process checking for a status update has more metainformation to pull from.
				// This is because there are two separate connections going on;
				// This one and the web connection.
				$data['redirect'] = \Core\resolve_link('/gpgauth/configure2/' . $newnonce);
				$nonce->set('data', $data);
				$nonce->markUsed();
				echo "Step 1 of 2 complete!  Please check your email for further instructions to verify this key!\n";
				return;
			}
			elseif(!$user){
				// It's a registration!
				$data['redirect'] = \Core\resolve_link('/gpgauth/register2/' . $nonce->get('key'));
				$data['key'] = $key->fingerprint;
				$nonce->set('data', $data);
				$nonce->markUsed();
				echo "Step 1 complete!  Please check the website to continue.\n";
				return;
			}
		}
		catch(\phpmailerException $e){
			\Core\ErrorManagement\exception_handler($e);
			echo "Unable to send the verification email!  Please ensure that mail sending is enabled on the system.\n";
		}
		catch(\Exception $e){
			\Core\ErrorManagement\exception_handler($e);
			echo (DEVELOPMENT_MODE ? $e->getMessage() : "Invalid input provided, upload failed!") . "\n";
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

		if($nonce->isUsed()){
			echo "GPG key already submitted!\n";
			return;
		}
		
		if(!$nonce->isValid()){
			echo "Invalid nonce provided!\n";
			return;
		}

		if(!$input){
			echo "No key found!  Do you have one generated yet?\n";
			return;
		}
		
		// Verify that this user was the one provided by the nonce.
		$nonce->decryptData();

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

	public function rawLogin(){
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
		
		if($nonce->isUsed()){
			echo "GPG already used to login once, please login again!\n";
			return;
		}

		if(!$nonce->isValid()){
			echo "Invalid nonce provided!\n";
			return;
		}

		// Now is where the real fun begins.
		$nonce->decryptData();
		$data = $nonce->get('data');

		/** @var UserModel $user */
		$user = UserModel::Construct($data['user']);
		$keyid = $user->get('gpgauth_pubkey');

		//var_dump($data, $form->getElement('message')->get('value')); die();

		$gpg = new \Core\GPG\GPG();
		// I can skip the import here, as it was just checked for validity on the first step of login.
		$key = $gpg->getKey($keyid);

		if(!$key){
			echo "That key could not be loaded from local!\n";
			return;
		}

		if(!$key->isValid()){
			echo "Your GPG key is not valid anymore, is it revoked or expired?\n";
			return;
		}

		if(!$key->isValid($user->get('email'))){
			echo "Your GPG subkey containing your email address is not valid anymore, is it revoked or expired?\n";
			return;
		}

		// Lastly, verify that the signature is correct.
		if(!$gpg->verifyDataSignature($input, $data['sentence'])){
			echo "Invalid signature!  Did the command execute successfully?\n";
			return;
		}

		// Well, record this too!
		\SystemLogModel::LogSecurityEvent('/user/login', 'Login successful (via GPG Key)', null, $user->get('id'));

		// yay...
		$user->set('last_login', \CoreDateTime::Now('U', \Time::TIMEZONE_GMT));
		$user->save();
		
		$nonce->markUsed();
		echo "Login successful!  Please refresh your browser if it does not automatically.\n";
	}

	/**
	 * The public configure method for each user.
	 *
	 * This helps the user set his/her public key that the system will use to authenticate with.
	 */
	public function configure(){
		$view = $this->getView();
		$request = $this->getPageRequest();
		
		if(!$request->getParameter(0)){
			return View::ERROR_BADREQUEST;
		}
		
		$nonce = NonceModel::Construct($request->getParameter(0));
		$nonce->decryptData();
		$data = $nonce->get('data');
		/** @var UserModel $user */
		$user      = UserModel::Construct($data['user']);
		$isManager = \Core\user()->checkAccess('p:/user/users/manage');
		$loggedin  = \Core\user()->checkAccess('p:authenticated');
		$key       = $nonce->get('key');
		
		if(!$nonce->isValid()){
			// If the user is currently a manager, allow the first key to be the user ID that is being edited.
			if($isManager){
				$user = UserModel::Construct($request->getParameter(0));
				$key = NonceModel::Generate(
					'5 minutes',
					null,
					[
						'user' => $user->get('id')
					]
				);
			}
			else{
				\Core\set_message('t:MESSAGE_ERROR_BAD_OR_EXPIRED_SESSION');
				\Core\go_back();
				return View::ERROR_BADREQUEST;	
			}
		}

		if(!$isManager && $user->get('id') != \Core\user()->get('id')){
			// Current user is not a manager and is not the same user as the one submitting!
			// This is triggered if the user is logged in and it's not their account.
			// The idea is anonymous users can reset their own account if necessary.
			return View::ERROR_ACCESSDENIED;
		}

		if(!$user->exists()){
			// Current user does not have access to manage the provided user's data.
			return View::ERROR_ACCESSDENIED;
		}

		$currentkey = $user->get('gpgauth_pubkey');

		$eml = $user->get('email');
		//$key = $user->get('apikey');
		$url = \Core\resolve_link('/gpgauth/rawupload');
		$cmd = <<<EOD
gpg --export -a $eml 2&gt;/dev/null | curl --data-binary @- \\
--header "X-Core-Nonce-Key: $key" \\
$url

EOD;
		$form = new \Core\Forms\Form();
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
		$view->assign('current_key', $loggedin ? $currentkey : null);
		$view->assign('cmd', $cmd);
		$view->assign('nonce', $key);
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

		$form = new \Core\Forms\Form();
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
		$view->assign('nonce', $nonce->get('key'));
	}

	/**
	 * View for checking if there was a successful action from the command line,
	 * and redirect to the next page when successful.
	 */
	public function jsoncheck(){
		$view = $this->getView();
		$request = $this->getPageRequest();
		
		$view->mode = View::MODE_AJAX;
		$view->contenttype = View::CTYPE_JSON;
		// Do not record this in the user activity; it will just clutter that.
		$view->record = false;
		
		// The nonce key should be the first parameter.
		if(!$request->getParameter(0)){
			$view->jsondata = ['status' => 'error', 'message' => 'No Nonce key provided!'];
			return;
		}
		
		$nonce = NonceModel::Construct($request->getParameter(0));
		if(!$nonce->exists()){
			$view->jsondata = ['status' => 'error', 'message' => 'Nonce completed or does not exist anymore!'];
			return;
		}

		$nonce->decryptData();
		$data = $nonce->get('data');

		if($nonce->isUsed()){
			if(isset($data['redirect'])){
				$view->jsondata = ['status' => 'complete', 'message' => 'Nonce completed.', 'redirect' => $data['redirect']];
			}
			else{
				$view->jsondata = ['status' => 'complete', 'message' => 'Nonce completed.'];
			}
			return;
		}
		
		if($nonce->isValid()){
			$view->jsondata = ['status' => 'pending', 'message' => 'Still valid Nonce, waiting.'];
			return;
		}
		else{
			$view->jsondata = ['status' => 'error', 'message' => 'Nonce completed or does not exist anymore!'];
			return;
		}
	}

	/**
	 * Form submission page for anonymous-based set or reset attempts.
	 * This requires an additional step because I don't want to expose the user ID based on their email.
	 *
	 * @param Form $form
	 *
	 * @return bool|string
	 */
	public static function ResetHandler(\Core\Forms\Form $form) {
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
	public static function ConfigureHandler(\Core\Forms\Form $form){
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
	public static function Configure2Handler(\Core\Forms\Form $form){
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
	public static function LoginHandler(\Core\Forms\Form $form){
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
				'redirect' => ($form->getElementValue('redirect') ? $form->getElementValue('redirect') : ROOT_URL),
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
	public static function Login2Handler(\Core\Forms\Form $form){
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
		$keyid = $user->get('gpgauth_pubkey');

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
		if(!$gpg->verifyDataSignature($form->getElement('message')->get('value'), $data['sentence'])){
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
	public static function RegisterHandler(\Core\Forms\Form $form) {
		$keyid = $form->getElement('keyid');
		$key   = $form->getElement('key');
		$email = $form->getElement('email');

		// Search for that email address on the remote servers.
		$gpg = new \Core\GPG\GPG();
		
		if($key){
			// Was there a key manually uploaded?
			$pubKey = $gpg->importKey($key->get('value'));
		}
		elseif($keyid){
			// If uploaded automatically from a script, the value will simply be the key ID.
			$pubKey = $gpg->getKey($keyid->get('value'));
		}
		else{
			\Core\set_message('Please either upload a key or run the command to automatically upload one!', 'error');
			return false;
		}

		if(!$pubKey->isValid()){
			\SystemLogModel::LogSecurityEvent('/user/register', 'FAILED GPG register - revoked or expired public key');
			$keyid->setError('Key is not valid, is it revoked or expired?');
			return false;
		}
		
		if(!$pubKey->getUID($email->get('value'))){
			$email->setError('That email address was not listed in for your key!');
			\SystemLogModel::LogSecurityEvent('/user/register', 'FAILED GPG register - Email not included in public key');
			return false;
		}
		
		if(!$pubKey->isValid($email->get('value'))){
			$email->setError('That email address is either revoked or expired!');
			\SystemLogModel::LogSecurityEvent('/user/register', 'FAILED GPG register - Email marked as expired or revoked');
			return false;
		}

		// All the GPG checks went through, tme to actually load the user object.
		try{
			$user = new \UserModel();
			$user->set('email', $email->get('value'));
			$user->enableAuthDriver('gpg');
			$user->set('gpgauth_pubkey', $pubKey->fingerprint);
			
			// Was there a photo attached to this public key?
			if(sizeof($pubKey->getPhotos()) > 0){
				$p = $pubKey->getPhotos();
				// I just want the first.
				/** @var Core\Filestore\File $p */
				$p = $p[0];
				
				$localFile = \Core\Filestore\Factory::File('public/user/avatar/' . $pubKey->fingerprint . '.' . $p->getExtension());
				$p->copyTo($localFile);
				$user->set('avatar', $localFile->getFilename(false));
			}
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
		if($user->get('gpgauth_pubkey')){
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