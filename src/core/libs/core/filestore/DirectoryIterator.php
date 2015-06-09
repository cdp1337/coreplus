<?php
/**
 * File for class DirectorySearch definition in the Core Plus project
 * 
 * @package Core\Filestore
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20141126.1803
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

namespace Core\Filestore;


/**
 * Advanced version of "ls" for directories
 *
 * More lengthy description of what DirectorySearch does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for DirectoryIterator
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
 * @package Core\Filestore
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class DirectoryIterator implements \Iterator {
	/** @var bool Include Files in the search results */
	public $findFiles = true;
	/** @var bool Include Directories in the search results */
	public $findDirectories = true;
	/** @var bool Recurse into directories */
	public $recursive = false;
	/** @var array Match only extensions in this array */
	public $findExtensions = [];
	/** @var array Ignore filenames and directories that match the following  */
	public $ignores = [];
	/** @var string Optional preg match for files and directories */
	public $pregMatch = '';
	/** @var bool Set to true to enable automatic sorting of results */
	public $sort = false;
	/** @var string How to sort results, currently only filename is supported */
	public $sortOn = 'filename';
	/** @var string Which direction to sort results by */
	public $sortDir = 'asc';

	private $_results = null;

	/** @var Directory Base directory to start searching from within. */
	private $_baseDirectory;

	public function __construct(Directory $directory){
		$this->_baseDirectory = $directory;
	}

	/**
	 * Enable sorting on a specific key.
	 *
	 * @param string $on
	 * @param string $dir
	 *
	 * @throws \Exception
	 */
	public function sortBy($on = 'filename', $dir = 'asc'){
		// Enable sort first off.
		$this->sort = true;

		$on = strtolower($on);
		if($on == 'filename'){
			$this->sortOn = $on;
		}
		else{
			throw new \Exception('Unsupported sort by requested, [' . $on . ']');
		}

		$dir = strtolower($dir);
		if($dir == 'asc' || $dir == 'desc'){
			$this->sortDir = $dir;
		}
		else{
			throw new \Exception('Unsupported sort direction requested, [' . $dir . ']');
		}
	}

	/**
	 * Scan the directory for the given matches and return the results.
	 *
	 * @return array
	 */
	public function scan(){
		$this->_results = $this->_sift();

		// If sort was requested, then sort the results!
		if($this->sort){
			$sorted = [];

			foreach($this->_results as $result){
				if($this->sortOn == 'filename'){
					if($result instanceof File){
						$sorted[$result->getFilename()] = $result;
					}
					elseif($result instanceof Directory){
						$sorted[$result->getPath()] = $result;
					}
				}
				// @todo Add other sort options as required.
			}

			if($this->sortDir == 'asc'){
				ksort($sorted);
			}
			else{
				krsort($sorted);
			}

			$this->_results = array_values($sorted);
		}

		return $this->_results;
	}


	//////////////  ITERATOR METHODS  //////////////

	public function rewind() {
		if($this->_results === null){
			$this->scan();
		}

		reset($this->_results);
	}

	public function current() {
		if($this->_results === null){
			$this->scan();
		}

		return current($this->_results);
	}

	public function key() {
		if($this->_results === null){
			$this->scan();
		}

		return key($this->_results);
	}

	public function next() {
		if($this->_results === null){
			$this->scan();
		}

		return next($this->_results);
	}

	public function valid() {
		if($this->_results === null){
			$this->scan();
		}

		return ($this->current() !== false);
	}

	/**
	 * Sift through the specified directory for any matches and return them,
	 *
	 * this can be called recursively
	 *
	 * @param null|string $base
	 * @param null|Directory $directory
	 *
	 * @return array
	 */
	private function _sift($base = null, $directory = null){
		if(!$directory){
			$directory = $this->_baseDirectory;
		}
		if(!$base){
			$base = '';
		}

		$results = [];
		$ls      = $directory->ls();

		foreach($ls as $sub){
			/** @var File|Directory $sub */

			$match     = true;
			$scanmatch = $this->recursive;
			$fbase     = $sub->getBasename();

			if($this->pregMatch && !preg_match($this->pregMatch, $fbase)){
				// If a preg match was requested, but it doesn't match, skip!
				$match = false;
				$scanmatch = false;
			}

			if(sizeof($this->ignores) && in_array($base . $fbase, $this->ignores)){
				// Filename is in the path of ignores, skip!
				$match = false;
				$scanmatch = false;
			}

			if($sub instanceof File){
				$ext   = $sub->getExtension();

				if(!$this->findFiles){
					// Find files is not requested and it's a file... skip!
					$match = false;
				}

				if(sizeof($this->findExtensions) && !in_array($ext, $this->findExtensions)){
					// Extension match requested, but the file was not in the extension set, skip!
					$match = false;
				}

				if($match){
					$results[] = $sub;
				}


			}
			elseif($sub instanceof Directory){

				if(!$this->findDirectories){
					$match = false;
				}

				if(sizeof($this->ignores) && in_array($base . $fbase . '/', $this->ignores)){
					// Filename is in the path of ignores, skip!
					// This is an extension of the check above, should the user search for directory foo/
					$match = false;
					$scanmatch = false;
				}

				if($match){
					$results[] = $sub;
				}

				if($scanmatch){
					$results = array_merge($results, $this->_sift($base . $fbase . '/', $sub));
				}
			}
		}

		return $results;
	}
} 