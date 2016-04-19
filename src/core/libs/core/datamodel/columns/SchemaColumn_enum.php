<?php
/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 4/12/16
 * Time: 3:26 PM
 */

namespace Core\Datamodel\Columns;


class SchemaColumn_enum extends SchemaColumn {
	public function __construct(){
		// Defaults
		$this->type = \Model::ATT_TYPE_ENUM;
		$this->encoding = \Model::ATT_ENCODING_UTF8;
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
		if(!\Core\is_numeric_array($schema['options'])){
			$this->options = array_keys($schema['options']);
		}
		else{
			$this->options = $schema['options'];
		}
	}
}