<?php
/**
 * Dataset
 * 
 * -- EXPERIMENTAL! --
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @package Core
 * @subpackage Datamodel
 * @since 20110610
 */

/**
 * Description of Dataset
 *
 * @author powellc
 */
class Dataset {
	
	const MODE_GET = 'get';
	const MODE_INSERT = 'insert';
	const MODE_UPDATE = 'update';
	const MODE_INSERTUPDATE = 'insertupdate';
	const MODE_DELETE = 'delete';
	
	public $_name;
	
	public $_selects = array();
	
	public $_where = array();
	
	public $_mode = Dataset::MODE_GET;
	
	public function __construct($name){
		$this->_name = $name;
	}
	
	public function select($select){
		if(is_array($select)) $this->_selects = array_merge($this->_selects, $select);
		else $this->_selects[] = $select;

		// Ensure no duplicate entries.
		$this->_selects = array_unique($this->_selects);

		// Allow chaining
		return $this;
	}
	
	public function execute($interface = null){
		// Default to the system interface.
		if(!$interface) $interface = DMI::GetSystemDMI();
		
		// This actually goes the other way, as the interface has the logic.
		return $interface->connection()->execute($this);
	}
}

?>
