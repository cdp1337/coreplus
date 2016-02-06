<?php
/**
 * File for class ContentCSV definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130419.1445
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

namespace Core\Filestore\Contents;

use Core\Filestore;

/**
 * A short teaser of what \Core\Filestore\Contents\ContentCSV does.
 *
 * More lengthy description of what \Core\Filestore\Contents\ContentCSV does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for \Core\Filestore\Contents\ContentCSV
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
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class ContentCSV implements Filestore\Contents {

	/**
	 * Has Header flag.
	 *
	 * Set to null to try to guess if there is a header (default).
	 * Set to true to force the first record to be picked up as a header.
	 * Set to false to force no header.
	 *
	 * @var null|bool
	 */
	public $_hasheader = null;

	/**
	 * @var array
	 */
	private $_header = null;

	/**
	 * The original file object
	 *
	 * @var Filestore\File
	 */
	public function __construct(Filestore\File $file) {
		$this->_file = $file;
	}

	public function parse($delimiter = ','){
		$filename = $this->_file->getFilename();

		if(!$this->_file->exists()){
			throw new \Exception('Unable to open ' . $filename . ' for parsing, does not exist!');
		}

		// Some CSV files may ship with a header line.
		$hasheader = $this->hasHeader();
		// Here, I just need the keys.
		$header = array_keys($this->getHeader());

		// Try to get a lock on the file handle.
		$fh = fopen($filename, 'r');
		if(!$fh){
			throw new \Exception('Unable to lock ' . $filename . ' for parsing!');
		}

		$data = array();

		$i = 0;
		while(!feof($fh)){
			$record = fgetcsv($fh, 1024, $delimiter);
			$i++;

			// End of file?
			if(!$record) continue;

			if($i == 1 && $hasheader){
				// Skip the header if there is one.
				continue;
			}
			elseif($hasheader){
				// A header is present and set, associate this record!
				$data[] = array_combine($header, $record);
			}
			else{
				// Just a plain'ol indexed record.
				$data[] = $record;
			}
		}

		fclose($fh);

		return $data;
	}

	/**
	 * Actively do a simple check with heuristics on this file and see if it has a header.
	 * (of just return the explicitly set value)
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function hasHeader(){
		if($this->_hasheader === null){
			// Autodetect!

			$line = $this->getHeader();

			// If there are duplicate records present, it's probably not a header.
			$counts = array_values(array_count_values($line));
			sort($counts); // Sort them ascending, (biggest number at the end)
			if(array_pop($counts) > 1){
				$this->_hasheader = false;
				return false;
			}

			// Do some heuristics on each individual record.
			foreach($line as $val){
				// If there is an empty column, it's probably not a header.
				if(!trim($val)){
					$this->_hasheader = false;
					return false;
				}
				// If it's a number, it's probably not a header.
				if(is_int($val)){
					$this->_hasheader = false;
					return false;
				}
				// If it's a date, it's probably not a header.
				if(preg_match('/[0-9]+[\/\-][0-9]+[\/\-][0-9]+/', $val)){
					$this->_hasheader = false;
					return false;
				}
			}

			// None of the scans detected that it's a value... maybe.
			$this->_hasheader = true;
			return true;
		}
		else{
			// The user, (or this method), set it... yay/nay.
			return $this->_hasheader;
		}
	}

	/**
	 * Get the header columns
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function getHeader(){
		if($this->_header === null){
			$filename = $this->_file->getFilename();

			if(!$this->_file->exists()){
				throw new \Exception('Unable to open ' . $filename . ' for parsing, does not exist!');
			}

			// Try to get a lock on the file handle.
			$fh = fopen($filename, 'r');
			if(!$fh){
				throw new \Exception('Unable to lock ' . $filename . ' for parsing!');
			}

			// Just get the first line and close.
			$line = fgetcsv($fh, 1024);
			fclose($fh);

			// If there are duplicate records present, it's probably not a header.
			$counts = array_values(array_count_values($line));
			sort($counts); // Sort them ascending, (biggest number at the end)
			if(array_pop($counts) > 1){
				$this->_hasheader = false;
				return false;
			}

			// Do some heuristics on each individual record.
			foreach($line as $val){
				// If there is an empty column, it's probably not a header.
				if(!trim($val)){
					$this->_hasheader = false;
					return false;
				}
				// If it's a number, it's probably not a header.
				if(is_int($val)){
					$this->_hasheader = false;
					return false;
				}
				// If it's a date, it's probably not a header.
				if(preg_match('/[0-9]+[\/\-][0-9]+[\/\-][0-9]+/', $val)){
					$this->_hasheader = false;
					return false;
				}
			}

			// None of the scans detected that it's a value... maybe.
			$this->_header = array();
			foreach($line as $title){
				$val = strtolower(str_replace(' ', '_', $title));
				$this->_header[$val] = $title;
			}
		}

		return $this->_header;
	}
}