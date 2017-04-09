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

class SchemaColumn {

	/**
	 * The field name or key name of this column
	 * @var string
	 */
	public $field;

	/**
	 * Specifies the data type contained in this column.  Must be one of the \Model::ATT_TYPE_* fields.
	 * @var string
	 */
	public $type = '__UNDEFINED__';

	/**
	 * Set to true to disallow blank values
	 * @var bool
	 */
	public $required = false;

	/**
	 * Maximum length in characters (or bytes), of data stored.
	 * @var bool|int
	 */
	public $maxlength = false;

	/**
	 * ATT_TYPE_ENUM column types expect a set of values.  This is defined here as an array.
	 * @var null|array
	 */
	public $options = null;

	/**
	 * Default value to use for this column
	 * @var bool|int|float|string
	 */
	public $default = false;

	/**
	 * Allow null values for this column.  If set to true, null is preserved as null.  False will change null values to blank.
	 * @var bool
	 */
	public $null = false;

	/**
	 * Comment to add onto the database column.  Useful for administrative comments for.
	 * @var string
	 */
	public $comment = '';

	/**
	 * ATT_TYPE_FLOAT supports precision for its data.  Should be set as a string such as "6,2" for 6 digits left of decimal,
	 * 2 digits right of decimal.
	 * @var null|string
	 */
	public $precision = null;

	/**
	 * Core+ allows data to be encrypted / decrypted on-the-fly.  This is useful for sensitive information such as
	 * credit card data or authorization credentials for external sources.  Setting this to true will store all
	 * information as encrypted, and allow it to be read decrypted.
	 * @var bool
	 */
	public $encrypted = false;

	/**
	 * Indicator if this column needs to be auto incremented from the datamodel.
	 * @var bool
	 */
	public $autoinc = false;

	/**
	 * The default encoding of this schema column.
	 * @var string|null
	 */
	public $encoding = null;

	/**
	 * @var null|string If this column is actually an alias of another column, that other column name is here.
	 */
	public $aliasof = null;

	/**
	 * @var mixed The value of this column according to the database
	 */
	public $valueDB = null;

	/**
	 * @var mixed The translated value of this column
	 */
	public $valueTranslated = null;

	/**
	 * @var mixed The untranslated value of this column
	 */
	public $value = null;

	/** @var null|\Model Parent Model this column is attached to, may or may not be set. */
	public $parent = null;
	
	/** @var array Array of form attributes for this column, generally set from the Model's metadata. */
	public $formAttributes = [];
	
	/** @var array|string|null Formatter method for this column, new as of 201611 */
	public $formatter = null;

	/**
	 * Check to see if this column is datastore identical to another column.
	 *
	 * @param SchemaColumn $col
	 *
	 * @return bool
	 */
	public function isIdenticalTo(SchemaColumn $col){
		$diff = $this->getDiff($col);

		return ($diff === null);
	}

	/**
	 * Get the actual differences between this schema and another column.
	 *
	 * Will return null if there are no differences.
	 *
	 * @param SchemaColumn $col
	 * @return null|string
	 */
	public function getDiff(SchemaColumn $col){

		// Do an array comparison.
		$thisarray = (array)$this;
		$colarray  = (array)$col;

		// If the schemas-to-arrays are identical, no need to proceed.
		if($thisarray === $colarray) return null;

		// What has changed?
		$differences = [];

		if($this->field != $col->field) $differences[] = 'field name';

		//if($this->required != $col->required) return false;
		if($this->maxlength != $col->maxlength) $differences[] = 'max length';
		if($this->null != $col->null) $differences[] = 'is null';
		if($this->comment != $col->comment) $differences[] = 'comment';
		if($this->precision != $col->precision) $differences[] = 'precision';
		if($this->autoinc !== $col->autoinc) $differences[] = 'auto increment';
		if($this->encoding != $col->encoding) $differences[] = 'encoding';

		// Default is a bit touchy because it can have database-specific defaults if not set locally.
		if($this->default === false){
			// I don't care what the database is, it'll pick its own defaults.
		}
		elseif($this->default === $col->default){
			// They're identical... yay!
		}
		elseif(\Core\compare_values($this->default, $col->default)){
			// They're close enough....
			// Core will check and see if val1 === (string)"12" and val2 === (int)12.
			// Consider it a fuzzy comparison that actually acknowledges the difference between NULL, "", and 0.
		}
		elseif($col->default === false && $this->default !== false){
			$differences[] = 'default value (#1)';
		}
		else{
			$differences[] = 'default value (#2)';
		}

		// If one is an array but not the other....
		if(is_array($this->options) != is_array($col->options)) $differences[] = 'options set/unset';

		if(is_array($this->options) && is_array($col->options)){
			// If they're both arrays, I need another way to check them.
			if(implode(',', $this->options) != implode(',', $col->options)) $differences[] = 'options changed';
		}

		// Type needs to allow for a few special cases.
		// Here, there are several columns that are all identical.
		$typematches = array(
			array(
				\Model::ATT_TYPE_INT,
				\Model::ATT_TYPE_UUID,
				\Model::ATT_TYPE_UUID_FK,
				\Model::ATT_TYPE_CREATED,
				\Model::ATT_TYPE_UPDATED,
				\Model::ATT_TYPE_DELETED,
				\Model::ATT_TYPE_SITE,
			)
		);

		$typesidentical = false;
		foreach($typematches as $types){
			if(in_array($this->type, $types) && in_array($col->type, $types)){
				// Found an identical pair!  break out to continue;
				$typesidentical = true;
				break;
			}
		}

		// If the types aren't found to be identical from above, then they have to actually be identical!
		if(!$typesidentical && $this->type != $col->type) $differences[] = 'type';

		if(sizeof($differences)){
			return implode(', ', $differences);
		}
		else{
			// Otherwise....
			return null;
		}
	}

	/**
	 * Get the value appropriate for INSERT statements.
	 * 
	 * @return string
	 */
	public function getInsertValue(){
		return $this->value;
	}

	/**
	 * Get the value appropriate for UPDATE statements.
	 *
	 * @return string
	 */
	public function getUpdateValue(){
		return $this->value;
	}

	/**
	 * Get the form element type that is the default for this type of field type.
	 * 
	 * @return string
	 */
	public function getFormElementType(){
		return 'text';
	}

	/**
	 * Get an array of the form element attributes for this column.
	 * 
	 * @return array
	 */
	public function getFormElementAttributes(){
		$i18nKey = '_MODEL_' . strtoupper(get_class($this->parent)) . '_';

		// NEW i18n support for Models!
		$title       = t('STRING' . $i18nKey . strtoupper($this->field));
		$description = t('MESSAGE' . $i18nKey . strtoupper($this->field));
		
		$na = $this->formAttributes;

		$na['title'] = $title;
		$na['description'] = $description;
		$na['value'] = $this->valueTranslated;
		$na['name'] = $this->field;
		
		return $na;
	}

	/**
	 * Get this column value as a valid form element.
	 * 
	 * @return \Core\Forms\FormElement|null
	 */
	public function getAsFormElement(){
		
		$attributes = $this->getFormElementAttributes();
		$type = isset($this->formAttributes['type']) ? $this->formAttributes['type'] : 'text';
		
		if($type == 'disabled'){
			// Disabled form elements do not render to anything.
			return null;
		}
		
		$el = \Core\Forms\FormElement::Factory($type, $attributes);
		
		// Ensure that the form element's "parent" is the same as this column's parent.
		// If it's null, then it'll be null there! (which is fine.)
		// Remember since this is an object, only the REFERENCE will be used.
		$el->parent = $this->parent;
		
		return $el;
	}

	/**
	 * Check if this value has changed between the database and working copy.
	 * 
	 * @return bool
	 */
	public function changed(){
		return ($this->valueDB != $this->value);
	}

	/**
	 * Simple method to mark this data as committed to the database.
	 * 
	 * This is expected to be called from the Model's save procedure.
	 */
	public function commit(){
		$this->valueDB = $this->value;
	}

	/**
	 * Load rendered schema data, usually from a Model declaration, for this column.
	 * 
	 * @param array $schema
	 * 
	 * @throws \Exception
	 */
	public function setSchema($schema){
		if($this->type != $schema['type']){
			throw new \Exception('Type mismatch, please use Factory to construct a correctly typed SchemaColumn (' . $this->type . ' vs ' . $schema['type'] . ')');
		}

		$this->required  = $schema['required'];
		// Options have been moved below to support associative arrays.
		//$column->options   = $def['options'];
		if($schema['default'] !== false){
			$this->default = $schema['default'];	
		}

		if($schema['null'] !== false) {
			$this->null = $schema['null'];
		}
		
		if($schema['comment'] !== false) {
			$this->comment = $schema['comment'];
		}

		if($schema['encoding'] !== false) {
			$this->encoding = $schema['encoding'];
		}
		
		if($schema['maxlength'] !== false){
			$this->maxlength = $schema['maxlength'];
		}

		if(isset($schema['precision'])){
			$this->precision = $schema['precision'];
		}
		
		// This field is set by the DMI but not by the Model.
		if(isset($schema['name'])){
			$this->field = $schema['name'];
		}
		
		if(isset($schema['autoinc'])){
			$this->autoinc = $schema['autoinc'];
		}
		
		if(isset($schema['formatter']) && $schema['formatter'] !== false){
			// New formatter support as of 201611, Core 6.2
			$this->formatter = $schema['formatter'];
		}
		
		// Set the value as the default by well... default.
		$this->value = $this->default;
		$this->valueTranslated = $this->default;
		// Do not set the database value, as at the time of loading, there is nothing in the database!
		
		// Set any of the form attributes that are defined in the schema, as well as the automatic/default ones.
		if(isset($schema['form'])){
			$this->formAttributes = array_merge($this->formAttributes, $schema['form']);
		}
		
		// Allow for some legacy/shortcut fields.
		if(isset($schema['formtype'])){
			$this->formAttributes['type'] = $schema['formtype'];
		}
		if(!isset($this->formAttributes['required'])){
			$this->formAttributes['required'] = $this->required;
		}
		if(!isset($this->formAttributes['maxlength']) && $this->maxlength){
			$this->formAttributes['maxlength'] = $this->maxlength;
		}
	}

	/**
	 * Set the value from the database for this column
	 *
	 * Handles all translations and conversions as necessary.
	 *
	 * @param mixed $val
	 */
	public function setValueFromDB($val){
		$this->valueDB = $val;
		$this->value = $val;

		if($this->encrypted){
			// Decrypt the value first.
			$val = \Model::DecryptValue($val);
		}

		$this->valueTranslated = $val;
	}

	/**
	 * Set the value from the application/userspace for this column
	 *
	 * Handles all translations and conversions as necessary.
	 *
	 * @param mixed $val
	 */
	public function setValueFromApp($val){
		$this->valueTranslated = $val;


		if($this->encrypted){
			// Decrypt the value last.
			$val = \Model::EncryptValue($val);
		}

		$this->value = $val;
	}

	/**
	 * @param string $type
	 *
	 * @return SchemaColumn
	 */
	public static function Factory($type){
		if(class_exists('\\Core\\Datamodel\\Columns\\SchemaColumn_' . $type)){
			$c = '\\Core\\Datamodel\\Columns\\SchemaColumn_' . $type;
		}
		else{
			$c = '\\Core\\Datamodel\\Columns\\SchemaColumn';
		}
		
		return new $c();
	}
	
	public static function FactoryFromSchema($schema){
		$c = self::Factory($schema['type']);
		$c->setSchema($schema);
		return $c;
	}
}