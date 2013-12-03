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
	public function __construct(\UserModel $usermodel);

	/**
	 * Check that the supplied password or key is valid for this user.
	 *
	 * @param string $password The password to verify
	 * @return boolean
	 */
	public function checkPassword($password);

	/**
	 * Set the user's password using the necessary hashing
	 *
	 * @param $password
	 *
	 * @return bool|string True/False on success or failure, a string if on error.
	 */
	public function setPassword($password);

	/**
	 * Check if this user is active and can login.
	 *
	 * @return boolean
	 */
	public function isActive();

	/**
	 * Get if this user can set their password via the site.
	 *
	 * @return bool|string True if backend allows for password management, a string if cannot.
	 */
	public function canSetPassword();

	/**
	 * Get if this user can login via a password on the traditional login interface.
	 *
	 * @return boolean
	 */
	public function canLoginWithPassword();
} 