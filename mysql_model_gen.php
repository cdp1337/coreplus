#!/usr/bin/env php
<?php
/**
 * The purpose of this file is to archive up the core, components, and bundles.
 * and to set all the appropriate information.
 */


if(!isset($_SERVER['SHELL'])){
	die("Please run this script from the command line.");
}


/********************* Initial system defines *********************************/
require_once('core/bootstrap_predefines.php');

$predefines_time = microtime(true);



/********************** Critical file inclusions ******************************/

require_once('core/bootstrap_preincludes.php');


require_once(ROOT_PDIR . "core/libs/core/HookHandler.class.php");
HookHandler::singleton();
require_once(ROOT_PDIR . "core/libs/core/ConfigHandler.class.php");
ConfigHandler::singleton();


// Give me core settings!
// This will do the defines for the site, and provide any core variables to get started.
$core_settings = ConfigHandler::LoadConfigFile("core");


if(!DEVELOPMENT_MODE){
	die('Installation cannot proceed while site is NOT in Development mode.');
}


// Datamodel, GOGO!
require_once(ROOT_PDIR . 'core/libs/datamodel/DMI.class.php');


$backend = DMI::GetSystemDMI()->connection();
if(!$backend instanceof DMI_mysqli_backend) die("This script only works with MySQL or MySQLi based datamodels!");



CLI::LoadSettingsFile('packager');

if(!$packagername){
	$packagername = CLI::PromptUser('Please provide your name you wish to use for packaging', 'text-required');
}
if(!$packageremail){
	$packageremail = CLI::Promptuser('Please provide your email you wish to use for packaging.', 'text-required');
}




foreach($backend->_getTables() as $name){
	
	$classname = explode('_', substr($name, 4));
	
	foreach($classname as $k => $v){
		$classname[$k] = ucwords($v);
	}
	$classname = implode('', $classname);
	
	$classname .= 'Model';
	
	$schema = $backend->_describeTableSchema($name);
	$index = $backend->_describeTableIndexes($name);
	// All I care about is the defition part of it.
	$def = $schema['def'];
	
	$cols = array();
	foreach($def as $row){
		$c = array();
		$c['name'] = $row['field'];
		//$c['type'] = $backend->_getTypeFromSchema($d);
		
		if($row['type'] == "enum('0','1')" || $row['type'] == "enum('1','0')"){
			$c['type'] = 'Model::ATT_TYPE_BOOL';
		}
		elseif(strpos($row['type'], 'enum(') !== false){
			$c['type'] = 'Model::ATT_TYPE_ENUM';
			$c['options'] = 'array(' . substr($row['type'], 5, -1) . ')';
		}
		elseif(strpos($row['type'], 'int(') !== false && $c['name'] == 'updated'){
			$c['type'] = 'Model::ATT_TYPE_UPDATED';
		}
		elseif(strpos($row['type'], 'int(') !== false && $c['name'] == 'created'){
			$c['type'] = 'Model::ATT_TYPE_CREATED';
		}
		elseif(strpos($row['type'], 'int(') !== false && isset($index['PRIMARY']) && in_array($c['name'], $index['PRIMARY']['columns'])){
			$c['type'] = 'Model::ATT_TYPE_ID';
		}
		elseif(strpos($row['type'], 'int(') !== false){
			$c['type'] = 'Model::ATT_TYPE_INT';
		}
		elseif($row['type'] == 'text'){
			$c['type'] = 'Model::ATT_TYPE_TEXT';
		}
		elseif(strpos($row['type'], 'varchar(') !== false){
			$c['type'] = 'Model::ATT_TYPE_STRING';
			$c['maxlength'] = substr($row['type'], 8, -1);
		}
		else{
			die('Unsupported column type [' . $row['type'] . '] from table ' . $name . "\n");
		}
		
		// Check if this is a key.
		if(isset($index['PRIMARY']) && in_array($c['name'], $index['PRIMARY']['columns'])){
			$c['required'] = 'true';
		}
		
		// Comment?
		if($row['comment']) $c['comment'] = trim(str_replace("'", "\\'", $row['comment']));
		
		
		// Default
		if($row['default'] === NULL && $row['null'] == 'YES') $c['default'] = 'null';
		elseif($row['default']) $c['default'] = "'" . str_replace("'", "\\'", $row['default']) . "'";
		
		// Null?
		if($row['null'] == 'YES') $c['null'] = 'true';
		else $c['null'] = 'false';
		
		$cols[] = $c;
	}
	//var_dump($cols);
	
	
	$indexes = array();
	foreach($index as $i){
		if($i['name'] == 'PRIMARY'){
			$iname = 'primary';
		}
		else{
			$iname = (($i['nonunique'])? '' : 'unique:') . $i['name'];
		}
		
		$indexes[$iname] = $i['columns'];
	}
	
	// Gen the actual code!
	$code = "<?php
/**
 * Model for $classname
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author $packagername <$packageremail>
 * @date " . date('Y-m-d H:i:s') . "
 */
class $classname extends Model {
	public static \$Schema = array(";
foreach($cols as $k => $col){
	$code .= "\n\t\t'{$col['name']}' => array("
		. "\n\t\t\t'type' => {$col['type']},"
		. (isset($col['maxlength'])? "\n\t\t\t'maxlength' => {$col['maxlength']}," : '')
		. (isset($col['options'])? "\n\t\t\t'options' => {$col['options']}," : '')
		. (isset($col['default'])? "\n\t\t\t'default' => {$col['default']}," : '')
		. (isset($col['required'])? "\n\t\t\t'required' => {$col['required']}," : '')
		. (isset($col['comment'])? "\n\t\t\t'comment' => '{$col['comment']}'," : '')
		. (isset($col['null'])? "\n\t\t\t'null' => {$col['null']}," : '')
		. "\n\t\t),";
}
$code .= "\n\t);
	
	public static \$Indexes = array(";
foreach($indexes as $k => $idx){
	$code .= "\n\t\t'$k' => array('" . implode("', '", $idx) . "'),";
}
$code .= "\n\t);

\t// @todo Put your code here.

} // END class $classname extends Model
";
	
	echo "Writing $classname.class.php...\n";
	file_put_contents('_gen/' . $classname . '.class.php', $code);
	//var_dump($cols, $indexes); die();

}

