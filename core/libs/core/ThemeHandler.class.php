<?php
/**
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 * 
 * Copyright (C) 2009	Charlie Powell <powellc@powelltechs.com>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, version 3.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.	If not, see http://www.gnu.org/licenses/agpl-3.0.txt.
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
