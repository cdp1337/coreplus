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

require_once(ROOT_PDIR . 'core/libs/core/Theme.class.php');
require_once(ROOT_PDIR . 'core/libs/core/IFile.interface.php');
require_once(ROOT_PDIR . 'core/libs/core/File.class.php');
//require_once(ROOT_PDIR . 'core/libs/core/FileAWSS3.class.php');
require_once(ROOT_PDIR . 'core/libs/core/Asset.class.php');


/**
 * Just provides some simple tools for handling themes.
 */
class ThemeHandler implements ISingleton{
	/**
	 * The instance of this object.
	 * @var ComponentHandler
	 */
	private static $instance = null;
	
	/**
	 * A list of every valid theme on the system.
	 * @var array <<Theme>>
	 */
	private $_themeCache = array();
	
	/**
	 * Internal check variable to know if this handler has been loaded.
	 * @var boolean
	 */
	private $_loaded = false;
		
	/**
	 * Private constructor class to prevent outside instantiation.
	 * 
	 * @return void
	 */
	private function __construct(){
		
		// First, build my cache of components, regardless if the component is installed or not.
		$dh = opendir(ROOT_PDIR . 'themes');
			if(!$dh) return;
			while($file = readdir($dh)){
				// skip hidden directories.
				if($file{0} == '.') continue;
				
				// skip non-directories
				if(!is_dir(ROOT_PDIR . 'themes/' . $file)) continue;
				
				// Skip directories that do not have a readable theme.xml file.
				if(!is_readable(ROOT_PDIR . 'themes/' . $file . '/theme.xml')) continue;
				
				// Finally, load the theme and keep it in cache.
				
				$t = new Theme($file);
				
				$this->_themeCache[$file] = $t;
				unset($t);
			}
		closedir($dh);
	}
	
	
	/**
	 * Get the single instance of the theme handler.
	 * @return ThemeHandler
	 */
	public static function Singleton(){
		if(is_null(self::$instance)) self::$instance = new self();
		return self::$instance;
	}
	
	public static function Load(){
		// Run through all the installed themes and just make sure they're updated.
		foreach(self::GetAllThemes() as $t){
			if($t->isInstalled() && $t->needsUpdated()){
				$t->upgrade();
			}
			// Allow themes to be installed automatically.
			elseif(DEVELOPMENT_MODE && !$t->isInstalled()){
				$t->install();
			}
		}
	}
	
	
	/**
	 * Alias of Singleton.
	 * @return ComponentHandler
	 */
	public static function GetInstance(){ return self::Singleton(); }
	
	/**
	 * Get the theme object of a requested theme.
	 * 
	 * @param string $themeName
	 * @return Theme
	 */
	public static function GetTheme($themeName){
		if(isset(self::Singleton()->_themeCache[$themeName])) return self::Singleton()->_themeCache[$themeName];
		else return false;
	}
	
	public static function GetAllThemes(){
		return self::Singleton()->_themeCache;
	}
	
}
