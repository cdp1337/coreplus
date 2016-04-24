<?php
/**
 * Core / ConfigHandler class
 *
 * Core configuration handling class;
 *  handles getting and setting config values from the database and XML config files.
 *
 * The class that handles all configuration getting and setting.
 * Can handle calls to XML config files and DB configuration `configs` table.
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

class ConfigHandler implements ISingleton {

	/**
	 * The main instance of the config handler.  Used as the backend of all static calls.
	 * @var null|ConfigHandler
	 */
	private static $Instance = null;

	/**
	 * The directory of the configuration options, set from the constructor
	 * @var string
	 */
	private $_directory;

	/**
	 * Cache of datamodels of the configuration options from the database.
	 * @var array
	 */
	private $_cacheFromDB = array();

	/**
	 * Cache of overrides set from other components.  These are available in memory ONLY.
	 * @var array
	 */
	private $_overrides = array();

	/**
	 * Private constructor class to prevent outside instantiation.
	 *
	 * @throws Exception
	 * @return \ConfigHandler
	 */
	private function __construct() {
		// Run through the config directory, looking for XML files.

		$this->_directory = ROOT_PDIR . "config/";

		if (!is_readable($this->_directory)) {
			throw new Exception("Could not open config directory [" . $this->_directory . "] for reading.");
		}
	}

	/**
	 * Load the configuration variables from a requested config file, located inside of the config directory.
	 *
	 * @param $config string
	 *
	 * @return boolean | array
	 */
	private function _loadConfigFile($config) {

		// Return array (if the XML provides 'return' elements).
		$return = array();

		$file = $this->_directory . $config . '.xml';

		if (!file_exists($file)) {
			trigger_error("Requested config file $config.xml not located within " . $this->_directory, E_USER_NOTICE);
			return false;
		}
		if (!is_readable($file)) {
			trigger_error("Unable to read $file, please ensure it's permissions are set correctly", E_USER_NOTICE);
			return false;
		}

		// Read in the XML data.
		$xml = new DOMDocument();
		$xml->load($file);

		// Get any 'defines' in the configuration file.
		foreach ($xml->getElementsByTagName("define") as $xmlEl) {
			$name  = $xmlEl->getAttribute("name");
			$type  = $xmlEl->getAttribute("type");
			$value = trim($xmlEl->getElementsByTagName("value")->item(0)->nodeValue);
			switch (strtolower($type)) {
				case 'int':
					$value = (int)$value;
					break;
				case 'octal':
					$value = octdec($value);
					break;
				case 'boolean':
					$value = (($value == 'true' || $value == '1' || $value == 'yes') ? true : false);
					break;
			}
			if (!defined($name))
				define($name, $value);
		} // foreach($xml->getElementsByTagName("define") as $xmlEl)
		// Get any 'returns' in the configuration file.
		foreach ($xml->getElementsByTagName("return") as $xmlEl) {
			$name  = $xmlEl->getAttribute("name");
			$type  = $xmlEl->getAttribute("type");
			$value = trim($xmlEl->getElementsByTagName("value")->item(0)->nodeValue);
			switch (strtolower($type)) {
				case 'int':
					$value = (int)$value;
					break;
				case 'octal':
					$value = octdec($value);
					break;
				case 'boolean':
					$value = (($value == 'true' || $value == '1' || $value == 'yes') ? true : false);
					break;
			}
			$return[$name] = $value;
		} // foreach($xml->getElementsByTagName("define") as $xmlEl)

		return (!count($return) ? true : $return);
	}

	private function _clearCache(){
		$this->_cacheFromDB = array();
		$this->_overrides = array();
	}

	/**
	 * Get the value for a given configuration key
	 *
	 * @param string $key
	 * @return mixed
	 */
	private function _get($key){

		// If it's a standard config, pull the value from config.
		if(isset($this->_cacheFromDB[$key])){
			// Is it already overridden?
			if(isset($this->_overrides[$key])){
				return ConfigModel::TranslateValue($this->_cacheFromDB[$key]->get('type'), $this->_overrides[$key]);
			}
			else{
				return $this->_cacheFromDB[$key]->getValue();
			}
		}
		// Not there either?  Allow the SESSION to contain config variables.  This is critical for installation.
		elseif(\Core\Session::Get('configs/' . $key) !== null){
			return \Core\Session::Get('configs/' . $key);
		}
		// Else, just return null.
		else{
			return null;
		}
	}

	private function _loadDB(){
		Core\Utilities\Logger\write_debug('Config data loading from database');
		// Clear out the cache, (if it has any)
		$this->_clearCache();

		// Get a list of config models in the system.
		// These will be the root configuration options needed for any other system.
		$fac = ConfigModel::Find();

		foreach ($fac as $config) {
			/** @var $config ConfigModel */
			$key = $config->get('key');
			$this->_cacheFromDB[$key] = $config;
			$val = $config->getValue();

			// Set the defines on any that need to be defined, (via the "mapto" attribute)
			if($config->get('mapto') && !defined($config->get('mapto'))){
				define($config->get('mapto'), $val);
			}
		}
		
		Core\Utilities\Logger\write_debug('Config data loaded from database');
	}

	/**
	 * @return ConfigHandler
	 */
	public static function Singleton() {
		if (self::$Instance === null) {
			self::$Instance = new self();
		}
		return self::$Instance;
	}

	/**
	 * @return ConfigHandler
	 */
	public static function GetInstance() {
		return self::Singleton();
	}

	/**
	 * Load the configuration variables from a requested config file, located inside of the config directory.
	 *
	 * @param $config string
	 *
	 * @return boolean | array
	 */
	public static function LoadConfigFile($config) {
		return self::Singleton()->_loadConfigFile($config);
	}

	/**
	 * Retrieve a value for a requested key.
	 *
	 * Alias of ConfigHandler::Get()
	 *
	 * @param string $key
	 *
	 * @return string|int|boolean
	 */
	public static function GetValue($key) {
		return self::Singleton()->_get($key);
	}


	/**
	 * Get the config model that is attached to the core configuration system.
	 *
	 * This is the easiest way to create new config options.
	 *
	 * If $autocreate is set to false and the key does not exist, the corresponding model will NOT be created.
	 *
	 * @param string  $key        The configuration key to get
	 * @param boolean $autocreate Whether or not to create a model if not found
	 *
	 * @return ConfigModel|null
	 */
	public static function GetConfig($key, $autocreate = true) {
		$instance = self::GetInstance();

		if(!isset($instance->_cacheFromDB[$key])){
			// Is autocreate set to false?
			if(!$autocreate) return null;

			// Otherwise, go ahead and create it.  This is used by the component system.
			$instance->_cacheFromDB[$key] = new ConfigModel($key);
		}

		return $instance->_cacheFromDB[$key];
	}

	/**
	 * Get a configuration value.
	 *
	 * @param string $key
	 *
	 * @return string|int|boolean
	 */
	public static function Get($key) {
		return self::Singleton()->_get($key);
	}

	/**
	 * Set a configuration value.
	 *
	 * This CANNOT create new configuration keys!
	 * Please use GetConfig() for that.
	 *
	 * @param string $key   The key to set
	 * @param string $value The value to set
	 * @return bool True/False on success or failure.
	 */
	public static function Set($key, $value) {
		$instance = self::GetInstance();

		if(!isset($instance->_cacheFromDB[$key])){
			return false;
		}

		/** @var $config ConfigModel */
		$config = $instance->_cacheFromDB[$key];

		// This is required because enterprise multisite mode has a different location for site configs.
		// Instead of having this outside the code, it's here for now at least.
		// This is a trade-off between standard procedure and convenience.
		if(
			$config->get('overrideable') == 1 &&
			Core::IsComponentAvailable('multisite') &&
			MultiSiteHelper::GetCurrentSiteID()
		){
			$siteconfig = MultiSiteConfigModel::Construct($key, MultiSiteHelper::GetCurrentSiteID());
			$siteconfig->set('value', $value);
			$siteconfig->save();
			$instance->_overrides[$key] = $value;
		}
		else{
			$config->setValue($value);
			$config->save();
		}

		return true;
	}

	/**
	 * Find config options based on a given string.
	 *
	 * @param string $keymatch
	 *
	 * @return array
	 */
	public static function FindConfigs($keymatch){

		$return = [];

		foreach(self::Singleton()->_cacheFromDB as $k => $config){
			/** @var ConfigModel $config */
			if(strpos($k, $keymatch) !== false){
				$return[$k] = $config;
			}
		}
/*
		foreach(self::Singleton()->_overrides as $k => $v){
			if(strpos($k, $keymatch) !== false){
				$return[$k] = $v;
			}
		}

		if(isset($_SESSION['configs'])){
			foreach($_SESSION['configs'] as $k => $v){
				if(strpos($k, $keymatch) !== false){
					$return[$k] = $v;
				}
			}
		}
*/

		return $return;
	}

	/**
	 * Set a configuration override value.  This is NOT saved in the database or anything, simply available in memory.
	 *
	 * @param $key
	 * @param $value
	 */
	public static function SetOverride($key, $value){
		self::Singleton()->_overrides[$key] = $value;
	}

	/**
	 * See if a given key is overridden via non-config means, such as enterprise options or what not.
	 *
	 * @param $key
	 * @return bool
	 */
	public static function IsOverridden($key){
		$s = self::Singleton();

		// If the override itself isn't present...
		if(!isset($s->_overrides[$key])) return false;

		// If it's the same...
		if($s->_overrides[$key] == $s->_cacheFromDB[$key]) return false;

		// otherwise
		return true;
	}

	/**
	 * Add a config model to the system cache.
	 *
	 * @param ConfigModel $config
	 */
	public static function CacheConfig(ConfigModel $config) {
		$instance = self::GetInstance();

		$instance->_cacheFromDB[$config->get('key')] = $config;
	}


	public static function _DBReadyHook() {
		// This may be called before the componenthandler is ready.

		/** @var $singleton Confighandler */
		$singleton = self::Singleton();

		$singleton->_loadDB();
	}

	public static function var_dump_cache() {
		var_dump(ConfigHandler::$cacheFromDB);
	}

}