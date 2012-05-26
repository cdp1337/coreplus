<?php
/**
 * [PAGE DESCRIPTION HERE]
 *
 * @package Core Plus\Core
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
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
 * Basically, the Component Handler is the previous library handler, only completed!
 */
class ComponentHandler implements ISingleton {
	/**
	 * The instance of this object.
	 * @var ComponentHandler
	 */
	private static $instance = null;

	/**
	 * A list of every valid component on the system.
	 * @var array <<Component>>
	 */
	private $_componentCache = array();

	/**
	 * List of every installed class and its location on the system.
	 * @var array <<String>>
	 */
	private $_classes = array();

	/**
	 * List of widgets available on the system.
	 *
	 * @var array
	 */
	private $_widgets = array();

	/**
	 * List of every installed view class and its location on the system.
	 * @var array <<String>>
	 */
	private $_viewClasses = array();


	/**
	 * List of every available jslibrary and its call..
	 * @var array <<String>>
	 */
	private $_scriptlibraries = array();


	/**
	 * Internal check variable to know if this handler has been loaded.
	 * @var boolean
	 */
	private $_loaded = false;

	/**
	 * Every component that has been loaded into the system.
	 * @var array <<Component>>
	 */
	private $_loadedComponents = array();

	private $_viewSearchDirs = array();

	/**
	 * key/value array of records in the database.
	 * Used as a lookup so the components only have to be queried once.
	 *
	 * @var array
	 */
	public $_dbcache = array();

	/**
	 * Private constructor class to prevent outside instantiation.
	 *
	 * @return void
	 */
	private function __construct() {
		// Add in the core component for the first element.
		$this->_componentCache['core'] = ComponentHandler::_Factory(ROOT_PDIR . 'core/component.xml');

		// Run through the libraries directory and look for, well... components.

		// First, build my cache of components, regardless if the component is installed or not.
		$dh = opendir(ROOT_PDIR . 'components');
		if (!$dh) return;
		while ($file = readdir($dh)) {
			// skip hidden directories.
			if ($file{0} == '.') continue;

			// skip non-directories
			if (!is_dir(ROOT_PDIR . 'components/' . $file)) continue;

			// Skip directories that do not have a readable component.xml file.
			if (!is_readable(ROOT_PDIR . 'components/' . $file . '/component.xml')) continue;

			$c = ComponentHandler::_Factory(ROOT_PDIR . 'components/' . $file . '/component.xml');

			// All further operations are case insensitive.
			// The original call to Component needs to be case sensitive because it sets the filename to pull.
			$file = strtolower($file);

			// If the component was flagged as invalid.. just skip to the next one.
			if (!$c->isValid()) {
				if (DEVELOPMENT_MODE) {
					CAEUtils::AddMessage('Component ' . $c->getName() . ' appears to be invalid.');
				}
				continue;
			}

			$this->_componentCache[$file] = $c;
			unset($c);
		}
		closedir($dh);
	}

	private function load() {
		if ($this->_loaded) return;

		// Load in all the data in the components table.
		try {
			$res            = Dataset::Init()->table('component')->select('*')->execute();
			$this->_dbcache = array();
			foreach ($res as $r) {
				$n                  = strtolower($r['name']);
				$this->_dbcache[$n] = $r;
			}
		}
		catch (Exception $e) {
			//echo '<pre>' . $e->getTraceAsString() . '</pre>';
			return;
		}

		/*
				// Add all the libraries from the LibraryHandler.
				foreach(LibraryHandler::singleton()->librariesLoaded as $l){
					$ch->_libraries[$l->getName()] = $l->getVersion();
				}

				// Add any classes that come from libraries.
				$ch->_classes = array_merge($ch->_classes, LibraryHandler::singleton()->getClassList());
				 */

		// Load every component first.
		foreach ($this->_componentCache as $n => $c) {
			$c->load();

			// If the component is not in the initial dbcache, it must not be installed.
			// Keep it in the component cache, but do not try to load it just yet.
			if (!isset($this->_dbcache[$n])) {
				//unset($this->_componentCache[$n]);
				continue;
			}

			// Set the data from the loaded cache
			$c->_versionDB = $this->_dbcache[$n]['version'];
			$c->enabled    = ($this->_dbcache[$n]['enabled']);

			// First check before anything else is even done.... Did the user disable it?
			if (!$c->enabled) {
				//echo "Skipping " . $c->getName() . " because it is disabled<br/>";
				unset($this->_componentCache[$n]);
				continue;
			}

			//var_dump($c);
			// Doesn't contain a valid xml, just remove it.
			if (!$c->isValid()) {
				if (DEVELOPMENT_MODE) {
					echo 'Component ' . $c->getName() . ' appears to be invalid due to:<br/>' . $c->getErrors();
					//CAEUtils::AddMessage('Component ' . $c->name . ' appears to be invalid due to:<br/>' . $c->_invalidReason);
				}
				unset($this->_componentCache[$n]);
			}
		}

		// If the execution mode is CLI, ensure the CLI tools are installed!
		if (EXEC_MODE == 'CLI') {
			$cli_component = $this->getComponent('CLI');
			// CLI is bundled with the core.
			// How do you expect to use the CLI tools if they're not installed?	hmm???
			//if(!$cli_component) die("Cannot execute anything in CLI mode without the CLI component, please download that.\n");
			//if(!$cli_component->isInstalled()) $cli_component->install();
		}


		//echo "Loading...";
		// Now that I have a list of components available, copy them into a list of 
		//	components that are installed.

		$list = $this->_componentCache;

		do {
			$size = sizeof($list);
			foreach ($list as $n => $c) {

				// If it's loaded, register it and remove it from the list!
				if ($c->isInstalled() && $c->isLoadable() && $c->loadFiles()) {

					// Allow for on-the-fly package upgrading regardless of DEV mode or not.
					if ($c->needsUpdated()) {
						$c->upgrade();
					}

					$this->_registerComponent($c);
					unset($list[$n]);
					continue;
				}


				// Allow for on-the-fly package upgrading regardless of DEV mode or not.
				if ($c->isInstalled() && $c->needsUpdated() && $c->isLoadable()) {
					$c->upgrade();
					$c->loadFiles();
					$this->_registerComponent($c);
					unset($list[$n]);
					continue;
				}

				// Allow packages to be auto-installed if in DEV mode.
				// this should NEVER be enabled on production, due to the GIANT
				// security risk that it could potentially cause if someone manages
				// to get a rogue component.xml file on the filesystem. (in theory at least)
				if (!$c->isInstalled() && DEVELOPMENT_MODE && $c->isLoadable()) {
					// w00t
					$c->install();
					$c->loadFiles();
					$this->_registerComponent($c);
					unset($list[$n]);
					continue;
				}
			}
		}
		while ($size > 0 && ($size != sizeof($list)));

		// If dev mode is enabled, display a list of components installed but not loadable.
		if (DEVELOPMENT_MODE) {
			foreach ($list as $l) {
				// Ignore anything with the execmode different, those should be minor notices for debugging if anything.
				if ($l->error & Component::ERROR_WRONGEXECMODE) continue;

				$msg = 'Could not load installed component ' . $l->getName() . ' due to requirement failed.<br/>' . $l->getErrors();
				echo $msg . '<br/>';
				//Core::AddMessage($msg);
			}
		}


		$this->_loaded = true;
	}

	/**
	 * Internally used method to notify the rest of the system that a given
	 *    component has been loaded and is available.
	 *
	 * Expects all checks to be done already.
	 */
	public function _registerComponent($c) {
		$name = strtolower($c->getName());

		if ($c->hasLibrary()) {
			$this->_libraries = array_merge($this->_libraries, $c->getLibraryList());

			// Register the include paths if set.
			foreach ($c->getIncludePaths() as $path) {
				set_include_path(get_include_path() . PATH_SEPARATOR . $path);
			}

			$this->_scriptlibraries = array_merge($this->_scriptlibraries, $c->getScriptLibraryList());
		}
		if ($c->hasModule()) $this->_modules[$name] = $c->getVersionInstalled();

		$this->_classes                 = array_merge($this->_classes, $c->getClassList());
		$this->_viewClasses             = array_merge($this->_viewClasses, $c->getViewClassList());
		$this->_widgets                 = array_merge($this->_widgets, $c->getWidgetList());
		$this->_loadedComponents[$name] = $c;
	}

	/**
	 * Get the single instance of the component handler.
	 * @return ComponentHandler
	 */
	public static function Singleton() {
		if (is_null(self::$instance)) {
			//$cached = Core::Cache()->get('componenthandler');
			$cached = false;
			if ($cached) {
				self::$instance = unserialize($cached);
			}
			else {
				self::$instance = new self();
				// And cache this instance.
				//Core::Cache()->set('componenthandler', serialize(self::$instance), 10);
			}
			self::$instance->load();
		}
		return self::$instance;
	}

	/**
	 * Trigger the requested JS library to be included in the current page load.
	 */
	/*public static function LoadJSLibrary($library){
		if(!ComponentHandler::IsJSLibraryAvailable($library)) return false;
		
		// Get all the elements from the <jslibrary> and load them.
		// This depends on the page to be made available...
		$ch = ComponentHandler::Singleton();
		foreach($ch->_jslibraries[$library]->getElementsByTagName('*') as $el){
			switch($el->tagName){
				case 'include':
					// Currently the only include type supported is jslibrary...
					ComponentHandler::LoadJSLibrary($el->getAttribute('name'));
					break;
				case 'file':
					// Only filetypes supported inside jslibraries are 'js'.
					CurrentPage::AddJSInclude($el->getAttribute(
			}
		}
	}*/

	/**
	 * Alias of Singleton.
	 * @return ComponentHandler
	 */
	public static function GetInstance() {
		return self::Singleton();
	}

	/**
	 * Get the component object of a requested component.
	 *
	 * @param string $componentName
	 *
	 * @return Component
	 */
	public static function GetComponent($componentName) {
		$componentName = strtolower($componentName);
		if (isset(ComponentHandler::Singleton()->_componentCache[$componentName])) return ComponentHandler::Singleton()->_componentCache[$componentName];
		else return false;
	}

	public static function IsLibraryAvailable($name, $version = false, $operation = 'ge') {
		$ch   = ComponentHandler::Singleton();
		$name = strtolower($name);
		//var_dump($ch->_libraries[$name], version_compare(str_replace('~', '-', $ch->_libraries[$name]), $version, $operation));
		//if($name == 'DB') return true;
		//echo "Checking library name[$name] v[$version] op[$operation]<br>";
		if (!isset($ch->_libraries[$name])) {
			//echo "Library " . $name . " is not available!"; // DEBUG //
			return false;
		}
		// There's a bit of an issue with the debian-style versions... PHP considers 1.2.3~1 < 1.2.3...
		elseif ($version !== false) {
			//var_dump($ch->_libraries[$name], $operation, $version, version_compare($ch->_libraries[$name], $version, $operation));
			//var_dump(Core::VersionCompare($ch->_libraries[$name], $version, $operation));
			//return version_compare(str_replace('~', '-', $ch->_libraries[$name]), $version, $operation);

			// Core provides more accurate comparison for Debian-style versions.
			return Core::VersionCompare($ch->_libraries[$name], $version, $operation);
		}
		else return true;
	}

	public static function IsJSLibraryAvailable($name, $version = false, $operation = 'ge') {
		$ch   = ComponentHandler::Singleton();
		$name = strtolower($name);
		//if($name == 'DB') return true;
		//echo "Checking jslibrary name[$name] v[$version] op[$operation]<br>";
		if (!isset($ch->_jslibraries[$name])) return false;
		// There's a bit of an issue with the debian-style versions... PHP considers 1.2.3~1 < 1.2.3...
		elseif ($version) return version_compare(str_replace('~', '-', $ch->_jslibraries[$name]->version), $version, $operation);
		else return true;
	}

	public static function GetJSLibrary($library) {
		$library = strtolower($library);
		return ComponentHandler::Singleton()->_jslibraries[$library];
	}

	public static function LoadScriptLibrary($library) {
		return Core::LoadScriptLibrary($library);

		$library = strtolower($library);
		$obj     = ComponentHandler::Singleton();

		if (isset($obj->_scriptlibraries[$library])) {
			return call_user_func($obj->_scriptlibraries[$library]);
		}
		else {
			return false;
		}
	}

	public static function IsComponentAvailable($name, $version = false, $operation = 'ge') {
		$ch   = ComponentHandler::Singleton();
		$name = strtolower($name);
		// The DB object is specifically a library, and MUST remain as such.
		if ($name == 'DB') return ComponentHandler::IsLibraryAvailable($name, $version, $operation);

		//echo "Checking component name[$name] v[$version] op[$operation]<br>";
		if (!isset($ch->_loadedComponents[$name])) return false;
		// There's a bit of an issue with the debian-style versions... PHP considers 1.2.3~1 < 1.2.3...
		elseif ($version) return version_compare(str_replace('~', '-', $ch->_loadedComponents[$name]->getVersionInstalled()), $version, $operation);
		else return true;
	}

	public static function IsViewClassAvailable($name, $casesensitive = true) {
		if (!$casesensitive) $name = strtolower($name);
		foreach (ComponentHandler::Singleton()->_viewClasses as $c => $l) {
			if (!$casesensitive && strtolower($c) == $name) return $c;
			elseif ($c == $name) return $c;
		}
		return false;
	}

	/**
	 * Simple autoload register function to lookup a classname and resolve it.
	 *
	 * @param string $classname
	 *
	 * @return void
	 */
	public static function CheckClass($classname) {
		if (class_exists($classname)) return;

		// Make sure it's case insensitive.
		$classname = strtolower($classname);

		if (isset(ComponentHandler::Singleton()->_classes[$classname])) {
			require_once(ComponentHandler::Singleton()->_classes[$classname]);
		}
	}

	/**
	 * Just check if the class is available, do not load it.
	 *
	 * @return boolean
	 */
	public static function IsClassAvailable($classname) {
		return (isset(self::Singleton()->_classes[$classname]));
	}

	public static function GetAllComponents() {
		return ComponentHandler::Singleton()->_componentCache;
	}

	public static function GetLoadedComponents() {
		$ret = array();
		foreach (ComponentHandler::Singleton()->_loadedComponents as $c) {
			$ret[] = $c;
		}
		return $ret;
	}

	/**
	 * Get an array of every loaded class in the system.
	 * Each key is the class name (lowercase), and the value is the fully resolved path
	 *
	 * @return array
	 */
	public static function GetLoadedClasses() {
		return ComponentHandler::Singleton()->_classes;
	}

	/**
	 * Get every loaded widget in the system.
	 *
	 * @return array
	 */
	public static function GetLoadedWidgets() {
		return ComponentHandler::Singleton()->_widgets;
	}

	public static function GetLoadedViewClasses() {
		return ComponentHandler::Singleton()->_viewClasses;
	}

	/**
	 * Get all the loaded libraries and their versions.
	 *
	 * @return array
	 */
	public static function GetLoadedLibraries() {
		return ComponentHandler::Singleton()->_libraries;
	}
}

/**
 * Register a function to fire whenever a class is instantiated.    Will
 * automatically look up the class and include the appropriate file.
 *
 * @deprecated 2011.12
 *             Disabling due to the port to API version 2.1
 */
//spl_autoload_register('ComponentHandler::CheckClass');
