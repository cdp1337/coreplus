<?php

/**
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 * 
 * Copyright (C) 2009  Charlie Powell <powellc@powelltechs.com>
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
 * Core / ConfigHandler class
 * 
 * Core configuration handling class; 
 *  handles getting and setting config values from the database and XML config files.
 * 
 * @author powellc <powellc@powelltechs.com>
 * @package CAE Core
 * @version 1.0.0-dev
 */

/**
 * The class that handles all configuration getting and setting.
 * Can handle calls to XML config files and DB configuration `configs` table.
 * 
 * @package CAE Core
 */
class ConfigHandler implements ISingleton {

	private static $instance = null;
	public static $directory;
	/**
	 * Cache of {configset => (key/value)} pairs so that I do not have to continuesly access the database.
	 * @var array
	 */
	private static $cacheFromDB = array();

	/**
	 * Private constructor class to prevent outside instantiation.
	 *
	 * @return void
	 */
	private function __construct() {
		// Run through the config directory, looking for XML files.

		ConfigHandler::$directory = ROOT_PDIR . "config/";

		if (!is_readable(ConfigHandler::$directory)) {
			throw new Exception("Could not open config directory [ConfigHandler::$directory] for reading.");
		}
	}

	public static function Singleton() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
			HookHandler::AttachToHook('db_ready', 'ConfigHandler::_DBReadyHook');
		}
		return self::$instance;
	}

	public static function getInstance() {
		return self::singleton();
	}

	/**
	 * Load the configuration variables from a requested config file, located inside of the config directory.
	 *
	 * @param $config string
	 * @return boolean | array
	 */
	public static function LoadConfigFile($config) {

		// Return array (if the XML provides 'return' elements).
		$return = array();
		
		$file = ConfigHandler::$directory . $config . '.xml';
		
		if(!file_exists($file)){
			trigger_error("Requested config file $config.xml not located within " . ConfigHandler::$directory, E_USER_NOTICE);
			return false;
		}
		if(!is_readable($file)){
			trigger_error("Unable to read $file, please ensure it's permissions are set correctly", E_USER_NOTICE);
			return false;
		}

		// Read in the XML data.
		$xml = new DOMDocument();
		$xml->load($file);

		// Get any 'defines' in the configuration file.
		foreach ($xml->getElementsByTagName("define") as $xmlEl) {
			$name = $xmlEl->getAttribute("name");
			$type = $xmlEl->getAttribute("type");
			$value = $xmlEl->getElementsByTagName("value")->item(0)->nodeValue;
			switch (strtolower($type)) {
				case 'int': $value = (int) $value;
					break;
				case 'boolean': $value = (($value == 'true' || $value == '1' || $value == 'yes') ? true : false);
					break;
			}
			if (!defined($name))
				define($name, $value);
		} // foreach($xml->getElementsByTagName("define") as $xmlEl)
		// Get any 'returns' in the configuration file.
		foreach ($xml->getElementsByTagName("return") as $xmlEl) {
			$name = $xmlEl->getAttribute("name");
			$type = $xmlEl->getAttribute("type");
			$value = $xmlEl->getElementsByTagName("value")->item(0)->nodeValue;
			switch (strtolower($type)) {
				case 'int': $value = (int) $value;
					break;
				case 'boolean': $value = (($value == 'true' || $value == '1' || $value == 'yes') ? true : false);
					break;
			}
			$return[$name] = $value;
		} // foreach($xml->getElementsByTagName("define") as $xmlEl)

		return (!count($return) ? true : $return);
	}

	/**
	 * Retrieve a value for a requested configSet and key.
	 *
	 * <b>**Note, currently ONLY supports DB**</b>
	 *
	 * @param $configSet string
	 * @param $key string
	 * @return string | int | boolean
	 */
	public static function GetValue($key) {
		return (isset(ConfigHandler::$cacheFromDB[$key])) ? ConfigHandler::$cacheFromDB[$key] : null;
	}

	public static function SetValue($key, $value) {
        ConfigHandler::$cacheFromDB[$key] = $value;
		$rs = DB::Execute("UPDATE `" . DB_PREFIX . "config` SET `value` = ? WHERE `key` = ? LIMIT 1", array($value, $key));
	}

	/**
	 * Hook listener for when the database is ready.
	 * Query the database for all configuration elements that may be hiding in there.
	 * Assemble them into a cache of variables internally to prevent having to make repeated DB calls.
	 *
	 * @param $hookName
	 * @param $args
	 * @return unknown_type
	 */
	public static function _DBReadyHook($hookName, $args) {
		// No core application, no config's in the database...
		//if(!Core::IsInstalled()) return;
		// Any defines that may be in the dabase.
		/*
		$rs = DB::Execute("SELECT `key`, `value`, `type` FROM " . DB_PREFIX . "config WHERE `type` = 'define'");

		// No configs table present?  Maybe the data is not available.
		if(!$rs){
			header('Location: install.php');
			die('If your browser does not refresh, please <a href="install.php">Click Here</a>');
		}
		foreach ($rs as $row) {
			switch ($row['value_type']) {
				case 'int': $row['value'] = (int) $row['value'];
					break;
				case 'boolean': $row['value'] = ($row['value'] == '1' || $row['value'] == 'true') ? true : false;
					break;
				// Default is not needed, already comes through as a string.
			}
			define($row['key'], $row['value']);
		}
		*/

		// Any config strings that may be set, (cache them to speed up later requests.)
		$rs = DB::Execute("SELECT `key`, `value`, `type` FROM " . DB_PREFIX . "config");
		if(!$rs) return false;
		foreach ($rs as $row) {
			switch ($row['type']) {
				case 'int': $row['value'] = (int) $row['value'];
					break;
				case 'boolean': $row['value'] = ($row['value'] == '1' || $row['value'] == 'true') ? true : false;
					break;
				// Default is not needed, already comes through as a string.
			}
			
			ConfigHandler::$cacheFromDB[$row['key']] = $row['value'];
		}
	}

	public static function var_dump_cache() {
		var_dump(ConfigHandler::$cacheFromDB);
	}

}
