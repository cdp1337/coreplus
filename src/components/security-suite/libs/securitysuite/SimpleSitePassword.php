<?php
/**
 * File for class SimpleSitePassword definition in the Ticketing Application project
 * 
 * @package SecuritySuite
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20140214.2130
 * @copyright Copyright (C) 2009-2013  Author
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

namespace SecuritySuite;


/**
 * Helper for the security configuration option /security/site_password.
 *
 * Provides a simple hook to check to see if the config is populated and HTTP authentication passes.
 *
 * This is essentially a built-in .htpasswd system.
 *
 * @package SecuritySuite
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class SimpleSitePassword {
	public static function Check(){

		$pw = \ConfigHandler::Get('/security/site_password');

		if($pw == ''){
			return true;
		}

		if(isset($_SERVER['PHP_AUTH_PW'])){
			$userpw = $_SERVER['PHP_AUTH_PW'];
		}
		else{
			$userpw = null;
		}

		if($userpw != $pw){
			header('WWW-Authenticate: Basic realm="' . SITENAME . '"');
			header('HTTP/1.0 401 Unauthorized');
			echo 'Access to ' . SITENAME . ' requires a password.';
			exit;
		}

		// Else everything.
		return true;
	}
}