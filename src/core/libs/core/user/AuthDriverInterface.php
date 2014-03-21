<?php
/**
 * Enter a meaningful file description here!
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20131113.1510
 * @package PackageName
 * 
 * Created with PhpStorm.
 */

namespace Core\User;


interface AuthDriverInterface {
	/**
	 * @param \UserModel|null $usermodel
	 */
	public function __construct(\UserModel $usermodel = null);

	/**
	 * Check if this user is active and can login.
	 *
	 * If true is returned, the user is valid.
	 * If false is returned, the user is invalid with no message.
	 * If a string is returned, the user is invalid and a message is to be displayed to the user.
	 *
	 * @return boolean|string
	 */
	public function isActive();

	/**
	 * Generate and print the rendered login markup to STDOUT.
	 *
	 * @param array $form_options
	 * 
	 * @return void
	 */
	public function renderLogin($form_options = []);

	/**
	 * Generate and print the rendered registration markup to STDOUT.
	 *
	 * @return void
	 */
	public function renderRegister();

	/**
	 * Get the title for this Auth driver.  Used in some automatic messages.
	 *
	 * @return string
	 */
	public function getAuthTitle();

	/**
	 * Get the icon name for this Auth driver.
	 *
	 * @return string
	 */
	public function getAuthIcon();
} 