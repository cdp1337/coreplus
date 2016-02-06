<?php
/**
 * Index file for the entire system.
 *
 * This file receives all requests for any dynamic
 * script in the application and starts the bootstrap process.
 *
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
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

/**
 * Include the system bootstrap.
 * This basically does everything.....
 */

// When working on the core, it's best to switch this back to core/bootstrap.php!
// Set this to true to skip checking for the compiled version.
$skipcompiled = true;

try{
	if(!$skipcompiled && file_exists('core/bootstrap.compiled.php')) require_once('core/bootstrap.compiled.php');
	else require_once('core/bootstrap.php');

	$request   = PageRequest::GetSystemRequest();
	$request->execute();
	$request->render();	
}
catch(Exception $e){
	if(function_exists('\\Core\\ErrorManagement\\exception_handler')){
		\Core\ErrorManagement\exception_handler($e, true);
	}
}
