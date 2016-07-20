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