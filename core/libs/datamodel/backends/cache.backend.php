<?php
/**
 * Cache datamodel backend system
 * 
 * Tries to store data in the system cache backend.
 * 
 * ## EXPERIMENTAL! ##
 * This is an experimental system that is not finished, use of it is highly discouraged.
 * 
 * @package Core Plus\Datamodel
 * @since 0.1
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl.html>
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class DMI_cache_backend implements DMI_Backend {
	
	/**
	 * Number of reads done from the database from the 'execute' function.
	 * @var int
	 */
	private $_reads = 0;
	
	/**
	 * Number of writes done to the database from the 'execute' function.
	 * @var int
	 */
	private $_writes = 0;
	
	
	/**
	 *
	 * @var mysqli
	 */
	private $_conn = null;
	
	
	/**
	 * Create a new connection to a mysql server.
	 * 
	 * @param type $host
	 * @param type $user
	 * @param type $pass
	 * @param type $database
	 * @return type 
	 */
	public function connect($host = null, $user = null, $pass = null, $database = null){
		// ok....
		$this->_conn = Core::Cache();
		
		return $this->_conn;
	}
	
	public function execute(Dataset $dataset){
		switch($dataset->_mode){
			case Dataset::MODE_GET:
				++$this->_reads;
				$this->_executeGet($dataset);
				break;
			case Dataset::MODE_INSERT:
				++$this->_writes;
				$this->_executeInsert($dataset);
				break;
			case Dataset::MODE_UPDATE:
				++$this->_writes;
				$this->_executeUpdate($dataset);
				break;
			case Dataset::MODE_DELETE:
				++$this->_writes;
				$this->_executeDelete($dataset);
				break;
			default:
				throw new Exception('Invalid dataset mode [' . $dataset->_mode . ']');
				break;
		}
	}
	
	
	public function tableExists($tablename){
		// Cache is completely dynamic, so tables always exist!
		return true;
	}
	
	public function createTable($table, $schema){
		// Cache is completely dynamic, so tables always exist!
		return true;
	}
	
	public function modifyTable($table, $newschema){
		// Cache is completely dynamic, so tables always exist!
		return true;
	}
	
	public function readCount() {
		return $this->_reads;
	}
	
	public function writeCount() {
		return $this->_writes;
	}
	
	
	/**
	 * Translate the database-independent types into mysql-specific ones.
	 * 
	 * Used internally for some schema operations.
	 * 
	 * @param array $coldef
	 * @return string 
	 */
	public function _getSchemaFromType($coldef){
		return null;
	}
	
	
	
	public function _getTables(){
		return array();
	}
	
	public function _describeTableSchema($table){
		return array('def' => null, 'ord' => null);
	}
	
	public function _describeTableIndexes($table){
		return array();
	}
	
	
	private function _executeGet(Dataset $dataset){
		$key = 'DM-' . $dataset->_table;
		// Generate a query to run.
		$q = 'SELECT';
		$ss = array();
		foreach($dataset->_selects as $s){
			// Check the escaping for this column.
			if(strpos($s, '.')) $s = '`' . str_replace('.', '`.`', trim($s)) . '`';
			// `*` is not a valid column.
			elseif($s == '*') $s = $s; // (just don't change it)
			else $s = '`' . $s . '`';
			
			$ss[] = $s;
		}
		$q .= ' ' . implode(', ', $ss);
		
		$q .= ' FROM `' . $dataset->_table . '`';
		
		if(sizeof($dataset->_where)){
			$ws = array();
			foreach($dataset->_where as $w){
				$w['value'] = $this->_conn->real_escape_string($w['value']);
				$ws[] = "`{$w['field']}` {$w['op']} '{$w['value']}'";
			}
			$q .= ' WHERE ' . implode(' AND ', $ws);
		}
		
		if($dataset->_limit) $q .= ' LIMIT ' . $dataset->_limit;
		
		if($dataset->_order) $q .= ' ORDER BY ' . $dataset->_order;
		
		// Execute this and populate the dataset appropriately.
		$res = $this->_rawExecute($q);
		
		$dataset->num_rows = $res->num_rows;
		$dataset->_data = array();
		while($row = $res->fetch_assoc()){
			$dataset->_data[] = $row;
		}
	}
	
	private function _executeInsert(Dataset $dataset){
		// Generate a query to run.
		$q = "INSERT INTO `" . $dataset->_table . "`";
		
		$keys = array();
		$vals = array();
		foreach($dataset->_sets as $k => $v){
			$keys[] = "`$k`";
			$vals[] = "'" . $this->_conn->real_escape_string($v) . "'";
		}
		
		$q .= " ( " . implode(', ', $keys) . " )";
		$q .= " VALUES ";
		$q .= " ( " . implode(', ', $vals) . " )";
		
		
		// Execute this and populate the dataset appropriately.
		$res = $this->_rawExecute($q);
		
		$dataset->num_rows = $this->_conn->affected_rows;
		$dataset->_data = array();
		// Inserts don't have any data, but do have an ID, (which mysql handles internally)
		if($dataset->_idcol) $dataset->_idval = $this->_conn->insert_id;
	}
	
	private function _executeUpdate(Dataset $dataset){
		// Generate a query to run.
		$q = "UPDATE `" . $dataset->_table . "`";
		
		$sets = array();
		foreach($dataset->_sets as $k => $v){
			$sets[] = "`$k` = '" . $this->_conn->real_escape_string($v) . "'";
		}
		
		$q .= ' SET ' . implode(', ', $sets);
		
		if(sizeof($dataset->_where)){
			$ws = array();
			foreach($dataset->_where as $w){
				$w['value'] = $this->_conn->real_escape_string($w['value']);
				$ws[] = "`{$w['field']}` {$w['op']} '{$w['value']}'";
			}
			$q .= ' WHERE ' . implode(' AND ', $ws);
		}
		
		if($dataset->_limit) $q .= ' LIMIT ' . $dataset->_limit;
		
		// Execute this and populate the dataset appropriately.
		$res = $this->_rawExecute($q);
		
		$dataset->num_rows = $this->_conn->affected_rows;
		$dataset->_data = array();
		// Inserts don't have any data, but do have an ID, (which mysql handles internally)
		//if($dataset->_idcol) $dataset->_idval = $this->_conn->insert_id;
	}
	
	private function _executeDelete(Dataset $dataset){
		$q = 'DELETE FROM `' . $dataset->_table . '`';
		
		if(sizeof($dataset->_where)){
			$ws = array();
			foreach($dataset->_where as $w){
				$w['value'] = $this->_conn->real_escape_string($w['value']);
				$ws[] = "`{$w['field']}` {$w['op']} '{$w['value']}'";
			}
			$q .= ' WHERE ' . implode(' AND ', $ws);
		}
		
		if($dataset->_limit) $q .= ' LIMIT ' . $dataset->_limit;
		
		// Execute this and populate the dataset appropriately.
		$res = $this->_rawExecute($q);
		
		$dataset->num_rows = $this->_conn->affected_rows;
	}
	
	
	/**
	 * Execute a raw query
	 * 
	 * Returns FALSE on failure. For successful SELECT, SHOW, DESCRIBE or 
	 * EXPLAIN queries mysqli_query() will return a result object. For other 
	 * successful queries mysqli_query() will return TRUE. 
	 * 
	 * @param string $string 
	 * @return mixed
	 */
	private function _rawExecute($string){
		// If there are additional arguments and a placeholder in the query.... sanitize and parse it.
		if(func_num_args() > 1 && strpos($string, '?') !== false){
			$argv = func_get_args();
			// Drop the first argument, that's the string...
			array_shift($argv);
			$offset = 0;
			foreach($argv as $k){
				// Find the next recurrence, (after the last offset if applicable)
				$pos = strpos($string, '?', $offset);
				
				if($k === null) $sanitizedk = 'NULL';
				else $sanitizedk = "'" . $this->_conn->escape_string($k) . "'";
				
				// Replace it with the sanitized version.
				$string = substr($string, 0, $pos) . $sanitizedk . substr($string, $pos+1);
				// NEXT
				$offset = $pos + strlen($sanitizedk);
			}
		}
		//echo $string . '<br/>'; // DEBUGGING //
		$res = $this->_conn->query($string);
		if($this->_conn->errno){
			// @todo Should this be implemented in the DMI_Exception?
			if(DEVELOPMENT_MODE) echo '<pre class="cae2_debug">' . $string . '</pre>';
			throw new DMI_Exception($this->_conn->error, $this->_conn->errno);
		}
		return $res;
	}
}

?>
