<?php
/**
 * MySQLi Schema
 *
 * @package Core\Datamodel
 * @since 1.9
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2017  Charlie Powell
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
use Core\Datamodel\Schema;

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
		$this->aliases     = [];

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
	 * @return \Core\Datamodel\Columns\SchemaColumn
	 */
	private function _getColumnDefinition($def){
		// First thing will be to generate a valid Schema array based on the mysqli data.
		$schema = [
			'type'      => false,
			'name'      => false,
			'required'  => false,
			'default'   => false,
			'null'      => false,
			'comment'   => false,
			'encoding'  => false,
			'maxlength' => false,
			'precision' => false,
			'options'   => false,
			'autoinc'   => false,
		];

		// The simple ones, these are 1-to-1 translations.
		$schema['name']     = $def['Field'];
		$schema['comment']  = $def['Comment'];
		$schema['autoinc']  = ($def['Extra'] == 'auto_increment');
		$schema['null']     = ($def['Null'] == 'YES');
		$schema['default']  = $def['Default'];
		$schema['required'] = (($def['Key'] == 'PRI') || ($def['Null'] == 'NO' && $def['Key'] == 'UNI'));
		
		if(!$schema['null'] && $schema['default'] === null){
			// TEXT types will sometimes incorrectly report that their default value is NULL
			// when NULL is disabled.
			// This is because those fields cannot properly set a 'None' default value without returning NULL.
			$schema['default'] = false;
		}
		
		switch($def['Type']){
			case "enum('0','1')":
			case "enum('1','0')":
				$schema['type'] = \Model::ATT_TYPE_BOOL;
				break;
			case 'text':
			case 'longtext':
				$schema['type'] = \Model::ATT_TYPE_TEXT;
				break;
			case 'datetime':
				$schema['type'] = \Model::ATT_TYPE_ISO_8601_DATETIME;
				break;
			case 'timestamp':
				$schema['type'] = \Model::ATT_TYPE_ISO_8601_DATETIME;
				break;
			case 'date':
				$schema['type'] = \Model::ATT_TYPE_ISO_8601_DATE;
				break;
			case 'blob':
			case 'mediumblob':
			case 'longblob':
				$schema['type'] = \Model::ATT_TYPE_DATA;
				break;
			case 'float':
				$schema['type'] = \Model::ATT_TYPE_FLOAT;
				break;
		}

		// None of the above cases matched?  Maybe it's a more complex if statement.
		if($schema['type'] === false){
			if(strpos($def['Type'], 'varchar(') !== false){
				// This field is a variable character field, "VARCHAR(123)"
				$schema['type'] = \Model::ATT_TYPE_STRING;
				$schema['maxlength'] = (int)substr($def['Type'], 8, -1);
			}
			elseif(strpos($def['Type'], 'enum(') !== false){
				$schema['type'] = \Model::ATT_TYPE_ENUM;
				$schema['options'] = eval('return array(' . substr($def['Type'], 5, -1) . ');');
			}
			elseif(strpos($def['Type'], 'int(') !== false && $def['Field'] == 'updated'){
				$schema['type'] = \Model::ATT_TYPE_UPDATED;
				$schema['maxlength'] = (int)substr($def['Type'], 4, -1);
			}
			elseif(strpos($def['Type'], 'int(') !== false && $def['Field'] == 'created'){
				$schema['type'] = \Model::ATT_TYPE_CREATED;
				$schema['maxlength'] = (int)substr($def['Type'], 4, -1);
			}
			elseif(strpos($def['Type'], 'int(') !== false && $def['Field'] == 'deleted'){
				$schema['type'] = \Model::ATT_TYPE_DELETED;
				$schema['maxlength'] = (int)substr($def['Type'], 4, -1);
			}
			elseif(strpos($def['Type'], 'int(') !== false && $def['Field'] == 'site'){
				$schema['type'] = \Model::ATT_TYPE_SITE;
				$schema['maxlength'] = (int)substr($def['Type'], 4, -1);
			}
			elseif(
				strpos($def['Type'], 'int(') !== false &&
				isset($this->indexes['primary']) &&
				in_array($def['Field'], $this->indexes['primary']) &&
				sizeof($this->indexes['primary']) == 1
			){
				$schema['type'] = \Model::ATT_TYPE_ID;
				$schema['maxlength'] = (int)substr($def['Type'], 4, -1);
			}
			elseif(strpos($def['Type'], 'int(') !== false){
				$schema['type'] = \Model::ATT_TYPE_INT;
				$schema['maxlength'] = (int)substr($def['Type'], 4, -1);
			}

			elseif(strpos($def['Type'], 'decimal(') !== false){
				$schema['type'] = \Model::ATT_TYPE_FLOAT;
				$schema['precision'] = substr($def['Type'], 8, -1);
			}
			// Version < 5.0.1 of UUIDs
			elseif(
				strpos($def['Type'], 'char(') !== false &&
				strpos($def['Type'], '32') !== false &&
				isset($this->indexes['primary']) &&
				in_array($def['Field'], $this->indexes['primary'])
			){
				$schema['type'] = \Model::ATT_TYPE_UUID;
				$schema['maxlength'] = 32;
			}
			elseif(
				strpos($def['Type'], 'char(') !== false &&
				strpos($def['Type'], '32') !== false
			){
				$schema['type'] = \Model::ATT_TYPE_UUID_FK;
				$schema['maxlength'] = 32;
			}
			// Version < 5.0.1 of UUIDs
			elseif(
				strpos($def['Type'], 'char(') !== false &&
				strpos($def['Type'], '21') !== false &&
				isset($this->indexes['primary']) &&
				in_array($def['Field'], $this->indexes['primary'])
			){
				$schema['type'] = \Model::ATT_TYPE_UUID;
				$schema['maxlength'] = 21;
			}
			elseif(
				strpos($def['Type'], 'char(') !== false &&
				strpos($def['Type'], '21') !== false
			){
				$schema['type'] = \Model::ATT_TYPE_UUID_FK;
				$schema['maxlength'] = 21;
			}
			else{
				// Well hmm...
				$schema['type'] = 'text';
			}
		}

		// Handle the encoding type
		if($def['Collation'] !== null){
			$schema['encoding'] = substr($def['Collation'], 0, strpos($def['Collation'], '_'));
		}

		// Now that everything has been translated to a standard array...
		return \Core\Datamodel\Columns\SchemaColumn::FactoryFromSchema($schema);
	}
}

