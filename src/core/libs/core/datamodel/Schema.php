<?php
/**
 * File for class Schema definition in the coreplus project
 * 
 * @package Core\Datamodel
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20131022.1655
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
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class Schema {
	/** @var array All SchemaColumn objects. */
	public $definitions = [];

	/** @var array names of the columns in this schema. */
	public $order = [];

	/** @var array Flat array of indexes on this schema */
	public $indexes = [];
	
	/** @var array Flat array of aliases and their destination key */
	public $aliases = [];
	
	/** @var array Any meta fields that may be defined. */
	public $metas = [];

	/**
	 * Get a column by order (int) or name
	 *
	 * @param string|int $column
	 * @return Columns\SchemaColumn|null
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
				// Only complain about these if this column is not an alias.
				// Aliased columns are ignored otherwise.
				$diffs[] = array(
					'title' => 'A does not have column ' . $name,
					'column' => $name,
					'a' => null,
					'b' => $dat,
				);
				continue;
			}
			// Conversely, if *that* column is an alias, I can also safely skip it.
			if($dat->type == \Model::ATT_TYPE_ALIAS){
				continue;
			}

			if(($colchange = $thiscol->getDiff($dat)) !== null){
				$diffs[] = array(
					'title' => 'Column ' . $name . ' does not match up: ' . $colchange,
					'column' => $name,
					'a' => $thiscol,
					'b' => $dat,
				);
			}
		}

		$a_order = $this->order;
		foreach($this->definitions as $name => $dat){
			if(!$schema->getColumn($name)){
				// If A has a column but B does not, drop that from the b order so the checks are accurate.
				unset($a_order[array_search($name, $a_order)]);
			}
			elseif($schema->getColumn($name)->type == \Model::ATT_TYPE_ALIAS){
				// If the other column is an alias, it also doesn't matter.
				unset($a_order[array_search($name, $a_order)]);
			}
		}

		// Check the order of them.
		if(implode(',', $a_order) != implode(',', $schema->order)){
			$diffs[] = array(
				'title' => 'Order of columns is different',
				'column' => '*MANY*',
				'a' => null,
				'b' => null,
			);
		}

		// And lastly, the indexes.
		$thisidx = '';
		foreach($this->indexes as $name => $cols) $thisidx .= ';' . $name . '-' . implode(',', $cols);
		$thatidx = '';
		foreach($schema->indexes as $name => $cols) $thatidx .= ';' . $name . '-' . implode(',', $cols);

		if($thisidx != $thatidx){
			$diffs[] = array(
				'title' => 'Indexes do not match up',
				'column' => '*MANY*',
				'a' => null,
				'b' => null,
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