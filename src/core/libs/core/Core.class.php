<?php
/**
 * Core class of this entire system.
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

class Core implements ISingleton {
	/**
	 * The singleton instance of the Core object.
	 *
	 * @access private
	 * @var Core
	 */
	private static $instance;

	/**
	 * Is set to true when the components are loaded.
	 */
	private static $_LoadedComponents = false;

	/**
	 * An array of every enabled Component in the system.
	 *
	 * @var array
	 */
	private $_components = null;

	/**
	 * Array of the disabled components on the system.
	 *
	 * This is useful for the updater where the admin can enable/disable packages.
	 *
	 * @var array
	 */
	private $_componentsDisabled = array();

	/**
	 * An array of every library in the system.
	 * This is useful because some components register additional libraries.
	 *
	 * @var array
	 */
	private $_libraries = array();

	/**
	 * List of every installed class and its location on the system.
	 * @var array <<String>>
	 */
	private $_classes = array();

	/**
	 * @var array Temporary list of classes on the current component, used during upgrades.
	 */
	private $_tmpclasses = array();

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

	private $_loaded = false;


	/**
	 * The component object that contains the 'Core' definition.
	 * @var Component
	 */
	private $_componentobj;

	/**
	 * Events and the microtime it took to get there from initialization.
	 *
	 * Useful for benchmarking and performance tuning.
	 * @var array
	 */
	private $_profiletimes = array();

	/**
	 * All permissions that are registered from components.
	 *
	 * @var array
	 */
	private $_permissions = array();


	/*****     PUBLIC METHODS       *********/


	public function load() {
		return;
		if ($this->_loaded) return;

		// Get the filename for the component, MUST follow a specific naming convention.
		$XMLFilename = ROOT_PDIR . 'core/core.xml';

		// Start the load procedure.
		$this->setFilename($XMLFilename);
		$this->setRootName('core');


		/*if (!parent::load()) {
			$this->error     = $this->error | Component::ERROR_INVALID;
			$this->errstrs[] = $XMLFilename . ' parsing failed, not valid XML.';
			$this->valid     = false;
			return;
		}*/

		/*
		// Can't read the file? nothing to load...
		if(!is_readable($XMLFilename)){
			$this->valid = false;
			return;
		}
		
		// Save the DOM object so I have it in the future.
		$this->DOM = new DOMDocument();
		if(!@$this->DOM->load($XMLFilename)){
			$this->valid = false;
			return;
		}
		
		$xmlObjRoot = $this->DOM->getElementsByTagName("core")->item(0);
		
		$this->version = $xmlObjRoot->getAttribute("version");
		*/
		$this->version = $this->getRootDOM()->getAttribute("version");
		$this->_loaded = true;
	}

	/**
	 * Just a simple function to make this object compatable with the Component objects.
	 * @return boolean
	 */
	public function isLoadable() {
		return $this->_isInstalled();
	}

	public function isValid() {
		return $this->valid;
	}

	/**
	 * Another simple function to make this object compatible with the Component objects.
	 * @return unknown_type
	 */
	public function loadFiles() {
		return true;
	}

	public function hasLibrary() {
		return true;
	}

	public function hasModule() {
		return true;
	}

	public function hasJSLibrary() {
		return false;
	}

	public function getClassList() {
		return array('Core'     => ROOT_PDIR . 'core/Core.class.php',
		             'CoreView' => ROOT_PDIR . 'core/CoreView.class.php');
	}

	public function getViewClassList() {
		return array('CoreView' => ROOT_PDIR . 'core/CoreView.class.php');
	}

	public function getLibraryList() {
		return array('Core' => $this->versionDB);
	}

	public function getViewSearchDirs() {
		return array(ROOT_PDIR . 'core/view/');
	}

	public function getIncludePaths() {
		return array();
	}

	public function install() {

		if ($this->_isInstalled()) return;

		if (!class_exists('DB')) return; // I need a database present before I can install.

		InstallTask::ParseNode(
			$this->getRootDOM()->getElementsByTagName('install')->item(0),
			ROOT_PDIR . 'core/'
		);

		DB::Execute("REPLACE INTO `" . DB_PREFIX . "component` (`name`, `version`) VALUES (?, ?)", array('Core', $this->version));
		$this->versionDB = $this->version;
	}

	public function upgrade() {
		if (!$this->_isInstalled()) return false;

		if (!class_exists('DB')) return; // I need a database present before I can install.

		$canBeUpgraded = true;
		while ($canBeUpgraded) {
			// Set as false to begin with, (will be set back to true if an upgrade is ran).
			$canBeUpgraded = false;
			foreach ($this->getElements('upgrade') as $u) {
				// look for a valid upgrade path.
				if (Core::GetComponent()->getVersionInstalled() == @$u->getAttribute('from')) {
					// w00t, found one...
					$canBeUpgraded = true;

					InstallTask::ParseNode($u, ROOT_PDIR . 'core/');

					$this->versionDB = @$u->getAttribute('to');
					DB::Execute("REPLACE INTO `" . DB_PREFIX . "component` (`name`, `version`) VALUES (?, ?)", array($this->name, $this->versionDB));
				}
			}
		}
	}


	/******      PRIVATE METHODS     *******/


	private function __construct() {
		//$this->load();
		//$this->_componentobj = new Component('core');
		//$this->_componentobj->load();
		//var_dump($this->_componentobj); die();
	}

	private function _isInstalled() {
		//var_dump($this->_componentobj, $this->_componentobj->getVersion()); die();
		return ($this->_componentobj->getVersionInstalled() === false) ? false : true;
	}

	private function _needsUpdated() {
		return ($this->_componentobj->getVersionInstalled() != $this->_componentobj->getVersion());
	}

	/**
	 * Load all the components in the system, replacement for the Core.
	 * @throws CoreException
	 */
	private function _loadComponents() {
		// cannot reload components.
		if ($this->_components) return null;

		$this->_components = array();
		$this->_libraries  = array();
		$tempcomponents    = false;
		Core\Utilities\Logger\write_debug('Starting loading of component metadata');

		// If the site is in DEVELOPMENT mode, component caching would probably be a bad idea; ie: the developer probably wants
		// those component files loaded everytime.
		if(DEVELOPMENT_MODE){
			$enablecache = false;
		}
		else{
			$enablecache = true;
		}

		// Is there a cache of elements available?  This is a primary system cache that greatly increases performance,
		// since it will no longer have to run through each component.xml file to register each one.
		if($enablecache){
			Core\Utilities\Logger\write_debug('Checking core-components cache');
			// Try to load up the cached components and check them first.
			$tempcomponents = Cache::GetSystemCache()->get('core-components', (3600 * 24));

			if($tempcomponents !== false){
				// Cached components only need to be loaded.
				foreach ($tempcomponents as $c) {
					try {
						$c->load();
					}
					catch (Exception $e) {
						// Don't completely bail out here, just invalidate the cache and continue on.
						Cache::GetSystemCache()->delete('core-components');
						$tempcomponents = false;
					}
				}
			}
		}


		if(!$enablecache || $tempcomponents == false){
			\Core\Utilities\Profiler\Profiler::GetDefaultProfiler()->record('Scanning for component.xml files manually');
			Core\Utilities\Logger\write_debug('Scanning for component.xml files manually');

			// Core is first, (obviously)
			$tempcomponents['core'] = ComponentFactory::Load(ROOT_PDIR . 'core/component.xml');
			Core\Utilities\Logger\write_debug('Core component loaded');

			// First, build my cache of components, regardless if the component is installed or not.
			$dh = opendir(ROOT_PDIR . 'components');
			if (!$dh) throw new CoreException('Unable to open directory [' . ROOT_PDIR . 'components/] for reading.');

			// This will read through every directory in 'components', which is
			// where all components in the system are installed to.
			while (($file = readdir($dh)) !== false) {
				// skip hidden directories.
				if ($file{0} == '.') continue;

				// skip non-directories
				if (!is_dir(ROOT_PDIR . 'components/' . $file)) continue;

				// Skip directories that do not have a readable component.xml file.
				if (!is_readable(ROOT_PDIR . 'components/' . $file . '/component.xml')) continue;

				//Core\Utilities\Logger\write_debug(' * Loading component ' . $file);
				$c = ComponentFactory::Load(ROOT_PDIR . 'components/' . $file . '/component.xml');
				Core\Utilities\Logger\write_debug('Opened component ' . $file);

				// All further operations are case insensitive.
				// The original call to Component needs to be case sensitive because it sets the filename to pull.
				$file = strtolower($file);

				// If the component was flagged as invalid.. just skip to the next one.
				if (!$c->isValid()) {
					if (DEVELOPMENT_MODE) {
						Core::SetMessage('Component ' . $c->getName() . ' appears to be invalid.');
					}
					continue;
				}


				$tempcomponents[$file] = $c;
				unset($c);
			}
			closedir($dh);
			\Core\Utilities\Profiler\Profiler::GetDefaultProfiler()->record('Component XML files scanned');

			// Now I probably could actually load the components!

			foreach ($tempcomponents as $c) {
				try {
					// Load some of the data in the class so that it's available in the cached version.
					// This is because the component 2.1 has built-in caching for many of the XML requests.
					// by calling them once, that lookup data is cached in that component, which in turn gets
					// copied to the cache version here!
					$c->load();
					$c->getClassList();
					$c->getViewSearchDir();
					$c->getSmartyPluginDirectory();
					$c->getWidgetList();
				}
				catch (Exception $e) {
					var_dump($e);
					die();
				}
			}

			// Cache this list!
			if($enablecache){
				Core\Utilities\Logger\write_debug(' * Caching core-components for next pass');
				Cache::GetSystemCache()->set('core-components', $tempcomponents, (3600 * 24));
			}
		}

		$list = $tempcomponents;

		\Core\Utilities\Profiler\Profiler::GetDefaultProfiler()->record('Component metadata loaded, starting registration');
		Core\Utilities\Logger\write_debug(' * Component metadata loaded, starting registration');

		// The core component at a minimum needs to be loaded and registered.
//		$this->_registerComponent($list['core']);
//		$this->_components['core']->loadFiles();
//		unset($list['core']);

		// Now that I have a list of components available, copy them into a list of 
		//	components that are installed.

		do {
			$size = sizeof($list);
			foreach ($list as $n => $c) {
				/** @var $c Component_2_1 */

				// Disabled components don't get recognized.
				if($c->isInstalled() && !$c->isEnabled()){
					// But they do get sent to the disabled list!
					$this->_componentsDisabled[$n] = $c;

					unset($list[$n]);
					continue;
				}

				// Clear out the temporary class list
				$this->_tmpclasses = [];

				// If it's loaded, register it and remove it from the list!
				if ($c->isInstalled() && $c->isLoadable() && $c->loadFiles()) {

					try{
						// Allow for on-the-fly package upgrading regardless of DEV mode or not.
						if ($c->needsUpdated()) {

							// Load this component's classes in case an upgrade operation requires one.
							// This allows a component to be loaded partially without completely being loaded.
							$this->_tmpclasses = $c->getClassList();

							// Lock the site first!
							// This is because some upgrade procedures take a long time to upgrade.
							file_put_contents(TMP_DIR . 'lock.message', 'Core Plus is being upgraded, please try again in a minute. ');
							$c->upgrade();
							unlink(TMP_DIR . 'lock.message');
						}
					}
					catch(Exception $e){
						SystemLogModel::LogErrorEvent('/core/component/failedupgrade', 'Ignoring component [' . $n . '] due to an error during upgrading!', $e->getMessage());

						unlink(TMP_DIR . 'lock.message');
						//$c->disable();
						$this->_componentsDisabled[$n] = $c;
						unset($list[$n]);
						continue;
					}

					try{
						$this->_components[$n] = $c;
						$this->_registerComponent($c);
					}
					catch(Exception $e){
						SystemLogModel::LogErrorEvent('/core/component/failedregister', 'Ignoring component [' . $n . '] due to an error during registration!', $e->getMessage());

						//$c->disable();
						$this->_componentsDisabled[$n] = $c;
						unset($list[$n]);
						continue;
					}

					unset($list[$n]);
					continue;
				}


				// Allow for on-the-fly package upgrading regardless of DEV mode or not.
				// Guess this is needed for the loadFiles part...
				if ($c->isInstalled() && $c->needsUpdated() && $c->isLoadable()) {
					// Lock the site first!
					// This is because some upgrade procedures take a long time to upgrade.
					file_put_contents(TMP_DIR . 'lock.message', 'Core Plus is being upgraded, please try again in a minute. ');

					$c->upgrade();
					$c->loadFiles();
					$this->_components[$n] = $c;
					$this->_registerComponent($c);
					unlink(TMP_DIR . 'lock.message');

					unset($list[$n]);
					continue;
				}

				// Allow packages to be auto-installed if in DEV mode.
				// If DEV mode is not enabled, just install the new component, do not enable it.
				if (!$c->isInstalled() && $c->isLoadable()) {
					// Load this component's classes in case an install operation requires one.
					// This allows a component to be loaded partially without completely being loaded.
					$this->_tmpclasses = $c->getClassList();

					// w00t
					$c->install();
					// BLAH, until I fix the disabled-packages-not-viewable bug...
					$c->enable();
					$c->loadFiles();
					$this->_components[$n] = $c;
					$this->_registerComponent($c);

					/*
					if(!DEVELOPMENT_MODE){
						$c->disable();
					}
					else{
						$c->enable();
						$c->loadFiles();
						$this->_components[$n] = $c;
						$this->_registerComponent($c);
					}
					*/
					unset($list[$n]);
					continue;
				}
			}
		}
		while ($size > 0 && ($size != sizeof($list)));

		// If dev mode is enabled, display a list of components installed but not loadable.

		foreach ($list as $n => $c) {

			//$this->_components[$n] = $c;
			$this->_componentsDisabled[$n] = $c;

			// Ignore anything with the execmode different, those should be minor notices for debugging if anything.
			if ($c->error & Component::ERROR_WRONGEXECMODE) continue;

			if (DEVELOPMENT_MODE) {
				SystemLogModel::LogErrorEvent('/core/component/missingrequirement', 'Could not load installed component ' . $n . ' due to requirement failed.', $c->getErrors());
			}
		}

		// Don't forget to load the themes too!
		if(class_exists('ThemeHandler')){
			foreach(ThemeHandler::GetAllThemes() as $theme){
				/** @var $theme Theme */
				$theme->load();
			}
		}

		// Lastly, make sure that the template path cache is updated!
		\Core\Templates\Template::RequeryPaths();
	}

	/**
	 * Internally used method to notify the rest of the system that a given
	 *    component has been loaded and is available.
	 *
	 * Expects all checks to be done already.
	 */
	public function _registerComponent(Component_2_1 $c) {
		$name = str_replace(' ', '-', strtolower($c->getName()));

		if ($c->hasLibrary()) {

			$liblist = $c->getLibraryList();

			$this->_libraries = array_merge($this->_libraries, $liblist);

			// Register the include paths if set.
			foreach ($c->getIncludePaths() as $path) {
				set_include_path(get_include_path() . PATH_SEPARATOR . $path);
			}
		}

		$this->_scriptlibraries = array_merge($this->_scriptlibraries, $c->getScriptLibraryList());

		if ($c->hasModule()) $this->_modules[$name] = $c->getVersionInstalled();

		$this->_classes           = array_merge($this->_classes, $c->getClassList());
		$this->_viewClasses       = array_merge($this->_viewClasses, $c->getViewClassList());
		$this->_widgets           = array_merge($this->_widgets, $c->getWidgetList());
		$this->_components[$name] = $c;

		// Permissions were not enabled prior to 2.1, so the legacy components do not have the function.
		if($c instanceof Component_2_1){
			$this->_permissions       = array_merge($this->_permissions, $c->getPermissions());
			ksort($this->_permissions);

			// Register this component's user authdrivers, if any.
			$auths = $c->getUserAuthDrivers();
			foreach($auths as $name => $class){
				\Core\User\Helper::$AuthDrivers[$name] = $class;
			}
		}

		// Lastly, mark this component as available!
		$c->_setReady(true);
	}


	/*****      PUBLIC STATIC METHODS       *******/


	/**
	 * Simple autoload register function to lookup a classname and resolve it.
	 *
	 * This was a direct port from the Core.
	 *
	 * @throws Exception
	 *
	 * @param string $classname
	 *
	 * @return void
	 */
	public static function CheckClass($classname) {
		if (class_exists($classname)) return;

		// Make sure it's case insensitive.
		$classname = strtolower($classname);

		// The system needs to be loaded first.
		//if (!self::$_LoadedComponents) {
		//	// Ok, so load it!
		//	self::LoadComponents();
		//}

		if (isset(Core::Singleton()->_classes[$classname])) {
			if(!file_exists(Core::Singleton()->_classes[$classname])){
				// Eek, I can't open the file!
				throw new Exception('Unable to open file for class ' . $classname . ' (' . Core::Singleton()->_classes[$classname] . ')');
			}

			require_once(Core::Singleton()->_classes[$classname]);
		}
		elseif (isset(Core::Singleton()->_tmpclasses[$classname])) {
			if(!file_exists(Core::Singleton()->_tmpclasses[$classname])){
				// Eek, I can't open the file!
				throw new Exception('Unable to open file for class ' . $classname . ' (' . Core::Singleton()->_tmpclasses[$classname] . ')');
			}

			require_once(Core::Singleton()->_tmpclasses[$classname]);
		}
	}

	public static function LoadComponents() {
		$self = self::Singleton();
		$self->_loadComponents();
	}

	/**
	 * Shortcut function to get the current system database/datamodel interface.
	 *
	 * @deprecated 2011.11
	 * @return DMI_Backend
	 */
	public static function DB() {
		return \Core\DB();
		//return DMI::GetSystemDMI()->connection();
	}

	/**
	 * Shortcut function to get the current system cache interface.
	 *
	 * @deprecated 2011.11
	 * @return Cache
	 */
	public static function Cache() {
		return Cache::GetSystemCache();
	}

	/**
	 * Get the global FTP connection.
	 *
	 * Returns the FTP resource or false on failure.
	 *
	 * @deprecated 2011.11
	 * @return resource | false
	 */
	public static function FTP() {
		return \Core\FTP();
	}

	/**
	 * Get the current user model that is logged in.
	 *
	 * @deprecated 2011.11
	 * @return User
	 */
	public static function User() {
		return \Core\user();
	}

	/**
	 * Instantiate a new File object, ready for manipulation or access.
	 *
	 * @deprecated 2011.11
	 * @since 2011.07.09
	 *
	 * @param string $filename
	 *
	 * @return \Core\Filestore\File
	 */
	public static function File($filename = null) {
		return \Core\Filestore\Factory::File($filename);
	}


	/**
	 * Instantiate a new Directory object, ready for manipulation or access.
	 *
	 * @deprecated 2011.11
	 * @since 2011.07.09
	 *
	 * @param string $directory
	 *
	 * @return Directory_Backend
	 */
	public static function Directory($directory) {
		return \Core\directory($directory);
	}

	/**
	 * Translate a dimension, (or dimensions), to a "preview size"
	 * of sm, med, lg or xl.
	 *
	 * @param string $dimensions Dimensions to translate
	 * @param [optional] int $width If second parameter is sent, assume width, height.
	 *
	 * @return string
	 */
	public static function TranslateDimensionToPreviewSize($dimensions) {
		// Load in the theme sizes for reference.
		$themesizes = array(
			'sm'  => ConfigHandler::Get('/theme/filestore/preview-size-sm'),
			'med' => ConfigHandler::Get('/theme/filestore/preview-size-med'),
			'lg'  => ConfigHandler::Get('/theme/filestore/preview-size-lg'),
			'xl'  => ConfigHandler::Get('/theme/filestore/preview-size-xl'),
		);

		if (sizeof(func_get_args()) == 2) {
			// Assume $width, $height.
			$width  = (int)func_get_arg(0);
			$height = (int)func_get_arg(1);
		}
		elseif (is_numeric($dimensions)) {
			// It's a straight single number, use that for both dimensions.
			$width  = $dimensions;
			$height = $dimensions;
		}
		elseif (stripos($dimensions, 'x') !== false) {
			// It's a string joining both dimensions.
			$ds     = explode('x', strtolower($dimensions));
			$width  = trim($ds[0]);
			$height = trim($ds[1]);
		}
		else {
			// Invalid size given.
			return null;
		}

		$smaller = min($width, $height);

		if ($smaller >= $themesizes['xl']) return 'xl';
		elseif ($smaller >= $themesizes['lg']) return 'lg';
		elseif ($smaller >= $themesizes['med']) return 'med';
		else return 'sm';
	}

	/**
	 * Get all registered permissions for all loaded components.
	 *
	 * @static
	 * @return array
	 */
	public static function GetPermissions(){
		return self::Singleton()->_permissions;
	}

	/**
	 * Get the component object by its name.
	 *
	 * @param string $name Name of the requested component
	 *
	 * @return Component_2_1
	 */
	public static function GetComponent($name = 'core') {
		$s = self::Singleton();
		if(isset($s->_components[$name])) return $s->_components[$name];

		// maybe it's a disabled component.  Those should be returned too.
		if(isset($s->_componentsDisabled[$name])) return $s->_componentsDisabled[$name];

		// Not that either?
		return null;
	}

	/**
	 * Get all components
	 * @return array
	 */
	public static function GetComponents() {
		return self::Singleton()->_components;
	}

	public static function GetDisabledComponents(){
		return self::Singleton()->_componentsDisabled;
	}

	/**
	 * Lookup a component by a controller.
	 * Useful for figuring out what API version a given controller needs to be handled as.
	 *
	 * @param string $controller
	 *
	 * @return Component_2_1|null
	 */
	public static function GetComponentByController($controller) {
		$controller = strtolower($controller);

		$self = self::Singleton();
		foreach ($self->_components as $c) {
			$controllers = $c->getControllerList();
			if (isset($controllers[$controller])) return $c;
		}

		// No?
		return null;
	}

	/**
	 * Get the standard HTTP request headers for retrieving remote files.
	 *
	 * @param bool $forcurl
	 *
	 * @return array | string
	 */
	public static function GetStandardHTTPHeaders($forcurl = false, $autoclose = false) {
		$headers = array(
			'User-Agent: Core Plus ' . self::GetComponent()->getVersion() . ' (http://corepl.us)',
			'Servername: ' . SERVERNAME,
		);

		if ($autoclose) {
			$headers[] = 'Connection: close';
		}

		if ($forcurl) {
			return $headers;
		}
		else {
			return implode("\r\n", $headers);
		}
	}

	/**
	 * Get the core singleton object
	 *
	 * @return Core
	 */
	public static function Singleton() {
		if(self::$instance === null){
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get the core singleton object
	 *
	 * @return Core
	 */
	public static function GetInstance() {
		return self::Singleton();
	}

	// @todo Is this really needed?...
	public static function _LoadFromDatabase() {
		if (!self::GetComponent()->load()) {
			// Guess the core isn't installed.  If it's in development mode install it!
			if (DEVELOPMENT_MODE) {
				self::GetComponent()->install();
				die('Installed core!  <a href="' . ROOT_WDIR . '">continue</a>');
			}
			else {
				die('There was a server error, please notify the administrator of this.');
			}
		}
		return;
		/*
		// Retrieve some information from the database when it becomes available.
		$q = @DB::Execute("SELECT `version` FROM `" . DB_PREFIX . "component` WHERE `name` = 'Core'");
		if(!$q) return;
		if($q->numRows() > 0){
			Core::Singleton()->versionDB = $q->fields['version'];
		}
		else{
			Core::Singleton()->versionDB = false;
		}
		 */
	}

	/**
	 * Check if a given class is available in the system as-is.
	 *
	 * @param string $classname
	 *
	 * @return boolean
	 */
	public static function IsClassAvailable($classname) {
		// let's see if I can't speed this function up a little...
		if(self::$instance == null){
			self::Singleton();
		}

		if(isset(self::$instance->_classes[$classname])){
			return true;
		}
		else{
			return false;
		}
	}

	public static function IsLibraryAvailable($name, $version = false, $operation = 'ge') {
		$ch   = self::Singleton();
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
		$ch   = self::Singleton();
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
		return self::Singleton()->_jslibraries[$library];
	}

	public static function LoadScriptLibrary($library) {
		$library = strtolower($library);
		$obj     = self::Singleton();

		if (isset($obj->_scriptlibraries[$library])) {
			return call_user_func($obj->_scriptlibraries[$library]);
		}
		else {
			return false;
		}
	}


	public static function IsComponentAvailable($name, $version = false, $operation = 'ge') {
		$self = self::Singleton();

		$name = strtolower($name);

		//echo "Checking component name[$name] v[$version] op[$operation]<br>";
		if (!isset($self->_components[$name])){
			return false;
		}

		// Only included enabled components.
		elseif (!$self->_components[$name]->isEnabled()){
			return false;
		}

		elseif ($version){
			return Core::VersionCompare($self->_components[$name]->getVersionInstalled(), $version, $operation);
		}

		else{
			return true;
		}
	}


	public static function IsInstalled() {
		return Core::Singleton()->_isInstalled();
	}

	public static function NeedsUpdated() {
		return Core::Singleton()->_needsUpdated();
	}

	public static function GetVersion() {
		return Core::GetComponent()->getVersionInstalled();
	}

	/**
	 * Resolve an asset to a fully-resolved URL.
	 *
	 * @todo Add support for external assets.
	 *
	 * @param string $asset
	 *
	 * @return string The full url of the asset, including the http://...
	 */
	public static function ResolveAsset($asset) {
		// Allow already-resolved links to be returned verbatim.
		if (strpos($asset, '://') !== false) return $asset;

		// Since an asset is just a file, I'll use the builtin file store system.
		// (although every file coming in should be assumed to be an asset, so
		//  allow for a partial path name to come in, assuming asset/).

		if (strpos($asset, 'assets/') !== 0) $asset = 'assets/' . $asset;

		$version = ConfigHandler::Get('/core/filestore/assetversion');


		$file     = \Core\Filestore\Factory::File($asset);
		$filename = $file->getFilename();
		$ext      = $file->getExtension();

		if(ConfigHandler::Get('/core/javascript/minified')){
			// Core is set to use minified css and javascript assets, try to locate those!
			// I need to do the check based on the base $filename, because 'assets/css/reset.css' may reside in one
			// of many locations, and not all of them may have a minified version.
			if($ext == 'js'){
				// Try to load the minified version instead.
				$minified = substr($filename, 0, -3) . '.min.js';
				$minfile = \Core\Filestore\Factory::File($minified);
				if($minfile->exists()){
					// Overwrite the $file variable so it's returned instead.
					$file = $minfile;
				}
			}
			elseif($ext == 'css'){
				// Try to load the minified version instead.
				$minified = substr($filename, 0, -4) . '.min.css';
				$minfile = \Core\Filestore\Factory::File($minified);
				if($minfile->exists()){
					// Overwrite the $file variable so it's returned instead.
					$file = $minfile;
				}
			}
		}

		return $file->getURL() . ($version ? '?v=' . $version : '');
	}

	/**
	 * Resolve a url or application path to a fully-resolved URL.
	 *
	 * This can also be an already-resolved link.  If so, no action is taken
	 *  and the original URL is returned unchanged.
	 *
	 * @param string $url
	 *
	 * @return string The full url of the link, including the http://...
	 */
	public static function ResolveLink($url) {
		// Allow "#" to be verbatim without translation.
		if ($url == '#') return $url;

		// Allow links starting with ? to be read as the current page.
		if($url{0} == '?'){
			$url = REL_REQUEST_PATH . $url;
		}

		// Allow already-resolved links to be returned verbatim.
		if (strpos($url, '://') !== false) return $url;

		// Allow multisite URLs to be passed in natively.
		if(strpos($url, 'site:') === 0){
			$slashpos = strpos($url, '/');
			$site = substr($url, 5, $slashpos-5);
			$url = substr($url, $slashpos);
		}
		else{
			$site = null;
		}

		try{
			$a = PageModel::SplitBaseURL($url, $site);
		}
		catch(\Exception $e){
			// Well, this isn't a fatal error, so just warn the admin and continue on.
			\Core\ErrorManagement\exception_handler($e);
			error_log('Unable to resolve URL [' . $url . '] due to exception [' . $e->getMessage() . ']');
			return '';
		}

		// Instead of going through the overhead of a pagemodel call, SplitBaseURL provides what I need!
		return $a['fullurl'];
	}

	/**
	 * Resolve filename to ... script.
	 * Useful for converting a physical filename to an accessable URL.
	 * @deprecated
	 */
	public static function ResolveFilenameTo($filename, $base = ROOT_URL) {
		// If it starts with a '/', figure out if that's the ROOT_PDIR or ROOT_DIR.
		$file = preg_replace('/^(' . str_replace('/', '\\/', ROOT_PDIR . '|' . ROOT_URL) . ')/', '', $filename);
		// swap the requested base onto that.
		return $base . $file;
		//return preg_replace('/^' . str_replace('/', '\\/', ROOT_PDIR) . '/', $base, $filename);
	}

	/**
	 * Redirect the user to another page via sending the Location header.
	 *    Prevents any POST data from being reloaded.
	 *
	 * @deprecated 2013.06.11 Please use the namespaced versions.
	 *
	 * @param  string $page The page URL to redirect to
	 * @param  int    $code  The HTTP status code to send to the browser, MUST be 301 or 302.
	 *
	 * @throws \Exception
	 *
	 * @return bool|null False on failure, success will halt the script.
	 */
	static public function Redirect($page, $code = 302) {
		error_log('Core::Redirect is deprecated, please use \\Core\\redirect() instead.', E_USER_DEPRECATED);
		\Core\redirect($page, $code);
	}

	/**
	 * @deprecated 2013.06.11 Please use the namespaced versions.
	 */
	static public function Reload() {
		error_log('Core::Reload is deprecated, please use \\Core\\reload() instead.', E_USER_DEPRECATED);
		\Core\reload();
	}

	/**
	 * Helper function to just go back to a page before this one.
	 *
	 * @deprecated 2013.06.11 Please use the namespaced versions.
	 *
	 * @param int $depth The amount of pages back to go
	 */
	static public function GoBack($depth=1) {
		error_log('Core::GoBack is deprecated, please use \\Core\\go_back() instead.', E_USER_DEPRECATED);

		\Core\go_back($depth);
	}

	/**
	 * Get the page that was last called $depth ago.
	 *
	 * @param int $depth
	 * @return string
	 */
	public static function GetHistory($depth = 2){
		if(!isset($_SESSION['nav'])){
			//navigation isn't set, HOME PAGE!
			return ROOT_WDIR;
		}

		$s = sizeof($_SESSION['nav']);
		if($depth > $s){
			// Requested depth greater than the amount of data saved?  HOME PAGE!
			return ROOT_WDIR;
		}

		if($depth <= 0){
			return ROOT_WDIR;
		}
		//var_dump($_SESSION['nav'], $depth, $_SESSION['nav'][$s - $depth]); die();
		// I now have the total size of the array and the requested depth of it.
		// Since the depth will be a One-Index base and the array itself is Zero-Index based,
		// this will work perfectly to take the sizeof (one-index base) and subtract the requested depth to get the actual key!

		// If the array is 3 keys deep and a depth of 1 was requested (last element),
		// it'll be 3 - 1, or 2, the last key in a zero-base array!
		return $_SESSION['nav'][$s - $depth]['uri'];
	}

	/**
	 * If this is called from any page, the user is forced to redirect to the SSL version if available.
	 * @return void
	 */
	static public function RequireSSL() {
		// No ssl, nothing much to do about nothing.
		if (!ENABLE_SSL) return;

		if (!isset($_SERVER['HTTPS'])) {
			$page = ViewClass::ResolveURL($_SERVER['REQUEST_URI'], true);
			//$page = ROOT_URL_SSL . $_SERVER['REQUEST_URI'];

			header("Location:" . $page);

			// Just before the page stops execution...
			HookHandler::DispatchHook('/core/page/postrender');

			die("If your browser does not refresh, please <a href=\"{$page}\">Click Here</a>");
		}
	}

	/**
	 * Return the page the user viewed x amount of pages ago based on the navigation stack.
	 *
	 * @param string $base The base URL to lookup history for
	 *
	 * @return string
	 */
	static public function GetNavigation($base) {
		//var_dump($_SESSION); die();
		// NO nav history, guess I can't do much of anything...
		if (!isset($_SESSION['nav'])) return $base;

		if (!isset($_SESSION['nav'][$base])) return $base;

		// Else, it must have been found!
		$coreparams  = array();
		$extraparams = array();
		foreach ($_SESSION['nav'][$base]['parameters'] as $k => $v) {
			if (is_numeric($k)) $coreparams[] = $v;
			else $extraparams[] = $k . '=' . $v;
		}
		return $base .
			(sizeof($coreparams) ? '/' . implode('/', $coreparams) : '') .
			(sizeof($extraparams) ? '?' . implode('&', $extraparams) : '');
	}

	/**
	 * Record this page into the navigation history.
	 *
	 * This will hook into the "/core/page/postrender" hook.
	 *
	 */
	static public function _RecordNavigation() {
		$request = PageRequest::GetSystemRequest();
		$view = $request->getView();

		// If the page is set to be ignored, do not record it.
		if(!$view->record) return;

		// Also do not record anything other than a GET request.
		if(!$request->isGet()) return;

		// If it's an ajax or json request, don't record that either!
		if($request->isAjax()) return;
		if($request->isJSON()) return;

		// If it's an error... don't record either.
		if($view->error != View::ERROR_NOERROR) return;


		if (!isset($_SESSION['nav'])) $_SESSION['nav'] = array();

		// I can record the base URI here because it's easier to record the actual inbound string than to parse the request afterwards.
		// (it's just going to get put back into the useragent request to be re-parsed anyway)
		$rel = substr($_SERVER['REQUEST_URI'], strlen(ROOT_WDIR));
		if($rel === false) $rel = '';

		$dat = array(
			'uri' => ROOT_URL . $rel,
			'title' => $view->title,
		);

		// Skip duplicate requests
		$s = sizeof($_SESSION['nav']);
		if($s && $_SESSION['nav'][$s-1]['uri'] == $dat['uri']) return;

		// Otherwise, YAY!
		// But keep it neatly trimmed at 5 entries.
		if($s >= 5){
			array_shift($_SESSION['nav']);
			$_SESSION['nav'] = array_values($_SESSION['nav']);
		}
		$_SESSION['nav'][] = $dat;
		return;
	}

	/**
	 * Add a message to the user's stack.
	 *    It will be displayed the next time the user (or session) renders the page.
	 *
	 * @param string $messageText The message to send to the user
	 * @param string $messageType The type of message, "success", "info", or "error"
	 *
	 * @return void
	 */
	static public function SetMessage($messageText, $messageType = 'info') {

		if (trim($messageText) == '') return;

		$messageType = strtolower($messageType);

		// CLI doesn't use sessions.
		if (EXEC_MODE == 'CLI') {
			$messageText = preg_replace('/<br[^>]*>/i', "\n", $messageText);
			echo "[" . $messageType . "] - " . $messageText . "\n";
		}
		else {
			if (!isset($_SESSION['message_stack'])) $_SESSION['message_stack'] = array();

			// Look for this message in the stack.  This helps prevent duplicate messages.
			$key = md5($messageType . '-' . $messageText);

			$_SESSION['message_stack'][$key] = array(
				'mtext' => $messageText,
				'mtype' => $messageType,
			);
		}
	}

	static public function AddMessage($messageText, $messageType = 'info') {
		Core::SetMessage($messageText, $messageType);
	}

	/**
	 * Retrieve the messages and optionally clear the message stack.
	 *
	 * @param unknown_type $return_type
	 *
	 * @return unknown
	 */
	static public function GetMessages($returnSorted = FALSE, $clearStack = TRUE) {
		/*
		global $_DB;
		global $_SESS;

		$fetches = $_DB->Execute(
			"SELECT `mtext`, `mtype` FROM `" . DB_PREFIX . "messages` WHERE `sid` = '{$_SESS->sid}'"
		);

		if($fetches->fields === FALSE) return array(); //Return a blank array, there are no messages.

		foreach($fetches as $fetch){
			$return[] = $fetch;
		}
		*/
		if (!isset($_SESSION['message_stack'])) return array();

		$return = $_SESSION['message_stack'];
		if ($returnSorted) $return = Core::SortByKey($return, 'mtype');

		if ($clearStack) unset($_SESSION['message_stack']);
		return $return;
	}

	static public function SortByKey($named_recs, $order_by, $rev = false, $flags = 0) {
		// Create 1-dimensional named array with just
		// sortfield (in stead of record) values
		$named_hash = array();
		foreach ($named_recs as $key=> $fields) $named_hash["$key"] = $fields[$order_by];

		// Order 1-dimensional array,
		// maintaining key-value relations
		if ($rev) arsort($named_hash, $flags);
		else asort($named_hash, $flags);

		// Create copy of named records array
		// in order of sortarray
		$sorted_records = array();
		foreach ($named_hash as $key=> $val) $sorted_records["$key"] = $named_recs[$key];

		return $sorted_records;
	}


	/**
	 * Return a string of the keys of the given array glued together.
	 *
	 * @param $glue string
	 * @param $array array
	 *
	 * @return string
	 *
	 * @version 2008.06.05
	 * @author Charlie Powell <charlie@eval.bz>
	 */
	static public function ImplodeKey($glue, &$array) {
		$arrayKeys = array();
		foreach ($array as $key => $value) {
			$arrayKeys[] = $key;
		}
		return implode($glue, $arrayKeys);
	}


	/**
	 * Generate a random hex-deciman value of a given length.
	 *
	 * @param int     $length
	 * @param boolean $casesensitive [false] Set to true to return a case-sensitive string.
	 *                              Otherwise the resulting string will simply be all uppercase.
	 *
	 * @return string
	 */
	static public function RandomHex($length = 1, $casesensitive = false) {
		return \Core\random_hex($length, $casesensitive);
	}


	/**
	 * Utility function to translate a filesize in bytes into a human-readable version.
	 *
	 * @deprecated 2013.05.31
	 *
	 * @param int $filesize Filesize in bytes
	 * @param int $round Precision to round to
	 *
	 * @return string
	 */
	public static function FormatSize($filesize, $round = 2) {
		return \Core\Filestore\format_size($filesize, $round);
	}

	public static function GetExtensionFromString($str) {
		// File doesn't have any extension... easy enough!
		if (strpos($str, '.') === false) return '';

		return substr($str, strrpos($str, '.') + 1);
	}

	/**
	 * @deprecated 2013.05.31
	 * @return float
	 */
	public static function GetProfileTimeTotal() {
		error_log(__FUNCTION__ . ' is deprecated, please use \Core\Utilities\Profiler\Profiler::GetDefaultProfiler()->getTime() instead', E_USER_DEPRECATED);
		return \Core\Utilities\Profiler\Profiler::GetDefaultProfiler()->getTime();
	}

	/**
	 * Validate an email address.
	 * Provide email address (raw input)
	 * Returns true if the email address has the email
	 * address format and the domain exists.
	 *
	 * Copied (almost) verbatim from http://www.linuxjournal.com/article/9585?page=0,3
	 * @author Douglas Lovell @ Linux Journal
	 *
	 * @param string $email The email to validate
	 * @return boolean
	 */
	public static function CheckEmailValidity($email) {
		$atIndex = strrpos($email, "@");
		if (is_bool($atIndex) && !$atIndex) return false;

		$domain    = substr($email, $atIndex + 1);
		$local     = substr($email, 0, $atIndex);
		$localLen  = strlen($local);
		$domainLen = strlen($domain);
		if ($localLen < 1 || $localLen > 64) {
			// local part length exceeded
			return false;
		}

		if ($domainLen < 1 || $domainLen > 255) {
			// domain part length exceeded
			return false;
		}

		if ($local[0] == '.' || $local[$localLen - 1] == '.') {
			// local part starts or ends with '.'
			return false;
		}

		if (preg_match('/\\.\\./', $local)) {
			// local part has two consecutive dots
			return false;
		}
		if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
			// character not valid in domain part
			return false;
		}

		if (preg_match('/\\.\\./', $domain)) {
			// domain part has two consecutive dots
			return false;
		}

		if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local))) {
			// character not valid in local part unless local part is quoted
			if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local))) {
				return false;
			}
		}

		// Allow the admin to skip DNS checks via config.
		if (ConfigHandler::Get('/core/email/verify_with_dns') && !(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A"))) {
			// domain not found in DNS
			return false;
		}

		// All checks passed?
		return true;
	}

	/**
	 * Function that attaches the core javascript to the page.
	 *
	 * This should be called automatically from the hook /core/page/prerender.
	 */
	public static function _AttachCoreJavascript() {

		if(Core::IsComponentAvailable('User')){
			$userid   = (\Core\user()->get('id') ? \Core\user()->get('id') : 0);
			$userauth = \Core\user()->exists() ? 'true' : 'false';
		}
		else{
			$userid   = 0;
			$userauth = 'false';
		}

		$ua = \Core\UserAgent::Construct();
		$uastring = '';
		foreach($ua->asArray() as $k => $v){
			if($v === true){
				$uastring .= "\t\t\t$k: true,\n";
			}
			elseif($v === false){
				$uastring .= "\t\t\t$k: false,\n";
			}
			else{
				$uastring .= "\t\t\t$k: \"$v\",\n";
			}

		}
		$uastring .= "\t\t\tis_mobile: " . ($ua->isMobile() ? 'true' : 'false') . "\n";

		$script = '<script type="text/javascript">
	var Core = {
		Version: "' . (DEVELOPMENT_MODE ? self::GetComponent()->getVersion() : '') . '",
		ROOT_WDIR: "' . ROOT_WDIR . '",
		ROOT_URL: "' . ROOT_URL . '",
		ROOT_URL_SSL: "' . ROOT_URL_SSL . '",
		ROOT_URL_NOSSL: "' . ROOT_URL_NOSSL . '",
		SSL: ' . (SSL ? 'true' : 'false') . ',
		SSL_MODE: "' . SSL_MODE . '",
		User: {
			id: "' . $userid . '",
			authenticated: ' . $userauth . '
		},
		Browser: {
' . $uastring . '
		}
	};
</script>';

		$minified = \ConfigHandler::Get('/core/javascript/minified');
		if($minified){
			$script = str_replace(["\t", "\n"], ['', ''], $script);
		}

		\Core\view()->addScript($script, 'head');

		// And the static functions.
		\Core\view()->addScript('js/core.js', 'foot');
		//\Core\view()->addScript('js/core-foot.js', 'foot');
	}

	/**
	 * Add the Core.Strings library to the page
	 *
	 * @return bool
	 */
	public static function _AttachCoreStrings() {
		\Core\view()->addScript('js/core.strings.js');

		return true;
	}

	/**
	 * Add the Core.Ajaxlinks library to the page
	 *
	 * @return bool
	 */
	public static function _AttachAjaxLinks(){
		JQuery::IncludeJQueryUI();
		\Core\view()->addScript('js/core.ajaxlinks.js', 'foot');

		return true;
	}

	/**
	 * Add the LESS library to the page
	 *
	 * @return bool
	 */
	public static function _AttachLessJS(){
		\Core\view()->addScript('js/less-1.5.0.js', 'head');

		return true;
	}


	/**
	 * Clone of the php version_compare function, with the exception that it treats
	 * version numbers the same that Debian treats them.
	 *
	 * @param string $version1 Version to compare
	 * @param string $version2 Version to compare against
	 * @param string $operation Operation to use or null
	 *
	 * @return bool | int Boolean if $operation is provided, int if omited.
	 */
	public static function VersionCompare($version1, $version2, $operation = null) {
		// Just to make sure they're strings at least.
		if (!$version1) $version1 = 0;
		if (!$version2) $version2 = 0;

		$version1 = Core::VersionSplit($version1);
		$version2 = Core::VersionSplit($version2);

		// version1 and 2 are now standardized.
		//$keys = array('major', 'minor', 'point', 'core', 'user', 'stability');

		// @todo Support user and stability checks.

		// The standard keys I can compare pretty easily.
		$v1    = $version1['major'] . '.' . $version1['minor'] . '.' . $version1['point'];
		$v2    = $version2['major'] . '.' . $version2['minor'] . '.' . $version2['point'];
		$check = version_compare($v1, $v2);

		// If both upstream versions are identical, drop into the "user" version, (or core-specific).
		// This is used as both user and core versions because both essentially indicate the same thing;
		// that the original package maintainer of the project is *not* the one creating the core plus package.

		// If the check is the same and one or the other version doesn't care about the user string....
		// don't even run the check, they're close enough.
		// If both strings request the user string, then check it too.
		// This is done so that maintainer X can say a package requires AcmeABC version 1.2, without
		// distinguishing if maintainer Y or maintainer Z did the actual package creation.
		// HOWEVER if versions 1.2.0~core3 and 1.2.0~core5 are compared, it'll use the user version.
		if($check == 0 && $version1['user'] && $version2['user']){
			$check = version_compare($version1['user'], $version2['user']);
		}

		// Apply the same to stability
		if($check == 0 && ($version1['stability'] || $version2['stability'])){
			$check = version_compare($version1['stability'], $version2['stability']);
		}

		// Will preserve PHP's -1, 0, 1 nature.
		if ($operation === null){
			return $check;
		}
		elseif($check == -1){
			// v1 is less than v2...
			switch($operation){
				case 'lt':
				case '<':
				case 'le':
				case '<=':
					return true;
				default:
					return false;
			}
		}
		elseif($check == 0){
			// v1 is identical to v2...
			switch($operation){
				case 'le':
				case '<=':
				case 'eq':
				case '=':
				case '==':
				case 'ge':
				case '>=':
					return true;
				default:
					return false;
			}
		}
		else{
			// v1 is greater than v2...
			switch($operation){
				case 'ge':
				case '>=':
				case 'gt':
				case '>':
					return true;
				default:
					return false;
			}
		}
	}

	/**
	 * Break a version string into the corresponding parts.
	 *
	 * Major Version
	 * Minor Version
	 * Point Release
	 * Core Version
	 * Developer-Specific Version
	 * Development Status
	 *
	 * Optimized 2013.08.17
	 *
	 * @param string $version
	 *
	 * @return array
	 */
	public static function VersionSplit($version) {
		$ret = array(
			'major'     => 0,
			'minor'     => 0,
			'point'     => 0,
			//'core'      => 0,
			'user'      => 0,
			'stability' => '1',
		);

		// dev < alpha = a < beta = b < RC = rc < # < pl = p

		$parts = explode('.', strtolower($version));

		// This version of the code executes about twice as fast as the traditional foreach version below!

		if(isset($parts[0])){
			$ret['major'] = $parts[0];
		}
		if(isset($parts[1])){
			if(is_numeric($parts[1])){
				// Strictly an int... process as usual.
				$ret['minor'] = $parts[1];
			}
			else{
				$digit = $parts[1];

				if(($pos = strpos($digit, '~')) !== false){
					$ret['minor'] = substr($digit, 0, $pos);
					$ret['user'] = substr($digit, $pos);
				}
				elseif(($pos = strpos($digit, 'a')) !== false){
					$ret['minor'] = substr($digit, 0, $pos);
					$ret['stability'] = substr($digit, $pos);
				}
				elseif(($pos = strpos($digit, 'b')) !== false){
					$ret['minor'] = substr($digit, 0, $pos);
					$ret['stability'] = substr($digit, $pos);
				}
				elseif(($pos = strpos($digit, 'rc')) !== false){
					$ret['minor'] = substr($digit, 0, $pos);
					$ret['stability'] = substr($digit, $pos);
				}
			}
		}
		if(isset($parts[2])){
			if(is_numeric($parts[2])){
				// Strictly an int... process as usual.
				$ret['point'] = $parts[2];
			}
			else{
				$digit = $parts[2];

				if(($pos = strpos($digit, '~')) !== false){
					$ret['point'] = substr($digit, 0, $pos);
					$ret['user'] = substr($digit, $pos);
				}
				elseif(($pos = strpos($digit, 'a')) !== false){
					$ret['point'] = substr($digit, 0, $pos);
					$ret['stability'] = substr($digit, $pos);
				}
				elseif(($pos = strpos($digit, 'b')) !== false){
					$ret['point'] = substr($digit, 0, $pos);
					$ret['stability'] = substr($digit, $pos);
				}
				elseif(($pos = strpos($digit, 'rc')) !== false){
					$ret['point'] = substr($digit, 0, $pos);
					$ret['stability'] = substr($digit, $pos);
				}
			}
		}

		/*
		$v = [0, 0, 0];
		$k         = 0;
		foreach($parts as $digit){
			if(($pos = strpos($digit, '~')) !== false){
				$v[$k] = substr($digit, 0, $pos);
				$ret['user'] = substr($digit, $pos);
			}
			elseif(($pos = strpos($digit, 'a')) !== false){
				$v[$k] = substr($digit, 0, $pos);
				$ret['stability'] = substr($digit, $pos);
			}
			elseif(($pos = strpos($digit, 'b')) !== false){
				$v[$k] = substr($digit, 0, $pos);
				$ret['stability'] = substr($digit, $pos);
			}
			elseif(($pos = strpos($digit, 'rc')) !== false){
				$v[$k] = substr($digit, 0, $pos);
				$ret['stability'] = substr($digit, $pos);
			}
			else{
				$v[$k] = $digit;
			}
			++$k;
		}

		$ret['major'] = $v[0];
		$ret['minor'] = $v[1];
		$ret['point'] = $v[2];
		*/
		return $ret;
	}

	/**
	 * Simple method to compare two values with each other in a more restrictive manner than == but not quite fully typecasted.
	 *
	 * This is useful for the scenarios that involve needing to check that "3" == 3, but "" != 0.
	 *
	 * @param $val1
	 * @param $val2
	 *
	 * @deprecated 2013.09 Please use the namespaced function instead.
	 *
	 * @return boolean
	 */
	public static function CompareValues($val1, $val2){
		return \Core\compare_values($val1, $val2);
	}

	/**
	 * Compare two values as strings explictly.
	 * This is useful for numbers that need to behave like strings, ie: postal codes with their leading zeros.
	 *
	 * @param $val1
	 * @param $val2
	 *
	 * @deprecated 2013.09 Please use the namespaced function instead.
	 *
	 * @return boolean
	 */
	public static function CompareStrings($val1, $val2) {
		return \Core\compare_strings($val1, $val2);
	}

	/**
	 * Generate a globally unique identifier that can be used as a replacement for an autoinc or similar.
	 *
	 * This method IS compatible with multiple servers on a single codebase!
	 *
	 * An example of a UUID returned by this function would be: "1-c5dbcaaf9db-8d77"
	 *
	 * @since 2.4.2
	 *
	 * @return string
	 */
	public static function GenerateUUID(){
		// @todo Make this dynamic based on the server ID assigned by the administrator.
		$serverid = 1;
		return dechex($serverid) . '-' . dechex(microtime(true) * 10000) . '-' . strtolower(Core::RandomHex(4));
	}

}

class CoreException extends Exception {

}


// Because this doesn't really fit anywhere else right now.
/**
 * Register a function to fire whenever a class is instantiated.    Will
 * automatically look up the class and include the appropriate file.
 */
spl_autoload_register('Core::CheckClass');
