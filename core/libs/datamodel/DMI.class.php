<?php
/**
 * Provides the main interface system for the DMI subsystem.
 * 
 * @package Core
 * @subpackage Datamodel
 * @since 2011.06
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Lesser General Public License v3 <http://www.gnu.org/licenses/lgpl-3.0.html>
 * This system is licensed under the GNU LGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/lgpl-3.0.html>, 
 * and please contribute back to the community :)
 */



// I has some dependencies...
define('__DMI_PDIR', dirname(__FILE__) . '/');
require_once(__DMI_PDIR . 'DMI_Backend.interface.php');
require_once(__DMI_PDIR . 'Dataset.class.php');


/**
 * A top level interface class for the datamodel system.  Provides abstraction 
 * for different backends
 *
 * @author powellc
 */
class DMI {
	
	/**
	 * The backend currently in use for this DMI object.
	 * @var DMI_Backend
	 */
	protected $_backend = null;
	
	
	/**
	 * This points to the system/global DMI object.
	 * 
	 * @var DMI
	 */
	static protected $_Interface = null;
	
	public function __construct($backend = null, $host = null, $user = null, $pass = null, $database = null){
		// Provide shortcut to set the backend directly in the constructor.
		if($backend) $this->setBackend($backend);
		
		// Provide shortcut to set connection information directly in the constructor.
		if($host) $this->connect($host, $user, $pass, $database);
	}
	
	public function setBackend($backend){
		if($this->_backend) throw new DMI_Exception('Backend already set');
		
		$class = 'DMI_' . $backend . '_backend';
		$classfile = strtolower($backend);
		if(!file_exists(__DMI_PDIR . 'backends/' . strtolower($classfile) . '.backend.php')){
			throw new DMI_Exception('Could not locate backend file for ' . $class);
		}
		
		require_once(__DMI_PDIR . 'backends/' . strtolower($classfile) . '.backend.php');
		
		$this->_backend = new $class();
	}
	
	public function connect($host, $user, $pass, $database){
		$this->_backend->connect($host, $user, $pass, $database);
		
		return $this->_backend;
	}
	
	public function connection(){
		return $this->_backend;
	}
	
	
	/**
	 * Get the current system DMI based on configuration values.
	 * @return DMI
	 */
	public static function GetSystemDMI(){
		if(self::$_Interface !== null) return self::$_Interface;
		
		self::$_Interface = new DMI();
		
		// Because this is the system data connection, I also need to pull the settings automatically.
		
		$cs = ConfigHandler::LoadConfigFile("configuration");
		
		self::$_Interface->setBackend($cs['database_type']);
		
		self::$_Interface->connect($cs['database_server'], $cs['database_user'], $cs['database_pass'], $cs['database_name']);
		
		return self::$_Interface;
	}
	
}

class DMI_Exception extends Exception{
	const ERRNO_NODATASET = '42S02';
	const ERRNO_UNKNOWN = '07000';
	
	public $ansicode;
	
	public function __construct($message, $code = null, $previous = null, $ansicode = null) {
		parent::__construct($message, $code, $previous);
		if($ansicode) $this->ansicode = $ansicode;
	}
}

?>
