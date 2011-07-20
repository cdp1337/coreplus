<?php
/**
 * Dataset
 * 
 * -- EXPERIMENTAL! --
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
	
	public $_sets = array();
	
	public $_idcol = null;
	
	public $_idval = null;
	
	public $_limit = false;
	
	public $_order = false;
	
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
		
		$n = func_num_args();
		
		if($n == 0) throw new DMI_Exception ('Invalid amount of parameters requested for Dataset::set()');
		
		// Allow null to clear out the selects.
		if($n == 1 && func_get_arg(0) === null){
			$this->_selects = array();
			return $this;
		}
		
		// This is a "get"
		$this->_mode = Dataset::MODE_GET;
		
		$args = func_get_args();
		foreach($args as $a){
			if(is_array($a)){
				// It should just be a flat array, ie: array('cola', 'colb', 'colc', ...);
				$this->_selects = array_merge($this->_selects, $a);
			}
			elseif(strpos($a, ',') !== false){
				// User submitted a comma-separated list... damn them
				$parts = explode(',', $a);
				foreach($parts as $p){
					$this->_selects[] = trim($p);
				}
			}
			else{
				// Just a regular column, yay
				$this->_selects[] = $a;
			}
		}

		// Ensure no duplicate entries.
		$this->_selects = array_unique($this->_selects);

		// Allow chaining
		return $this;
	}
	
	public function insert(){
		call_user_func_array(array($this, '_set'), func_get_args());
		$this->_mode = Dataset::MODE_INSERT;
		
		return $this;
	}
	
	public function update(){
		call_user_func_array(array($this, '_set'), func_get_args());
		$this->_mode = Dataset::MODE_UPDATE;
		
		return $this;
	}
	
	public function set(){
		call_user_func_array(array($this, '_set'), func_get_args());
		$this->_mode = Dataset::MODE_INSERTUPDATE;
		
		return $this;
	}
	
	public function delete(){
		// Just a simple function that doesn't actually delete anything,
		// but it needs to be called to set the correct flag.
		$this->_mode = Dataset::MODE_DELETE;
		
		return $this;
	}
	
	private function _set(){
		$n = func_num_args();
		
		if($n == 0 || $n > 2){
			throw new DMI_Exception ('Invalid amount of parameters requested for Dataset::set(), ' . $n . ' provided, exactly 1 or 2 expected');
		}
		elseif($n == 1){
			$a = func_get_arg(0);
			if(!is_array($a)) throw new DMI_Exception ('Invalid parameter sent for Dataset::set()');
			
			foreach($a as $k => $v){
				$this->_sets[$k] = $v;
			}
		}
		else{
			$k = func_get_arg(0);
			$v = func_get_arg(1);
			$this->_sets[$k] = $v;
		}
	}
	
	public function setID($key, $val = null){
		$this->_idcol = $key;
		$this->_idval = $val;
		
		if($val) $this->where("$key = $val");
	}
	
	public function getID(){
		return $this->_idval;
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
		
		// Allow $k, $v to be passed in.
		if(sizeof($args) == 2 && !is_array($args[0]) && !is_array($args[1])){
			$this->_where[] = array('field' => $args[0], 'op' => '=', 'value' => $args[1]);
			
			// Allow chaining.
			return $this;
		}
		
		// Otherwise, interpret each argument as its own entity.
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
	
	public function limit(){
		$n = func_num_args();
		if($n == 1) $this->_limit = func_get_arg(0);
		elseif($n == 2) $this->_limit = func_get_arg(0) . ', ' . func_get_arg(1);
		else throw new DMI_Exception('Invalid amount of parameters requested for Dataset::limit()');
		
		// Allow chaining
		return $this;
	}
	
	
	public function order(){
		$n = func_num_args();
		if($n == 1) $this->_order = func_get_arg(0);
		elseif($n == 2) $this->_order = func_get_arg(0) . ', ' . func_get_arg(1);
		else throw new DMI_Exception('Invalid amount of parameters requested for Dataset::order()');
		
		// Allow chaining
		return $this;
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
		
		if($this->_data !== null) reset($this->_data);
		
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
	
	
	
	private function _parseWhere($statement){
		// The user may have sent something like "blah = mep" or "datecreated < somedate"
		
		// @FIXME There is a potential bug wherein if the clause contains
		//        `somefield` = 'the equation is mxb+a=c'
		//        the explode function will cut that into 3 pieces, not 2 like it should.
		
		$chars = array('=', '>', '<', '<=', '>=', ' LIKE ');
		
		foreach($chars as $c){
			if(($pos = strpos($statement, $c)) !== false){
				list($k, $v) = explode($c, $statement);
				$this->_where[] = array('field' => trim($k), 'op' => $c, 'value' => trim($v));
				return;
			}
		}
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

