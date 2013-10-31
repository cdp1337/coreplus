<?php
/**
 * File for class Schema definition in the coreplus project
 * 
 * @package Core\Datamodel
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20131022.1655
 * @copyright Copyright (C) 2009-2013  Author
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

namespace Core\Datamodel;


/**
 * A short teaser of what DMISchema does.
 *
 * More lengthy description of what DMISchema does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for Schema
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
 * @package Core\Datamodel
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class Schema {
	/**
	 * An associative array of SchemaColumn objects.
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

	/**
	 * Flat array of indexes on this schema
	 *
	 * @var array
	 */
	public $indexes = array();

	/**
	 * Get a column by order (int) or name
	 *
	 * @param string|int $column
	 * @return SchemaColumn|null
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
	 * Get an array of differences between this schema and another schema.
	 *
	 * @param Schema $schema
	 * @return array
	 */
	public function getDiff(Schema $schema){
		$diffs = array();

		// This will only check for incoming changes.  If schema B (that schema), has a column that
		// schema A does not, flag that as different.
		// If schema A has a column that schema B does not, ignore that change as it is not relevant.
		// Do the same for the order; ignore columns that A has but B does not.
		foreach($schema->definitions as $name => $dat){
			$thiscol = $this->getColumn($name);

			// This model doesn't have a column the other one has... DIFFERENCE!
			if(!$thiscol){
				$diffs[] = array(
					'title' => 'A does not have column ' . $name,
					'type' => 'column',
				);
				continue;
			}

			if(($colchange = $thiscol->getDiff($dat)) !== null){
				$diffs[] = array(
					'title' => 'Column ' . $name . ' does not match up: ' . $colchange,
					'type' => 'column',
				);
			}
		}

		$a_order = $this->order;
		foreach($this->definitions as $name => $dat){
			// If A has a column but B does not, drop that from the b order so the checks are accurate.
			if(!$schema->getColumn($name)) unset($a_order[array_search($name, $a_order)]);
		}

		// Check the order of them.
		if(implode(',', $a_order) != implode(',', $schema->order)){
			$diffs[] = array(
				'title' => 'Order of columns is different',
				'type' => 'order',
			);
		}

		// And lastly, the indexes.
		$thisidx = '';
		foreach($this->indexes as $name => $cols) $thisidx .= ';' . $name . '-' . implode(',', $cols);
		$thatidx = '';
		foreach($this->indexes as $name => $cols) $thatidx .= ';' . $name . '-' . implode(',', $cols);

		if($thisidx != $thatidx){
			$diffs[] = array(
				'title' => 'Indexes do not match up',
				'type' => 'index'
			);
		}

		return $diffs;
	}

	/**
	 * Test if this schema is identical (from a datastore perspective) to another model schema.
	 *
	 * Useful for reinstallations.
	 *
	 * @param Schema $schema
	 * @return bool
	 */
	public function isDataIdentical(Schema $schema){
		// Get a diff of the two.
		$diff = $this->getDiff($schema);

		// And see if there is something there.
		return !sizeof($diff);
	}
}

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
	public $type = null;

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
			$differences[] = 'default value (1)';
		}
		else{
			$differences[] = 'default value (2)';
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
}