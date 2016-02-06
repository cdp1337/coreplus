<?php
/**
 * File for class FTPMetaFile definition in the coreplus project
 * 
 * @package Core\Filestore\FTP
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140715.1419
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

namespace Core\Filestore\FTP;
use Core\Date\DateTime;
use Core\Filestore\Backends\FileLocal;
use Core\Filestore\Factory;


/**
 * A short teaser of what FTPMetaFile does.
 *
 * More lengthy description of what FTPMetaFile does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for FTPMetaFile
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
 * @package Core\Filestore\FTP
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class FTPMetaFile {
	/** @var FTPConnection */
	private $_ftp;
	private $_dir;
	private $_contents;
	/** @var FileLocal */
	private $_local;
	private $_changed = false;

	/**
	 * @param string $directory
	 * @param FTPConnection $ftp
	 */
	public function __construct($directory, $ftp){
		$this->_dir = $directory;
		$this->_ftp = $ftp;
	}

	/**
	 * Get an associative array of all metadata associated to the requested file.
	 *
	 * @param string $file
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function getMetas($file){
		$allkeys = ['filename', 'hash', 'modified', 'size'];

		if($this->_contents === null){

			$this->_contents = [];

			$remotefile = $this->_dir . '.ftpmetas';
			$f = md5($remotefile);

			$this->_local = Factory::File('tmp/remotefile-cache/' . $f);

			if(
				(!$this->_local->exists()) ||
				($this->_local->exists() && $this->_local->getMTime() + 1800 < DateTime::NowGMT())
			){
				// Only try to open the remote file if it exists.
				if(ftp_size($this->_ftp->getConn(), $remotefile) != -1){
					// The file doesn't exist OR the file does but it hasn't been modified in the past 30 minutes.
					$this->_local->putContents('');
					ftp_get($this->_ftp->getConn(), $this->_local->getFilename(), $remotefile, FTP_BINARY);
				}
			}


			if(!$this->_local->exists()){
				// The remote file doesn't exist, so nothing was downloaded.
				// Just return a blank array.
				return array_merge($allkeys, ['filename' => $file]);
			}

			// Read this CSV file into the contents array.
			$fh = fopen($this->_local->getFilename(), 'r');
			if(!$fh){
				throw new \Exception('Unable to open ' . $this->_local->getFilename() . ' for reading.');
			}
			$line    = 0;
			$map     = [];
			$headers = [];
			do{
				$data = fgetcsv($fh, 2048);

				// Meh.  Could do this inside a standard while statement, but same diff.
				if($data === null) break;
				if($data === false) break;

				$line++;
				if($line == 1){
					// This is the header.
					$map = $data;
					foreach($data as $k => $v){
						$headers[$v] = $k;
					}

					foreach($allkeys as $key){
						if(!isset($headers[$key])){
							$map[] = $key;
							$headers[$key] = -1;
						}
					}
				}
				else{
					$assoc = [];
					foreach($map as $k => $v){
						$assoc[$v] = isset($data[$k]) ? $data[$k] : '';
					}
					if(!isset($assoc['filename'])){
						// Invalid CSV input.
						fclose($fh);
						return array_merge($allkeys, ['filename' => $file]);
					}

					$this->_contents[ $assoc['filename'] ] = $assoc;
				}
			}
			while(true);
		}

		return isset($this->_contents[$file]) ? $this->_contents[$file] : array_merge($allkeys, ['filename' => $file]);
	}

	/**
	 * Set an arbitrary key with a value on a given file.
	 *
	 * @param string $file
	 * @param string $key
	 * @param string $value
	 * @param bool   $commit
	 */
	public function set($file, $key, $value, $commit = false){
		// Make sure that the cache is fresh.
		$this->getMetas($file);

		if(!isset($this->_contents[$file])){
			$this->_contents[$file] = [
				'filename' => $file,
				'hash' => '',
				'modified' => '',
				'size' => '',
			];
		}

		$this->_contents[$file][$key] = $value;
		$this->_changed = true;

		if($commit){
			$this->saveMetas();
		}
	}

	/**
	 * Save this meta file back up to the FTP server.
	 *
	 * @throws \Exception
	 */
	public function saveMetas(){
		if($this->_contents === null){
			// Contents never loaded, nothing to save.
			return;
		}

		if(!$this->_changed){
			// File wasn't changed, nothing to save.
			return;
		}

		$remotefile = $this->_dir . '.ftpmetas';

		// Make sure the local directory exists and is writable first!
		// This will effectively touch the file to ensure it's writable and everything.
		$this->_local->putContents('');

		$fh = fopen($this->_local->getFilename(), 'w');
		if(!$fh){
			throw new \Exception('Unable to open ' . $this->_local->getFilename() . ' for writing.');
		}

		// Write the current header.
		fputcsv($fh, ['filename', 'hash', 'modified', 'size']);

		// And each line.
		foreach($this->_contents as $c){
			fputcsv($fh, array_values($c));
		}
		fclose($fh);

		// And publish to the FTP server.
		ftp_put($this->_ftp->getConn(), $remotefile, $this->_local->getFilename(), FTP_BINARY);
		$this->_changed = false;
	}
} 