<?php
/**
 * File for class DatasetWhere definition in the coreplus project
 * 
 * @package Core\Datamodel
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20131022.1745
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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
 * A short teaser of what DatasetWhere does.
 *
 * More lengthy description of what DatasetWhere does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for DatasetWhere
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
class DatasetWhere{
	public $field;
	public $op;
	public $value;

	public function __construct($arguments = null){
		if($arguments) $this->_parseWhere($arguments);
	}

	/**
	 * Parse a single where statement for the key, operation, and value.
	 *
	 * @param string $statement The where statement to parse and evaluate
	 * @return void
	 */
	private function _parseWhere($statement){
		// The user may have sent something like "blah = mep" or "datecreated < somedate"
		$valid = false;
		$operations = array('!=', '<=', '>=', '=', '>', '<', 'LIKE ', 'NOT LIKE', 'IN');

		// First, extract out the key.  This is the simplest thing to look for.
		$k = preg_replace('/^([^ !=<>]*).*/', '$1', $statement);

		// and the rest of the query...
		$statement = trim(substr($statement, strlen($k)));


		// Now I can sift through each operation and find the one that this query is.
		foreach($operations as $c){
			// The match MUST be the first character.
			if(($pos = strpos($statement, $c)) === 0){
				$op = $c;
				$statement = trim(substr($statement, strlen($op)));
				$valid = true;

				if($op == 'IN'){
					// the IN statement has a bit of an extra functionality.
					// This expects a comma separated list of values.
					// If there are any spaces or parentheses, remove them first.
					$statement = ltrim($statement, " \t\n\r\0\x0B(");
					$statement = rtrim($statement, " \t\n\r\0\x0B)");

					$statement = array_map('trim', explode(',', $statement));
				}
				elseif($statement == 'NULL'){
					// Allow NULL to be translated to literal null.
					$statement = null;
				}
				break;
			}
		}

		if($valid){
			$this->field = $k;
			$this->op = $op;
			$this->value = $statement;
		}
	}
}