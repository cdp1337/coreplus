<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of mysql
 *
 * @author powellc
 */
class DMI_mysqli_backend implements DMI_Backend {
	
	/**
	 *
	 * @var mysqli
	 */
	private $_conn = null;
	
	public function connect($host, $user, $pass, $database){
		
		// Did the host come in with a port attached?
		if(strpos($host, ':') !== false) list($host, $port) = explode(':', $host);
		else $port = 3306;
		
		$this->_conn = new mysqli();
		$this->_conn->real_connect($host, $user, $pass, $database, $port);
		
		return ($this->_conn);
	}
	
	public function execute(Dataset $dataset){
		
	}
	
	public function tableExists($tablename){
		$q = "SHOW TABLES LIKE ?";
		$rs = $this->_rawExecute($q, $tablename);
		return ($rs->num_rows);
	}
	
	public function createTable($table, $schema){
		// Check if the table exists to begin with.
		if($this->tableExists($table)){
			throw new DMI_Exception('Cannot create table [' . $table . '] as it already exists');
		}
		// Table doesn't exist, just do a simple create
		$q = 'CREATE TABLE `' . $table . '` ';
		$directives = array();
		foreach($schema['schema'] as $column => $coldef){
			$d = '`' . $column . '`';
			
			if(!isset($coldef['type'])) $coldef['type'] = Model::ATT_TYPE_TEXT; // Default if not present.
			if(!isset($coldef['maxlength'])) $coldef['maxlength'] = false;
			
			// Will provide valid mysql string for the data type.
			$d .= ' ' . $this->_getSchemaFromType($coldef);
			
			if(!isset($coldef['null'])) $coldef['null'] = false;
			$d .= ' ' . (($coldef['null'])? 'NULL' : 'NOT NULL');
			
			if(!isset($coldef['default'])) $coldef['default'] = false;
			if($coldef['default']) $d .= ' DEFAULT ' . "'" . $coldef['default'] . "'";
			
			if(!isset($coldef['comment'])) $coldef['comment'] = false;
			if($coldef['comment']) $d .= ' COMMENT \'' . $coldef['comment'] . '\' ';
			
			if($coldef['type'] == Model::ATT_TYPE_ID) $d .= ' AUTO_INCREMENT';
			
			$directives[] = $d;
		}
		

		foreach($schema['indexes'] as $key => $idxdef){
			$d = '';
			if($key == 'primary'){
				$d .= 'PRIMARY KEY ';
			}
			elseif(strpos($key, 'unique:') !== false){
				// Unique keys should all have "unique:something" as the key to differentiate them from regular indexes.
				$d .= 'UNIQUE KEY `' . substr($key, 7) . '` ';
			}
			else{
				$d .= 'KEY `' . $key . '` ';
			}

			// Tack on the columns that are in this index.
			if(is_array($idxdef)){
				$d .= '(`' . implode('`, `', $idxdef) . '`)';
			}
			else{
				$d .= '(`' . $idxdef . '`)';
			}
			
			$directives[] = $d;
		}
		

		$q .= '( ' . implode(', ', $directives) . ' ) ';
		
		// and GO!
		return ($this->_rawExecute($q));
		
		
		//$q .= 'ENGINE=' . $tblnode->getAttribute('engine') . ' ';
		//$q .= 'DEFAULT CHARSET=' . $tblnode->getAttribute('charset') . ' ';
		//if($tblnode->getAttribute('comment')) $q .= 'COMMENT=\'' . $tblnode->getAttribute('comment') . '\' ';
		// @todo should AUTO_INCREMENT be available here?
	}
	
	public function modifyTable($table, $newschema){
		// Check if the table exists to begin with.
		if(!$this->tableExists($table)){
			throw new DMI_Exception('Cannot modify table [' . $table . '] as it does not exist');
		}
		
		// Table does exist... I need to do a merge of the data schemas.
		// Create a temp table to do the operations on.
		$this->_rawExecute('CREATE TEMPORARY TABLE _tmptable LIKE ' . $table);
		$this->_rawExecute('INSERT INTO _tmptable SELECT * FROM ' . $table);

		// My simple counter.  Helps keep track of column order.
		$x = 0;
		// This will contain the current table schema of the tmptable.
		// It will get reloaded after any change.
		$schema = $this->_describeTableSchema('_tmptable');
		
		// To make the indexing logic a little easier...
		foreach($newschema['indexes'] as $k => $v){
			if(!is_array($v)) $newschema['indexes'][$k] = array($v);
		}
		if(!isset($newschema['indexes']['primary'])) $newschema['indexes']['primary'] = false; // No primary key on this table.
		
		if(!sizeof($newschema['schema'])){
			throw new DMI_Exception('No schema provided for table ' . $table);
		}
		
		foreach($newschema['schema'] as $column => $coldef){
			
			
			if(!isset($coldef['type'])) $coldef['type'] = Model::ATT_TYPE_TEXT; // Default if not present.
			if(!isset($coldef['maxlength'])) $coldef['maxlength'] = false;
			if(!isset($coldef['null'])) $coldef['null'] = false;
			if(!isset($coldef['comment'])) $coldef['comment'] = false;
			if(!isset($coldef['default'])) $coldef['default'] = null;
			
			$type = $this->_getSchemaFromType($coldef);
			$null = ($coldef['null'])? 'NULL' : 'NOT NULL'; // Required for the query.
			$checknull = ($coldef['null'])? 'YES' : 'NO'; // Required for the schema check.
			$default = (($coldef['default'])? "'" . $this->_conn->escape_string($coldef['default']) . "'" : (($coldef['null'])? 'NULL' : "''"));
			$checkdefault = (($coldef['default'])? $coldef['default'] : (($coldef['null'])? 'NULL' : ''));
			
			
			//'type' => string 'string' (length=6)
			//'maxlength' => int 256
			//'required' => boolean true
			//'options' => array()
			//'comment' => string 'The define constant to map the value to on system load.'

			
			// coldef should now contain:
			// array(
			//   'field' => 'name_of_field',
			//   'type' => 'type definition, ie: int(11), varchar(32), etc',
			//   'null' => 'NO|YES',
			//   'key' => 'PRI|MUL|UNI|[blank]'
			//   'default' => default value
			//   'extra' => 'auto_increment|[blank]',
			//   'collation' => 'some collation type',
			//   'comment' => 'some comment',
			// );

			// Check that the current column is in the same location as in the database.
			if($schema['ord'][$x] != $column){
				// Is it even present?
				if(isset($schema['def'][$column])){
					// w00t, move it to this position.
					// ALTER TABLE `test` MODIFY COLUMN `fieldfoo` mediumint AFTER `something`
					$q = 'ALTER TABLE _tmptable MODIFY COLUMN `' . $column . '` ' . $type . ' ';
					$q .= ($x == 0)? 'FIRST' : 'AFTER ' . $schema['ord'][$x-1];
					$this->_rawExecute($q);

					// Moving the column will change the definition... reload that.
					$schema = $this->_describeTableSchema('_tmptable');
				}
				// No? Ok, create it.
				else{
					// ALTER TABLE `test` ADD `newfield` TEXT NOT NULL AFTER `something` 
					$q = 'ALTER TABLE _tmptable ADD `' . $column . '` ' . $type . ' ';
					$q .= $null . ' ';
					$q .= ($x == 0)? 'FIRST' : 'AFTER ' . $schema['ord'][$x-1];
					$this->_rawExecute($q);

					// Adding the column will change the definition... reload that.
					$schema = $this->_describeTableSchema('_tmptable');
				}
			}
			
			
			


			// Now the column should exist and be in the correct location.  Check its structure.
			// ALTER TABLE `test` CHANGE `newfield` `newfield` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL 
			// Check its AI and primary states first.
			if(
				$coldef['type'] == Model::ATT_TYPE_ID && ($newschema['indexes']['primary'] && in_array($column, $newschema['indexes']['primary'])) && 
				(!isset($schema['def'][$column]) || ($schema['def'][$column]['extra'] == '' && $schema['def'][$column]['key'] == ''))
			){
				// An AI value was added to the table.  I need to add that column as the primary key first, then
				// tack on the AI property.
				// ALTER TABLE `test` ADD PRIMARY KEY(`id`)
				$q = 'ALTER TABLE _tmptable ADD PRIMARY KEY (`' . $column . '`)';
				$this->_rawExecute($q);
				$q = 'ALTER TABLE _tmptable CHANGE `' . $column . '` `' . $column . '` ' . $type . ' ';
				$q .= $null . ' ';
				$q .= 'AUTO_INCREMENT';
				$this->_rawExecute($q);

				// And reload the schema.
				$schema = $this->_describeTableSchema('_tmptable');
			}

			// Now, check everything else.
			if(
				$type != $schema['def'][$column]['type'] ||
				$checknull != $schema['def'][$column]['null'] ||
				$checkdefault != $schema['def'][$column]['default'] ||
				//$coldef['collation'] != $schema['def'][$coldef['field']]['collation'] || 
				$coldef['comment'] != $schema['def'][$column]['comment']
			){
				$q = 'ALTER TABLE _tmptable CHANGE `' . $column . '` `' . $column . '` ';
				$q .= $type . ' ';
				//if($coldef['collation']) $q .= 'COLLATE ' . $coldef['collation'] . ' ';
				$q .= $null . ' ';
				$q .= 'DEFAULT ' . $default . ' ';
				if($coldef['comment']) $q .= 'COMMENT \'' . $coldef['comment'] . '\' ';
				//echo $q . '<br/>';
				$this->_rawExecute($q);

				// And reload the schema.
				$schema = $this->_describeTableSchema('_tmptable');
			}

			$x++;
		} // foreach($this->getElementFrom('column', $tblnode, false) as $colnode)

var_dump($column, $type, $coldef, $schema); die();
		// The columns should be done; onto the indexes.
		$schema = $this->_describeTableIndexes('_tmptable');
		foreach($this->getElementsFrom('index', $tblnode) as $idxnode){
			$idxdef = $this->elementToArray($idxnode);
			// Ensure that idxdef['column'] is an array if it's not.
			if(!is_array($idxdef['column'])) $idxdef['column'] = array($idxdef['column']);
			// @todo do all the indexes here.
			if($idxdef['name'] == 'PRIMARY'){
				$name = 'PRIMARY KEY';
			}
			elseif($idxdef['nonunique'] == 0){
				$name = 'UNIQUE `' . $idxdef['name'] . '`';
			}
			else{
				$name = 'INDEX `' . $idxdef['name'] . '`';
			}

			if(!isset($schema[$idxdef['name']])){
				$this->_rawExecute('ALTER TABLE `_tmptable` ADD ' . $name . ' (`' . implode('`, `', $idxdef['column']) . '`)');
				$schema = $this->_describeTableIndexes('_tmptable');
			}
			// There can only be 1!
			elseif(sizeof(array_diff($idxdef['column'], $schema[$idxdef['name']]['columns']))){
				$this->_rawExecute('ALTER TABLE `_tmptable` DROP ' . $name . ', ADD ' . $name . ' (`' . implode('`, `', $idxdef['column']) . '`)');
				$schema = $this->_describeTableIndexes('_tmptable');
			}
		} // foreach($this->getElementFrom('index', $tblnode, false) as $idxnode)


		// All operations should be completed now; move the temp table back to the original one.
		$this->_rawExecute('DROP TABLE `' . $table . '`');
		$this->_rawExecute('CREATE TABLE `' . $table . '` LIKE _tmptable');
		$this->_rawExecute('INSERT INTO `' . $table . '` SELECT * FROM _tmptable');

		// Drop the table so it's ready for the next table.
		$this->_rawExecute('DROP TABLE _tmptable');
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
		
		switch($coldef['type']){
			case Model::ATT_TYPE_BOOL:
				$type = " enum('0','1')";
				break;
			case Model::ATT_TYPE_ENUM:
				if(!(isset($coldef['options']) && is_array($coldef['options']) && sizeof($coldef['options']))){
					throw new DMI_Exception('Invalid column definition for ' . $table . '.' . $column . ', type ENUM must include at least one option.');
				}
				foreach($coldef['options'] as $k => $opt){
					// Ensure that any single quotes are escaped out.
					$coldef['options'][$k] = str_replace("'", "\\'", $opt);
				}
				$type = "enum('" . implode("','", $coldef['options']) . "')";
				break;
			case Model::ATT_TYPE_FLOAT:
				$type = "float";
				break;
			case Model::ATT_TYPE_ID:
				$type = "int(15)";
				break;
			case Model::ATT_TYPE_INT:
				$type = "int(15)";
				break;
			case Model::ATT_TYPE_STRING:
				$maxlength = ($coldef['maxlength'])? $coldef['maxlength'] : 256; // It needs something...
				$type = "varchar($maxlength)";
				break;
			case Model::ATT_TYPE_TEXT:
				$type = "text";
				break;
		}
		
		return $type;
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
	
	
	public function _getTables(){
		$rs = $this->_rawExecute('SHOW TABLES');
		$ret = array();
		while($row = $rs->fetch_row()){
			$ret[] = $row[0];
		}
		return $ret;
	}
	
	public function _describeTableSchema($table){
		$rs = $this->_rawExecute('SHOW FULL COLUMNS FROM `' . $table . '`');
		$tabledef = array();
		$tableord = array();
		
		while($row = $rs->fetch_assoc()){
			$tabledef[$row['Field']] = array();
			foreach($row as $k => $v){
				$tabledef[$row['Field']][strtolower($k)] = $v;
			}
			$tableord[] = $row['Field'];
		}
		return array('def' => $tabledef, 'ord' => $tableord);
	}
	
	public function _describeTableIndexes($table){
		$rs = $this->_rawExecute('SHOW INDEXES FROM `' . $table . '`');
		$def = array();
		
		while($row = $rs->fetch_assoc()){
			// Non_unique | Key_name | Column_name | Comment
			if(isset($def[$row['Key_name']])){
				// Add a column.
				$def[$row['Key_name']]['columns'][] = $row['Column_name'];
			}
			else{
				$def[$row['Key_name']] = array(
					'name' => $row['Key_name'],
					'nonunique' => $row['Non_unique'],
					'comment' => $row['Comment'],
					'columns' => array($row['Column_name']),
				);
			}
		}
		return $def;
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
		
		$res = $this->_conn->query($string);
		if($this->_conn->errno){
			throw new DMI_Exception($this->_conn->error, $this->_conn->errno);
		}
		return $res;
	}
}

?>
