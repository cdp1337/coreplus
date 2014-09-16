<?php
/**
 * Dataset
 *
 * @package Core\Datamodel
 * @since 0.1
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

namespace Core\Datamodel;

class Dataset implements \Iterator{

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
	 *
	 * INSERT INTO (`key`, `key2) VALUES ('val1', 'val2');
	 */
	const MODE_INSERT = 'insert';
	/**
	 * Mode for bulk inserting data into the datastore
	 *
	 * INSERT INTO (`key`, `key2) VALUES ('val1', 'val2'), ('val1', 'val2'), ('val1', 'val2'), ('val1', 'val2')...;
	 */
	const MODE_BULK_INSERT = 'bulk_insert';
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

	/**
	 * Set to true to return only unique records, ala SELECT DISTINCT
	 *
	 * @var bool
	 */
	public $uniquerecords = false;

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
	 * @throws \DMI_Exception
	 * @internal param mixed $select
	 * @return Dataset
	 */
	public function select(){

		$n = func_num_args();

		if($n == 0) throw new \DMI_Exception ('Invalid amount of parameters requested for Dataset::set()');

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
			throw new \DMI_Exception ('Invalid amount of parameters requested for Dataset::set(), ' . $n . ' provided, exactly 1 or 2 expected');
		}
		elseif($n == 1){
			$a = func_get_arg(0);
			if(!is_array($a)) throw new \DMI_Exception ('Invalid parameter sent for Dataset::set()');

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
			throw new \DMI_Exception ('Invalid amount of parameters requested for Dataset::renameColumn(), ' . $n . ' provided, exactly 2 expected');
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
		/** @noinspection PhpUndefinedConstantInspection */
		if(DB_PREFIX && strpos($tablename, DB_PREFIX) === false){
			/** @noinspection PhpUndefinedConstantInspection */
			$tablename = DB_PREFIX . $tablename;
		}

		$this->_table = $tablename;

		// Allow chaining
		return $this;
	}

	/**
	 * @param bool $unique
	 *
	 * @return Dataset
	 */
	public function unique($unique = true){
		$this->uniquerecords = $unique;

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
	 * Set the limit for this dataset.
	 *
	 * Supports a single argument for a hard limit or two arguments for starting at and limit.
	 * @throws \DMI_Exception
	 * @return Dataset
	 */
	public function limit(){
		$n = func_num_args();
		if($n == 1) $this->_limit = func_get_arg(0);
		elseif($n == 2) $this->_limit = func_get_arg(0) . ', ' . func_get_arg(1);
		else throw new \DMI_Exception('Invalid amount of parameters requested for Dataset::limit()');

		// Allow chaining
		return $this;
	}

	/**
	 * @throws \DMI_Exception
	 * @return Dataset
	 */
	public function order(){
		$n = func_num_args();
		if($n == 1) $this->_order = func_get_arg(0);
		elseif($n == 2) $this->_order = func_get_arg(0) . ', ' . func_get_arg(1);
		else throw new \DMI_Exception('Invalid amount of parameters requested for Dataset::order()');

		// Allow chaining
		return $this;
	}


	/**
	 *
	 * @param BackendInterface $interface
	 * @return Dataset
	 */
	public function execute($interface = null){
		// Default to the system interface.
		if(!$interface) $interface = \DMI::GetSystemDMI();

		// This actually goes the other way, as the interface has the logic.
		$interface->connection()->execute($this);

		if( $this->_data === null && $this->_mode == Dataset::MODE_GET ){
			// It's been executed, so data should at least be something.
			$this->_data = [];
			reset($this->_data);
		}

		// Allow Chaining
		return $this;
	}

	/**
	 * Execute this query and return the records or record, based on requested criteria.
	 *
	 * If limit == 1 and only one select was issued, that singular value or null is returned.
	 * If limit == 1 and more than one select was issued, an associative array is returned.
	 * Otherwise, an array of associative arrays is returned.
	 *
	 * @param null $interface
	 *
	 * @return array|null|mixed
	 */
	public function executeAndGet($interface = null){
		$this->execute($interface);

		if($this->_mode == Dataset::MODE_COUNT){
			// The user only requested the total number of rows, so return just that.
			return $this->num_rows;
		}
		elseif($this->_limit == 1 && $this->num_rows == 1){
			if(sizeof($this->_selects) == 1 && $this->_selects[0] != '*'){
				$k = $this->_selects[0];

				// Return a single key's value or null if not found.
				return (isset($this->_data[0][$k])) ? $this->_data[0][$k] : null;
			}
			else{
				// Return a single record
				return $this->_data[0];
			}
		}
		else{
			$ret = [];
			foreach($this as $d){
				$ret[] = $d;
			}
			return $ret;
		}
	}

	/****  Iterator Methods *****/

	function rewind() {
		if($this->_data !== null) reset($this->_data);
	}

	function current() {
		// If no data was selected before... I need to execute the query!
		if($this->_data === null) $this->execute();

		$k = key($this->_data);
		return isset($this->_data[$k]) ? $this->_data[$k] : null;
		//return $this->_data[key($this->_data)];
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






