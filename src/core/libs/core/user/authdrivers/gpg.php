<?php
/**
 * File for class datastore definition in the tenant-visitor project
 * 
 * @package User\AuthDrivers
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20131113.1512
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
	 * Set the user's password using the necessary hashing
	 *
	 * @param $password
	 *
	 * @return bool|string True/False on success or failure, a string if on error.
	 */
	public function setPassword($password) {
		// Use Core's built-in validation.
		$isvalid = $this->_usermodel->validatePassword($password);

		if($isvalid !== true){
			// Core validation returned a string.... it's INVALID!
			return $isvalid;
		}

		// hash the password.
		$hasher = new \PasswordHash(datastore::HASH_ITERATIONS);
		$password = $hasher->hashPassword($password);

		// Still here?  Then try to set it.
		return $this->_usermodel->set('password', $password);
	}

	/**
	 * Check if this user is active and can login.
	 *
	 * @return boolean|string
	 */
	public function isActive(){
		$keyid = $this->_usermodel->get('/user/gpgauth/pubkey');
		if(!$keyid){
			return 'No GPG key is set for your user account, please add one before logging in.';
		}

		$gpg = new \Core\GPG\GPG();
		$key = $gpg->importKey($keyid);
		if(!$key){
			return 'Your GPG key was not found on the remote servers, please upload it to ' . $gpg->keyserver . ' first.';
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
}