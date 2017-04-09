<?php
/**
 * 
 * 
 * @package User
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20131113.1512
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

namespace Core\User;


/**
 * The interface that dictates how user authentication backends function.
 *
 * @author Charlie Powell <charlie@evalagency.com>
 * @package Core\User
 */
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