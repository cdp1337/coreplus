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
class Dataset implements Iterator{
	
	
	const MODE_GET = 'get';
	const MODE_INSERT = 'insert';
	const MODE_UPDATE = 'update';
	const MODE_INSERTUPDATE = 'insertupdate';
	const MODE_DELETE = 'delete';
	
	
	public $_table;
	
	public $_selects = array();
	
	public $_where = array();
	
	public $_mode = Dataset::MODE_GET;
	
	public $_data = null;
	
	public $num_rows = null;
	
	public function __construct(){
		
	}
	
	/**
	 * Set the columns to select.
	 * 
	 * Argument can be the following:
	 * null: reset the array to blank
	 * single value: add the value to the columns
	 * array of values: add each value to the columns
	 * multiple arguments: add each value to the columns
	 * 
	 * @param mixed $select
	 * @return Dataset
	 */
	public function select(){
		
		// Allow null to clear out the selects.
		if(func_num_args() == 1 && func_get_arg(0) === null){
			$this->_selects = array();
			return $this;
		}
		
		// This is a "get"
		$this->_mode = Dataset::MODE_GET;
		
		$args = func_get_args();
		foreach($args as $a){
			if(is_array($a)) $this->_selects = array_merge($this->_selects, $a);
			else $this->_selects[] = $a;
		}

		// Ensure no duplicate entries.
		$this->_selects = array_unique($this->_selects);

		// Allow chaining
		return $this;
	}
	
	/**
	 *
	 * @param string $tablename
	 * @return Dataset
	 */
	public function table($tablename){
		// Is this name prefixed by the DB_PREFIX variable?
		if(strpos($tablename, DB_PREFIX) === false) $tablename = DB_PREFIX . $tablename;
		
		$this->_table = $tablename;
		
		// Allow chaining
		return $this;
	}
	
	/**
	 * Set or add to the where clause for this query.
	 * 
	 * Argument passed in can be a multitude of options:
	 * key/value paired array: 
	 * 
	 * @return Dataset
	 */
	public function where(){
		$args = func_get_args();
		foreach($args as $a){
			if(is_array($a)){
				foreach($a as $k => $v){
					if(is_numeric($k)) $this->_parseWhere($v);
					else $this->_where[] = array('field' => $k, 'op' => '=', 'value' => $v);
				}
			}
			else{
				$this->_parseWhere($a);
			}
		}
		
		// Allow chaining
		return $this;
	}
	
	private function _parseWhere($statement){
		// The user may have sent something like "blah = mep" or "datecreated < somedate"
		
		$chars = array('=', '>', '<', '<=', '>=');
		
		foreach($chars as $c){
			if(($pos = strpos($statement, $c)) !== false){
				list($k, $v) = explode($c, $statement);
				$this->_where[] = array('field' => trim($k), 'op' => $c, 'value' => trim($v));
				return;
			}
		}
	}
	
	
	/**
	 *
	 * @param type $interface
	 * @return Dataset
	 */
	public function execute($interface = null){
		// Default to the system interface.
		if(!$interface) $interface = DMI::GetSystemDMI();
		
		// This actually goes the other way, as the interface has the logic.
		$interface->connection()->execute($this);
		
		reset($this->_data);
		
		// Allow Chaining
		return $this;
	}
	
	/****  Iterator Methods *****/
	
	function rewind() {
		if($this->_data !== null) reset($this->_data);
	}

	function current() {
		// If no data was selected before... I need to execute the query!
		if($this->_data === null) $this->execute();
		
		return $this->_data[key($this->_data)];
	}

	function key() {
		// If no data was selected before... I need to execute the query!
		if($this->_data === null) $this->execute();
		
		return key($this->_data);
	}

	function next() {
		// If no data was selected before... I need to execute the query!
		if($this->_data === null) $this->execute();
		
		next($this->_data);
	}

	function valid() {
		// If no data was selected before... I need to execute the query!
		if($this->_data === null) $this->execute();
		
		return isset($this->_data[key($this->_data)]);
	}
	
	
	/****  Static Methods  *****/
	
	/**
	 * Simple constructor that allows chaining.
	 * @return Dataset
	 */
	public static function Init(){
		
		// Allow chaining
		return new self();
	}
}

