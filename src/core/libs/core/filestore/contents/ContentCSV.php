<?php
/**
 * File for class ContentCSV definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130419.1445
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
	
	private $_readerHandle = null;
	
	/** @var null|int Total lines (minus header) of this file, generated dynamically from getTotalLines */
	private $_totalLines = null;

	/**
	 * The original file object
	 *
	 * @var Filestore\File
	 */
	public function __construct(Filestore\File $file) {
		$this->_file = $file;
	}

	/**
	 * Read an entire file into memory as an associative or indexes array.
	 * 
	 * @param string   $delimiter
	 * @param int|null $lines     Set to an int to limit that number of lines to be returned.
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function parse($delimiter = ',', $lines = null){
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
		
		// Read 1 more line than requested if there's a header.
		if($lines !== null && $hasheader){
			$lines ++;
		}

		$i = 0;
		while(!feof($fh) && ($lines === null || $i < $lines)){
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
	 * Read up to N lines of a file into memory as an associative or indexes array.
	 * 
	 * Useful for large files!
	 *
	 * @param string $delimiter
	 * @param int    $lines
	 *
	 * @return array|false
	 * @throws \Exception
	 */
	public function parseChunked($delimiter = ',', $lines = 100){
		$filename = $this->_file->getFilename();
		$firstRead = false;

		if(!$this->_file->exists()){
			throw new \Exception('Unable to open ' . $filename . ' for parsing, does not exist!');
		}

		// Some CSV files may ship with a header line.
		$hasheader = $this->hasHeader();
		// Here, I just need the keys.
		$header = array_keys($this->getHeader());
		
		// Try to get a lock on the file handle.
		if($this->_readerHandle === null){
			$this->_readerHandle = fopen($filename, 'r');
			if(!$this->_readerHandle){
				throw new \Exception('Unable to lock ' . $filename . ' for parsing!');
			}
			$firstRead = true;
		}
		
		if($this->_readerHandle === false){
			// Return false to indicate it's the end of the file and reset to allow this running again.
			$this->_readerHandle = null;
			return false;
		}

		$data = array();

		// Read 1 more line than requested if there's a header.
		if($lines !== null && $hasheader && $firstRead){
			$lines ++;
		}

		$i = 0;
		while(!feof($this->_readerHandle) && $i < $lines){
			$record = fgetcsv($this->_readerHandle, 1024, $delimiter);
			$i++;

			// End of file?
			if(!$record){
				fclose($this->_readerHandle);
				$this->_readerHandle = false;
				break;
			}

			if($i == 1 && $hasheader && $firstRead){
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
		
		// End of file?  Cleanup.
		if($this->_readerHandle && feof($this->_readerHandle)){
			fclose($this->_readerHandle);
			$this->_readerHandle = false;
		}

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
		if($this->_hasheader === null) {
			// Autodetect!
			$line = $this->getHeader();
		}
		return $this->_hasheader;
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
				$this->_header = [];
				$line = [];
			}

			// Do some heuristics on each individual record.
			foreach($line as $val){
				// If there is an empty column, it's probably not a header.
				if(!trim($val)){
					$this->_header = [];
				}
				// If it's a number, it's probably not a header.
				if(is_int($val)){
					$this->_header = [];
				}
				// If it's a date, it's probably not a header.
				if(preg_match('/[0-9]+[\/\-][0-9]+[\/\-][0-9]+/', $val)){
					$this->_header = [];
				}
			}

			// None of the scans detected that it's a value... maybe.
			$this->_header = array();
			foreach($line as $title){
				$val = strtolower(str_replace(' ', '_', $title));
				$this->_header[$val] = $title;
			}
		}
		
		if($this->_hasheader === null){
			$this->_hasheader = (sizeof($this->_header) > 0);
		}

		return $this->_header;
	}

	/**
	 * Get the total number of data lines in this CSV.
	 * 
	 * If there is a header, omit that, so 1000 lines with a header returns 999.
	 * 
	 * @throws \Exception
	 * 
	 * @return int
	 */
	public function getTotalLines(){
		if($this->_totalLines === null){
			// WC is a LOT quicker than native PHP, so simply use that to calculate this.
			exec('wc -l ' . escapeshellarg($this->_file->getFilename()), $out, $ret);
			
			if($ret !== 0){
				throw new \Exception('Unable to calculate number of lines in requested file ' . $this->_file->getFilename() . ': ' . print_r($out, true));
			}
			
			// This is in the format of "1234 filename-here.blah"
			$c = (int)preg_replace('#^([0-9]*)\s.*#', '$1', $out[0]);
			
			if($this->hasHeader()){
				$c--;
			}
			$this->_totalLines = $c;
		}
		
		return $this->_totalLines;
	}
}