<?php
/**
 * MySQLi datamodel backend system
 *
 * @package Core\Datamodel
 * @since 1.9
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

class DMI_mysqli_backend implements DMI_Backend {

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
	 * A log of every query ran on this page.  Useful for debugging.
	 *
	 * @var array
	 */
	private $_querylog = array();


	/**
	 * Create a new connection to a mysql server.
	 *
	 * @param string $host
	 * @param string $user
	 * @param string $pass
	 * @param string $database
	 *
	 * @throws DMI_Authentication_Exception
	 * @throws DMI_ServerNotFound_Exception
	 * @throws DMI_Exception
	 *
	 * @return mixed|void
	 */
	public function connect($host, $user, $pass, $database){

		// Did the host come in with a port attached?
		if(strpos($host, ':') !== false) list($host, $port) = explode(':', $host);
		else $port = 3306;

		if(!class_exists('mysqli', false)){
			throw new DMI_Exception('Unable to locate the PHP MySQLi library.  Please switch to a supported driver or see http://us3.php.net/manual/en/book.mysqli.php for more information.');
		}

		$this->_conn = new mysqli();

		// Errors, SHHH!  I'll handle them manually!
		@$this->_conn->real_connect($host, $user, $pass, $database, $port);

		// Setting the correct exception would be useful!
		switch($this->_conn->errno){
			// Server not found
			case 2002:
				throw new DMI_ServerNotFound_Exception($this->_conn->error, $this->_conn->errno);
			// User not allowed
			case 1045:
				throw new DMI_Authentication_Exception($this->_conn->error, $this->_conn->errno);
			// No error, just break;
			case 0:
				break;
			// Everything else gets a generic error.
			default:
				throw new DMI_Exception($this->_conn->error, $this->_conn->errno);
		}
	}

	/**
	 * Execute a given Dataset object on this backend
	 *
	 * @param Dataset $dataset
	 *
	 * @throws DMI_Exception
	 */
	public function execute(Dataset $dataset){
		switch($dataset->_mode){
			case Dataset::MODE_GET:
				$this->_executeGet($dataset);
				break;
			case Dataset::MODE_INSERT:
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
				throw new DMI_Exception('Invalid dataset mode [' . $dataset->_mode . ']');
				break;
		}
	}

	/**
	 * @since 2.4.0
	 * @return mysqli|null
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

	public function createTable($table, $newschema){
		// Check if the table exists to begin with.
		// To increase performance, skip this check.  It's expected to be checked from the calling logic.
		//if($this->tableExists($table)){
		//	throw new DMI_Exception('Cannot create table [' . $table . '] as it already exists');
		//}

		if($newschema instanceof ModelSchema){
			$newmodelschema = $newschema;
			$newschema = new MySQLi_Schema($this, null);
			$newschema->fromModelSchema($newmodelschema);
		}
		elseif($newschema instanceof MySQLi_Schema){
			$newmodelschema = $newschema->toModelSchema();
		}
		else{
			throw new DMI_Exception('Unsupported object sent in for modifyTable: [' . get_class($newschema) . ']');
		}

		// Table doesn't exist, just do a simple create
		$q = 'CREATE TABLE `' . $table . '` ';
		$directives = array();
		foreach($newschema->definitions as $column){
			/** @var $column MySQLi_Schema_Column */
			$directives[] = '`' . $column->field . '` ' . $column->getColumnString();
		}


		foreach($newschema->indexes as $idx){
			$d = '';
			if($idx['name'] == 'PRIMARY'){
				$d .= 'PRIMARY KEY (`' . implode('`, `', $idx['columns']) . '`)';
			}
			elseif($idx['nonunique'] == 0){
				$d .= 'UNIQUE KEY `' . $idx['name'] . '` (`' . implode('`, `', $idx['columns']) . '`)';
			}
			else{
				$d .= 'KEY `' . $idx['name'] . '` (`' . implode('`, `', $idx['columns']) . '`)';
			}
			$directives[] = $d;
		}

		if(!sizeof($directives)){
			throw new DMI_Exception('Cowardly refusing to create a table [ ' . $table . ' ] with no column definitions!');
		}

		$q .= '( ' . implode(', ', $directives) . ' ) ';

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
	 * @param ModelSchema|MySQLi_Schema $newschema
	 *
	 * @return bool
	 * @throws DMI_Exception
	 * @throws DMI_Query_Exception
	 */
	public function modifyTable($table, $newschema){
		$changed    = false;

		// Check if the table exists to begin with.
		// To speed up the system, ignore this check.
		// External scripts should check for the presence anyway.
		//if(!$this->tableExists($table)){
		//	throw new DMI_Exception('Cannot modify table [' . $table . '] as it does not exist');
		//}

		// Also ignore this check, it should have been deleted at the end of the previous process.
		//if($this->tableExists('_tmptable')){
		//	// It's supposed to have been dropped by now....
		//	$this->_rawExecute('write', 'DROP TABLE _tmptable');
		//}


		// BEFORE I do all the exhaustive work of sifting through the table and what not, do a quick check to see if this table is unchanged.
		$oldschema = $this->_describeTableSchema($table);
		$oldmodelschema = $oldschema->toModelSchema();
		if($newschema instanceof ModelSchema){
			$newmodelschema = $newschema;
			$newschema = new MySQLi_Schema($this, null);
			$newschema->fromModelSchema($newmodelschema);
		}
		elseif($newschema instanceof MySQLi_Schema){
			$newmodelschema = $newschema->toModelSchema();
		}
		else{
			throw new DMI_Exception('Unsupported object sent in for modifyTable: [' . get_class($newschema) . ']');
		}
		// At this stage, (Just to recap)
		/** @var $oldschema MySQLi_Schema */
		/** @var $newschema MySQLi_Schema */
		/** @var $oldmodelschema ModelSchema */
		/** @var $newmodelschema ModelSchema */

		if($oldmodelschema->isDataIdentical($newmodelschema)) return false;

		//var_dump($table, $oldmodelschema->getDiff($newmodelschema)); // DEBUG //
		//var_dump('Model declaration says:', $newmodelschema); // DEBUG //
		//var_dump('Database says:', $oldmodelschema); // DEBUG //
		//die();

		// Table does exist... I need to do a merge of the data schemas.
		// Create a temp table to do the operations on.
		$this->_rawExecute('write', 'CREATE TEMPORARY TABLE _tmptable LIKE ' . $table);
		$this->_rawExecute('write', 'INSERT INTO _tmptable SELECT * FROM ' . $table);

		// The oldschema from above will get reloaded after each change.

		// To make the indexing logic a little easier...
		foreach($newmodelschema->indexes as $k => $v){
			if(!is_array($v)) $newmodelschema->indexes[$k] = array($v);
		}
		if(!isset($newmodelschema->indexes['primary'])) $newmodelschema->indexes['primary'] = false; // No primary key on this table.

		if(!sizeof($newmodelschema->definitions)){
			throw new DMI_Exception('No schema provided for table ' . $table);
		}

		// The simpliest way to handle this is to strip the auto_increment setting (if set),
		// any/all primary keys and indexes, do the operations, then reset the ai and indexes afterwards.

		// This will search for and strip the AI attribute.
		foreach($oldschema->definitions as $column){
			/** @var $column MySQLi_Schema_Column */
			if($column->extra == 'auto_increment'){
				$columndef = str_replace(' AUTO_INCREMENT', '', $column->getColumnString());
				// This statement will perform the alter statement, removing the AUTO INCREMENT attribute.
				$this->_rawExecute('write', 'ALTER TABLE `_tmptable` CHANGE `' . $column->field . '` `' . $column->field . '` ' . $columndef);
			}
		}

		// Now remove the indexes
		foreach($oldschema->indexes as $idx){
			if($idx['name'] == 'PRIMARY'){
				$this->_rawExecute('write', 'ALTER TABLE _tmptable DROP PRIMARY KEY');
			}
			else{
				$this->_rawExecute('write', 'ALTER TABLE `_tmptable` DROP INDEX `' . $idx['name'] . '`');
			}
		}

		// Useful for determining if I need to do weird voodoo for UUIDs...
		$originalindexes = $oldschema->indexes;
		$newcolumns      = array();

		// My simple counter.  Helps keep track of column order.
		$x = 0;
		// Now I can start running through the new schema and create/move the columns as necessary.
		foreach($newschema->definitions as $column){
			/** @var $column MySQLi_Schema_Column */

			// This is the column definition, (without the AI attribute, I'll get to that in a second).
			$columndef = str_replace(' AUTO_INCREMENT', '', $column->getColumnString());

			if(isset($oldschema->order[$x]) && $oldschema->order[$x] == $column->field){
				// Yay, the column is in the same order in the new schema as the old schema!
				// All I need to do here is just ensure the structure is appropriate.
				$q = 'ALTER TABLE _tmptable MODIFY COLUMN `' . $column->field . '` ' . $columndef;
				$neednewschema = false;
			}
			elseif(isset($oldschema->definitions[$column->field])){
				// Well, it's in the old schema, just not necessarily in the same order.
				// Move it along with the updated attributes.
				$q = 'ALTER TABLE _tmptable MODIFY COLUMN `' . $column->field . '` ' . $columndef . ' ';
				$q .= ($x == 0)? 'FIRST' : 'AFTER `' . $oldschema->order[$x-1] . '`';
				$neednewschema = true;
			}
			else{
				$newcolumns[$column->field] = $column;
				// It's a new column altogether!  ADD IT!
				$q = 'ALTER TABLE _tmptable ADD `' . $column->field . '` ' . $columndef . ' ';
				$q .= ($x == 0)? 'FIRST' : 'AFTER `' . $oldschema->order[$x-1] . '`';
				$neednewschema = true;
			}

			// Execute this query, increment X, and re-read the "old" structure.
			$this->_rawExecute('write', $q);
			$x++;
			if($neednewschema){
				// Only update the schema if the column order changed.
				// This is to increase performance a little.
				$oldschema = $this->_describeTableSchema('_tmptable');
			}
		}



		// Here's where some voodoo begins real quick.
		// If there is a new column that's added to an existing table,
		// that let's say has data already... and this new column is a UUID based column...
		// The easiest way to handle this is to convert that new column to an auto-inc column first.
		// This will allow the mysql engine to give a unique, (albeit not secure), ID to each record.
		// This works because IDs can never conflict with UUIDs due to formatting, (UUIDs have additional formatting),
		// and thus are acceptable replacements for existing data.
		if(isset($newschema->indexes['PRIMARY']) && sizeof($newschema->indexes['PRIMARY']['columns']) == 1){
			$col = $newschema->indexes['PRIMARY']['columns'][0];
			if(
				isset($newcolumns[$col]) &&
				isset($newmodelschema->definitions[$col]) &&
				$newmodelschema->definitions[$col]->type == Model::ATT_TYPE_UUID
			){
				// This is a UUID that didn't exist before!  AUTOINC-VOODOO-BEGIN!
				$column = $newcolumns[$col];
				$q = 'ALTER TABLE `_tmptable` CHANGE `' . $column->field . '` `' . $column->field . '` int(16) NOT NULL AUTO_INCREMENT key';
				$this->_rawExecute('write', $q);
				// This new column is now unique and meets the criteria of the PRIMARY KEY about to happen.... erm, again.... ;)
				// However, I need to reset this table back to how it was before this voodoo.
				$q = 'ALTER TABLE `_tmptable` CHANGE `' . $column->field . '` `' . $column->field . '` ' . $column->getColumnString();
				$this->_rawExecute('write', $q);
				$this->_rawExecute('write', 'ALTER TABLE _tmptable DROP PRIMARY KEY');
			}
		}

		// Columns have been setup; now to (re)create the indexes.
		foreach($newschema->indexes as $idx){
			if($idx['name'] == 'PRIMARY'){
				$q = 'ALTER TABLE `_tmptable` ADD PRIMARY KEY (`' . implode('`, `', $idx['columns']) . '`)';
			}
			elseif($idx['nonunique'] == 0){
				$q = 'ALTER TABLE `_tmptable` ADD UNIQUE KEY ' . $idx['name'] . ' (`' . implode('`, `', $idx['columns']) . '`)';
			}
			else{
				$q = 'ALTER TABLE `_tmptable` ADD INDEX ' . $idx['name'] . ' (`' . implode('`, `', $idx['columns']) . '`)';
			}

			$this->_rawExecute('write', $q);
		}

		// And lastly, search and re-add the AI attribute!
		// This has to be done last because it requires the PRIMARY KEY to already be set.
		foreach($newschema->definitions as $column){
			/** @var $column MySQLi_Schema_Column */
			if($column->extra == 'auto_increment'){
				$this->_rawExecute('write', 'ALTER TABLE `_tmptable` CHANGE `' . $column->field . '` `' . $column->field . '` ' . $column->getColumnString());
			}
		}

		$this->_rawExecute('write', 'DROP TABLE `' . $table . '`');
		$this->_rawExecute('write', 'CREATE TABLE `' . $table . '` LIKE _tmptable');
		$this->_rawExecute('write', 'INSERT INTO `' . $table . '` SELECT * FROM _tmptable');

		// Drop the table so it's ready for the next table.
		$this->_rawExecute('write', 'DROP TABLE _tmptable');
		return true;
	}

	public function readCount() {
		return $this->_reads;
	}

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
	 * Reverse of getSchemaFromType.  Will return valid code and values for a database schema.
	 * @param type $colschema
	 */
	/*public function _getTypeFromSchema($colschema){
		if(isset($colschema['Type'])) $t = $colschema['Type'];
		elseif(isset($colschema['type'])) $t = $colschema['type'];
		else return false;
		
		
	}*/

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

	public function _getTables(){
		trigger_error('mysqli_backend->_getTables is deprecated, please use showTables instead!', E_USER_DEPRECATED);
		return $this->showTables();
	}

	/**
	 * Get the schema for a given table.
	 *
	 * @param string $table
	 *
	 * @return MySQLi_Schema
	 */
	public function _describeTableSchema($table){
		$s = new MySQLi_Schema($this, $table);
		return $s;
	}

	/**
	 * Alias of _describeTableSchema
	 *
	 * Now that they're combined, there's no need to keep them separate.
	 *
	 * @param $table
	 *
	 * @return MySQLi_Schema
	 */
	public function _describeTableIndexes($table){
		$s = new MySQLi_Schema($this, $table);
		return $s;
	}


	private function _executeGet(Dataset $dataset){
		// Generate a query to run.
		$q = 'SELECT';
		if($dataset->uniquerecords) $q .= ' DISTINCT';
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

	private function _executeInsert(Dataset $dataset){
		// Generate a query to run.
		$q = "INSERT INTO `" . $dataset->_table . "`";

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


		// Execute this and populate the dataset appropriately.
		$res = $this->_rawExecute('write', $q);

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
		$res = $this->_rawExecute('write', $q);

		$dataset->num_rows = $this->_conn->affected_rows;
		$dataset->_data = array();
		// Inserts don't have any data, but do have an ID, (which mysql handles internally)
		//if($dataset->_idcol) $dataset->_idval = $this->_conn->insert_id;
	}

	private function _executeDelete(Dataset $dataset){
		$q = 'DELETE FROM `' . $dataset->_table . '`';
		$q .= $this->_parseWhere($dataset);

		if($dataset->_limit) $q .= ' LIMIT ' . $dataset->_limit;

		// Execute this and populate the dataset appropriately.
		$res = $this->_rawExecute('write', $q);

		$dataset->num_rows = $this->_conn->affected_rows;
	}

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
		$schema = $this->_describeTableSchema('_tmptable');

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
				$q .= ' CHANGE `' . $old . '` `' . $new . '` ' . $col->getColumnString();
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
					throw new DMI_Exception('Column [' . $old . '] does not exist in table [' . $table . '], unable to rename to [' . $new . ']');
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
		$q = '';

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
				$ws[] = '( ' . $this->_parseWhereClause($w) . ' )';
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
	 * Execute a raw query
	 *
	 * Returns FALSE on failure. For successful SELECT, SHOW, DESCRIBE or
	 * EXPLAIN queries mysqli_query() will return a result object. For other
	 * successful queries mysqli_query() will return TRUE.
	 *
	 * @param string type Either read or write.
	 * @param string $string The string to execute
	 * @return mixed
	 * @throws DMI_Query_Exception
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

		$debug = debug_backtrace();
		$callinglocation = array();
		$count = 0;
		$totalcount = 0;
		foreach($debug as $d){
			$class = (isset($d['class'])) ? $d['class'] : null;
			++$totalcount;

			if($class == 'DMI_mysqli_backend') continue;
			if($class == 'Dataset') continue;
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

		$start = microtime(true) * 1000;
		//echo $string . '<br/>'; // DEBUGGING //
		$res = $this->_conn->query($string);

		// Record this query!
		// This needs to include the query itself, what type it was, how long it took to execute,
		// any errors it produced, and where in the code it was called.
		$this->_querylog[] = array(
			'query' => $string,
			'type' => $type,
			'time' => round( (microtime(true) * 1000 - $start), 3),
			'errno' => $this->_conn->errno,
			'error' => $this->_conn->error,
			'caller' => $callinglocation,
		);

		// And increase the count.
		if($type == 'read') ++$this->_reads;
		else ++$this->_writes;

		if($this->_conn->errno){
			$e = new DMI_Query_Exception($this->_conn->error, $this->_conn->errno);
			$e->query = $string;
			throw $e;
		}
		return $res;
	}


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
	 * $records = DMI_mysqli_backend::ProcessSQLFile($file);
	 * foreach($records as $rec){
	 *     $rec->execute();
	 * }
	 * </code>
	 *
	 * @param $file
	 *
	 * @throws Exception
	 *
	 * @return array
	 */
	public static function ProcessSQLFile($file){
		if(is_scalar($file)){
			$file = \Core\Filestore\Factory::File($file);
		}
		elseif(!$file instanceof \Core\Filestore\File){
			throw new Exception('Please ensure that the argument for ProcessSQLFile is either a string or a valid File object!');
		}

		$contents = $file->getContents();

		$parser = new SQL_Parser_Dataset($contents, SQL_Parser::DIALECT_MYSQL);
		return $parser->parse();
	}

	/**
	 * Convert a raw SQL statement to a generic dataset, (if possible).
	 *
	 * @param string $rawstatement
	 *
	 * @throws Exception
	 *
	 * @return array
	 */
	public static function ProcessSQLStatement($rawstatement) {
		$parser = new SQL_Parser_Dataset($rawstatement, SQL_Parser::DIALECT_MYSQL);
		return $parser->parse();
	}
}


class MySQLi_Schema {


	/**
	 * An associative array of MySQLi_Schema_Column objects.
	 *
	 * @var array
	 */
	public $definitions = array();

	/**
	 * An indexed array of the names of the columns in this schema.
	 *
	 * @var array
	 */
	public $order = array();

	public $indexes = array();

	private $_backend;

	public function __construct(DMI_mysqli_backend $backend, $table = null){
		$this->_backend = $backend;

		if($table !== null){
			$this->readTable($table);
		}
	}

	/**
	 * Read a table and populate this schema's column definitions.
	 *
	 * Generally called automatically from the constructor.
	 *
	 * @param $table
	 */
	public function readTable($table){
		$rs = $this->_backend->_rawExecute('read', 'SHOW FULL COLUMNS FROM `' . $table . '`');

		while($row = $rs->fetch_assoc()){
			$column = new MySQLi_Schema_Column($this->_backend, $this);

			foreach($row as $k => $v){
				$lowkey = strtolower($k);
				$column->{$lowkey} = $v;
			}

			$this->definitions[$row['Field']] = $column;
			$this->order[] = $row['Field'];
		}

		// And now get the indexes.
		$rs = $this->_backend->_rawExecute('read', 'SHOW INDEXES FROM `' . $table . '`');

		while($row = $rs->fetch_assoc()){
			// Non_unique | Key_name | Column_name | Comment
			if(isset($this->indexes[$row['Key_name']])){
				// Just add a column.
				$this->indexes[$row['Key_name']]['columns'][] = $row['Column_name'];
			}
			else{
				$this->indexes[$row['Key_name']] = array(
					'name' => $row['Key_name'],
					'nonunique' => $row['Non_unique'],
					'comment' => $row['Comment'],
					'columns' => array($row['Column_name']),
				);
			}
		}
	}

	/**
	 * Get a column by order (int) or name
	 *
	 * @param string|int $column
	 * @return MySQLi_Schema_Column|null
	 */
	public function getColumn($column){
		// This will resolve an int to the column name.
		if(is_int($column)){
			if(isset($this->order[$column])) $column = $this->order[$column];
			else return null;
		}

		if(isset($this->definitions[$column])) return $this->definitions[$column];
		else return null;
	}

	/**
	 * Convert this mysqli-based schema to a backend agnostic version.
	 *
	 * @return ModelSchema
	 */
	public function toModelSchema(){
		$schema = new ModelSchema();

		foreach($this->definitions as $column){
			/** @var $column MySQLi_Schema_Column */
			$schema->definitions[$column->field] = $column->toModelSchemaColumn();
		}

		// The order is the easy part :)
		$schema->order = $this->order;

		$schema->indexes = array();
		foreach($this->indexes as $dat){
			if($dat['name'] == 'PRIMARY'){
				$schema->indexes['primary'] = $dat['columns'];
			}
			elseif($dat['nonunique'] == '0'){
				$schema->indexes['unique:' . $dat['name']] = $dat['columns'];
			}
			else{
				$schema->indexes[$dat['name']] = $dat['columns'];
			}
		}

		return $schema;
	}

	/**
	 * Convert a model schema to a mysqli-specifc version.
	 * @param ModelSchema $schema
	 */
	public function fromModelSchema(ModelSchema $schema){
		foreach($schema->indexes as $key => $dat){
			if($key == 'primary'){
				$this->indexes['PRIMARY'] = array(
					'name' => 'PRIMARY',
					'nonunique' => 0,
					'columns' => $dat,
				);
			}
			elseif(strpos($key, 'unique:') === 0){
				$n = substr($key, 7);
				$this->indexes[$n] = array(
					'name' => $n,
					'nonunique' => 0,
					'columns' => $dat,
				);
			}
			else{
				$this->indexes[$key] = array(
					'name' => $key,
					'nonunique' => 1,
					'columns' => $dat,
				);
			}
		}

		// The order is the easy part :)
		$this->order = $schema->order;

		foreach($schema->definitions as $column){
			/** @var $column ModelSchemaColumn */
			$newcol = new MySQLi_Schema_Column($this->_backend, $this);
			$newcol->fromModelSchemaColumn($column);

			$this->definitions[$column->field] = $newcol;
		}
	}
}

class MySQLi_Schema_Column {

	public $field; // 'field' => string 'VERSION'
	public $type; // 'type' => string 'varchar(255)'
	public $collation; // 'collation' => string 'latin1_swedish_ci'
	public $null; // 'null' => string 'YES'
	public $key; // 'key' => string ''
	public $default; // 'default' => null
	public $extra; // 'extra' => string ''
	public $privileges; // 'privileges' => string 'select,insert,update,references'
	public $comment; // 'comment' => string ''

	private $_backend;

	private $_parent;

	public function __construct(DMI_mysqli_backend $backend, MySQLi_Schema $parent){
		$this->_backend = $backend;
		$this->_parent = $parent;
	}

	/**
	 * Build the column string (for ALTER and CREATE statements) for this schema definition.
	 *
	 * @return string
	 */
	public function getColumnString(){

		$type = $this->type;
		$null = ($this->null == 'YES') ? 'NULL' : 'NOT NULL';

		if($this->null == 'YES' && $this->default === null) $default = 'NULL';
		elseif($this->default !== null) $default = "'" . $this->_backend->getConnection()->escape_string($this->default) . "'";
		else $default = false;

		$ai = ($this->extra == 'auto_increment');

		// INT(11) or ENUM('blah', 'foo').  Has maxlength with it.
		$q = $type;
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
		if($this->comment) $q .= ' COMMENT \'' . str_replace("'", "\\'", $this->comment) . '\'';

		// Yay, all done.
		return $q;
	}

	/**
	 * Convert this column to a database-agnostic version.
	 *
	 * @return ModelSchemaColumn
	 */
	public function toModelSchemaColumn(){
		$column = new ModelSchemaColumn();

		// Make a link to this just for reference.
		$index = $this->_parent->indexes;

		// The simple ones, these are 1-to-1 translations.
		$column->field = $this->field;
		$column->comment = $this->comment;
		$column->type = null; // This will get reset below.

		switch($this->type){
			case "enum('0','1')":
			case "enum('1','0')":
				$column->type = Model::ATT_TYPE_BOOL;
				break;
			case 'text':
			case 'longtext':
				$column->type = Model::ATT_TYPE_TEXT;
				break;
			case 'datetime':
				$column->type = Model::ATT_TYPE_ISO_8601_DATETIME;
				break;
			case 'timestamp':
				$column->type = Model::ATT_TYPE_MYSQL_TIMESTAMP;
				break;
			case 'date':
				$column->type = Model::ATT_TYPE_ISO_8601_DATE;
				break;
			case 'blob':
			case 'mediumblob':
			case 'longblob':
				$column->type = Model::ATT_TYPE_DATA;
				break;
			case 'float':
				$column->type = Model::ATT_TYPE_FLOAT;
				break;
		}

		// None of the above cases matched?  Maybe it's a more complex if statement.
		if($column->type === null){
			if(strpos($this->type, 'varchar(') !== false){
				$column->type = Model::ATT_TYPE_STRING;
				$column->maxlength = (int)substr($this->type, 8, -1);
			}
			elseif(strpos($this->type, 'enum(') !== false){
				$column->type = Model::ATT_TYPE_ENUM;
				$column->options = eval('return array(' . substr($this->type, 5, -1) . ');');
			}
			elseif(strpos($this->type, 'int(') !== false && $column->field == 'updated'){
				$column->type = Model::ATT_TYPE_UPDATED;
				$column->maxlength = (int)substr($this->type, 4, -1);
			}
			elseif(strpos($this->type, 'int(') !== false && $column->field == 'created'){
				$column->type = Model::ATT_TYPE_CREATED;
				$column->maxlength = (int)substr($this->type, 4, -1);
			}
			elseif(
				strpos($this->type, 'int(') !== false &&
				isset($index['PRIMARY']) &&
				in_array($column->field, $index['PRIMARY']['columns']) &&
				sizeof($index['PRIMARY']['columns']) == 1
			){
				$column->type = Model::ATT_TYPE_ID;
				$column->maxlength = (int)substr($this->type, 4, -1);
			}
			elseif(strpos($this->type, 'int(') !== false){
				$column->type = Model::ATT_TYPE_INT;
				$column->maxlength = (int)substr($this->type, 4, -1);
			}

			elseif(strpos($this->type, 'decimal(') !== false){
				$column->type = Model::ATT_TYPE_FLOAT;
				$column->precision = substr($this->type, 8, -1);
			}
			elseif(
				strpos($this->type, 'char(') !== false &&
				strpos($this->type, '21') !== false &&
				isset($index['PRIMARY']) &&
				in_array($column->field, $index['PRIMARY']['columns'])
			){
				$column->type = Model::ATT_TYPE_UUID;
				$column->maxlength = 21;
			}
			elseif(
				strpos($this->type, 'char(') !== false &&
				strpos($this->type, '21') !== false
			){
				$column->type = Model::ATT_TYPE_UUID_FK;
				$column->maxlength = 21;
			}
			else{
				// Well huhm...
				$column->type = 'text';
			}

			// AI could be attached to the Primary key, but mysql has its own declaration for that.
			if($this->extra == 'auto_increment') $column->autoinc = true;
		}

		// Check if this is a key.
		if($this->key == 'PRI'){
			$column->required = true;
		}
		elseif($this->null == 'NO' && $this->key == 'UNI'){
			$column->required = true;
		}

		// Default
		if($this->default === null && $this->null == 'YES'){
			// YAY, null is allowed!
			$column->default = null;
		}
		elseif($this->default === false){
			// Default should be intelligent based on the column type!
			// Since the column type is already setup, I can just use that :)
			switch($column->type){
				case Model::ATT_TYPE_INT:
				case Model::ATT_TYPE_BOOL:
				case Model::ATT_TYPE_CREATED:
				case Model::ATT_TYPE_UPDATED:
				case Model::ATT_TYPE_FLOAT:
					$column->default = 0;
					break;
				case Model::ATT_TYPE_ISO_8601_DATE:
					$column->default = '0000-00-00';
					break;
				case Model::ATT_TYPE_ISO_8601_DATETIME:
					$column->default = '0000-00-00 00:00:00';
					break;
				default:
					$column->default = '';
			}
		}
		else{
			$column->default = $this->default;
		}

		// Null?
		if($this->null == 'YES') $column->null = true;
		else $column->null = false;

		return $column;
	}

	public function fromModelSchemaColumn(ModelSchemaColumn $column){
		// The simple ones, these are 1-to-1 translations.
		$this->field = $column->field;
		$this->comment = $column->comment;

		switch($column->type){
			case Model::ATT_TYPE_BOOL:
				$this->type = "enum('0','1')";
				break;
			case Model::ATT_TYPE_ENUM:
				if(!sizeof($column->options)){
					throw new DMI_Exception('Invalid column definition for, type ENUM must include at least one option.');
				}

				$opts = array();
				foreach($column->options as $k => $opt){
					// Ensure that any single quotes are escaped out.
					$opts[] = str_replace("'", "\\'", $opt);
				}
				$this->type = "enum('" . implode("','", $opts) . "')";
				break;
			case Model::ATT_TYPE_FLOAT:
				if(!$column->precision){
					// No precision requested, just a standard float works here.
					$this->type = "float";
				}
				else{
					// DB-level precision requested.  This is not recommended in Core+, but still supported.
					$this->type = "decimal(" . $column->precision . ")";
				}
				break;
			case Model::ATT_TYPE_ID:
				$this->type = 'int(' . $column->maxlength . ')';
				// IDs are also auto_increment!
				$this->extra = 'auto_increment';
				$this->default = false;
				break;
			case Model::ATT_TYPE_ID_FK:
				$this->type = 'int(' . $column->maxlength . ')';
				break;
			case Model::ATT_TYPE_UUID:
				$this->type = 'char(21)';
				break;
			case Model::ATT_TYPE_UUID_FK:
				$this->type = 'char(21)';
				break;
			case Model::ATT_TYPE_STRING:
				$maxlength = ($column->maxlength)? $column->maxlength : 255; // It needs something...
				$this->type = "varchar($maxlength)";
				break;
			case Model::ATT_TYPE_TEXT:
				$this->type = "text";
				break;
			case Model::ATT_TYPE_DATA:
				$this->type = 'mediumblob';
				break;
			case Model::ATT_TYPE_INT:
			case Model::ATT_TYPE_CREATED:
			case Model::ATT_TYPE_UPDATED:
			case Model::ATT_TYPE_SITE:
				$this->type = 'int(' . $column->maxlength . ')';
				break;
			case Model::ATT_TYPE_ISO_8601_DATETIME:
				$this->type = 'datetime';
				break;
			case Model::ATT_TYPE_MYSQL_TIMESTAMP:
				$this->type = 'timestamp';
				break;
			case Model::ATT_TYPE_ISO_8601_DATE:
				$this->type = 'date';
				break;
			default:
				throw new DMI_Exception('Unsupported model type for [' . $column->type . ']');
				break;
		}

		// Null?
		if($column->null) $this->null = 'YES';
		else $this->null = 'NO';

		// Default
		if($column->default === null && $this->null == 'YES'){
			// YAY, null is allowed!
			$this->default = null;
		}
		elseif($column->default === false){
			// Default should be intelligent based on the column type!
			switch($column->type){
				case Model::ATT_TYPE_INT:
				case Model::ATT_TYPE_BOOL:
				case Model::ATT_TYPE_CREATED:
				case Model::ATT_TYPE_UPDATED:
				case Model::ATT_TYPE_FLOAT:
					$this->default = 0;
					break;
				default:
					$this->default = '';
			}
		}
		else{
			$this->default = $column->default;
		}

		// Lookup the indexes too.
		$index = $this->_parent->indexes;
		if(isset($index['PRIMARY']) && in_array($this->field, $index['PRIMARY']['columns'])){
			$this->key = 'PRI';
		}
		else{
			foreach($index as $key => $dat){
				if(in_array($this->field, $dat['columns'])){
					// Match found!  Is it unique?
					if(!$dat['nonunique']) $this->key = 'UNI';
				}
			}
		}

	}
}
