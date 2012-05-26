#!/usr/bin/env php
<?php
/**
 * Build some quick scaffolding for a component, including component.xml file and the appropriate directories.
 * 
 * @package Core
 * @since 2011.11
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl.html>
 * This system is licensed under the GNU LGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/lgpl-3.0.html>, 
 * and please contribute back to the community :)
 */


if(!isset($_SERVER['SHELL'])){
	die("Please run this script from the command line.");
}


// Inlude the core bootstrap, this will get the system functional.
require_once('../core/bootstrap.php');

$component = CLI::PromptUser('Please enter the name of the component to create scaffolding for', 'text');

// Lowercase is used in the directory name.
$name = strtolower($component);

// @TODO Yeah... finish the scaffolding system sometime....

/*
 * Prompt the user for any controller to create, and for each controller ask for any method in the controller.
 * Prompt the user for any models to create.
 * 
 * Create the directories {controllers,models,templates/pages}
 * populate the appropriate files with the generated code
 * generate a basic component.xml. 
 */