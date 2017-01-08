#!/usr/bin/env php
<?php
/**
 * Script to scan for and generate i18n ".ini" files for translation.
 *
 * @author Charlie Powell <charlie@evalagency.com>
 * @package Core
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
	'core' => [
		'description' => 'Minify Core resources.',
		'value' => false,
		'shorthand' => [],
	],
	'component' => [
		'description' => 'Minify a requested component resources.',
		'value' => true,
		'shorthand' => ['c'],
	],
	'theme' => [
		'description' => 'Minify a requested theme resources.',
		'value' => true,
		'shorthand' => ['t'],
	],
    'lang' => [
	    'description' => 'Language to generate the ini',
        'value' => true,
        'required' => false,
    ],
    'dry-run' => [
	    'description' => 'Only perform a "dry-run" test of the import, do not change any file but instead output the resulting .ini to stdout.',
        'value' => false,
    ],
]);
$arguments->usageHeader = 'This script will generate an i18n ini file for the requested component or theme.' . NL . NL .
	$argv[0] . ' --(core|component=[name]|theme=[name]) --lang=[lang]';
$arguments->processArguments();

$lang = $arguments->getArgumentValue('lang');
$component = $arguments->getArgumentValue('component');
$theme = $arguments->getArgumentValue('theme');
$core = $arguments->getArgumentValue('core');

// Shortcut helper to allow the option "-c core" to be used.
if($component == 'core'){
	$component = null;
	$core = true;
}
/*
if(!$lang){
	$arguments->printError('Please specify a language to compile');
	$arguments->printUsage();
	exit;
}
*/
/*
// Lang should be [a-z]{2,3}_[A-Z]{2}!
if(!preg_match('/^[a-z]{2,3}(_[A-Z]{2})?$/', $lang)){
	$arguments->printError('Invalid lang format, please specify the format in either en or en_US (for example)');
	$arguments->printUsage();
	exit;
}

if(strpos($lang, '_')){
	$base = substr($lang, 0, strpos($lang, '_'));
}
else{
	$base = $lang;
}
*/
if($component){
	$dir       = ROOT_PDIR . 'components/' . $component;
	$configKey = $component;
	$comp      = Core::GetComponent($component);
}
elseif($theme){
	$dir       = ROOT_PDIR . 'themes/' . $theme;
	$configKey = 'theme/' . $theme;
	$comp      = null;
}
elseif($core){
	$dir       = ROOT_PDIR . 'core/';
	$configKey = 'core';
	$comp      = Core::GetComponent('core');
}
else{
	$dir       = null;
	$configKey = null;
	$comp      = null;
}

if(!is_dir($dir)){
	$arguments->printError('Requested directory does not exist, ' . $dir);
	$arguments->printUsage();
	exit;
}

// I need to get the list of only PHP, TPL, and XML files.
exec("find " . escapeshellarg($dir) . " -type f -regex '.*\\.\\(php\\|tpl\\|xml\\)' -exec egrep -R 'STRING_|MESSAGE_|FORMAT_' {} \\;", $output);

$matches = [];
foreach($output as $line){
	preg_match_all('/([\'"]|t:)((STRING|MESSAGE|FORMAT)_[A-Za-z0-9_ -]*)/', $line, $match);
	foreach($match[2] as $m){
		$m = trim($m);
		// If the last character of this string is NOT an underscore, then add it to the list.
		// This is to skip instances where message checks are caught, such as in this exact script!
		// As above, the exec line will trigger this catch if this check is not in place, giving false positives in the results.
		if(strrpos($m, '_') + 1 != strlen($m)){
			$matches[] = $m;

			// If this match contains _N_, then also add _0_ for 0 results and _1_ for 1 result.
			if(substr_count($m, '_N_') === 1){
				$matches[] = str_replace('_N_', '_0_', $m);
				$matches[] = str_replace('_N_', '_1_', $m);
			}
		}
	}
}

// Pull all the configuration options for this component
// These get transposed to STRING_CONFIG_config_name_blah
$configs = ConfigModel::Find(['component = ' . $configKey]);
foreach($configs as $c){
	/** @var ConfigModel $c */
	$key = \Core\i18n\I18NLoader::KeyifyString($c->get('key'));
	$matches[] = 'STRING_CONFIG_' . $key;
	$matches[] = 'MESSAGE_CONFIG_' . $key;
}

// Give me permissions!
if($comp){
	foreach($comp->getPermissions() as $key => $p){
		// Every permission will be grouped by its parent name, calculate that!
		$exploded = explode('/', substr($key, 1));

		// Build the name for this permission group.
		array_pop($exploded);
		$matches[] = 'STRING_PERMISSION_' . strtoupper(implode('_', $exploded));
		
		$key = \Core\i18n\I18NLoader::KeyifyString($key);
		$matches[] = 'STRING_PERMISSION_' . $key;
		//$matches[] = 'MESSAGE_CONFIG_' . $key;
	}
}

// Retrieve the models for this component
if($comp){
	$models = array_merge($comp->getModelList(), $comp->getSupplementalModelList());
	foreach($models as $key => $file){
		$class = new ReflectionClass($key);
		if($class->isSubclassOf('Model')){
			$modelName = strtoupper($key);
			$schema = $class->getMethod('GetSchema')->invoke(null);
			$isSupplemental = false;
		}
		else{
			// This model name needs some trimming performed to chop off the dynamic prefix and "modelsupplemental" suffix.
			$modelName = strtoupper(substr($key, strpos($key, '_')+1, -17)) . 'MODEL';
			$schema = $class->getStaticPropertyValue('Schema', []);
			$isSupplemental = true;
		}

		// Schema now contains the schema for this model!
		// These should support translation strings
		foreach($schema as $column => $dat){
			if(isset($dat['formtype'])){
				$formType = $dat['formtype'];
			}
			elseif(isset($dat['form']) && isset($dat['form']['type'])){
				$formType = $dat['form']['type'];
			}
			else{
				$formType = 'text';
			}

			if(
				$dat['type'] == Model::ATT_TYPE_ALIAS
			){
				continue;
			}
			
			// Skip supplementally defined attributes, they should only be present on the source component.
			if(!$isSupplemental && isset($dat['_is_supplemental']) && $dat['_is_supplemental']){
				continue;
			}

			$matches[] = 'STRING_MODEL_' . $modelName . '_' . strtoupper($column);
			$matches[] = 'MESSAGE_MODEL_' . $modelName . '_' . strtoupper($column);
		}
	}
}

// Strip duplicates
$matches = array_unique($matches);

// If this is a component, then load Core's strings so that this script knows which strings are NOT required.
// Anything in Core is assumed to be present already, as that's the basis for every package!
if($configKey != 'core' && file_exists(ROOT_PDIR . 'core/i18n/strings.yml')){
	$spyc = new Spyc();
	$coreTrans = $spyc->loadFile(ROOT_PDIR . 'core/i18n/strings.yml');

	foreach($matches as $k => $m){
		// For each match, skim through Core's i18n file for this same string.
		// If found, remove from array.
		if(isset($coreTrans[$m])){
			unset($matches[$k]);
		}
	}
}

if(!sizeof($matches)){
	\Core\CLI\CLI::PrintError($dir . ' does not seem to contain any translation strings!');
	exit;
}

$translations = [];
$current = [];

// Load any legacy ini files from this source.
// If so, load that ini into a new array to preserve legacy data.
$legacyFiles = [];
if(is_dir($dir . '/i18n/')){
	$dh = opendir($dir . '/i18n/');
	if($dh){
		while (($file = readdir($dh)) !== false) {

			// I only want ini files here.
			if(substr($file, -4) != '.ini'){
				continue;
			}
			$legacyFiles[] = $dir . '/i18n/' . $file;
		}
		closedir($dh);
	}
}

foreach($legacyFiles as $f){
	$ini = parse_ini_file($f, true);

	foreach($ini as $lang => $dat){
		foreach($dat as $k => $s){
			if($s == ''){
				// Skip processing any keys that do not have anything set!
				continue;
			}
			
			if(!isset($current[$k])){
				$current[$k] = [];
			}

			$current[$k][$lang] = $s;
		}
	}
}


// Is there a current version of the file?
// If so, load the current settings to preserve data.
if(file_exists($dir . '/i18n/strings.yml')){
	$existing = new Spyc();
	$r = $existing->loadFile($dir . '/i18n/strings.yml');
	
	foreach($r as $k => $dat){
		if(isset($translations[$k])){
			$current[$k] = array_merge($translations[$k], $dat);
		}
		else{
			$current[$k] = $dat;
		}
	}
}


/*
 * SO NOW...
 * $current contains all the current + legacy translations in the system, (if any).
 * $matches is the list of current string keys in the system.
 * 
 * I need to merge all string keys, ($matches), with the current translations and remove any that are unused.
 */
foreach($matches as $m){
	$translations[$m] = isset($current[$m]) ?
		// Set to the current value OR
		$current[$m] :
		// An empty string for the user's default language if not set yet.
		[ \Core\i18n\I18NLoader::GetFallbackLanguage() => '' ];
}

// Sort the translations!
ksort($translations);

// Dump this file back to a flat version!
$yml = new Spyc();
$output = $yml->dump($translations, 2, 80);

if($arguments->getArgumentValue('dry-run')){
	echo $output;
}
else{
	// Write this output to the requested ini file!
	$file = \Core\Filestore\Factory::File($dir . '/i18n/strings.yml');
	$file->putContents($output);

	\Core\CLI\CLI::PrintSuccess('Updated ' . $file->getFilename() . ' successfully!');
}
