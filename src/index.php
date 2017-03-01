<?php
/*
<!DOCTYPE html>
<html>
	<head>
		<title>Please install PHP</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<style>
			body {
				background-color: #FFDFDF;
				font-size: 120%;
			}
			body p {
				text-align: center;
				min-width: 400px;
				max-width: 75%;
				margin: 0 auto;
				padding: 2em 0;
				border: 1px solid #333;
				background-color: white;
			}
		</style>
	</head>
	<body>
		<p>
			If you can see this, then it means that you need to install PHP.<br/><br/>
			Please read <a href="https://portal.eval.bz/tech-guides/install-php-on-linux" target="_blank">about installing PHP</a>
			on Linux systems.<br/><br/>
			Commercial support is available, just reach out to
			<a href="https://eval.agency/contact-us" target="_blank">eVAL Agency for getting official support.</a>
		</p>
		<!--
			If you can see this, then it means you may be a developer ;)

			If you think you have what it takes to keep pace with security
			specialists, then checkout Core Plus's main sponsor's job postings.
			
			https://eval.agency/jobs

			They're also open to internship opportunities.
			
			https://git.eval.bz/coreplus/CorePlus is the official git repo for this framework
			and https://rm.eval.bz/projects/coreplus is the official bug tracker.
		-->
	</body>
</html>
<!--
*/

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

// When working on the core, it's best to switch this back to core/bootstrap.php!
// Set this to true to skip checking for the compiled version.
$skipcompiled = false;

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

/*
-->
*/