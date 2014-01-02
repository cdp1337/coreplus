<?php
/**
 * Index file for the entire system.
 *
 * This file receives all requests for any dynamic
 * script in the application and starts the bootstrap process.
 *
 * @package Core
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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
$skipcompiled = false;

if(!$skipcompiled && file_exists('core/bootstrap.compiled.php')) require_once('core/bootstrap.compiled.php');
else require_once('core/bootstrap.php');

// Anything that needs to fire off *before* the page is rendered.
// This includes widgets, script addons, and anything else that needs a CurrentPage.
HookHandler::DispatchHook('/core/page/prerender');


$request   = PageRequest::GetSystemRequest();
$pagedat   = $request->splitParts();
$component = Core::GetComponentByController($pagedat['controller']);

//////////////////////////////////////////////////////////////////////////////
///  In this block of logic, either the page is executed and a view returned,
///  or a view is generated with an error.
//////////////////////////////////////////////////////////////////////////////
if (!$component) {
	// Not found
	$view        = $request->getView();
	$view->error = View::ERROR_NOTFOUND;
}
elseif (is_a($component, 'Component')) {
	// It's a 1.0 style component...
	CurrentPage::Render();
	die();
}
elseif (is_a($component, 'Component_2_1')) {
	$view = $request->execute();
}
else {
	$view        = new View();
	$view->error = View::ERROR_NOTFOUND;
}

// There is a valid view one way or another now, (or the legacy script kicked in already).

// Dispatch the hooks here if it's a 404 or 403.
if ($view->error == View::ERROR_ACCESSDENIED || $view->error == View::ERROR_NOTFOUND) {
	// Let other things chew through it... (optionally)
	HookHandler::DispatchHook('/core/page/error-' . $view->error, $view);
}

//var_dump($view);
try {
	$view->render();
}
// If something happens in the rendering of the template... consider it a server error.
catch (Exception $e) {
	$view->error   = View::ERROR_SERVERERROR;
	$view->baseurl = '/Error/Error/500';
	$view->setParameters(array());
	$view->templatename   = '/pages/error/error500.tpl';
	$view->mastertemplate = ConfigHandler::Get('/theme/default_template');
	$view->assignVariable('exception', $e);

	$view->render();
}

// Just before the page stops execution...
HookHandler::DispatchHook('/core/page/postrender');
