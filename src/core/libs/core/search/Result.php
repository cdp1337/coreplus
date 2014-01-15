<?php
/**
 * File for class Result definition in the tenant-visitor project
 * 
 * @package Core\Search
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20131122.1936
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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

namespace Core\Search;


/**
 * A generic search result object.
 *
 * Not extremely useful by itself, but acts as a useful base for other Result types!
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for Result
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
 * @package Core\Search
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class Result implements \ArrayAccess {
	/**
	 * @var string Title of this search result.
	 */
	public $title;
	/**
	 * @var string Link of this search result.
	 */
	public $link;
	/**
	 * @var string Original query string, (mainly for debugging).
	 */
	public $query;
	/**
	 * @var float Relevancy of this result in context of the query, 0.0 - 100.0 scale.
	 */
	public $relevancy = 0.0;

	/**
	 * Get this result entry as rendered HTML.
	 *
	 * @return string
	 */
	public function fetch(){
		if($this->link){
			return '<a href="' . $this->link . '">' . $this->title . '</a>';
		}
		else{
			return $this->title;
		}
	}

	/**
	 * Write this result to STDOUT.
	 *
	 * @return void
	 */
	public function render(){
		echo $this->fetch();
	}


	/*************************************************************************
	 ****                    ARRAY ACCESS METHODS                         ****
	 *************************************************************************/

	/**
	 * Whether an offset exists
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param mixed $offset An offset to check for.
	 *
	 * @return boolean Returns true on success or false on failure.
	 */
	public function offsetExists($offset) {
		return ($offset == 'link' || $offset == 'query' || $offset == 'relevancy' || $offset == 'title');
	}

	/**
	 * Offset to retrieve
	 *
	 * Alias of Model::get()
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param mixed $offset The offset to retrieve.
	 *
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset) {
		return $this->$offset;
	}

	/**
	 * Offset to set
	 *
	 * Alias of Model::set()
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param mixed $offset The offset to assign the value to.
	 * @param mixed $value The value to set.
	 *
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		// This system doesn't support settings variables via the model accessors.
		return;
	}

	/**
	 * Offset to unset
	 *
	 * This just sets the value to null.
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset The offset to unset.
	 *
	 * @return void
	 */
	public function offsetUnset($offset) {
		// This system doesn't support settings variables via the model accessors.
		return;
	}

}
