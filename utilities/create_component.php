#!/usr/bin/env php
<?php
/**
 * Create a component from some scafolding templates.
 */

if(!isset($_SERVER['SHELL'])){
	die("Please run this script from the command line.");
}

define('ROOT_PDIR', realpath(dirname(__DIR__) . '/src/') . '/');
define('BASE_DIR', realpath(dirname(__DIR__)) . '/');

// Include the core bootstrap, this will get the system functional.
require_once(ROOT_PDIR . 'core/bootstrap.php');

CLI::RequireEditor();


// I need a few variables first about the user...
$packagername = '';
$packageremail = '';

CLI::LoadSettingsFile('packager');

if(!$packagername){
	$packagername = CLI::PromptUser('Please provide your name you wish to use for packaging', 'text-required');
}
if(!$packageremail){
	$packageremail = CLI::Promptuser('Please provide your email you wish to use for packaging.', 'text-required');
}

CLI::SaveSettingsFile('packager', array('packagername', 'packageremail'));





if($argc > 1){
	$arguments = $argv;
	// Drop the first, that is the filename.
	array_shift($arguments);

	// I'm using a for here instead of a foreach so I can increment $i artificially if an argument is two part,
	// ie: --option value_for_option --option2 value_for_option2
	for($i = 0; $i < sizeof($arguments); $i++){

		// The next argument is the component name.
		$arg = $arguments[$i];

		/** @var string $component The name of the component as supplied by the user */
		$component = $arg;
	}
}
else{
	/** @var string $component The name of the component as supplied by the user */
	$component = CLI::PromptUser('Enter the name of the component to create', 'text-required');
}



// Sanitize this name to a point for everything
$component = trim($component);
$component = preg_replace('/[^a-zA-Z\- ]/', '', $component);

// The proper name can have capitals and spaces in it.
/** @var string $componentname The proper human-readable Component Name, spaces and all. */
$componentname = $component;

// And the key name must be a little more strict.
$component = str_replace(' ', '-', $component);
// The directory will be all lowercase.
$component = strtolower($component);
$dirname = ROOT_PDIR . 'components/' . $component . '/';

if(is_dir($dirname)){
	// See if there's a component.xml file herein.
	// If there is, do not create the scaffolding!
	if(file_exists($dirname . 'component.xml')){
		die($component . ' already exists, corwardly refusing to overwrite with scaffolding' . "\n");
	}

	$exists_prior = true;
}
else{
	$exists_prior = false;
}

$directories = array(
	'assets/css',
	'assets/js',
	'classes',
	'controllers',
	'models',
	'templates/pages',
	'templates/widgets',
	'widgets',
);
$models = array();
$controllers = array();


$modellines = CLI::PromptUser('Enter the models to create on this component initially, separated by a newline', 'textarea');
foreach(explode("\n", $modellines) as $line){
	$line = trim($line);

	// Skip blank lines
	if(!$line) continue;

	// See if this line ends with "Model".. if it does drop that.
	if(strtolower(substr($line, -5)) == 'model'){
		$line = substr($line, 0, -5);
	}

	$models[] = $line;
	$controllers[] = $line;
}


$controllerlines = CLI::PromptUser('Enter the controllers to create on this component initially, separated by a newline.', 'textarea', implode("\n", $controllers));
$controllers = array();
foreach(explode("\n", $controllerlines) as $line){
	$line = trim($line);

	// Skip blank lines
	if(!$line) continue;

	// See if this line ends with "Model".. if it does drop that.
	if(strtolower(substr($line, -10)) == 'controller'){
		$line = substr($line, 0, -10);
	}

	$controllers[] = $line;
}



/// The scaffolding code to insert.
$modelscaffolding = <<<EOF
<?php
/**
 * Class file for the model %CLASS%
 *
 * @package %COMPONENT%
 * @author %AUTHORNAME% <%AUTHOREMAIL%>
 */
class %CLASS% extends Model {
	/**
	 * Schema definition for %CLASS%
	 * @todo Fill this in with your model structure
	 *
	 * @static
	 * @var array
	 */
	public static \$Schema = array(
		'id' => [
			'type' => Model::ATT_TYPE_UUID,
		],
	);

	/**
	 * Index definition for %CLASS%
	 * @todo Fill this in with your model indexes
	 *
	 * @static
	 * @var array
	 */
	public static \$Indexes = array(
		'primary' => array('id'),
	);
}
EOF;

$controllerscaffolding = <<<EOF
<?php
/**
 * Class file for the controller %CLASS%
 *
 * @package %COMPONENT%
 * @author %AUTHORNAME% <%AUTHOREMAIL%>
 */
class %CLASS% extends Controller_2_1 {
	// @todo Add your views here
	// Each controller can have many views, each defined by a different method.
	// These methods should be regular public functions that DO NOT begin with an underscore (_).
	// Any method that begins with an underscore or is static will be assumed as an internal method
	// and cannot be called externally via a url.
}
EOF;


if(CLI::PromptUser('Create standard directory structure?', 'bool', true)){
// Start making the directories and writing everything.
	foreach($directories as $d){
		$dir = new \Core\Filestore\Backends\DirectoryLocal($dirname . $d);
		$dir->mkdir();
	}
}



// I need to create a basic xml file for the component to use initially.
$implementation = new DOMImplementation();

$dtd = $implementation->createDocumentType('component', 'SYSTEM', 'http://corepl.us/api/2_4/component.dtd');
$xml = $implementation->createDocument('', '', $dtd);
$xml->encoding = 'UTF-8';
$root = $xml->createElement('component');
$root->setAttribute('name', $componentname);
$root->setAttribute('version', '1.0.0');
$xml->appendChild($root);
$xml->save($dirname . 'component.xml');



// Now write the various files
$allfiles = array();

foreach($models as $class){
	/** @var $fname String relative filename */
	$fname = 'models/' . $class . 'Model.php';
	$freplaces = array(
		'%CLASS%' => $class . 'Model',
		'%COMPONENT%' => $componentname,
		'%AUTHORNAME%' => $packagername,
		'%AUTHOREMAIL%' => $packageremail,
	);
	$fcontents = str_replace(array_keys($freplaces), array_values($freplaces), $modelscaffolding);
	file_put_contents($dirname . $fname, $fcontents);
	$md5 = md5_file($dirname . $fname);

	$allfiles[] = array(
		'file' => $fname,
		'md5' => $md5,
		'classes' => array($class . 'Model')
	);
}

foreach($controllers as $class){
	/** @var $fname String relative filename */
	$fname = 'controllers/' . $class . 'Controller.php';
	$freplaces = array(
		'%CLASS%' => $class . 'Controller',
		'%COMPONENT%' => $componentname,
		'%AUTHORNAME%' => $packagername,
		'%AUTHOREMAIL%' => $packageremail,
	);
	$fcontents = str_replace(array_keys($freplaces), array_values($freplaces), $controllerscaffolding);
	file_put_contents($dirname . $fname, $fcontents);
	$md5 = md5_file($dirname . $fname);

	$allfiles[] = array(
		'file' => $fname,
		'md5' => $md5,
		'controllers' => array($class . 'Controller')
	);

	// Don't forget to create a template directory for any pages on this controller.
	$dir = new \Core\Filestore\Backends\DirectoryLocal($dirname . 'templates/pages/' . strtolower($class));
	$dir->mkdir();
}


// Write the changelog
$now = Time::GetCurrentGMT(Time::FORMAT_RFC2822);
file_put_contents($dirname . 'CHANGELOG', "$componentname 1.0.0

	* Initial version

");
$allfiles[] = array(
	'file' => 'CHANGELOG',
	'md5' => md5_file($dirname . 'CHANGELOG')
);

// And the readme
file_put_contents($dirname . 'README.md', "# About $componentname

@todo Write something about this component.
");
$allfiles[] = array(
	'file' => 'README.md',
	'md5' => md5_file($dirname . 'README.md')
);


$componentobject = new Component_2_1($dirname . 'component.xml');
$componentobject->setAuthors(
	array(
		array('name' => $packagername, 'email' => $packageremail)
	)
);
$componentobject->setFiles($allfiles);
$componentobject->save();


echo "Created new component " . $componentname . "\n";

if($exists_prior){
	echo "If there were files in this directory previously, please run the following to scan them:" . "\n" .
		"utilities/packager.php -r -c $component" . "\n";
}