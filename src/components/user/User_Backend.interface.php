<?php
/**
 * DESCRIPTION
 *
 * @package User
 * @since 1.9
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2013  Charlie Powell
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

interface User_Backend {
	/**
	 * Get the group IDs this user is a member of.
	 *
	 * @return array
	 */
	public function getGroups();
	public function setGroups($groups);

	public function delete();

	/**
	 * Get all user configs for this given user
	 *
	 * @since 2011.08
	 * @return array Key/value pair fo the configs.
	 */
	public function getConfigs();

	/**
	 * Simple check if this user exists in the database.
	 *
	 * @since 2011.08
	 * @return boolean
	 */
	public function exists();

	/**
	 * Get a key from this current user either from the core
	 * user table or from the config options.
	 *
	 * Will try the core table first, then check for a config key name
	 * that matches.
	 *
	 * @since 2011.08
	 * @param string $key
	 * @return mixed String, boolean, int or float if exists, null if otherwise.
	 */
	public function get($key);

	/**
	 * Set a key on either the core user table or its config options.
	 *
	 * Will try the core table first, then the config key.
	 *
	 * @since 2011.08
	 * @param string $k
	 * @param mixed $v
	 */
	public function set($k, $v);

	/**
	 * Save this user and all of its metadata, including configs and groups.
	 *
	 * @return void
	 */
	public function save();


	/**
	 * Check access for a given access string against this user.
	 *
	 * The access string is the core component to Core+ authentication.
	 *
	 * @since 2011.08
	 * @param type $accessstring
	 * @return bool
	 */
	public function checkAccess($accessstring);

	public function checkPassword($password);

	/**
	 * Get the display name for this user, based on the configuration settings.
	 *
	 * @return string
	 */
	public function getDisplayName();

	/**
	 * Simple function that can be used to return either true if this backend
	 * supports resetting the password, or a string to display as an error message
	 * if it cannot.
	 *
	 * Useful for facebook-type accounts, where an external system manages the password.
	 *
	 * @return true | string
	 */
	public function canResetPassword();
}
