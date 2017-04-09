<?php
/**
 * File for class Schema definition in the coreplus project
 * 
 * @package Core\Datamodel
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20131022.1655
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

namespace Core\Datamodel\Columns;


class SchemaColumn_enum extends SchemaColumn {
	public function __construct(){
		// Defaults
		$this->type = \Model::ATT_TYPE_ENUM;
		$this->encoding = \Model::ATT_ENCODING_UTF8;
		$this->formAttributes['type'] = 'select';
	}

	/**
	 * Load rendered schema data, usually from a Model declaration, for this column.
	 *
	 * @param array $schema
	 *
	 * @throws \Exception
	 */
	public function setSchema($schema){
		
		// Load all the data from the parent object
		parent::setSchema($schema);
		
		// And this one has options!
		if(\Core\is_numeric_array($schema['options'])){
			$this->options = [];
			foreach($schema['options'] as $k){
				$this->options[ $k ] = $k;
			}
		}
		else{
			$this->options = $schema['options'];
		}
	}

	/**
	 * Get an array of the form element attributes for this column.
	 *
	 * @return array
	 */
	public function getFormElementAttributes(){
		$na = parent::getFormElementAttributes();
		
		// Add special functionality here to allow passing in "this" as a valid reference for the "source" attribute.
		// This will allow the instantiated Model object as a whole to be used as a reference when retrieving options.
		if(isset($na['source'])){
			if(strpos($na['source'], 'this::') === 0){
				$na['source'] = [$this->parent, substr($na['source'], 6)];
			}
		}
		elseif(!isset($na['options'])){
			$opts = $this->options;
			if($this->null){
				$opts = array_merge(['' => '-- Select One --'], $opts);
			}
			$na['options'] = $opts;
		}

		return $na;
	}
}