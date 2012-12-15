<?php
/**
 * Dataset
 *
 * @package Core Plus\Datamodel
 * @since 0.1
 * @author Charlie Powell <charlie@eval.bz>
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

class Dataset implements Iterator{

	/**
	 * Mode for altering the structure of the datastore
	 */
	const MODE_ALTER = 'alter';
	/**
	 * Mode for getting data from the datastore
	 */
	const MODE_GET = 'get';
	/**
	 * Mode for inserting data into the datastore
	 */
	const MODE_INSERT = 'insert';
	/**
	 * Mode for updating data in the datastore
	 */
	const MODE_UPDATE = 'update';
	/**
	 * Mode for either updating or inserting data in the datastore
	 */
	const MODE_INSERTUPDATE = 'insertupdate';
	/**
	 * Mode for deleting data from the datastore
	 */
	const MODE_DELETE = 'delete';
	/**
	 * Mode for counting records in the datastore
	 */
	const MODE_COUNT = 'count';


	public $_table;

	public $_selects = array();

	/**
	 * The root where clause for this dataset
	 * @var null|DatasetWhereClause
	 */
	public $_where = null;

	public $_mode = Dataset::MODE_GET;

	public $_sets = array();

	public $_idcol = null;

	public $_idval = null;

	public $_limit = false;

	public $_order = false;

	public $_data = null;

	public $num_rows = null;

	/**
	 * Column renames used in the alter mode
	 * @var array
	 */
	public $_renames = array();

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

	/**
	 * @return Dataset
	 */
	public function insert(){
		call_user_func_array(array($this, '_set'), func_get_args());
		$this->_mode = Dataset::MODE_INSERT;

		return $this;
	}

	/**
	 * @return Dataset
	 */
	public function update(){
		call_user_func_array(array($this, '_set'), func_get_args());
		$this->_mode = Dataset::MODE_UPDATE;

		return $this;
	}

	/**
	 * @return Dataset
	 */
	public function set(){
		call_user_func_array(array($this, '_set'), func_get_args());
		$this->_mode = Dataset::MODE_INSERTUPDATE;

		return $this;
	}

	/**
	 * Rename a column in this dataset, primarlly an administrative / installer function.
	 * @return Dataset
	 */
	public function renameColumn(){
		call_user_func_array(array($this, '_renameColumn'), func_get_args());
		$this->_mode = Dataset::MODE_ALTER;

		return $this;
	}

	/**
	 * @return Dataset
	 */
	public function delete(){
		// Just a simple function that doesn't actually delete anything,
		// but it needs to be called to set the correct flag.
		$this->_mode = Dataset::MODE_DELETE;

		return $this;
	}

	/**
	 * Set this dataset to only return the count of records.
	 *
	 * @return Dataset
	 */
	public function count(){
		$this->_mode = Dataset::MODE_COUNT;

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

	private function _renameColumn(){
		$n = func_num_args();

		if($n != 2){
			throw new DMI_Exception ('Invalid amount of parameters requested for Dataset::renameColumn(), ' . $n . ' provided, exactly 2 expected');
		}

		$oldname = func_get_arg(0);
		$newname = func_get_arg(1);

		$this->_renames[$oldname] = $newname;
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
		if(DB_PREFIX && strpos($tablename, DB_PREFIX) === false) $tablename = DB_PREFIX . $tablename;

		$this->_table = $tablename;

		// Allow chaining
		return $this;
	}

	/**
	 * @return DatasetWhereClause
	 */
	public function getWhereClause(){
		// Make sure that the root where clause exists first!
		if($this->_where === null){
			$this->_where = new DatasetWhereClause('root');
		}

		return $this->_where;
	}

	/**
	 * Set or add to the where clause for this query.
	 *
	 * Argument passed in can be a multitude of options:
	 * key/value paired array:
	 *
	 *
	 * Supported formats:
	 *
	 * The most simple method, set the where clause to look where one specific key is a value.
	 * <pre>
	 * where("key", "value");
	 * </pre>
	 *
	 * Just a regular string for the where statement
	 * <pre>
	 * where('key = some value');
	 * where('key > 123');
	 * where('key LIKE something%foo');
	 * </pre>
	 *
	 * Associative array of simple equal wheres.  This method is limiting in that it only supports '=' checks.
	 * <pre>
	 * where(array('key' => 'value1', 'key2' => 'value2'));
	 * </pre>
	 *
	 * Indexed array of multiple where statements, allow any value check.
	 * <pre>
	 * where(array('key = value1', 'key2 > 123'));
	 * </pre>
	 *
	 * @return Dataset
	 */
	public function where(){
		$args = func_get_args();

		// The new addwhere doesn't support setting a string, string, but the legacy system does!
		if(sizeof($args) == 2 && is_string($args[0]) && is_string($args[1])){
			$this->getWhereClause()->addWhere($args[0] . ' = ' . $args[1]);
		}
		else{
			// Ya'know, I really don't care what the arguments that are passed in are!
			// But I bet the WhereClause object will! :p
			$this->getWhereClause()->addWhere($args);
		}

		// Allow chaining
		return $this;
	}

	/**
	 * Allow for grouping of groups of where clauses.
	 *
	 * This is useful for statements such as
	 * WHERE (this = 1 OR that = 1) AND something = blah;
	 *
	 * The where clause can either be a single array, a single string, or a list of arguments
	 *
	 * @param string $separator 'AND', 'OR'
	 * @param array|string $wheres
	 * @return Dataset
	 */
	public function whereGroup($separator, $wheres){
		$args = func_get_args();

		// Because the first argument is the 'AND' or 'OR' string.
		$sep = array_shift($args);
		$clause = new DatasetWhereClause();
		$clause->setSeparator($sep);
		$clause->addWhere($args);

		// Since everything will be under the root node anyway...
		$this->getWhereClause()->addWhere($clause);

		// Allow chaining
		return $this;
	}

	/**
	 * @return Dataset
	 */
	public function limit(){
		$n = func_num_args();
		if($n == 1) $this->_limit = func_get_arg(0);
		elseif($n == 2) $this->_limit = func_get_arg(0) . ', ' . func_get_arg(1);
		else throw new DMI_Exception('Invalid amount of parameters requested for Dataset::limit()');

		// Allow chaining
		return $this;
	}

	/**
	 * @return Dataset
	 */
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

class DatasetWhereClause{

	/**
	 * If multiple statements are contained herein, this is the separator of all statements.
	 *
	 * @var string
	 */
	private $_separator = 'AND';

	/**
	 * The array of statements (of groups) contained herein
	 *
	 * @var array
	 */
	private $_statements = array();


	/**
	 * The name of this group/clause.  Completely meaningless other than external lookups.
	 * (FUTURE FEATURE)
	 * @var string
	 */
	private $_name;

	/**
	 * @param string $name The name of this group/clause.  Completely meaningless other than external lookups.
	 */
	public function __construct($name = '_unnamed_'){
		$this->_name = $name;
	}

	/**
	 * Add a where statement to this clause.
	 *
	 * DOES NOT SUPPORT addWhere('key', 'value'); format!!!
	 *
	 * @param $arguments
	 *
	 * @return bool
	 */
	public function addWhere($arguments){

		// <strike>Allow $k, $v to be passed in.</strike>
		//
		// This format is no longer supported at the low level!  DON'T DO IT!
		//
//		if(sizeof($arguments) == 2 && !is_array($arguments[0]) && !is_array($arguments[1])){
//
//			$this->_statements[] = new DatasetWhere($arguments[0] . ' = ' . $arguments[1]);
//			return true;
//		}

		// Allow another clause to be sent in, that will be set as a child of this one.
		if($arguments instanceof DatasetWhereClause){
			$this->_statements[] = $arguments;
			return true;
		}

		// Allow just a plain ol string to be passed in too
		if(is_string($arguments)){
			$this->_statements[] = new DatasetWhere($arguments);
			return true;
		}

		// Otherwise, interpret each argument as its own entity.
		foreach($arguments as $a){
			if(is_array($a)){
				foreach($a as $k => $v){
					if(is_numeric($k)){
						// It's an indexed array of 'something = this or that';
						$this->_statements[] = new DatasetWhere($v);
					}
					else{
						// It's an associative array of key => 'this or that';
						$this->_statements[] = new DatasetWhere($k . ' = ' . $v);
					}
				}
			}
			elseif($a instanceof DatasetWhereClause){
				$this->_statements[] = $a;
			}
			elseif($a instanceof DatasetWhere){
				$this->_statements[] = $a;
			}
			else{
				$this->_statements[] = new DatasetWhere($a);
			}
		}
	}

	/**
	 * Shortcut function to add a subgroup to an existing group.
	 *
	 * @param $sep
	 * @param $arguments
	 */
	public function addWhereSub($sep, $arguments){
		$subgroup = new DatasetWhereClause();
		$subgroup->setSeparator($sep);
		$subgroup->addWhere($arguments);

		$this->addWhere($subgroup);
	}

	public function getStatements(){
		return $this->_statements;
	}

	public function setSeparator($sep){
		$sep = trim(strtoupper($sep));
		switch($sep){
			case 'AND':
			case 'OR':
				$this->_separator = $sep;
				break;
			default:
				throw new DMI_Exception('Invalid separator, [' . $sep . ']');
		}
	}

	public function getSeparator(){
		return $this->_separator;
	}

	/**
	 * Sometimes you just want a good'ol "flat" representation.
	 */
	public function getAsArray(){
		$children = array();
		foreach($this->_statements as $s){
			if($s instanceof DatasetWhereClause){
				$children[] = $s->getAsArray();
			}
			elseif($s instanceof DatasetWhere){
				if($s->field === null) continue;
				$children[] = $s->field . ' ' . $s->op . ' ' . $s->value;
			}
		}
		return array('sep' => $this->_separator, 'children' => $children);
	}

	/**
	 * Get any/all statements that have a field set to that which is requested.
	 *
	 * Useful for looking up to see if a specific column has been set in a where statement.
	 *
	 * @param string $fieldname The field to search for
	 * @return array
	 */
	public function findByField($fieldname){
		$matches = array();
		foreach($this->_statements as $s){
			if($s instanceof DatasetWhereClause){
				$matches = array_merge($matches, $s->findByField($fieldname));
			}
			elseif($s instanceof DatasetWhere){
				if($s->field == $fieldname) $matches[] = $s;
			}
		}

		return $matches;
	}

}

class DatasetWhere{
	public $field;
	public $op;
	public $value;

	public function __construct($arguments){
		$this->_parseWhere($arguments);
	}

	/**
	 * Parse a single where statement for the key, operation, and value.
	 *
	 * @param string $statement The where statement to parse and evaluate
	 * @param int $group The group to associate this statement to
	 * @return void
	 */
	private function _parseWhere($statement){
		// The user may have sent something like "blah = mep" or "datecreated < somedate"
		$valid = false;
		$operations = array('!=', '<=', '>=', '=', '>', '<', 'LIKE ', 'NOT LIKE');

		// First, extract out the key.  This is the simplest thing to look for.
		$k = preg_replace('/^([^ !=<>]*).*/', '$1', $statement);

		// and the rest of the query...
		$statement = trim(substr($statement, strlen($k)));


		// Now I can sift through each operation and find the one that this query is.
		foreach($operations as $c){
			// The match MUST be the first character.
			if(($pos = strpos($statement, $c)) === 0){
				$op = $c;
				$statement = trim(substr($statement, strlen($op)));
				$valid = true;
				break;
			}
		}

		if($valid){
			$this->field = $k;
			$this->op = $op;
			$this->value = $statement;
		}
	}
}