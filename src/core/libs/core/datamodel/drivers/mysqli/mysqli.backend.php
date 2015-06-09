<?php
/**
 * MySQLi Data Model backend system
 *
 * @package Core\Datamodel
 * @since 1.9
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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

namespace Core\Datamodel\Drivers\mysqli;

use Core\Datamodel\BackendInterface;
use Core\Datamodel\Dataset;
use Core\Datamodel\DatasetWhere;
use Core\Datamodel\DatasetWhereClause;
use Core\Datamodel\Schema;
use Core\Datamodel\SchemaColumn;
use Core\Filestore\Factory;
use Core\Filestore\File;

class mysqli_backend implements BackendInterface {

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
	 * @var \mysqli
	 */
	private $_conn = null;

	/**
	 * A log of every query ran on this page.  Useful for debugging.
	 *
	 * @var array
	 */
	private $_querylog = array();


	////////////////////\\\\\\\\\\\\\\\\\\\\
	////        PUBLIC METHODS          \\\\
	////////////////////\\\\\\\\\\\\\\\\\\\\

	/**
	 * Create a new connection to a mysql server.
	 *
	 * @param string $host
	 * @param string $user
	 * @param string $pass
	 * @param string $database
	 *
	 * @throws \DMI_Authentication_Exception
	 * @throws \DMI_ServerNotFound_Exception
	 * @throws \DMI_Exception
	 *
	 * @return mixed|void
	 */
	public function connect($host, $user, $pass, $database){

		// Did the host come in with a port attached?
		if(strpos($host, ':') !== false) list($host, $port) = explode(':', $host);
		else $port = 3306;

		if(!class_exists('mysqli', false)){
			throw new \DMI_Exception('Unable to locate the PHP MySQLi library.  Please switch to a supported driver or see http://us3.php.net/manual/en/book.mysqli.php for more information.');
		}

		$this->_conn = new \mysqli();

		// Errors, SHHH!  I'll handle them manually!
		@$this->_conn->real_connect($host, $user, $pass, $database, $port);

		// Setting the correct exception would be useful!
		switch($this->_conn->errno){
			// Server not found
			case 2002:
				throw new \DMI_ServerNotFound_Exception($this->_conn->error, $this->_conn->errno);
			// User not allowed
			case 1045:
				throw new \DMI_Authentication_Exception($this->_conn->error, $this->_conn->errno);
			// No error, just break;
			case 0:
				break;
			// Everything else gets a generic error.
			default:
				throw new \DMI_Exception($this->_conn->error, $this->_conn->errno);
		}

		// Set the encoding to UTF-8
		// This will prevent the mysql server from translating characters to their LATIN versions during the commit.
		$this->_conn->query("SET NAMES utf8");
	}

	/**
	 * Execute a given Dataset object on this backend
	 *
	 * @param Dataset $dataset
	 *
	 * @throws \DMI_Exception
	 */
	public function execute(Dataset $dataset){
		switch($dataset->_mode){
			case Dataset::MODE_GET:
				$this->_executeGet($dataset);
				break;
			case Dataset::MODE_INSERT:
			case Dataset::MODE_BULK_INSERT:
				$this->_executeInsert($dataset);
				break;
			case Dataset::MODE_UPDATE:
				$this->_executeUpdate($dataset);
				break;
			case Dataset::MODE_DELETE:
				$this->_executeDelete($dataset);
				break;
			case Dataset::MODE_COUNT:
				$this->_executeCount($dataset);
				break;
			case Dataset::MODE_ALTER:
				$this->_executeAlter($dataset);
				break;
			default:
				throw new \DMI_Exception('Invalid dataset mode [' . $dataset->_mode . ']');
				break;
		}
	}

	/**
	 * @since 2.4.0
	 * @return \mysqli|null
	 */
	public function getConnection(){
		return $this->_conn;
	}


	/**
	 * Check if a table exists in the database
	 *
	 * @param string $tablename
	 *
	 * @return bool
	 */
	public function tableExists($tablename){
		$q = "SHOW TABLES LIKE ?";
		$rs = $this->_rawExecute('read', $q, $tablename);
		return ($rs->num_rows > 0);
	}

	/**
	 * Create a table on this backend with the provided Schema.
	 *
	 * @param string $table
	 * @param Schema $newschema
	 *
	 * @return bool
	 *
	 * @throws \DMI_Exception
	 */
	public function createTable($table, Schema $newschema){
		// Check if the table exists to begin with.
		// To increase performance, skip this check.  It's expected to be checked from the calling logic.
		//if($this->tableExists($table)){
		//	throw new \DMI_Exception('Cannot create table [' . $table . '] as it already exists');
		//}

		// Table doesn't exist, just do a simple create
		$q = 'CREATE TABLE `' . $table . '` ';
		$directives = array();
		foreach($newschema->definitions as $column){
			/** @var SchemaColumn $column */

			if($column->aliasof){
				// Skip alias columns, as they do not need to be created.
				continue;
			}

			$directives[] = '`' . $column->field . '` ' . $this->_getColumnString($column);
		}


		foreach($newschema->indexes as $key => $idx){
			if($key == 'primary'){
				$directives[] = 'PRIMARY KEY (`' . implode('`, `', $idx) . '`)';
			}
			elseif(strpos($key, 'unique:') === 0){
				$key = substr($key, 7);
				$directives[] = 'UNIQUE KEY `' . $key . '` (`' . implode('`, `', $idx) . '`)';
			}
			else{
				$directives[] = 'KEY `' . $key . '` (`' . implode('`, `', $idx) . '`)';
			}
		}

		if(!sizeof($directives)){
			throw new \DMI_Exception('Cowardly refusing to create a table [ ' . $table . ' ] with no column definitions!');
		}

		$q .= '( ' . implode(', ', $directives) . ' ) ';

		// Don't forget to force this table to be UTF-8!
		$q .= ' CHARACTER SET utf8 COLLATE utf8_unicode_ci';

		//echo $q . '<br/>';
		// and GO!
		return ($this->_rawExecute('write', $q));
	}

	/**
	 * Modify a table to match a new schema.
	 *
	 * This is used to keep the database in sync with the code upon upgrades, installations and reinstalls.
	 *
	 * @param string $table
	 * @param Schema $schema
	 *
	 * @return bool
	 * @throws \DMI_Exception
	 * @throws \DMI_Query_Exception
	 */
	public function modifyTable($table, Schema $schema){
		// BEFORE I do all the exhaustive work of sifting through the table and what not, do a quick check to see if this table is unchanged.
		/** @var mysqli_Schema $old_schema */
		$old_schema = $this->describeTable($table);

		$differences = $old_schema->getDiff($schema);
		if(sizeof($differences) == 0){
			// If the schemas are identical, no need to change anything.
			// Indicate this with a false return status.
			return false;
		}

		//var_dump($table, $old_schema->getDiff($schema)); // DEBUG //
		//var_dump('Model declaration says:', $schema); // DEBUG //
		//var_dump('Database says:', $old_schema); // DEBUG //
		//die();

		// Table does exist... I need to do a merge of the data schemas.
		// Create a temp table to do the operations on.
		$this->_rawExecute('write', 'CREATE TEMPORARY TABLE _tmptable LIKE ' . $table);
		$this->_rawExecute('write', 'INSERT INTO _tmptable SELECT * FROM ' . $table);

		// The old_schema from above will get reloaded after each change.

		// To make the indexing logic a little easier...
		//if(!isset($schema->indexes['primary'])){
		//	// No primary key on this table.
		//	$schema->indexes['primary'] = false;
		//}

		if(!sizeof($schema->definitions)){
			throw new \DMI_Exception('No schema provided for table ' . $table);
		}

		// The simpliest way to handle this is to strip the auto_increment setting (if set),
		// any/all primary keys and indexes, do the operations, then reset the ai and indexes afterwards.

		// This will search for and strip the AI attribute.
		foreach($old_schema->definitions as $column){
			/** @var SchemaColumn $column */
			if($column->autoinc){
				$columndef = str_replace(' AUTO_INCREMENT', '', $this->_getColumnString($column));
				// This statement will perform the alter statement, removing the AUTO INCREMENT attribute.
				$this->_rawExecute('write', 'ALTER TABLE `_tmptable` CHANGE `' . $column->field . '` `' . $column->field . '` ' . $columndef);
			}
		}

		// Now remove the indexes completely.
		// These will get re-added after the modify logic runs through.
		foreach($old_schema->indexes as $key => $idx){
			if($key == 'primary'){
				$this->_rawExecute('write', 'ALTER TABLE `_tmptable` DROP PRIMARY KEY');
			}
			elseif(strpos($key, 'unique:') === 0){
				$key = substr($key, 7);
				$this->_rawExecute('write', 'ALTER TABLE `_tmptable` DROP INDEX `' . $key . '`');
			}
			else{
				$this->_rawExecute('write', 'ALTER TABLE `_tmptable` DROP INDEX `' . $key . '`');
			}
		}

		// Useful for determining if I need to do weird voodoo for UUIDs...
		$newcolumns      = array();

		// My simple counter.  Helps keep track of column order.
		$x = 0;
		// Now I can start running through the new schema and create/move the columns as necessary.
		foreach($schema->definitions as $column){
			/** @var SchemaColumn $column */

			// This is the column definition, (without the AI attribute, I'll get to that in a second).
			$columndef = str_replace(' AUTO_INCREMENT', '', $this->_getColumnString($column));
			$oldname   = isset($old_schema->definitions[$column->field]) ? $column->field : null;
			$newname   = $column->field;

			if($column->aliasof){
				// This column is an alias.
				// Check if it exists in the old schema (database), and rename it to the aliased column if necessary.

				if(!isset($old_schema->definitions[$column->field])){
					// This column doesn't exist in the database, no need to perform any changes!
					continue;
				}

				if(isset($old_schema->definitions[$column->aliasof])){
					// The new alias already exists in the new schema.
					// This could happen because the developer already ran this upgrade.
					continue;
				}

				$alias = false;
				foreach($schema->definitions as $checkcol){
					/** @var SchemaColumn $checkcol */
					if($checkcol->field == $column->aliasof){
						$alias = $checkcol;
						break;
					}
				}

				$columndef = str_replace(' AUTO_INCREMENT', '', $this->_getColumnString($alias));
				$newname   = $alias->field;

				//var_dump($column, $columndef, $alias); die();
			}


			if($oldname && isset($old_schema->order[$x]) && $old_schema->order[$x] == $oldname){
				// Yay, the column is in the same order in the new schema as the old schema!
				// All I need to do here is just ensure the structure is appropriate.
				$q = 'ALTER TABLE _tmptable CHANGE COLUMN `' . $oldname . '` `' . $newname . '` ' . $columndef;
				$neednewschema = ($oldname != $newname);
			}
			elseif($oldname){
				// Well, it's in the old schema, just not necessarily in the same order.
				// Move it along with the updated attributes.
				$q = 'ALTER TABLE _tmptable CHANGE COLUMN `' . $oldname . '` `' . $newname . '` ' . $columndef . ' ';
				$q .= ($x == 0)? 'FIRST' : 'AFTER `' . $old_schema->order[$x-1] . '`';
				$neednewschema = true;
			}
			elseif(!$column->aliasof){
				$newcolumns[$column->field] = $column;
				// It's a new column altogether!  ADD IT!
				$q = 'ALTER TABLE _tmptable ADD `' . $newname . '` ' . $columndef . ' ';
				$q .= ($x == 0)? 'FIRST' : 'AFTER `' . $old_schema->order[$x-1] . '`';
				$neednewschema = true;
			}
			else{
				// The column did not exist before and it is an alias... nothing needs done.
				$q = null;
				$neednewschema = false;
			}

			if($q){
				// Execute this query, increment X, and re-read the "old" structure.
				$this->_rawExecute('write', $q);
				//echo 'EXECUTE [' . $q . ']' . NL . '<br/>';
			}

			// Aliases do not count as steps.
			if(!$column->aliasof){
				$x++;
			}

			if($neednewschema){
				// Only update the schema if the column order changed.
				// This is to increase performance a little.
				$old_schema = $this->describeTable('_tmptable');
			}
		}



		// Here's where some voodoo begins real quick.
		// If there is a new column that's added to an existing table,
		// that let's say has data already... and this new column is a UUID based column...
		// The easiest way to handle this is to convert that new column to an auto-inc column first.
		// This will allow the mysql engine to give a unique, (albeit not secure), ID to each record.
		// This works because IDs can never conflict with UUIDs due to formatting, (UUIDs have additional formatting),
		// and thus are acceptable replacements for existing data.
		if(isset($schema->indexes['primary']) && sizeof($schema->indexes['primary']) == 1){
			$col = $schema->indexes['primary'][0];
			if(
				isset($newcolumns[$col]) &&
				isset($schema->definitions[$col]) &&
				$schema->definitions[$col]->type == \Model::ATT_TYPE_UUID
			){
				// This is a UUID that didn't exist before!  AUTOINC-VOODOO-BEGIN!
				$column = $newcolumns[$col];
				$q = 'ALTER TABLE `_tmptable` CHANGE `' . $column->field . '` `' . $column->field . '` int(16) NOT NULL AUTO_INCREMENT key';
				$this->_rawExecute('write', $q);
				// This new column is now unique and meets the criteria of the PRIMARY KEY about to happen.... erm, again.... ;)
				// However, I need to reset this table back to how it was before this voodoo.
				$q = 'ALTER TABLE `_tmptable` CHANGE `' . $column->field . '` `' . $column->field . '` ' . $this->_getColumnString($column);
				$this->_rawExecute('write', $q);
				$this->_rawExecute('write', 'ALTER TABLE _tmptable DROP PRIMARY KEY');
			}
		}

		// Columns have been setup; now to (re)create the indexes.
		foreach($schema->indexes as $key => $idx){
			if($key == 'primary'){
				$q = 'ALTER TABLE `_tmptable` ADD PRIMARY KEY (`' . implode('`, `', $idx) . '`)';
			}
			elseif(strpos($key, 'unique:') === 0){
				$key = substr($key, 7);
				$q = 'ALTER TABLE `_tmptable` ADD UNIQUE KEY ' . $key . ' (`' . implode('`, `', $idx) . '`)';
			}
			else{
				$q = 'ALTER TABLE `_tmptable` ADD INDEX ' . $key . ' (`' . implode('`, `', $idx) . '`)';
			}

			$this->_rawExecute('write', $q);
		}

		// And lastly, search and re-add the AI attribute!
		// This has to be done last because it requires the PRIMARY KEY to already be set.
		foreach($schema->definitions as $column){
			/** @var SchemaColumn $column */
			if($column->autoinc){
				$this->_rawExecute('write', 'ALTER TABLE `_tmptable` CHANGE `' . $column->field . '` `' . $column->field . '` ' . $this->_getColumnString($column));
			}
		}

		$this->_rawExecute('write', 'DROP TABLE `' . $table . '`');
		$this->_rawExecute('write', 'CREATE TABLE `' . $table . '` LIKE _tmptable');
		$this->_rawExecute('write', 'INSERT INTO `' . $table . '` SELECT * FROM _tmptable');

		// Drop the table so it's ready for the next table.
		$this->_rawExecute('write', 'DROP TABLE _tmptable');

		// Something changed, return true to indicate this.
		return true;
	}

	/**
	 * Drop a table from the system.
	 *
	 * @param $table
	 *
	 * @return bool
	 * @throws \DMI_Exception
	 */
	public function dropTable($table) {
		$q = 'DROP TABLE `' . $table . '`';
		// and GO!
		return ($this->_rawExecute('write', $q));
	}


	/**
	 * Get the number of reads that have been performed on this page load.
	 *
	 * @return int
	 */
	public function readCount() {
		return $this->_reads;
	}

	/**
	 * Get the number of writes that have been performed on this page load.
	 *
	 * @return int
	 */
	public function writeCount() {
		return $this->_writes;
	}

	/**
	 * Get the query log for this backend.
	 *
	 * @return array
	 */
	public function queryLog(){
		return $this->_querylog;
	}

	/**
	 * Get a flat array of table names currently available on this backend.
	 *
	 * @return array
	 */
	public function showTables(){
		$rs = $this->_rawExecute('read', 'SHOW TABLES');
		$ret = array();
		while($row = $rs->fetch_row()){
			$ret[] = $row[0];
		}
		return $ret;
	}

	/**
	 * @deprecated 2013.10  Please use showTables instead.
	 * @return array
	 */
	public function _getTables(){
		trigger_error('mysqli_backend->_getTables is deprecated, please use showTables instead!', E_USER_DEPRECATED);
		return $this->showTables();
	}

	/**
	 * Alias of describeTable
	 *
	 * @deprecated 2013.10.24  Please use describeTable instead.
	 *
	 * @param string $table
	 *
	 * @return MySQLi_Schema
	 */
	public function _describeTableSchema($table){
		return $this->describeTable($table);
	}

	/**
	 * Alias of describeTable
	 *
	 * Now that they're combined, there's no need to keep them separate.
	 *
	 * @deprecated 2013.10.24  Please use describeTable instead.
	 *
	 * @param $table
	 *
	 * @return MySQLi_Schema
	 */
	public function _describeTableIndexes($table){
		return $this->describeTable($table);
	}

	/**
	 * Execute a raw query
	 *
	 * Returns FALSE on failure. For successful SELECT, SHOW, DESCRIBE or
	 * EXPLAIN queries mysqli_query() will return a result object. For other
	 * successful queries mysqli_query() will return TRUE.
	 *
	 * @param string $type Either read or write.
	 * @param string $string The string to execute
	 * @return mixed
	 * @throws \DMI_Query_Exception
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function _rawExecute($type, $string){

		$arguments = func_get_args();

		// The first argument is required and is something else.
		$type = array_shift($arguments);

		// And the actual string itself
		$string = array_shift($arguments);

		// If there are additional arguments and a placeholder in the query.... sanitize and parse it.
		if(count($arguments) > 0 && strpos($string, '?') !== false){
			$offset = 0;
			foreach($arguments as $k){
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

		if(FULL_DEBUG || (DEVELOPMENT_MODE && $this->_reads+$this->_writes < 40)){
			// By skipping this in production, memory usage is cut by nearly 50% on über DB heavy pages!
			// (This occurs on pages that have more than 10k queries.
			$debug = debug_backtrace();
			$callinglocation = array();
			$count = 0;
			$totalcount = 0;
			foreach($debug as $d){
				$class = (isset($d['class'])) ? $d['class'] : null;
				++$totalcount;

				if($class == 'Core\\Datamodel\\Drivers\\mysqli\\mysqli_backend') continue;
				if($class == 'Core\\Datamodel\\Dataset') continue;
				if($class == 'Model') continue;

				$file = (isset($d['file'])) ? (substr($d['file'], strlen(ROOT_PDIR)+2)) : 'anonymous';
				$line = (isset($d['line'])) ? (':' . $d['line']) : '';
				$func = ($class !== null) ? ($d['class'] . $d['type'] . $d['function']) : $d['function'];

				$callinglocation[] = $file . $line . ', [' . $func . '()]';
				++$count;
				if($count >= 3 && sizeof($debug) >= $totalcount + 2){
					$callinglocation[] = '...';
					break;
				}
			}
		}
		else{
			$callinglocation = ['**SKIPPED**  Please enable FULL_DEBUG to see the calling stack.'];
		}


		$start = microtime(true) * 1000;
		//echo $string . '<br/>'; // DEBUGGING //
		$res = $this->_conn->query($string);

		if(DEVELOPMENT_MODE && is_object($res) && property_exists($res, 'num_rows')){
			// Tack on how many rows were selected or affected for debugging purposes.
			$rows = $res->num_rows;
		}
		else{
			$rows = null;
		}

		// Record this query!
		// This needs to include the query itself, what type it was, how long it took to execute,
		// any errors it produced, and where in the code it was called.
		$this->_querylog[] = array(
			'query'  => $string,
			'type'   => $type,
			'time'   => round( (microtime(true) * 1000 - $start), 3),
			'errno'  => $this->_conn->errno,
			'error'  => $this->_conn->error,
			'caller' => $callinglocation,
			'rows'   => $rows,
		);

		// And increase the count.
		if($type == 'read') ++$this->_reads;
		else ++$this->_writes;

		if($this->_conn->errno){
			$e = new \DMI_Query_Exception($this->_conn->error, $this->_conn->errno);
			$e->query = $string;
			throw $e;
		}
		return $res;
	}

	/**
	 * Describe the schema of a given table
	 *
	 * @param string $table Table name to query
	 *
	 * @return mysqli_Schema
	 */
	public function describeTable($table) {
		$schema = new mysqli_Schema($this, $table);
		return $schema;
	}


	////////////////////\\\\\\\\\\\\\\\\\\\\
	////        PRIVATE METHODS         \\\\
	////////////////////\\\\\\\\\\\\\\\\\\\\


	/**
	 * Parse and execute a GET/SELECT statement
	 *
	 * @param Dataset $dataset
	 */
	private function _executeGet(Dataset $dataset){
		// Generate a query to run.
		$q = 'SELECT';
		if($dataset->uniquerecords) $q .= ' DISTINCT';
		$ss = array();
		foreach($dataset->_selects as $s){
			// Check the escaping for this column.
			if(strpos($s, '.')){
				$s = '`' . str_replace('.', '`.`', trim($s)) . '`';
			}
			elseif($s != '*'){
				// `*` is not a valid column.
				// Everything else gets escaped.
				$s = '`' . $s . '`';
			}
			// else $s == '*', just don't change it

			$ss[] = $s;
		}
		// Nothing to select?  The user probably wanted '*'.
		if(!sizeof($ss)){
			$ss[] = '*';
		}
		$q .= ' ' . implode(', ', $ss);

		$q .= ' FROM `' . $dataset->_table . '`';
		$q .= $this->_parseWhere($dataset);

		if($dataset->_order){
			// Support keys as complex as "key DESC, value ASC"
			// and as simple as "sortnum"
			$orders = explode(',', $dataset->_order);
			$os = array();
			foreach($orders as $o){
				$o = trim($o);

				// Allow for mycolumn DESC or order ASC
				if(strpos($o, ' ') !== false) $os[] = '`' . substr($o, 0, strpos($o, ' ')) . '` ' . substr($o, strpos($o, ' ') + 1);
				// Allow for RAND() or other functions.
				elseif(strpos($o, '()') !== false) $os[] = $o;
				// Everything else just gets escaped normally.
				else $os[] = '`' . $o . '`';
			}
			$q .= ' ORDER BY ' . implode(', ', $os);
		}

		if($dataset->_limit) $q .= ' LIMIT ' . $dataset->_limit;

		// Execute this and populate the dataset appropriately.
		$res = $this->_rawExecute('read', $q);

		$dataset->num_rows = $res->num_rows;
		$dataset->_data = array();
		while($row = $res->fetch_assoc()){
			$dataset->_data[] = $row;
		}
	}

	/**
	 * Parse and execute an INSERT statement
	 * @param Dataset $dataset
	 */
	private function _executeInsert(Dataset $dataset){
		// Generate a query to run.
		$q = "INSERT INTO `" . $dataset->_table . "`";

		if($dataset->_mode == Dataset::MODE_BULK_INSERT){
			// New support for inserting multiple records at once.
			$keys  = [];
			$qvals = [];
			$i     = 0;
			foreach($dataset->_sets as $dat){
				++$i;
				$vals = array();

				if($i == 1){
					// Create the list of keys on the first pass.
					foreach($dat as $k => $v){
						$keys[] = "`$k`";
					}
					reset($dat);
				}

				foreach($dat as $v){
					if($v === null){
						$vals[] = 'NULL';
					}
					//elseif(is_int($v)){
					//	$vals[] = $v;
					//}
					else{
						$vals[] = "'" . $this->_conn->real_escape_string($v) . "'";
					}
				}

				$qvals[] = "( " . implode(', ', $vals) . " )";
			}

			$q .= " ( " . implode(', ', $keys) . " )";
			$q .= " VALUES ";
			$q .= implode(', ', $qvals);
		}
		else{
			$keys = array();
			$vals = array();
			foreach($dataset->_sets as $k => $v){
				$keys[] = "`$k`";
				if($v === null){
					$vals[] = 'NULL';
				}
				//elseif(is_int($v)){
				//	$vals[] = $v;
				//}
				else{
					$vals[] = "'" . $this->_conn->real_escape_string($v) . "'";
				}
			}

			$q .= " ( " . implode(', ', $keys) . " )";
			$q .= " VALUES ";
			$q .= " ( " . implode(', ', $vals) . " )";
		}

		// Execute this and populate the dataset appropriately.
		$this->_rawExecute('write', $q);

		$dataset->num_rows = $this->_conn->affected_rows;
		$dataset->_data = array();
		// Inserts don't have any data, but do have an ID, (which mysql handles internally)
		if($dataset->_idcol){
			// Unless it's set externally, say a UUID.
			if(!$dataset->_idval){
				$dataset->_idval = $this->_conn->insert_id;
			}
		}
	}

	/**
	 * Parse and execute an UPDATE statement
	 * @param Dataset $dataset
	 */
	private function _executeUpdate(Dataset $dataset){
		// Generate a query to run.
		$q = "UPDATE `" . $dataset->_table . "`";

		$sets = array();
		foreach($dataset->_sets as $k => $v){
			if($v === null){
				$valstr = 'NULL';
			}
			//elseif(is_int($v)){
			//	$valstr = $v;
			//}
			else{
				$valstr = "'" . $this->_conn->real_escape_string($v) . "'";
			}
			$sets[] = "`$k` = $valstr";
		}
		$q .= ' SET ' . implode(', ', $sets);

		$q .= $this->_parseWhere($dataset);

		if($dataset->_limit) $q .= ' LIMIT ' . $dataset->_limit;

		// Execute this and populate the dataset appropriately.
		$this->_rawExecute('write', $q);

		$dataset->num_rows = $this->_conn->affected_rows;
		$dataset->_data = array();
		// Inserts don't have any data, but do have an ID, (which mysql handles internally)
		//if($dataset->_idcol) $dataset->_idval = $this->_conn->insert_id;
	}

	/**
	 * Parse and execute a DELETE statement
	 * @param Dataset $dataset
	 */
	private function _executeDelete(Dataset $dataset){
		$q = 'DELETE FROM `' . $dataset->_table . '`';
		$q .= $this->_parseWhere($dataset);

		if($dataset->_limit) $q .= ' LIMIT ' . $dataset->_limit;

		// Execute this and populate the dataset appropriately.
		$this->_rawExecute('write', $q);

		$dataset->num_rows = $this->_conn->affected_rows;
	}

	/**
	 * Parse and execute a count on a given table with a given where clause
	 * @param Dataset $dataset
	 */
	private function _executeCount(Dataset $dataset){
		// Generate a query to run.
		$q = 'SELECT';
		// Count clauses only need a COUNT(*) for the select.
		$q .= ' COUNT(*) c';
		$q .= ' FROM `' . $dataset->_table . '`';
		$q .= $this->_parseWhere($dataset);

		// Execute this and populate the dataset appropriately.
		$res = $this->_rawExecute('read', $q);

		// Instead of using the traditional num_rows offered by mysql, I'll 
		// return the 1 "record" returned, which contains just 'c'.
		$row = $res->fetch_row();
		$dataset->num_rows = $row[0];
	}

	/**
	 * Process and execute an ALTER statement
	 * @param Dataset $dataset
	 *
	 * @throws \DMI_Exception
	 */
	private function _executeAlter(Dataset $dataset){
		$table = $dataset->_table;

		// Create a temp table to do the operations on.
		// This is a safety mechanism in case something goes horribly wrong with the query, (which tends to happen a lot).
		// If something blows up half way through, the temporary table will be hosed, but the original data and table
		// will be left intact unharmed.
		$this->_rawExecute('write', 'CREATE TEMPORARY TABLE _tmptable LIKE ' . $table);
		$this->_rawExecute('write', 'INSERT INTO _tmptable SELECT * FROM ' . $table);

		// Alter statements do not have where or set clauses, just structural changes!
		// I do however need the current schema so I know what changes there are to make.
		$schema = $this->describeTable('_tmptable');

		//var_dump($schema); die();

		// This is an alter statement, used primarily on installs and updates.
		// ALTER TABLE `controllers` CHANGE `ID` `id` INT( 11 ) NOT NULL AUTO_INCREMENT
		$q = 'ALTER TABLE `_tmptable`';

		// Set to true if there is something to do.
		$dosomething = false;

		foreach($dataset->_renames as $old => $new){
			// Renames are just simple renames, preserving all the previous attributes.
			// So, see if the old column is in the schema.
			$col = $schema->getColumn($old);
			if($col){
				$q .= ' CHANGE `' . $old . '` `' . $new . '` ' . $this->_getColumnString($col);
				$dosomething = true;
			}
			else{
				$col = $schema->getColumn($new);
				if($col){
					// No change needed, the column was already renamed.
					error_log('Column ' . $old . ' already renamed to ' . $new . ' in table ' . $table);
				}
				else{
					// Wait, it was never there to begin with, SO CONFUSED!
					throw new \DMI_Exception('Column [' . $old . '] does not exist in table [' . $table . '], unable to rename to [' . $new . ']');
				}
			}
		}

		//var_dump($q); die();

		if($dosomething){
			$this->_rawExecute('write', $q);
			$this->_rawExecute('write', 'DROP TABLE `' . $table . '`');
			$this->_rawExecute('write', 'CREATE TABLE `' . $table . '` LIKE _tmptable');
			$this->_rawExecute('write', 'INSERT INTO `' . $table . '` SELECT * FROM _tmptable');
			$this->_rawExecute('write', 'DROP TABLE `_tmptable`');
		}
		else{
			$this->_rawExecute('write', 'DROP TABLE `_tmptable`');
		}
	}

	/**
	 * Parse the where clause of a given dataset.
	 * This is abstracted away because it's common functionality between SELECT, UPDATE and DELETE.
	 *
	 * This method ONLY parses the WHERE clause and returns a valid SQL snippet.
	 * If no where clauses are found, a blank string is returned.
	 *
	 * @param Dataset $dataset
	 * @return string
	 */
	private function _parseWhere(Dataset $dataset){
		// If this dataset never initiated a where clause, there won't be any!
		if($dataset->_where === null) return '';

		/** @var $root DatasetWhereClause */
		$root = $dataset->_where;

		// If the root node doesn't have any WHERE statements... also easy enough!
		if(!sizeof($root->getStatements())) return '';

		// Well, I don't want to mess with that!  See if someone else does!
		$str = $this->_parseWhereClause($root);

		// No statements afterall? GREAT!
		if(!trim($str)) return '';

		// Ok ok....
		return ' WHERE ' . $str;
	}

	/**
	 * The recursive function that will return the actual SQL string from a group.
	 *
	 * @param DatasetWhereClause $group
	 * @return string
	 */
	private function _parseWhereClause(DatasetWhereClause $group){
		$statements = $group->getStatements();

		$ws = array();
		foreach($statements as $w){
			if($w instanceof DatasetWhereClause){
				// Recursively recurring recursion, RECURSE!
				$str = $this->_parseWhereClause($w);
				if($str){
					$ws[] = '( ' . $str . ' )';
				}
			}
			elseif($w instanceof DatasetWhere){
				// No field, what can I do?
				if(!$w->field) continue;

				$op = $w->op;

				// Null values should be IS NULL or IS NOT NULL, no sanitizing needed.
				if($w->value === null){
					$v = 'NULL';
					// NULL also has a fun trick with mysql.... = and != doesn't work :/
					if($op == '=') $op = 'IS';
					elseif($op == '!=') $op = 'IS NOT';

				}
				elseif($w->value === 1){
					// (int)1 is used sometimes to describe enum(1).
					$v = "'1'";
				}
				elseif($w->value === 0){
					// (int)0 is used sometimes to describe enum(0).
					$v = "'0'";
				}
				// Numbers are allowed with no sanitizing, they're just numbers.
				elseif(is_int($w->value)){
					$v = $w->value;
				}
				// IN statements allow an array to be passed in.  Check the values in the array and wrap them with parentheses.
				elseif(is_array($w->value) && $op == 'IN'){
					$vs = array();
					foreach($w->value as $bit){
						$vs[] = "'" . $this->_conn->real_escape_string($bit) . "'";
					}
					$v = '( ' . implode(',', $vs) . ' )';
				}
				else{
					$v = "'" . $this->_conn->real_escape_string($w->value) . "'";
				}
				$ws[] = '`' . $w->field . '` ' . $op . ' ' . $v;
			}
		}

		return implode(' ' . $group->getSeparator() . ' ', $ws);
	}

	/**
	 * Parse a column and get its mysql definition string.
	 *
	 * Useful for create table and modify table routines.
	 *
	 * @param SchemaColumn $column
	 *
	 * @return string
	 * @throws \DMI_Exception
	 */
	private function _getColumnString(SchemaColumn $column){
		$null = ($column->null) ? 'NULL' : 'NOT NULL';

		// Handle the default option
		if($column->null){
			if($column->default === null){
				$default = 'NULL';
			}
			else{
				$default = $column->default;
			}
		}
		else{
			if($column->default === null){
				$default = false;
			}
			else{
				$default = "'" . $this->getConnection()->escape_string($column->default) . "'";
			}
		}

		$ai = $column->autoinc;

		switch($column->type){
			case \Model::ATT_TYPE_BOOL:
				$type = "enum('0','1')";
				break;
			case \Model::ATT_TYPE_ENUM:
				if(!sizeof($column->options)){
					throw new \DMI_Exception('Invalid column definition for, type ENUM must include at least one option.');
				}

				$opts = array();
				foreach($column->options as $opt){
					// Ensure that any single quotes are escaped out.
					$opts[] = str_replace("'", "\\'", $opt);
				}
				$type = "enum('" . implode("','", $opts) . "')";
				break;
			case \Model::ATT_TYPE_FLOAT:
				if(!$column->precision){
					// No precision requested, just a standard float works here.
					$type = "float";
				}
				else{
					// DB-level precision requested.  This is not recommended in Core+, but still supported.
					$type = "decimal(" . $column->precision . ")";
				}
				break;
			case \Model::ATT_TYPE_ID:
				$type = 'int(' . $column->maxlength . ')';
				// IDs are also auto_increment!
				//$ai = true;
				//$default = false;
				break;
			case \Model::ATT_TYPE_ID_FK:
				$type = 'int(' . $column->maxlength . ')';
				break;
			case \Model::ATT_TYPE_UUID:
				$type = 'char(21)';
				break;
			case \Model::ATT_TYPE_UUID_FK:
				$type = 'char(21)';
				break;
			case \Model::ATT_TYPE_STRING:
				$maxlength = ($column->maxlength)? $column->maxlength : 255; // It needs something...
				$type = "varchar($maxlength)";
				break;
			case \Model::ATT_TYPE_TEXT:
				$type = "text";
				// A bug in MySQL 5.6 where text fields cannot have a default value!
				$default = false;
				break;
			case \Model::ATT_TYPE_DATA:
				$type = 'mediumblob';
				// A bug in MySQL 5.6 where text fields cannot have a default value!
				$default = false;
				break;
			case \Model::ATT_TYPE_INT:
			case \Model::ATT_TYPE_CREATED:
			case \Model::ATT_TYPE_UPDATED:
			case \Model::ATT_TYPE_DELETED:
			case \Model::ATT_TYPE_SITE:
				$type = 'int(' . $column->maxlength . ')';
				if($default === ''){
					// A bug in MySQL 5.6 where ints cannot have a '' default value.
					$default = false;
				}
				break;
			case \Model::ATT_TYPE_ISO_8601_DATETIME:
				$type = 'datetime';
				break;
			case \Model::ATT_TYPE_MYSQL_TIMESTAMP:
				$type = 'timestamp';
				break;
			case \Model::ATT_TYPE_ISO_8601_DATE:
				$type = 'date';
				break;
			case \Model::ATT_TYPE_ALIAS:
				return '__ALIAS__';
			default:
				throw new \DMI_Exception('Unsupported model type for [' . $column->type . ']');
				break;
		}

		// INT(11) or ENUM('blah', 'foo').  Has maxlength with it.
		$q = $type;

		// Collation!
		if($column->encoding == 'utf8'){
			$q .= ' CHARACTER SET utf8 COLLATE utf8_unicode_ci';
		}
		elseif($column->encoding){
			$q .= ' COLLATE ' . $column->encoding . '_swedish_ci';
		}
		// No else needed, INTs and the like do not contain encodings.

		// NULL or NOT NULL, either way it's needed.
		$q .= ' ' . $null;

		// ai tweaks how the default behaves slightly too.
		// namely, it doesn't allow it!
		if($ai){
			// Is there an AUTO_INCREMENT value here?
			$q .= ' AUTO_INCREMENT';
		}
		elseif($default !== false){
			// If there is a default option, tack that on.
			$q .= ' DEFAULT ' . $default;
		}

		// Don't forget the comments!
		if($column->comment) $q .= ' COMMENT \'' . str_replace("'", "\\'", $column->comment) . '\'';

		// Yay, all done.
		return $q;
	}



	////////////////////\\\\\\\\\\\\\\\\\\\\
	////     PUBLIC STATIC METHODS      \\\\
	////////////////////\\\\\\\\\\\\\\\\\\\\


	/**
	 * Process an SQL file and return an array of generic dataset objects.
	 *
	 * <h3>Usage Examples</h3>
	 *
	 *
	 * <h4>Example 1</h4>
	 * <p>Standard Usage</p>
	 * <code>
	 * // Some code for example 1
	 * $file = ROOT_PDIR . 'components/foo/upgrades/000-do-something-awesome.sql';
	 * $records = mysqli_backend::ProcessSQLFile($file);
	 * foreach($records as $rec){
	 *     $rec->execute();
	 * }
	 * </code>
	 *
	 * @param $file
	 *
	 * @throws \Exception
	 *
	 * @return array
	 */
	public static function ProcessSQLFile($file){
		if(is_scalar($file)){
			$file = Factory::File($file);
		}
		elseif(!$file instanceof File){
			throw new \Exception('Please ensure that the argument for ProcessSQLFile is either a string or a valid File object!');
		}

		$contents = $file->getContents();

		$parser = new \SQL_Parser_Dataset($contents, \SQL_Parser::DIALECT_MYSQL);
		return $parser->parse();
	}

	/**
	 * Convert a raw SQL statement to a generic dataset, (if possible).
	 *
	 * @param string $rawstatement
	 *
	 * @throws \Exception
	 *
	 * @return array
	 */
	public static function ProcessSQLStatement($rawstatement) {
		$parser = new \SQL_Parser_Dataset($rawstatement, \SQL_Parser::DIALECT_MYSQL);
		return $parser->parse();
	}
}
