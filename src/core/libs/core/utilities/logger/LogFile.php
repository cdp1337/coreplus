<?php
/**
 * File for class LogFile definition in the coreplus project
 * 
 * @package Core\Utilities\Logger
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20131231.1523
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

namespace Core\Utilities\Logger;
use Core\Filestore\Backends\DirectoryLocal;
use Core\Filestore\Backends\FileLocal;


/**
 * A short teaser of what LogFile does.
 *
 * More lengthy description of what LogFile does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for LogFile
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
 * @package Core\Utilities\Logger
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class LogFile extends FileLocal{
	private $base;
	private $archivedate;

	/**
	 * Construct a new Log File object of the requested filebase (log type).
	 *
	 * @param string      $filebase    File base name (log type), to use
	 * @param string|null $archivedate Optional, set to the datestamp of the archived date
	 */
	public function __construct($filebase, $archivedate = null){
		// Make sure it contains only valid characters!
		$filebase = preg_replace('/[^a-z0-9\-]/', '', str_replace(' ', '-', strtolower($filebase)));

		if(!$filebase) $filebase = 'unknown';

		$this->base = $filebase;
		$this->archivedate = $archivedate;

		$filename = 'logs/' . $this->base . '.log';
		if($this->archivedate){
			// Append the archivedate if requested.
			$filename .= '.' . $this->archivedate;

			if(\ConfigHandler::Get('/core/logs/rotate/compress')){
				// And if compression is enabled, set the extension as appropriately as well.
				$filename .= '.gz';
			}
		}

		$this->setFilename($filename);
	}

	/**
	 * Write/Append a message to this log.
	 *
	 * @param string      $message The message to append
	 * @param string|null $code    Code or error type to prefix the log with
	 *
	 * @throws \Exception
	 */
	public function write($message, $code = null){

		if($this->isArchived()){
			throw new \Exception('Refusing to write to an already-archived log!');
		}

		$logpath = $this->getDirectoryName();
		$outfile = $this->getFilename();

		if(!is_dir($logpath)){
			// Enable auto-creation of the directory and .htaccess file.
			if(!is_writable(ROOT_PDIR)){
				// Not writable, no log!
				throw new \Exception('Unable to open ' . $logpath . ' for writing, access denied on parent directory!');
			}

			if(!mkdir($logpath)){
				// Can't create log directory, no log!
				throw new \Exception('Unable to create directory ' . $logpath . ', access denied on parent directory!');
			}

			$htaccessfh = fopen($logpath . '.htaccess', 'w');
			if(!$htaccessfh){
				// Couldn't open the htaccess for writing!
				throw new \Exception('Unable to create protective .htaccess file in ' . $logpath . '!');
			}

			$htaccesscontents = <<<EOD
<Files *>
	Order deny,allow
	Deny from All
</Files>
EOD;

			fwrite($htaccessfh, $htaccesscontents);
			fclose($htaccessfh);
		}
		elseif(!is_writable($logpath)){
			throw new \Exception('Unable to write to log directory ' . $logpath . '!');
		}
		// No else needed, else is everything dandy!


		// Generate a nice line header to prepend on the line.
		$header = '[' . \Time::GetCurrent(\Time::TIMEZONE_DEFAULT, 'r') . '] ';
		if(EXEC_MODE == 'WEB'){
			$header .= '[client: ' . REMOTE_IP . '] ';
		}
		else{
			$header .= '[client: CLI] ';
		}

		if($code) $header .= '[' . $code . '] ';

		$logfh = fopen($outfile, 'a');
		if(!$logfh){
			throw new \Exception('Unable to open ' . $outfile . ' for appending!');
		}
		foreach(explode("\n", $message) as $line){
			fwrite($logfh, $header . $line . "\n");
		}
		fclose($logfh);
	}

	/**
	 * Get if this log file is already archived.
	 *
	 * @return bool
	 */
	public function isArchived(){
		// If archive date is set, then it's archived!
		return ($this->archivedate);
	}

	/**
	 * Method to archive a given log file.
	 *
	 * Will rename it and optionally compress if configured to do so.
	 *
	 * @throws \Exception
	 */
	public function archive(){
		if($this->isArchived()){
			throw new \Exception($this->getFilename('') . ' is already archived, unable to re-archive!');
		}

		if(!$this->exists()){
			throw new \Exception($this->getFilename('') . ' does not appear to exist!');
		}

		if(!is_writable($this->getDirectoryName())){
			throw new \Exception('Unable to write to directory ' . $this->getDirectoryName() . ', archive unsuccessful!');
		}

		$this->archivedate = \CoreDateTime::Now('YmdHi');

		$newfilename = $this->base . '.log.' . $this->archivedate;
		if(!$this->rename($newfilename)){
			throw new \Exception('Unable to move log to ' . $newfilename);
		}

		if(\ConfigHandler::Get('/core/logs/rotate/compress')){
			// Compression is requested too.
			$arg = escapeshellarg($this->getFilename());
			$output = null; // I don't actually care about this.
			exec('gzip ' . $arg, $output, $ret);

			if($ret != 0){
				throw new \Exception('Unable to compress archived file!');
			}

			// Gzip appends the gz extension to the file, so follow suit!
			$this->_filename .= '.gz';
		}
	}

	public static function RotateHourly(){
		if(\ConfigHandler::Get('/core/logs/rotate/frequency') == 'hourly'){
			return self::_Rotate();
		}
		else{
			echo 'Log rotation not set for hourly, skipping.';
			return true;
		}
	}

	public static function RotateDaily(){
		if(\ConfigHandler::Get('/core/logs/rotate/frequency') == 'daily'){
			return self::_Rotate();
		}
		else{
			echo 'Log rotation not set for daily, skipping.';
			return true;
		}
	}

	public static function RotateWeekly(){
		if(\ConfigHandler::Get('/core/logs/rotate/frequency') == 'weekly'){
			return self::_Rotate();
		}
		else{
			echo 'Log rotation not set for weekly, skipping.';
			return true;
		}
	}

	public static function RotateMonthly(){
		if(\ConfigHandler::Get('/core/logs/rotate/frequency') == 'monthly'){
			return self::_Rotate();
		}
		else{
			echo 'Log rotation not set for monthly, skipping.';
			return true;
		}
	}

	private static function _Rotate(){
		$dir = new DirectoryLocal('logs/');
		$entries = $dir->ls('log');
		foreach($entries as $file){
			/** @var \Core\Filestore\Backends\FileLocal $file */

			$log = new LogFile($file->getBasename(true));
			try{
				$log->archive();
				echo 'Archived ' . $file->getBasename(true) . ' log.' . NL;
			}
			catch(\Exception $e){
				echo $e->getMessage();
				return false;
			}
		}

		if(\ConfigHandler::Get('/core/logs/rotate/keep')){
			$entries = $dir->ls();
			$types = [];
			foreach($entries as $file){
				/** @var \Core\Filestore\Backends\FileLocal $file */

				$base = $file->getBasename();
				// I want only the filename before the first '.'.
				$base = substr($base, 0, strpos($base, '.'));

				if(!isset($types[$base])){
					$types[$base] = [];
				}

				$types[$base][ $file->getMTime() ] = $file;
			}

			foreach($types as $base => $files){
				// This will sort the files newest-to-oldest.
				krsort($files);

				$limit = \ConfigHandler::Get('/core/logs/rotate/keep');
				$x = 0;
				$deleted = 0;
				foreach($files as $file){
					/** @var \Core\Filestore\Backends\FileLocal $file */
					++$x;
					if($x > $limit){
						$file->delete();
						++$deleted;
					}
				}

				if($deleted > 0){
					echo 'Purged ' . $deleted . ' old ' . $base  . ' log file(s).' . NL;
				}
			}
		}

		return true;
	}
}