<?php
/**
 * File for class SQL_Parser_Dataset definition in the tenant-visitor project
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20131010.1643
 * @package Core\Datamodel
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


/**
 * A Core-specific build of the SQL_Parser customized to return a valid Dataset object instead.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * <h4>Example 1</h4>
 * <p>Standard Usage</p>
 * <code>
 * // Some code for example 1
 * $contents = '-- some sql code';
 * $parser = new SQL_Parser_Dataset($contents, SQL_Parser::DIALECT_MYSQL);
 * $datasets = $parser->parse();
 * foreach($datasets as $ds){
 *     $ds->execute();
 * }
 * </code>
 *
 * 
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class SQL_Parser_Dataset extends SQL_Parser {

	/**
	 * @access  public
	 *
	 * @param bool $subSelect
	 *
	 * @return Core\Datamodel\Dataset
	 */
	public function parseSelect($subSelect = false)
	{
		$tree = new Core\Datamodel\Dataset();
		$tree->_mode = Core\Datamodel\Dataset::MODE_GET;
		$this->getTok();
		if ($this->token == 'distinct') {
			$tree->uniquerecords = true;
			$this->getTok();
		}

		// SELECT columns
		$selects = [];
		while (1) {
			$exp = $this->parseCondition()['args'][0];

			if(isset($exp['name'])){
				$this->raiseError('Datasets do not support functions as SELECT parameters!');
			}

			if(isset($exp['value'])){
				$selects[] = $exp['value'];
			}
			elseif(isset($exp['column'])){
				$selects[] = $exp['column'];
			}
			else{
				$this->raiseError('Unknown SELECT object');
			}

			if ($this->token != ',') {
				break;
			}
			$this->getTok();
		}
		$tree->select($selects);

		// FROM
		if ($this->token != 'from') {
			return $tree;
		}

		$this->getTok();
		$table = $this->parseFrom();
		if(sizeof($table['table_references']['table_factors']) != 1){
			$this->raiseError('Datasets only support one table at a time!');
		}
		$tree->table($table['table_references']['table_factors'][0]['table']);

		// WHERE

		// GROUP BY

		// HAVING

		// ORDER BY

		// LIMIT

		// UNION
		while ($this->token != ';' && ! is_null($this->token) && (!$subSelect || $this->token != ')')
			&& $this->token != ')') {
			switch ($this->token) {
				case 'where':
					$this->getTok();
					$clause = $this->parseWhereCondition();
					if (false === $clause) {
						return $clause;
					}
					$tree->_where = $clause;
					break;
				case 'order':
					$this->getTok();
					if ($this->token != 'by') {
						$this->raiseError('Expected "by"');
					}
					$this->getTok();
					while ($this->token == 'ident') {
						$arg = $this->lexer->tokText;
						$this->getTok();
						if ($this->token == '.') {
							$this->getTok();
							if ($this->token == 'ident') {
								$arg .= '.'.$this->lexer->tokText;
							} else {
								$this->raiseError('Expected a column name');
							}
						} else {
							$this->lexer->pushBack();
						}
						$col = $arg;
						//$col = $this->lexer->tokText;
						$this->getTok();
						if (isset($this->synonyms[$this->token])) {
							$order = $this->synonyms[$this->token];
							if (($order != 'asc') && ($order != 'desc')) {
								$this->raiseError('Unexpected token');
							}
							$this->getTok();
						} else {
							$order = 'asc';
						}
						if ($this->token == ',') {
							$this->getTok();
						}
						$tree->order($col . ' ' . $order);
					}
					break;
				case 'limit':
					$this->getTok();
					if ($this->token != 'int_val') {
						$this->raiseError('Expected an integer value');
					}
					$length = $this->lexer->tokText;
					$start = 0;
					$this->getTok();
					if ($this->token == ',') {
						$this->getTok();
						if ($this->token != 'int_val') {
							$this->raiseError('Expected an integer value');
						}
						$start  = $length;
						$length = $this->lexer->tokText;
						$this->getTok();
					}
					$tree->limit($start . ' ' . $length);
					break;
				case 'group':
					$this->getTok();
					if ($this->token != 'by') {
						$this->raiseError('Expected "by"');
					}
					$this->getTok();
					while ($this->token == 'ident') {
						$arg = $this->lexer->tokText;
						$this->getTok();
						if ($this->token == '.') {
							$this->getTok();
							if ($this->token == 'ident') {
								$arg .= '.'.$this->lexer->tokText;
							} else {
								$this->raiseError('Expected a column name');
							}
						} else {
							$this->lexer->pushBack();
						}
						$col = $arg;
						//$col = $this->lexer->tokText;
						$this->getTok();
						if ($this->token == ',') {
							$this->getTok();
						}
						$this->raiseError('@TODO group by statements not supported yet');
						$tree['group_by'][] = $col;
					}
					break;
				default:
					$this->raiseError('Unexpected clause');
			}
		}
		return $tree;
	}

	/**
	 * @access  public
	 * @return array|Core\Datamodel\Dataset|bool
	 */
	public function parseInsert()
	{
		$this->getTok();
		if ($this->token != 'into') {
			$this->raiseError('Expected "into"');
		}

		$tree = new Core\Datamodel\Dataset();
		$tree->_mode = Core\Datamodel\Dataset::MODE_INSERT;
		$column_names = false;

		$this->getTok();
		if ($this->token != 'ident') {
			$this->raiseError('Expected table name');
		}

		$tree->table($this->lexer->tokText);

		$this->getTok();
		if ($this->token == '(') {
			$results = $this->getParams($values, $types);
			if (false === $results) {
				return $results;
			} elseif (sizeof($values)) {
				$column_names = $values;
			}
			$this->getTok();
		}

		if ($this->token != 'values') {
			$this->raiseError('Expected "values"');
		}

		$valsets = [];
		// loop over all (value[, ...])[,(value[, ...]), ...]
		while (1) {
			// get opening brace '('
			$this->getTok();
			if ($this->token != '(') {
				$this->raiseError('Expected "("');
			}
			$results = $this->getParams($values, $types);
			if (false === $results) {
				return $results;
			}
			if ($column_names && sizeof($column_names) != sizeof($values)) {
				$this->raiseError('field/value mismatch');
			}
			if (! sizeof($values)) {
				$this->raiseError('No fields to insert');
			}
			if(!$column_names){
				$this->raiseError('Datasets require inserts to be associative!');
			}

			$v = [];
			foreach ($values as $key => $value) {
				$v[ $column_names[$key] ] = $value;
			}
			$valsets[] = $v;

			$this->getTok();
			if ($this->token != ',') {
				break;
			}
		}

		if(sizeof($valsets) > 1){
			// I need to return multiple datasets, each one is one insert.
			$ret = [];
			foreach($valsets as $set){
				$clone = clone $tree;
				$clone->_sets = $set;
				$ret[] = $clone;
			}

			return $ret;
		}
		else{
			$tree->_sets = $valsets[0];
			return $tree;
		}
	}

	/**
	 * UPDATE tablename SET (colname = (value|colname) (,|WHERE searchclause))+
	 *
	 * @todo This is incorrect.  multiple where clauses would parse
	 * @access  public
	 * @return mixed array parsed update on success, otherwise Error
	 */
	public function parseUpdate()
	{
		$tree = array('command' => 'update');
		$this->getTok();
		$tree['tables'][] = $this->parseIdentifier('table');

		if ($this->token != 'set') {
			$this->raiseError('Expected "set"');
		}

		while (true) {
			$this->getTok();
			$set['column'] = $this->parseIdentifier();

			if ($this->token != '=') {
				$this->raiseError('Expected =');
			}

			$this->getTok();
			$set['column'] = $this->parseCondition();

			$tree['sets'][] = $set;

			if ($this->token != ',') {
				break;
			}
		}

		if ($this->token == 'from') {
			$this->getTok();
			$tree['from'] = $this->parseFrom();
		}

		if ($this->token == 'where') {
			$this->getTok();
			$clause = $this->parseCondition();
			if (false === $clause) {
				return $clause;
			}
			$tree['where_clause'] = $clause;
		}

		return $tree;
	}

	/**
	 * DELETE FROM tablename WHERE searchclause
	 *
	 * @access  public
	 * @return mixed array parsed delete on success, otherwise Error
	 */
	public function parseDelete()
	{
		$tree = new Core\Datamodel\Dataset();
		$tree->_mode = Core\Datamodel\Dataset::MODE_DELETE;

		$this->getTok();
		if ($this->token == 'from') {
			// FROM is not required
			$this->getTok();
		}

		if ($this->token != 'ident') {
			$this->raiseError('Expected a table name');
		}
		$tree->table($this->lexer->tokText);

		$this->getTok();
		if ($this->token == 'where') {
			// WHERE is not required
			$this->getTok();
			$clause = $this->parseWhereCondition();
			if (false === $clause) {
				return $clause;
			}
			$tree->_where = $clause;
		}

		return $tree;
	}

	/**
	 *
	 * @return  array   parsed data
	 * @uses  SQL_Parser::$lexeropts
	 * @uses  SQL_Parser::$lexer
	 * @uses  SQL_Parser::$symbols
	 * @uses  SQL_Parser::$token
	 * @uses  SQL_Parser::raiseError()
	 * @uses  SQL_Parser::getTok()
	 * @uses  SQL_Parser::parseSelect()
	 * @uses  SQL_Parser::parseUpdate()
	 * @uses  SQL_Parser::parseInsert()
	 * @uses  SQL_Parser::parseDelete()
	 * @uses  SQL_Parser::parseCreate()
	 * @uses  SQL_Parser::parseDrop()
	 * @uses  SQL_Parser_Lexer
	 * @uses  SQL_Parser_Lexer::$symbols
	 * @access  public
	 *
	 * @throws Exception
	 */
	public function parseQuery()
	{
		$tree = array();

		// get query action
		$this->getTok();
		while (1) {
			$branch = array();
			switch ($this->token) {
				case null:
					// null == end of string
					break;
				case 'select':
					$branch = $this->parseSelect();
					break;
				case 'update':
					$branch = $this->parseUpdate();
					break;
				case 'insert':
					$branch = $this->parseInsert();
					break;
				case 'delete':
				case 'truncate':
					$branch = $this->parseDelete();
					break;
				case 'create':
					$this->raiseError('Unsupported action: ' . $this->token);
					break;
				case 'drop':
					$this->raiseError('Unsupported action: ' . $this->token);
					break;
				case 'unlock':
					$this->raiseError('Unsupported action: ' . $this->token);
					break;
				case 'lock':
					$this->raiseError('Unsupported action: ' . $this->token);
					break;
				case '(':
					$this->raiseError('Unsupported action: ' . $this->token);
					break;
				default:
					$this->raiseError('Unknown action: ' . $this->token);
			}

			if(is_array($branch)){
				$tree = array_merge($tree, $branch);
			}
			else{
				$tree[] = $branch;
			}


			// another command separated with ; or a UNION
			if ($this->token == ';') {
				$this->getTok();
				if (! is_null($this->token)) {
					continue;
				}
			}

			// another command separated with ; or a UNION
			if ($this->token == 'UNION') {
				$this->getTok();
				continue;
			}

			// end? unknown?
			break;
		}

		return $tree;
	}

	/**
	 * parses conditions usually used in WHERE
	 *
	 * @return  array   parsed condition
	 * @uses  SQL_Parser::$token
	 * @uses  SQL_Parser::$lexer
	 * @uses  SQL_Parser::getTok()
	 * @uses  SQL_Parser::raiseError()
	 * @uses  SQL_Parser::getParams()
	 * @uses  SQL_Parser::isFunc()
	 * @uses  SQL_Parser::parseFunctionOpts()
	 * @uses  SQL_Parser::parseCondition()
	 * @uses  SQL_Parser::isReserved()
	 * @uses  SQL_Parser::isOperator()
	 * @uses  SQL_Parser::parseSelect()
	 * @uses  SQL_Parser_Lexer::$tokText
	 * @uses  SQL_Parser_Lexer::unget()
	 * @uses  SQL_Parser_Lexer::pushBack()
	 */
	public function parseWhereCondition()
	{
		$clause = new Core\Datamodel\DatasetWhereClause();

		$laststatement = new Core\Datamodel\DatasetWhere();

		while (true) {
			// parse the first argument
			if ($this->token == 'not') {
				$this->getTok();
			}

			if ($this->token == '(') {
				$this->getTok();
				$clause->addWhere($this->parseWhereCondition());
				if ($this->token != ')') {
					$this->raiseError('Expected ")"');
				}
				$this->getTok();
			} elseif ($this->isFunc()) {
				$result = $this->parseFunctionOpts();
				if (false === $result) {
					return $result;
				}
				var_dump($result); die('Umm, now what?');
				$clause['args'][] = $result;
			} elseif ($this->token == 'ident') {
				// This is a column key name
				$parsed = $this->parseIdentifier();
				$laststatement->field = $parsed['column'];
			} else {
				$arg = $this->lexer->tokText;

				// Translate NULL to the actual null value.
				if($arg === 'NULL' || $arg === 'null') $arg = null;

				$laststatement->value = $arg;
				$this->getTok();
			}

			if (! $this->isOperator()) {
				// no operator, return (after I append the final last statement)
				if($laststatement->field){
					$clause->addWhere($laststatement);
				}

				return $clause;
			}

			// parse the operator
			$op = $this->token;
			if ($op == 'not') {
				$this->getTok();
				$not = 'not ';
				$op = $this->token;
			} else {
				$not = '';
			}

			$this->getTok();
			switch ($op) {
				case 'is':
					// parse for 'is' operator
					if ($this->token == 'not') {
						$op .= ' not';
						$this->getTok();
					}
					$laststatement->op = $op;
					break;
				case 'like':
					$laststatement->op = $not . $op;
					break;
				case 'between':
					// @todo
					//$clause['ops'][] = $not . $op;
					//$this->getTok();
					break;
				case 'in':
					// parse for 'in' operator
					if ($this->token != '(') {
						$this->raiseError('Expected "("');
					}

					// read the subset
					$this->getTok();
					// is this a subselect?
					if ($this->token == 'select') {
						$this->raiseError('Datasets do not supported nested SELECT statements!');
						//$clause['args'][] = $this->parseSelect(true);
					} else {
						$this->lexer->pushBack();
						// parse the set
						$result = $this->getParams($values, $types);
						if (false === $result) {
							return $result;
						}

						$laststatement->value = $values;
					}
					if ($this->token != ')') {
						$this->raiseError('Expected ")"');
					}
					break;
				case 'and':
				case 'or':
					// AND and OR statements are where statement separators.
					$clause->setSeparator($not . $op);
					// Don't forget to append the previous statement and start a new one.
					$clause->addWhere($laststatement);
					$laststatement = new Core\Datamodel\DatasetWhere();
					continue;
					break;
				default:
					$laststatement->op = $not . $op;
			}
			// next argument [with operator]
		}

		return $clause;
	}

	/**
	 * Shorthand for creating a new object and calling parse.
	 *
	 * @param null   $string
	 * @param string $dialect
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public static function ConstructAndParse($string = null, $dialect = 'ANSI') {
		$parser = new self($string, $dialect);
		$tree = $parser->parseQuery();
		if (! is_null($parser->token)) {
			throw new Exception('Expected EOQ');
		}

		return $tree;
	}
}