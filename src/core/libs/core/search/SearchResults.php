<?php
/**
 * File for class SearchResults definition in the tenant-visitor project
 * 
 * @package Core\Search
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20131127.1404
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

namespace Core\Search;


/**
 * A short teaser of what SearchResults does.
 *
 * More lengthy description of what SearchResults does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for SearchResults
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
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class SearchResults {

	/**
	 * Set to true to look for (and remember), the pagination values.
	 *
	 * @var bool
	 */
	public $haspagination = false;

	/**
	 * The query for this search result query.
	 *
	 * Optional, but can be used by tracking software.
	 *
	 * @var string
	 */
	public $query = '';

	/**
	 * @var array Array of results in this search result, set by addResults.
	 */
	private $_results = array();

	/**
	 * The current page, only takes effect if $haspagination is set to true.
	 *
	 * @var int
	 */
	private $_currentpage = 1;

	/**
	 * The limit for this search results, only takes effect if $haspagination is set to true.
	 *
	 * @var int
	 */
	private $_limit = 50;

	/**
	 * @var bool Automatically set to true when sortResults is called and set to false when addResults is called.
	 */
	private $_sorted = false;

	/**
	 * Add a set of results onto this search
	 * @param array $results
	 */
	public function addResults($results){
		$this->_results = array_merge($this->_results, $results);
		$this->_sorted = false;
	}

	/**
	 * Add a single result onto this search.
	 *
	 * Useful if a foreach operation is required prior to adding to the stack.
	 *
	 * @param Result $result
	 */
	public function addResult(Result $result){
		$this->_results[] = $result;
		$this->_sorted = false;
	}

	/**
	 * Method to sort the results added by relevancy.
	 *
	 * @return void
	 */
	public function sortResults(){
		$relmap = [];
		$clone = $this->_results;

		// Create the map of relevancy weights onto a flat mapped array.
		foreach($this->_results as $k => $result){
			/** @var Result $result */

			$relmap[$k] = $result->relevancy;
		}

		// sort them.
		arsort($relmap);

		// Now that the result relevancy values are sorted by most accurate at the top, (accomplished by the r-sort),
		$this->_results = [];
		foreach($relmap as $originalkey => $rel){
			$this->_results[] = $clone[$originalkey];
		}

		$this->_sorted = true;
	}

	/**
	 * Get the array of results.
	 *
	 * @return array
	 */
	public function get(){
		if(!$this->_sorted){
			$this->sortResults();
		}

		// @todo Handle pagination

		return $this->_results;
	}

	/**
	 * Get the total size of the results matched.
	 *
	 * @return int
	 */
	public function getCount(){
		return sizeof($this->_results);
	}

	/**
	 * Display the results as rendered HTML.
	 */
	public function render(){
		foreach($this->get() as $record){
			/** @var \Core\Search\Result $record */
			echo $record->fetch();
		}
	}
} 
