<?php


abstract class ComponentFactory{
	
	/**
	 * Cache of versions in the database already.  Useful for reducing the number of lookups.
	 * @var array 
	 */
	private static $_DBCache = null;
	
	/**
	 * Internal function to lookup the saved data for a given component based on its name.
	 * 
	 * Will return null if it doesn't exist or an array.
	 * 
	 * @param string $componentname The name of the component to lookup
	 * @return array | null
	 */
	public static function _LookupComponentData($componentname){
		if(self::$_DBCache === null){
			self::$_DBCache = array();
			
			// Try to load the components
			try{
				$res = Dataset::Init()->table('component')->select('*')->execute();
			}
			// But since this function is called during the installer, it might fail... that's acceptable.
			catch(DMI_Exception $e){
				return false;
			}
			
			foreach($res as $r){
				$n = strtolower($r['name']);
				self::$_DBCache[$n] = $r;
			}
		}
		
		$componentname = strtolower($componentname);
		
		return (isset(self::$_DBCache[$componentname])) ? self::$_DBCache[$componentname] : null;
	}
	
	/**
	 * Create a Component of the appropriate version based on the XML file.
	 * 
	 * Will return either a Component if API 0.1, or a Component_2_1 if API 2.1
	 */
	public static function Create($filename){
		//$filename = ROOT_PDIR . 'components/' . $file . '/component.xml';
		
		// Check this version of the file.
		$fh = fopen($filename, 'r');
		if(!$fh) return null;
		
		$line = fgets($fh, 256);
		$line = fgets($fh, 256);
		// I need the second line.
		fclose($fh);
		
		if(strpos($line, 'http://corepl.us/api/2_1/component.dtd') !== false){
			return new Component_2_1($filename);
		}
		else{
			// Component version 0.1 still requires the basename.
			$name = substr($filename, 0, -14);
			$name = substr($name, strrpos($name, '/') + 1);
			return new Component($name);
		}
	}
}