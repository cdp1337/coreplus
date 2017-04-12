<?php
/**
 * File for class DatasetWhereClause definition in the coreplus project
 * 
 * @package Core\Datamodel
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20131022.1742
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

namespace Core\Datamodel;


/**
 * The full WHERE clause for a dataset or Model.
 * 
 * This can be named, has a separator, and can contain multiple DatasetWhere items 
 * as well as multiple sub clauses.
 * 
 * @package Core\Datamodel
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class DatasetWhereClause{

	/**
	 * If multiple statements are contained herein, this is the separator of all statements.
	 *
	 * @var string
	 */
	private $_separator = 'AND';

	/**
	 * The array of statements (of groups) contained herein
	 *
	 * @var array
	 */
	private $_statements = array();


	/**
	 * The name of this group/clause.  Completely meaningless other than external lookups.
	 * (FUTURE FEATURE)
	 * @var string
	 */
	private $_name;

	/**
	 * @param string $name The name of this group/clause.  Completely meaningless other than external lookups.
	 */
	public function __construct($name = '_unnamed_'){
		$this->_name = $name;
	}

	/**
	 * Add a where statement by the three components.
	 * Only supports one where at a time, but useful for some of the tricky statements.
	 *
	 * @since 2.4.0
	 *
	 * @param $field
	 * @param $operation
	 * @param $value
	 */
	public function addWhereParts($field, $operation, $value){
		$c = new DatasetWhere();
		$c->field = $field;
		$c->op = $operation;
		$c->value = $value;
		$this->_statements[] = $c;

	}

	/**
	 * Add a where statement to this clause.
	 *
	 * DOES NOT SUPPORT addWhere('key', 'value'); format!!!
	 *
	 * @param $arguments
	 *
	 * @return bool
	 */
	public function addWhere($arguments){

		// <strike>Allow $k, $v to be passed in.</strike>
		//
		// This format is no longer supported at the low level!  DON'T DO IT!
		//
//		if(sizeof($arguments) == 2 && !is_array($arguments[0]) && !is_array($arguments[1])){
//
//			$this->_statements[] = new DatasetWhere($arguments[0] . ' = ' . $arguments[1]);
//			return true;
//		}

		// Allow another clause to be sent in, that will be set as a child of this one.
		if($arguments instanceof DatasetWhereClause){
			$this->_statements[] = $arguments;
			return true;
		}

		// Allow a child statement to be passed in.
		if($arguments instanceof DatasetWhere){
			$this->_statements[] = $arguments;
			return true;
		}

		// Allow just a plain ol string to be passed in too
		if(is_string($arguments)){
			$this->_statements[] = new DatasetWhere($arguments);
			return true;
		}

		// Otherwise, interpret each argument as its own entity.
		foreach($arguments as $a){
			if(is_array($a)){
				foreach($a as $k => $v){
					if(is_numeric($k)){
						// It's an indexed array of 'something = this or that';
						$this->_statements[] = new DatasetWhere($v);
					}
					else{
						// It's an associative array of key => 'this or that';
						$dsw = new DatasetWhere();
						$dsw->field = $k;
						$dsw->op    = '=';
						$dsw->value = $v;
						$this->_statements[] = $dsw;
					}
				}
			}
			elseif($a instanceof DatasetWhereClause){
				$this->_statements[] = $a;
			}
			elseif($a instanceof DatasetWhere){
				$this->_statements[] = $a;
			}
			else{
				$this->_statements[] = new DatasetWhere($a);
			}
		}
	}

	/**
	 * Shortcut function to add a subgroup to an existing group.
	 *
	 * @param $sep
	 * @param $arguments
	 */
	public function addWhereSub($sep, $arguments){
		$subgroup = new DatasetWhereClause();
		$subgroup->setSeparator($sep);
		$subgroup->addWhere($arguments);

		$this->addWhere($subgroup);
	}

	public function getStatements(){
		return $this->_statements;
	}

	public function setSeparator($sep){
		$sep = trim(strtoupper($sep));
		switch($sep){
			case 'AND':
			case 'OR':
				$this->_separator = $sep;
				break;
			default:
				throw new DMI_Exception('Invalid separator, [' . $sep . ']');
		}
	}

	public function getSeparator(){
		return $this->_separator;
	}

	/**
	 * Sometimes you just want a good'ol "flat" representation.
	 */
	public function getAsArray(){
		$children = array();
		foreach($this->_statements as $s){
			if($s instanceof DatasetWhereClause){
				$children[] = $s->getAsArray();
			}
			elseif($s instanceof DatasetWhere){
				if($s->field === null) continue;
				$children[] = $s->field . ' ' . $s->op . ' ' . $s->value;
			}
		}
		return array('sep' => $this->_separator, 'children' => $children);
	}

	/**
	 * Get any/all statements that have a field set to that which is requested.
	 *
	 * Useful for looking up to see if a specific column has been set in a where statement.
	 *
	 * @param string $fieldname The field to search for
	 * @return array
	 */
	public function findByField($fieldname){
		$matches = array();
		foreach($this->_statements as $s){
			if($s instanceof DatasetWhereClause){
				$matches = array_merge($matches, $s->findByField($fieldname));
			}
			elseif($s instanceof DatasetWhere){
				if($s->field == $fieldname) $matches[] = $s;
			}
		}

		return $matches;
	}

}