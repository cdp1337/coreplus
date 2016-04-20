<?php
/**
 * File for class datastore definition in the tenant-visitor project
 * 
 * @package User\AuthDrivers
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20131113.1512
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

namespace Core\User\AuthDrivers;
use Core\User\AuthDriverInterface;


/**
 * A short teaser of what datastore does.
 *
 * More lengthy description of what datastore does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for datastore
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
 * @package User\AuthDrivers
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class gpg implements AuthDriverInterface{
	/**
	 * @var \UserModel The parent model object for this user.
	 */
	protected $_usermodel;

	public function __construct(\UserModel $usermodel = null){
		if($usermodel){
			$this->_usermodel = $usermodel;
		}
	}

	/**
	 * Check that the supplied password or key is valid for this user.
	 *
	 * @param string $password The password to verify
	 * @return boolean
	 */
	public function checkPassword($password){
		$hasher = new \PasswordHash(datastore::HASH_ITERATIONS);
		// The password for datastores are stored in the datastore.
		$currentpass = $this->_usermodel->get('password');

		return $hasher->checkPassword($password, $currentpass);
	}

	/**
	 * Check if this user is active and can login.
	 *
	 * @return boolean|string
	 */
	public function isActive(){
		$keyid = $this->_usermodel->get('gpgauth_pubkey');
		if(!$keyid){
			return 'No GPG key is set for your user account, please add one before logging in.';
		}

		$gpg = new \Core\GPG\GPG();
		$key = $gpg->getKey($keyid);
		if(!$key){
			return 'Your GPG key was not found on the remote servers, please upload it first.';
		}

		if(!$key->isValid()){
			return 'Your GPG key is not valid anymore, is it revoked or expired?';
		}

		if(!$key->isValid($this->_usermodel->get('email'))){
			return 'Your GPG subkey containing your email address is not valid anymore, is it revoked or expired?';
		}

		// Otherwise...
		return true;
	}

	/**
	 * Get if this user can set their password via the site.
	 *
	 * @return boolean
	 */
	public function canSetPassword() {
		// Datastore users CAN set their password.
		return false;
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
	 * @param array $form_options
	 *
	 * @return void
	 */
	public function renderLogin($form_options = []) {
		$form = new \Form($form_options);
		$form->set('callsMethod', 'GPGAuthController::LoginHandler');

		$form->addElement('text', array('name' => 'email', 'title' => 'Email', 'required' => true));
		$form->addElement('hidden', ['name' => 'redirect', 'value' => CUR_CALL]);

		$tpl = \Core\Templates\Template::Factory('includes/user/gpg_login.tpl');
		$tpl->assign('form', $form);

		$tpl->render();
	}

	/**
	 * Generate and print the rendered registration markup to STDOUT.
	 *
	 * @return void
	 */
	public function renderRegister() {
		/** @var \Form $form */
		$form = new \Form();

		// I can utilize this form, but tweak the necessary options as necessary.
		// Replace the password field with a text input for the GPG key.
		$form->set('callsmethod', 'GPGAuthController::RegisterHandler');
		$form->addElement(
			'text',
			[
				'required' => true,
				'name' => 'email',
				'title' => 'Email',
				'description' => 'Your email address, (must be attached to a valid GPG key)',
			]
		);
		$form->addElement(
			'text',
			[
				'required' => true,
				'name' => 'keyid',
				'title' => 'GPG Public Key ID',
				'description' => 'Your GPG public key ID, this should be just 8 digits.',
				'maxlength' => 8,
			]
		);

		$form->addElement('submit', ['value' => 'Continue With GPG']);

		$tpl = \Core\Templates\Template::Factory('includes/user/datastore_register.tpl');
		$tpl->assign('form', $form);

		$tpl->render();
	}

	/**
	 * Get the title for this Auth driver.  Used in some automatic messages.
	 *
	 * @return string
	 */
	public function getAuthTitle() {
		return 'Local with GPG Authentication';
	}

	/**
	 * Get the icon name for this Auth driver.
	 *
	 * @return string
	 */
	public function getAuthIcon(){
		return 'lock';
	}

	/**
	 * Send the commands to a user to verify they have access to the provided GPG key.
	 *
	 * @param \UserModel $user
	 * @param string     $fingerprint
	 * @param boolean    $cli         Set to false to send non-CLI instructions.
	 *
	 * @return false|string
	 */
	public static function SendVerificationEmail(\UserModel $user, $fingerprint, $cli = true){
		$sentence = trim(\BaconIpsumGenerator::Make_a_Sentence());

		$nonce = \NonceModel::Generate(
			'30 minutes',
			null,
			[
				'sentence' => $sentence,
				'key' => $fingerprint,
				'user' => $user->get('id'),
			]
		);

		$key = $user->get('apikey');
		$url = \Core\resolve_link('/gpgauth/rawverify');
		if($cli){
			$cmd = <<<EOD
echo -n "{$sentence}" \\
| gpg -b -a --default-key $fingerprint \\
| curl --data-binary @- \\
--header "X-Core-Nonce-Key: $nonce" \\
$url

EOD;
		}
		else{
			$cmd = <<<EOD
echo -n "{$sentence}" | gpg -b -a
EOD;
		}


		$email = new \Email();
		$email->templatename = 'emails/user/gpgauth_key_verification.tpl';
		$email->setSubject('GPG Key Change Request');
		$email->assign('key', $fingerprint);
		$email->assign('sentence', $sentence);
		$email->assign('user', $user);
		$email->assign('cmd', $cmd);
		$email->to($user->get('email'));
		$email->setEncryption($fingerprint);

		\SystemLogModel::LogSecurityEvent('/user/gpg/submit', 'Verification requested for key ' . $fingerprint, null, $user->get('id'));

		if(!$email->send()){
			return false;
		}
		else{
			return $nonce;
		}
	}

	/**
	 * Validate the verification email, part 2 of confirmation.
	 *
	 * @param string $nonce
	 * @param string $signature
	 *
	 * @return bool|string
	 */
	public static function ValidateVerificationResponse($nonce, $signature) {
		/** @var \NonceModel $nonce */
		$nonce = \NonceModel::Construct($nonce);

		if(!$nonce->isValid()){
			\SystemLogModel::LogSecurityEvent('/user/gpg/verified', 'FAILED to verify key (Invalid NONCE)', null);
			return 'Invalid nonce provided!';
		}

		// Now is where the real fun begins.

		$nonce->decryptData();

		$data = $nonce->get('data');

		/** @var \UserModel $user */
		$user = \UserModel::Construct($data['user']);
		$gpg  = new \Core\GPG\GPG();
		$key  = $data['key'];

		try{
			$sig = $gpg->verifyDataSignature($signature, $data['sentence']);
		}
		catch(\Exception $e){
			\SystemLogModel::LogSecurityEvent('/user/gpg/verified', 'FAILED to verify key ' . $key, null, $user->get('id'));
			return 'Invalid signature';
		}

		$fpr = str_replace(' ', '', $sig->fingerprint); // Trim spaces.
		if($key != $fpr && $key != $sig->keyID){
			// They must match!
			\SystemLogModel::LogSecurityEvent('/user/gpg/verified', 'FAILED to verify key ' . $key, null, $user->get('id'));
			return 'Invalid signature';
		}

		// Otherwise?
		$user->enableAuthDriver('gpg');
		$user->set('gpgauth_pubkey', $fpr);
		$user->save();

		$nonce->markUsed();

		\SystemLogModel::LogSecurityEvent('/user/gpg/verified', 'Verified key ' . $fpr, null, $user->get('id'));

		return true;
	}
}