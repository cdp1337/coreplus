<?php
/**
 * // enter a good description here
 * 
 * @package Core
 * @since 2011.06
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl.html>
 * This system is licensed under the GNU LGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/lgpl-3.0.html>, 
 * and please contribute back to the community :)
 */

/**
 * This object will cover the various xml tasks for installing, upgrading and 
 *	removing components and the core system.
 */
class InstallTask{
	
	/**
	 * The actual processing function, needs the parent node and the relative 
	 *	directory so I know where to run the scripts from.
	 * 
	 * This function will run through any node inside the <install>, <upgrade> or <uninstall> tasks
	 * and try to execute the assigned method via sending the tag name to the event handler.
	 * If a method is attached to that tag via a hook, the hook handler will call it.
	 */
	public static function ParseNode(DomElement $node, $relativeDir){
		foreach($node->getElementsByTagName('*') as $c){
			$result = HookHandler::DispatchHook('/install_task/' . $c->tagName, $c, $relativeDir);
			if(!$result) return false;
		}
		
		// Either no calls made, or all calls were successful.
		return true;
	}
	
	public static function _ParseSetConfig($node, $relativeDir){
		//$node = $args[0];
		//$relativeDir = $args[1];
		$set = $node->getAttribute('set');
		$key = $node->getAttribute('key');
		$value = $node->nodeValue;
		
		DB::Execute("UPDATE `".DB_PREFIX."configs` SET `value` = ? WHERE `config_set` = ? AND `key` = ? LIMIT 1", array($value, $set, $key));
	}
	
	public static function _ParseAddConfig($node, $relativeDir){
		//$node = $args[0];
		//$relativeDir = $args[1];
		
		// Just install the config manually.
		$set = $node->getAttribute('set');
		$key = $node->getAttribute('key');
		$type = @$node->getAttribute('type');
		$valueType = @$node->getAttribute('valuetype');
		$default = @$node->getAttribute('default');
		$value = @$node->getAttribute('value');
		$options = @$node->getAttribute('options');
		$description = @$node->getAttribute('description');
		
		// Defaults
		if(!$type) $type = 'setting';
		if(!$valueType) $valueType = 'string';
		if(!$default) $default = $value;
		if(!$value) $value = $default;
		//if(!$options) $options = null;
		if(!$description) $description = "";
		
		$options = null;
		
		// If the type is enum... look for options.
		if($valueType == 'enum'){
			$options = '';
			foreach($node->getElementsByTagName('option') as $opt){
				$value = @$opt->getAttribute('value');
				$title = $opt->nodeValue;
				if(!$value) $value = $title;
				// Neither can have a colon or semicolon.
				$title = str_replace(array(':', ';'), array('/:', '/;'), $title);
				$value = str_replace(array(':', ';'), array('/:', '/;'), $value);
				$options .= (($options == '')? '' : ";\n") . (($title == $value)? '' : $value . ':') . $title;
			}
		}
		
		$pkeys = array(
			'config_set' => $set,
			'key' => $key
		);
		$keys = array(
			'type' => $type,
			'value_type' => $valueType,
			'default_value' => $default,
			'value' => $value,
			'options' => $options,
			'description' => $description
		);
		
		$q = DB::CreateSQLFromHash(DB_PREFIX . 'configs', 'auto', $keys, $pkeys);
		DB::Execute($q);
		
		// We can make this available immediately... why not?
		if($type == 'define') define($key, $value);
	}
	
	public static function _ParseAddResourceDir($node, $relativeDir){
		//$node = $args[0];
		//$relativeDir = $args[1];
		
		$dir = $node->getAttribute('dir');
		
		// If it already exists, awesome...
		if(is_dir(ROOT_PDIR . '/resources/' . $dir)) return true;
		
		// And create the directory in the resources directory.
		return mkdir(ROOT_PDIR . '/resources/' . $dir, 0777, true);
	}
	
	public static function _ParseSql($node, $relativeDir){
		//$node = $args[0];
		//$relativeDir = $args[1];
		
		$file = @$node->getAttribute('file');
		if($file){
			$file = $relativeDir . $file;
			$sql = file_get_contents($file);
		}
		else{
			$sql = $node->nodeValue;
		}
		if(!$sql) return false;
		
		// Allow the use of an attribute "failonerror".  Self explainatory, defaults to true.
		$foe = @$node->getAttribute('failonerror');
		switch($foe){
			case null:
			case 'true':
			case 'yes':
			case '1':
				$foe = true;
				break;
			default:
				$foe = false;
				break;
		}
		
		// No replace the prefix, (as another site may use a different one).
		$prefix = $node->getAttribute('prefix');
		$sql = str_replace($prefix, DB_PREFIX, $sql);
		
		// Replace any windows or mac style newlines with unix-style.
		$sql = str_replace(array('\r\n', '\r'), '\n', $sql);
		// Try to break apart the sql, (as there may be multiple queries in here).
		$sqls = preg_split('/;[ ]*\n/', $sql);
		
		foreach($sqls as $sqlline){
			if(trim($sqlline) == '') continue;
			
			if($foe){
				DB::Execute($sqlline);
			}
			else{
				$saveErrHandlers = DB::GetConnection()->IgnoreErrors();
				DB::Execute($sqlline);
				DB::GetConnection()->IgnoreErrors($saveErrHandlers);
			}
		}
		
		return true;
	}
}

// Add the system install tasks.
HookHandler::RegisterNewHook('/install_task/sql');
HookHandler::AttachToHook('/install_task/sql', 'InstallTask::_ParseSql');
HookHandler::RegisterNewHook('/install_task/addconfig');
HookHandler::AttachToHook('/install_task/addconfig', 'InstallTask::_ParseAddConfig');
HookHandler::RegisterNewHook('/install_task/setconfig');
HookHandler::AttachToHook('/install_task/setconfig', 'InstallTask::_ParseSetConfig');
HookHandler::RegisterNewHook('/install_task/addresourcedir');
HookHandler::AttachToHook('/install_task/addresourcedir', 'InstallTask::_ParseAddResourceDir');