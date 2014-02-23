<?php

namespace Core\Datamodel\Drivers\mysqli;
use Core\Datamodel\Schema;
use Core\Datamodel\SchemaColumn;

class mysqli_Schema extends Schema{

	private $_backend;

	public function __construct(mysqli_backend $backend, $table = null){
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

		// RESET!
		$this->indexes     = [];
		$this->definitions = [];
		$this->order       = [];

		// I need to load the indexes before I can load the columns.
		// This is because some of the column data is stored in indexes.
		$rs = $this->_backend->_rawExecute('read', 'SHOW INDEXES FROM `' . $table . '`');

		while($row = $rs->fetch_assoc()){

			if($row['Key_name'] == 'PRIMARY'){
				$key = 'primary';
			}
			elseif($row['Non_unique'] == '0'){
				$key = 'unique:' . $row['Key_name'];
			}
			else{
				$key = $row['Key_name'];
			}

			if(!isset($this->indexes[$key])){
				$this->indexes[$key] = array();
			}

			$this->indexes[$key][] = $row['Column_name'];
		}

		$rs = $this->_backend->_rawExecute('read', 'SHOW FULL COLUMNS FROM `' . $table . '`');

		while($row = $rs->fetch_assoc()){

			$column = $this->_getColumnDefinition($row);
			$name = $column->field;

			$this->definitions[$name] = $column;
			$this->order[] = $name;
		}
	}

	/**
	 * Get the resolved column schema from a row returned by the mysql command SHOW FULL COLUMNS.
	 *
	 * @param array $def
	 *
	 * @return SchemaColumn
	 */
	private function _getColumnDefinition($def){
		$column = new SchemaColumn();

		// The simple ones, these are 1-to-1 translations.
		$column->field     = $def['Field'];
		$column->comment   = $def['Comment'];


		switch($def['Type']){
			case "enum('0','1')":
			case "enum('1','0')":
				$column->type = \Model::ATT_TYPE_BOOL;
				break;
			case 'text':
			case 'longtext':
				$column->type = \Model::ATT_TYPE_TEXT;
				break;
			case 'datetime':
				$column->type = \Model::ATT_TYPE_ISO_8601_DATETIME;
				break;
			case 'timestamp':
				$column->type = \Model::ATT_TYPE_MYSQL_TIMESTAMP;
				break;
			case 'date':
				$column->type = \Model::ATT_TYPE_ISO_8601_DATE;
				break;
			case 'blob':
			case 'mediumblob':
			case 'longblob':
				$column->type = \Model::ATT_TYPE_DATA;
				break;
			case 'float':
				$column->type = \Model::ATT_TYPE_FLOAT;
				break;
		}

		// None of the above cases matched?  Maybe it's a more complex if statement.
		if($column->type === null){
			if(strpos($def['Type'], 'varchar(') !== false){
				$column->type = \Model::ATT_TYPE_STRING;
				$column->maxlength = (int)substr($def['Type'], 8, -1);
			}
			elseif(strpos($def['Type'], 'enum(') !== false){
				$column->type = \Model::ATT_TYPE_ENUM;
				$column->options = eval('return array(' . substr($def['Type'], 5, -1) . ');');
			}
			elseif(strpos($def['Type'], 'int(') !== false && $column->field == 'updated'){
				$column->type = \Model::ATT_TYPE_UPDATED;
				$column->maxlength = (int)substr($def['Type'], 4, -1);
			}
			elseif(strpos($def['Type'], 'int(') !== false && $column->field == 'created'){
				$column->type = \Model::ATT_TYPE_CREATED;
				$column->maxlength = (int)substr($def['Type'], 4, -1);
			}
			elseif(strpos($def['Type'], 'int(') !== false && $column->field == 'deleted'){
				$column->type = \Model::ATT_TYPE_DELETED;
				$column->maxlength = (int)substr($def['Type'], 4, -1);
			}
			elseif(
				strpos($def['Type'], 'int(') !== false &&
				isset($this->indexes['primary']) &&
				in_array($column->field, $this->indexes['primary']) &&
				sizeof($this->indexes['primary']) == 1
			){
				$column->type = \Model::ATT_TYPE_ID;
				$column->maxlength = (int)substr($def['Type'], 4, -1);
			}
			elseif(strpos($def['Type'], 'int(') !== false){
				$column->type = \Model::ATT_TYPE_INT;
				$column->maxlength = (int)substr($def['Type'], 4, -1);
			}

			elseif(strpos($def['Type'], 'decimal(') !== false){
				$column->type = \Model::ATT_TYPE_FLOAT;
				$column->precision = substr($def['Type'], 8, -1);
			}
			elseif(
				strpos($def['Type'], 'char(') !== false &&
				strpos($def['Type'], '21') !== false &&
				isset($this->indexes['primary']) &&
				in_array($column->field, $this->indexes['primary'])
			){
				$column->type = \Model::ATT_TYPE_UUID;
				$column->maxlength = 21;
			}
			elseif(
				strpos($def['Type'], 'char(') !== false &&
				strpos($def['Type'], '21') !== false
			){
				$column->type = \Model::ATT_TYPE_UUID_FK;
				$column->maxlength = 21;
			}
			else{
				// Well hmm...
				$column->type = 'text';
			}

			// AI could be attached to the Primary key, but mysql has its own declaration for that.
			if($def['Extra'] == 'auto_increment') $column->autoinc = true;
		}

		// Check if this is a key.
		if($def['Key'] == 'PRI'){
			$column->required = true;
		}
		elseif($def['Null'] == 'NO' && $def['Key'] == 'UNI'){
			$column->required = true;
		}

		// Default
		if($def['Default'] === null && $def['Null'] == 'YES'){
			// YAY, null is allowed!
			$column->default = null;
		}
		elseif($def['Default'] === false || $def['Default'] === null){
			// Default should be intelligent based on the column type!
			// Since the column type is already setup, I can just use that :)

			// For some reason MySQL 5.5.35 changed from reporting as false to reporting as null.
			// As such, NULL on a non-null column needs to be handled as well.

			switch($column->type){
				case \Model::ATT_TYPE_INT:
				case \Model::ATT_TYPE_BOOL:
				case \Model::ATT_TYPE_CREATED:
				case \Model::ATT_TYPE_UPDATED:
				case \Model::ATT_TYPE_DELETED:
				case \Model::ATT_TYPE_FLOAT:
					$column->default = 0;
					break;
				case \Model::ATT_TYPE_ISO_8601_DATE:
					$column->default = '0000-00-00';
					break;
				case \Model::ATT_TYPE_ISO_8601_DATETIME:
					$column->default = '0000-00-00 00:00:00';
					break;
				default:
					$column->default = '';
			}
		}
		else{
			$column->default = $def['Default'];
		}

		// Null?
		if($def['Null'] == 'YES') $column->null = true;
		else $column->null = false;

		// Handle the encoding type
		if($def['Collation'] === null){
			$column->encoding = null;
		}
		else{
			$column->encoding = substr($def['Collation'], 0, strpos($def['Collation'], '_'));
		}

		return $column;
	}
}

