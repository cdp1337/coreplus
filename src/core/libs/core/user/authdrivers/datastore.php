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
use Core\Date\DateTime;
use Core\Templates\Template;
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
class datastore implements AuthDriverInterface{
	/**
	 * Default number of iterations to hash the password with.
	 * *WARNING* Setting this to 15 will take about 3 seconds on an 8-core system and 10 seconds on a 2-core system!
	 *
	 * @var int
	 */
	const HASH_ITERATIONS = 11;

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
		$isvalid = $this->validatePassword($password);

		if($isvalid !== true){
			// Core validation returned a string.... it's INVALID!
			return $isvalid;
		}

		// hash the password.
		$hasher = new \PasswordHash(datastore::HASH_ITERATIONS);
		$password = $hasher->hashPassword($password);

		// Still here?  Then try to set it.
		$status = $this->_usermodel->set('password', $password);
		$this->_usermodel->set('last_password', DateTime::NowGMT());
		return $status;
	}

	/**
	 * Check if this user is active and can login.
	 *
	 * This authentication driver doesn't support any additional checks, so just return the model's status.
	 *
	 * @return boolean|string
	 */
	public function isActive(){
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
		$form = new \Form($form_options);
		$form->set('callsMethod', 'DatastoreAuthController::LoginHandler');

		$form->addElement('hidden', ['name' => 'redirect']);
		$form->addElement('text', ['name' => 'email', 'title' => t('STRING_EMAIL'), 'required' => true]);
		$form->addElement('password', ['name' => 'pass', 'title' => t('STRING_PASSWORD'), 'required' => false]);
		$form->addElement('submit', ['name' => 'submit', 'value' => t('STRING_LOGIN')]);

		$tpl = Template::Factory('includes/user/datastore_login.tpl');
		$tpl->assign('form', $form);

		$tpl->render();
	}

	/**
	 * Generate and print the rendered registration markup to STDOUT.
	 *
	 * @return void
	 */
	public function renderRegister() {
		$form = new \Form();

		$complexity  = $this->getPasswordComplexityAsHTML();
		$usermanager = \Core\user()->checkAccess('p:/user/users/manage');

		if($complexity){
			$password_desc = 'Please set a secure password that <br/>' . $complexity;
		}
		else{
			$password_desc = t('MESSAGE_PLEASE_SET_SECURE_PASSWORD');
		}

		// I can utilize this form, but tweak the necessary options as necessary.
		// Replace the password field with a text input for the GPG key.
		$form->set('callsmethod', 'DatastoreAuthController::RegisterHandler');

		$form->addElement('hidden', ['name' => 'redirect']);
		$form->addElement(
			'text',
			[
				'required' => true,
				'name' => 'email',
				'title' => t('STRING_EMAIL'),
				'description' => ($usermanager ? 'The email address of the user to create' : 'Your email address'),
			]
		);
		if($usermanager){
			$form->addElement(
				'checkbox', [
					'name' => 'pwgen',
					'value' => '1',
					'title' => t('STRING_GENERATE_SECURE_PASSWORD'),
					'description' => t('MESSAGE_GENERATE_SECURE_PASSWORD'),
				]
			);
		}
		$form->addElement(
			'password',
			[
				'required' => ($usermanager ? false : true),
				'name' => 'pass',
				'title' => t('STRING_PASSWORD'),
				'description' => $password_desc,
			]
		);
		$form->addElement(
			'password',
			[
				'required' => ($usermanager ? false : true),
				'name' => 'pass2',
				'title' => t('STRING_CONFIRM_PASSWORD'),
				'description' => t('MESSAGE_CONFIRM_PASSWORD'),
			]
		);
		$form->addElement('submit', ['value' => t('STRING_CONTINUE')]);

		$tpl = Template::Factory('includes/user/datastore_register.tpl');
		$tpl->assign('is_manager', $usermanager);
		$tpl->assign('form', $form);

		$tpl->render();
	}

	/**
	 * Get the title for this Auth driver.  Used in some automatic messages.
	 *
	 * @return string
	 */
	public function getAuthTitle() {
		return 'Local Datastore';
	}

	/**
	 * Get the icon name for this Auth driver.
	 *
	 * @return string
	 */
	public function getAuthIcon(){
		return 'hdd-o';
	}

	/**
	 * Get the password complexity requirements as HTML.
	 *
	 * @return string
	 */
	public function getPasswordComplexityAsHTML(){
		$strs = [];

		if(\ConfigHandler::Get('/user/password/minlength')){
			$strs[] = t('MESSAGE_USER_PASSWORD_COMPLEXITY_REQUIREMENT_N_CHARACTER', \ConfigHandler::Get('/user/password/minlength'));
		}

		// complexity check from the config
		if(\ConfigHandler::Get('/user/password/requiresymbols') > 0){
			$strs[] = t('MESSAGE_USER_PASSWORD_COMPLEXITY_REQUIREMENT_N_SYMBOL', \ConfigHandler::Get('/user/password/requiresymbols'));
		}

		// complexity check from the config
		if(\ConfigHandler::Get('/user/password/requirecapitals') > 0){
			$strs[] = t('MESSAGE_USER_PASSWORD_COMPLEXITY_REQUIREMENT_N_CAPITAL', \ConfigHandler::Get('/user/password/requirecapitals'));
		}

		// complexity check from the config
		if(\ConfigHandler::Get('/user/password/requirenumbers') > 0){
			$strs[] = t('MESSAGE_USER_PASSWORD_COMPLEXITY_REQUIREMENT_N_NUMBER', \ConfigHandler::Get('/user/password/requirenumbers'));
		}

		return implode('<br/>', $strs);
	}

	/**
	 * Validate the password based on configuration rules.
	 *
	 * @param string $password
	 *
	 * @return bool|string
	 */
	public function validatePassword($password){
		$valid = true;
		// complexity check from the config
		if(strlen($password) < \ConfigHandler::Get('/user/password/minlength')){
			$valid = t('MESSAGE_USER_PASSWORD_COMPLEXITY_REQUIREMENT_N_CHARACTER', \ConfigHandler::Get('/user/password/minlength'));
		}

		// complexity check from the config
		if(\ConfigHandler::Get('/user/password/requiresymbols') > 0){
			preg_match_all('/[^a-zA-Z0-9]/', $password, $matches);
			if(sizeof($matches[0]) < \ConfigHandler::Get('/user/password/requiresymbols')){
				$valid = t('MESSAGE_USER_PASSWORD_COMPLEXITY_REQUIREMENT_N_SYMBOL', \ConfigHandler::Get('/user/password/requiresymbols'));
			}
		}

		// complexity check from the config
		if(\ConfigHandler::Get('/user/password/requirecapitals') > 0){
			preg_match_all('/[A-Z]/', $password, $matches);
			if(sizeof($matches[0]) < \ConfigHandler::Get('/user/password/requirecapitals')){
				$valid = t('MESSAGE_USER_PASSWORD_COMPLEXITY_REQUIREMENT_N_CAPITAL', \ConfigHandler::Get('/user/password/requirecapitals'));
			}
		}

		// complexity check from the config
		if(\ConfigHandler::Get('/user/password/requirenumbers') > 0){
			preg_match_all('/[0-9]/', $password, $matches);
			if(sizeof($matches[0]) < \ConfigHandler::Get('/user/password/requirenumbers')){
				$valid = t('MESSAGE_USER_PASSWORD_COMPLEXITY_REQUIREMENT_N_NUMBER', \ConfigHandler::Get('/user/password/requirenumbers'));
			}
		}

		return $valid;
	}

	/**
	 * Generate a password that meets the site complexity requirements.
	 *
	 * @return string
	 */
	public function pwgen(){
		$minlength       = \ConfigHandler::Get('/user/password/minlength');
		$requiresymbols  = \ConfigHandler::Get('/user/password/requiresymbols');
		$requirecapitals = \ConfigHandler::Get('/user/password/requirecapitals');
		$requirenumbers  = \ConfigHandler::Get('/user/password/requirenumbers');

		$length = ($minlength ? $minlength : 8) + rand(2,6);
		$str = '';
		$strS = $strC = $strN = 0;

		$symbols  = [
			'?', '!', '@', '#', '$', '%', '&', '*', '(', ')', // Common
			'?', '!', '@', '#', '$', '%', '&', '*', '(', ')', '^', '+', '"', "'", '/', '<', '>', '.', ',', ';', ':', '[', ']', '{', '}', '|' // Common + rest
		];
		$letters  = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
		$capitals = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

		// Capital letters and numbers are simple enough!
		// Add them even if the minimum is 0.
		$requirecapitals = max($requirecapitals, 1);
		$requirenumbers  = max($requirenumbers, 1);

		if($requiresymbols){
			$map = ['l', 's', 'l', 'c', 'l', 'n'];
		}
		else{
			$map = ['l', 'c', 'l', 'n'];
		}

		do{
			$key = rand(0, sizeof($map) - 1);

			switch($map[$key]){
				case 'l':
					$str .= $letters[ rand(0, sizeof($letters) - 1) ];
					break;
				case 'c':
					$str .= $capitals[ rand(0, sizeof($capitals) - 1) ];
					++$strC;
					break;
				case 'n':
					$str .= rand(0, 9);
					++$strN;
					break;
				case 's':
					$str .= $symbols[ rand(0, sizeof($symbols) - 1) ];
					++$strS;
					break;
			}

			// If the total number of everything has been met, break out of the do... while loop.
			if(
				strlen($str) >= $length &&
				$strS >= $requiresymbols &&
				$strC >= $requirecapitals &&
				$strN >= $requirenumbers
			){
				break;
			}

			if(strlen($str) > $length * 2){
				// Ok, the length is getting ridiculously long, drop the lowercase letters and start running just symbols/caps.
				$map = [];
				if($strS < $requiresymbols) $map[] = 's';
				if($strC < $requirecapitals) $map[] = 'c';
				if($strN < $requirenumbers) $map[] = 'n';
			}

		} while(strlen($str) < $length * 6);


		return $str;
	}
}