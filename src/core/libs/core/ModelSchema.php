<?php
/**
 * File for class ModelSchema definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20131022.1632
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
			if($def['type'] == Model::ATT_TYPE_ALIAS){
				$this->aliases[$name] = $def['alias'];
			}
			elseif($def['type'] == Model::ATT_TYPE_META){
				$this->metas[$name] = $def;
			}
			else{
				$def['name'] = $name;
				$column = \Core\Datamodel\Columns\SchemaColumn::FactoryFromSchema($def);

				$this->definitions[$name] = $column;
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
}