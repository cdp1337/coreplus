<?php
/**
 * File for class DatasetStream definition in the coreplus project
 * 
 * @package Core\Datamodel
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20131022.1751
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
 * A wrapper around the dataset system to allow streaming large numbers of records for processing.
 *
 * To use it, simply pass in a valid Dataset object into the constructor and proceed to use getRecord() at will.
 *
 * @example
 * <pre>
 *
 * $ds = new Dataset();
 * $ds->table('my_awesome_huge_table');
 * $ds->select('*');
 *
 * $stream = new DatasetStream($ds);
 * // If the record is really REALLY huge, feel free to do this:
 * // $stream->bufferlimit = 25; // Sets the size of the buffer ie: number of records pulled at a time.
 * while(($record = $stream->getRecord())){
 *   // do something with $record, which is an associative array of the current record
 * }
 *
 * </pre>
 */
class DatasetStream{

	private $_dataset;

	private $_totalcount;

	private $_counter = -1;

	private $_startlimit = 0;

	/**
	 * Total number of records to load into the buffer at a time.
	 *
	 * @var int
	 */
	public $bufferlimit = 100;

	public function __construct(Dataset $ds){
		$this->_dataset = $ds;

		$mode = $this->_dataset->_mode;

		// I need to know how many are records are present.
		$this->_totalcount = $this->_dataset->count()->execute()->num_rows;

		// And reset the mode back... damn count
		$this->_dataset->_mode = $mode;
	}

	/**
	 * Get the next record from the dataset, or null if at the end of the list.
	 *
	 * @return array|null
	 */
	public function getRecord(){
		// NEXT!
		++$this->_counter;

		if($this->_dataset->_data === null || $this->_counter >= $this->bufferlimit){
			// Get the next set of records from the database!

			$this->_dataset->limit($this->_startlimit, $this->bufferlimit);
			$this->_dataset->execute();

			// Increment the startlimit to the next counter!
			$this->_startlimit += $this->bufferlimit;
			// And reset the counter.
			$this->_counter = 0;
		}

		return isset($this->_dataset->_data[$this->_counter]) ? $this->_dataset->_data[$this->_counter] : null;
	}
}