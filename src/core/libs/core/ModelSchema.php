<?php
/**
 * File for class ModelSchema definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20131022.1632
 * @copyright Copyright (C) 2009-2016  Charlie Powell
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


/**
 * A short teaser of what ModelSchema does.
 *
 * More lengthy description of what ModelSchema does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for ModelSchema
 * <h4>Example 1</h4>
 * <p>Description 1</p>
 * <code>
 * // Some code for example 1
 * $a = $b;
 * </code>
 *
 *
 * <h4>Example 2</h4>
 * <p>Description 2</p>
 * <code>
 * // Some code for example 2
 * $b = $a;
 * </code>
 *
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class ModelSchema extends Core\Datamodel\Schema{

	public function __construct($model = null){
		if($model !== null){
			$this->readModel($model);
		}
	}

	public function readModel($model){

		// Construct a general object so I can use the getKeySchemas method inside.
		$ref = new ReflectionClass($model);
		/** @var Model $obj */
		$obj = $ref->newInstanceWithoutConstructor();
		$schema = $obj->getKeySchemas();
		$indexes = $ref->getMethod('GetIndexes')->invoke(null);

		// RESET!
		$this->indexes     = [];
		$this->definitions = [];
		$this->order       = [];

		foreach($schema as $name => $def){
			$def['name'] = $name;
			$column = $this->_getColumnDefinition($def);

			$this->definitions[$name] = $column;

			// Aliases are skipped when considering the order of columns.
			if($def['type'] != Model::ATT_TYPE_ALIAS){
				$this->order[] = $name;
			}
		}


		foreach($indexes as $key => $dat){
			if(!is_array($dat)){
				// Models can be defined with a single element for its index.
				// This is an acceptable shorthand standard, but the lower level utilities
				// are expecting an array.
				$this->indexes[$key] = array($dat);
			}
			else{
				$this->indexes[$key] = $dat;
			}
		}
	}

	/**
	 * Get the SchemaColumn for a given Model Schema.
	 *
	 * @param array $def
	 *
	 * @return \Core\Datamodel\SchemaColumn
	 */
	private function _getColumnDefinition($def){
		$column = new \Core\Datamodel\SchemaColumn();
		$column->field     = $def['name'];
		$column->type      = $def['type'];
		$column->required  = $def['required'];
		$column->maxlength = $def['maxlength'];
		// Options have been moved below to support associative arrays.
		//$column->options   = $def['options'];
		$column->default   = $def['default'];
		$column->null      = $def['null'];
		$column->comment   = $def['comment'];

		if(isset($def['precision'])) $column->precision = $def['precision'];

		// Some defaults.
		if($column->type == Model::ATT_TYPE_STRING && !$column->maxlength){
			$column->maxlength = 255;
		}

		if($column->type == Model::ATT_TYPE_ID && !$column->maxlength){
			$column->maxlength = 15;
			$column->autoinc = true;
		}

		if($column->type == Model::ATT_TYPE_ID_FK){
			$column->maxlength = 15;
		}

		if($column->type == Model::ATT_TYPE_UUID){
			// A UUID is in the format of:
			// siteid-timestamp-randomhex
			// or [1-3 numbers] - [11-12 hex] - [4 hex]
			// a total of up to 21 digits.
			$column->maxlength = 21;
			$column->autoinc = false;
		}

		if($column->type == Model::ATT_TYPE_UUID_FK){
			// Mimic the UUID column.
			$column->maxlength = 21;
		}

		if($column->type == Model::ATT_TYPE_INT && !$column->maxlength){
			$column->maxlength = 15;
		}

		if($column->type == Model::ATT_TYPE_CREATED && !$column->maxlength){
			$column->maxlength = 15;
		}

		if($column->type == Model::ATT_TYPE_UPDATED && !$column->maxlength){
			$column->maxlength = 15;
		}

		if($column->type == Model::ATT_TYPE_DELETED && !$column->maxlength){
			$column->maxlength = 15;
		}

		if($column->type == Model::ATT_TYPE_SITE){
			$column->default = 0;
			$column->comment = 'The site id in multisite mode, (or 0 otherwise)';
			$column->maxlength = 15;
		}

		if($column->type == Model::ATT_TYPE_ALIAS){
			$column->aliasof = $def['alias'];
		}

		if($column->type == Model::ATT_TYPE_ENUM){
			// This logic is to support model definitions such as
			// 'foo' => [
			//          'type' => Model::ATT_TYPE_ENUM,
			//          'options' => ['key-1' => 'Key One', 'key-2' => 'Key TWO'],
			// ...
			if(!\Core\is_numeric_array($def['options'])){
				$column->options = array_keys($def['options']);
			}
			else{
				$column->options = $def['options'];
			}
		}

		// Is default not set?  Some columns would really like this to be!
		if($column->default === false){
			if($column->null){
				$column->default = null;
			}
			else{
				switch($column->type){
					case Model::ATT_TYPE_INT:
					case Model::ATT_TYPE_BOOL:
					case Model::ATT_TYPE_CREATED:
					case Model::ATT_TYPE_UPDATED:
					case Model::ATT_TYPE_DELETED:
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
		}

		// Handle the default encoding for strings.
		switch($column->type){
			case Model::ATT_TYPE_BOOL:
			case Model::ATT_TYPE_ENUM:
			case Model::ATT_TYPE_STRING:
			case Model::ATT_TYPE_TEXT:
			case Model::ATT_TYPE_UUID:
			case Model::ATT_TYPE_UUID_FK:
				$column->encoding = 'utf8';
				break;
		}

		return $column;
	}
}