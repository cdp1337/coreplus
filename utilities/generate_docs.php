#!/usr/bin/env php
<?php
/**
 * Script to generate phpdoc documentation for the requested component or core.
 */


if(!isset($_SERVER['SHELL'])){
	die("Please run this script from the command line.");
}

// This is required to establish the root path of the system, (since it's always one directory up from "here"
define('ROOT_PDIR', realpath(dirname(__DIR__) . '/src/') . '/');
define('ROOT_WDIR', '/');
define('BASE_DIR', realpath(dirname(__DIR__)) . '/');

// Include the core bootstrap, this will get the system functional.
require_once(ROOT_PDIR . 'core/bootstrap.php');

require_once(ROOT_PDIR . 'core/libs/core/cli/Arguments.php');
require_once(ROOT_PDIR . 'core/libs/core/cli/Argument.php');

$arguments = new \Core\CLI\Arguments([
	'component' => [
		'description' => 'Generate documentation for a given component (or core)',
		'value' => true,
		'required' => true,
		'shorthand' => ['c'],
	],
]);
$arguments->usageHeader = 'This script will generate PHPDoc documentation based on the source code of a given component or core.' . NL . NL .
	$argv[0] . ' --component=[name]';
$arguments->processArguments();

$component = $arguments->getArgumentValue('component');

if($component == 'core'){
	$ignores = [
		'bootstrap.compiled.php',
		'libs/smarty/*',
		'libs/phpmailer/*',
		'libs/aws-sdk/*',
	];
	$base = ROOT_PDIR . 'core/';
	$out = BASE_DIR . 'docs/phpdoc';
	$build = BASE_DIR . 'build/phpdoc';
}
elseif($component == ''){
	\Core\CLI\CLI::PrintError('Please set -c with the component to generate documentation for.');
	$arguments->printUsage();
	die();
}
else{
	$ignores = [];
	$base = ROOT_PDIR . 'components/' . $component . '/';
	$out = ROOT_PDIR . 'components/' . $component . '/dev/docs/phpdoc';
	$build = BASE_DIR . 'build/phpdoc-' . $component;
}


$outdir = \Core\Filestore\Factory::Directory($out);
// Remove the contents to prevent legacy files from being included.
$outdir->delete();
$outdir->mkdir();

$dir = \Core\Filestore\Factory::Directory($build);
$dir->mkdir();


if(sizeof($ignores)){
	$_i = [];
	foreach($ignores as $i){
		$_i[] = $base . $i;
	}
	$ignore = ' -i ' . escapeshellarg(implode(',', $_i));
}
else{
	$ignore = '';
}
// Run phpdocumentor on the requested directory
system(
	'/opt/php/phpDocumentor.phar' .
	' -d ' . escapeshellarg($base) .
	' -t ' . escapeshellarg($build) .
	$ignore .
	' --parseprivate' .
	' --template xml'
);

// Then run the markdown compiler on the output structure.xml file.
system(
	BASE_DIR . 'vendor/phpdoc-md-master/bin/phpdocmd' .
	' ' . escapeshellarg($build . '/structure.xml') .
	' ' . escapeshellarg($out) .
	' --index index.md --lowercase 1'
);
