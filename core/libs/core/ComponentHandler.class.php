<?php
/**
 * // enter a good description here
 * 
 * @package Core
 * @since 2011.06
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Lesser General Public License v3 <http://www.gnu.org/licenses/lgpl-3.0.html>
 * This system is licensed under the GNU LGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/lgpl-3.0.html>, 
 * and please contribute back to the community :)
 */

require_once(ROOT_PDIR . 'core/libs/core/Component.class.php');
//require_once(ROOT_PDIR . 'core/libs/core/FileAWSS3.class.php');
//require_once(ROOT_PDIR . 'core/libs/core/Asset.class.php');


/**
 * Basically, the Component Handler is the previous library handler, only completed!
 */
class ComponentHandler implements ISingleton{
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
	 * List of every installed view class and its location on the system.
	 * @var array <<String>>
	 */
	private $_viewClasses = array();
	
	/**
	 * List of every installed library and its version.
	 * @var array <<String>>
	 */
	private $_libraries = array();
	
	/**
	 * List of every available jslibrary and its call..
	 * @var array <<String>>
	 */
	private $_scriptlibraries = array();
	
	/**
	 * List of every installed module and its version.
	 * @var array <<String>>
	 */
	private $_modules = array();
	
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
	private function __construct(){
		// Add in the core component for the first element.
		$this->_componentCache['core'] = $c = Core::GetComponent();
		
		// Run through the libraries directory and look for, well... components.
		
		// First, build my cache of components, regardless if the component is installed or not.
		$dh = opendir(ROOT_PDIR . 'components');
		if(!$dh) return;
		while($file = readdir($dh)){
			// skip hidden directories.
			if($file{0} == '.') continue;

			// skip non-directories
			if(!is_dir(ROOT_PDIR . 'components/' . $file)) continue;

			// Skip directories that do not have a readable component.xml file.
			if(!is_readable(ROOT_PDIR . 'components/' . $file . '/component.xml')) continue;

			// Finally, load the component and keep it in cache.
			$c = new Component($file);

			// All further operations are case insensitive.
			// The original call to Component needs to be case sensitive because it sets the filename to pull.
			$file = strtolower($file);

			// If the component was flagged as invalid.. just skip to the next one.
			if(!$c->isValid()){
				if(DEVELOPMENT_MODE){
					CAEUtils::AddMessage('Component ' . $c->getName() . ' appears to be invalid.');
				}
				continue;
			}

			$this->_componentCache[$file] = $c;
			unset($c);
		}
		closedir($dh);
	}
	
	private function load(){
		if($this->_loaded) return;
		
		// Load in all the data in the components table.
		$res = Dataset::Init()->table('component')->select('*')->execute();
		$this->_dbcache = array();
		foreach($res as $r){
			$n = strtolower($r['name']);
			$this->_dbcache[$n] = $r;
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
		foreach($this->_componentCache as $n => $c){
			$c->load();
			
			// If the component is not in the initial dbcache, it must not be installed.
			// Keep it in the component cache, but do not try to load it just yet.
			if(!isset($this->_dbcache[$n])){
				//unset($this->_componentCache[$n]);
				continue;
			}
			
			// Set the data from the loaded cache
			$c->_versionDB = $this->_dbcache[$n]['version'];
			$c->enabled = ($this->_dbcache[$n]['enabled']);
			
			// First check before anything else is even done.... Did the user disable it?
			if(!$c->enabled){
				//echo "Skipping " . $c->getName() . " because it is disabled<br/>";
				unset($this->_componentCache[$n]);
				continue;
			}
			
			//var_dump($c);
			// Doesn't contain a valid xml, just remove it.
			if(!$c->isValid()){
				if(DEVELOPMENT_MODE){
					echo 'Component ' . $c->getName() . ' appears to be invalid due to:<br/>' . $c->getErrors();
					//CAEUtils::AddMessage('Component ' . $c->name . ' appears to be invalid due to:<br/>' . $c->_invalidReason);
				}
				unset($this->_componentCache[$n]);
			}
		}
		
		// If the execution mode is CLI, ensure the CLI tools are installed!
		if(EXEC_MODE == 'CLI'){
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
		
		do{
			$size = sizeof($list);
			foreach($list as $n => $c){
				
				// If it's loaded, register it and remove it from the list!
				if($c->isInstalled() && $c->isLoadable() && $c->loadFiles()){
					
					// Allow for on-the-fly package upgrading regardless of DEV mode or not.
					if($c->needsUpdated()){
						$c->upgrade();
					}
					
					$this->_registerComponent($c);
					unset($list[$n]);
					continue;
				}
				
				
				// Allow for on-the-fly package upgrading regardless of DEV mode or not.
				if($c->isInstalled() && $c->needsUpdated() && $c->isLoadable()){
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
				if(!$c->isInstalled() && DEVELOPMENT_MODE && $c->isLoadable()){
					// w00t
					$c->install();
					$c->loadFiles();
					$this->_registerComponent($c);
					unset($list[$n]);
					continue;
				}
			}
		} while($size > 0 && ($size != sizeof($list)));
		
		// If dev mode is enabled, display a list of components installed but not loadable.
		if(DEVELOPMENT_MODE){
			foreach($list as $l){
				// Ignore anything with the execmode different, those should be minor notices for debugging if anything.
				if($l->error & Component::ERROR_WRONGEXECMODE) continue;
				
				$msg = 'Could not load installed component ' . $l->getName() . ' due to requirement failed.<br/>' . $l->getErrors();
				echo $msg . '<br/>';
				//Core::AddMessage($msg);
			}
		}
		
		
		$this->_loaded = true;
	}
	
	/**
	 * Internally used method to notify the rest of the system that a given
	 *	component has been loaded and is available.
	 * 
	 * Expects all checks to be done already.
	 */
	public function _registerComponent($c){
		$name = strtolower($c->getName());
		
		if($c->hasLibrary()){
			$this->_libraries = array_merge($this->_libraries, $c->getLibraryList());
			
			// Register the include paths if set.
			foreach($c->getIncludePaths() as $path){
				set_include_path(get_include_path() . PATH_SEPARATOR . $path);
			}
			
			$this->_scriptlibraries = array_merge($this->_scriptlibraries, $c->getScriptLibraryList());
		}
		if($c->hasModule()) $this->_modules[$name] = $c->getVersionInstalled();
		
		$this->_classes = array_merge($this->_classes, $c->getClassList());
		$this->_viewClasses = array_merge($this->_viewClasses, $c->getViewClassList());
		$this->_loadedComponents[$name] = $c;
	}
	
	/**
	 * Get the single instance of the component handler.
	 * @return ComponentHandler
	 */
	public static function Singleton(){
		if(is_null(self::$instance)){
			self::$instance = new self();
			
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
	public static function GetInstance(){ return self::Singleton(); }
	
	/**
	 * Get the component object of a requested component.
	 * 
	 * @param string $componentName
	 * @return Component
	 */
	public static function GetComponent($componentName){
		$componentName = strtolower($componentName);
		if(isset(ComponentHandler::Singleton()->_componentCache[$componentName])) return ComponentHandler::Singleton()->_componentCache[$componentName];
		else return false;
	}
	
	public static function IsLibraryAvailable($name, $version = false, $operation = 'ge'){
		$ch = ComponentHandler::Singleton();
		$name = strtolower($name);
		//var_dump($ch->_libraries[$name], version_compare(str_replace('~', '-', $ch->_libraries[$name]), $version, $operation));
		//if($name == 'DB') return true;
		//echo "Checking library name[$name] v[$version] op[$operation]<br>";
		if(!isset($ch->_libraries[$name])) return false;
		// There's a bit of an issue with the debian-style versions... PHP considers 1.2.3~1 < 1.2.3...
		elseif($version !== false) return version_compare(str_replace('~', '-', $ch->_libraries[$name]), $version, $operation);
		else return true;
	}
	
	public static function IsJSLibraryAvailable($name, $version = false, $operation = 'ge'){
		$ch = ComponentHandler::Singleton();
		$name = strtolower($name);
		//if($name == 'DB') return true;
		//echo "Checking jslibrary name[$name] v[$version] op[$operation]<br>";
		if(!isset($ch->_jslibraries[$name])) return false;
		// There's a bit of an issue with the debian-style versions... PHP considers 1.2.3~1 < 1.2.3...
		elseif($version) return version_compare(str_replace('~', '-', $ch->_jslibraries[$name]->version), $version, $operation);
		else return true;
	}
	
	public static function GetJSLibrary($library){
		$library = strtolower($library);
		return ComponentHandler::Singleton()->_jslibraries[$library];
	}
	
	public static function LoadScriptLibrary($library){
		$library = strtolower($library);
		$obj = ComponentHandler::Singleton();
		
		if(isset($obj->_scriptlibraries[$library])){
			return call_user_func($obj->_scriptlibraries[$library]);
		}
		else{
			return false;
		}
	}
	
	public static function IsComponentAvailable($name, $version = false, $operation = 'ge'){
		$ch = ComponentHandler::Singleton();
		$name = strtolower($name);
		// The DB object is specifically a library, and MUST remain as such.
		if($name == 'DB') return ComponentHandler::IsLibraryAvailable($name, $version, $operation);
		
		//echo "Checking component name[$name] v[$version] op[$operation]<br>";
		if(!isset($ch->_loadedComponents[$name])) return false;
		// There's a bit of an issue with the debian-style versions... PHP considers 1.2.3~1 < 1.2.3...
		elseif($version) return version_compare(str_replace('~', '-', $ch->_loadedComponents[$name]->getVersionInstalled()), $version, $operation);
		else return true;
	}
	
	public static function IsViewClassAvailable($name, $casesensitive = true){
		if(!$casesensitive) $name = strtolower($name);
		foreach(ComponentHandler::Singleton()->_viewClasses as $c => $l){
			if(!$casesensitive && strtolower($c) == $name) return $c;
			elseif($c == $name) return $c;
		}
		return false;
	}
	
	/**
	 * Simple autoload register function to lookup a classname and resolve it.
	 * 
	 * @param string $classname
	 * @return void
	 */
	public static function CheckClass($classname){
		if(class_exists($classname)) return;
		if(isset(ComponentHandler::Singleton()->_classes[$classname])){
			require_once(ComponentHandler::Singleton()->_classes[$classname]);
		}
	}
	
	/**
	 * Just check if the class is available, do not load it.
	 * 
	 * @return boolean
	 */
	public static function IsClassAvailable($classname){
		return (isset(self::Singleton()->_classes[$classname]));
	}
	
	public static function GetAllComponents(){
		return ComponentHandler::Singleton()->_componentCache;
	}
	
	public static function GetLoadedComponents(){
		$ret = array();
		foreach(ComponentHandler::Singleton()->_loadedComponents as $c){
			$ret[] = $c;
		}
		return $ret;
	}

	public static function GetLoadedClasses(){
		return ComponentHandler::Singleton()->_classes;
	}
	
	public static function GetLoadedViewClasses(){
		return ComponentHandler::Singleton()->_viewClasses;
	}
}

/**
 * Register a function to fire whenever a class is instantiated.	Will
 * automatically look up the class and include the appropriate file.
 */ 
spl_autoload_register('ComponentHandler::CheckClass');
