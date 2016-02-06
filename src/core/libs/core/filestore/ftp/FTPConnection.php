<?php
/**
 * File for class FTPConnection definition in the coreplus project
 * 
 * @package Core\Filestore\FTP
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140714.2051
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


/**
 * A short teaser of what FTPConnection does.
 *
 * More lengthy description of what FTPConnection does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for FTPConnection
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
class FTPConnection {
	/** @var resource Underlying FTP resource for this connection */
	private $conn;
	/** @var string Username for the FTP connection */
	public $username;
	/** @var string Password for the FTP connection, (blanked out on a successful connection) */
	public $password;
	/** @var string Hostname or IP address for the FTP connection */
	public $host;
	/** @var string Root URL that acts as a path mapping */
	public $url;
	/** @var string Root physical directory for the FTP connection */
	public $root;
	/** @var bool Flag used internally with FileFTP when performing file operations */
	public $isLocal = false;
	/** @var array Remote FTP servers will have a metadata file containing modified timestamps and hashes of files */
	protected $metaFiles = [];
	/** @var array Currently open connections for this session */
	private static $_OpenConnections = [];
	/** @var int Last save timestamp, used to auto-save the metafiles every few seconds. */
	private $lastSave = 0;
	/** @var bool Set to true when it's connected and ready. */
	private $connected = false;

	/**
	 * Connect and return the FTP resource.
	 *
	 * If already connected, then nothing is done.
	 *
	 * @return resource
	 */
	public function getConn(){
		$this->connect();
		return $this->conn;
	}

	/**
	 * Connect to the set hostname using the appropriate credentials.
	 *
	 * @throws \Exception
	 */
	public function connect(){
		if($this->connected){
			// Already connected, YAY!
			return;
		}

		if(!$this->host){
			throw new \Exception('Please set the host before connecting to an FTP server.');
		}

		if(!$this->root){
			throw new \Exception('Please set the root path before connecting to an FTP server.');
		}

		$this->conn = ftp_connect($this->host);
		if(!$this->conn){
			throw new \Exception('Unable to connect to the FTP server at ' . $this->host);
		}

		if($this->username){
			if(!ftp_login($this->conn, $this->username, $this->password)){
				throw new \Exception('Bad FTP username or password for ' . $this->host);
			}

			// Now that it's connected, hide the password.
			$this->password = '--hidden--';
		}
		else{
			if(!ftp_login($this->conn, 'anonymous', '')){
				throw new \Exception('Anonymous logins are disabled for ' . $this->host);
			}
		}

		ftp_set_option($this->conn, FTP_TIMEOUT_SEC, 600);

		$this->reset();

		if($this->host == '127.0.0.1'){
			$this->isLocal = true;
		}

		$this->connected = true;
		$this->lastSave = DateTime::NowGMT();

		self::$_OpenConnections[] = $this;
		if(sizeof(self::$_OpenConnections) == 1){
			// First open connection, register the shutdown hook!
			\HookHandler::AttachToHook('/core/shutdown', '\\Core\\Filestore\\FTP\\FTPConnection::ShutdownHook');
		}
	}

	/**
	 * Reset (or chdir), to the root directory.
	 *
	 * @throws \Exception
	 */
	public function reset(){
		// This serves two purposes, one it resets the location of the FTP back to the home directory
		// and two, it ensures that the directory exists!
		if(!ftp_chdir($this->conn, $this->root)){
			throw new \Exception('FTP functional, but root of [' . $this->root . '] was not valid or does not exist!');
		}
	}

	/**
	 * Get the file contents hash of a given FTP file.
	 *
	 * @param string $filename
	 *
	 * @return string
	 */
	public function getFileHash($filename){
		$dir   = dirname($filename) . '/';
		$file  = basename($filename);
		$obj   = $this->getMetaFileObject($dir);
		$metas = $obj->getMetas($file);

		return isset($metas['hash']) ? $metas['hash'] : '';
	}

	/**
	 * Get the file modified timestamp, (as UTC), of a given FTP file.
	 *
	 * @param string $filename
	 *
	 * @return int
	 */
	public function getFileModified($filename){
		$dir   = dirname($filename) . '/';
		$file  = basename($filename);
		$obj   = $this->getMetaFileObject($dir);
		$metas = $obj->getMetas($file);

		return isset($metas['modified']) ? $metas['modified'] : '';
	}

	/**
	 * Get the file size of a given FTP file.
	 *
	 * @param string $filename
	 *
	 * @return int
	 */
	public function getFileSize($filename){
		$dir   = dirname($filename) . '/';
		$file  = basename($filename);
		$obj   = $this->getMetaFileObject($dir);
		$metas = $obj->getMetas($file);

		return isset($metas['size']) ? $metas['size'] : '';
	}

	/**
	 * Set the hash of an FTP file
	 *
	 * @param string $filename
	 * @param string $hash
	 */
	public function setFileHash($filename, $hash){
		$dir = dirname($filename) . '/';
		$file = basename($filename);
		$obj = $this->getMetaFileObject($dir);

		$obj->set($file, 'hash', $hash);
		$this->_syncMetas();
	}

	/**
	 * Set the modified timestamp of an FTP file
	 *
	 * @param string $filename
	 * @param int    $timestamp
	 */
	public function setFileModified($filename, $timestamp){
		$dir = dirname($filename) . '/';
		$file = basename($filename);
		$obj = $this->getMetaFileObject($dir);

		$obj->set($file, 'modified', $timestamp);
		$this->_syncMetas();
	}

	/**
	 * Set the size of an FTP file
	 *
	 * @param string $filename
	 * @param int    $size
	 */
	public function setFileSize($filename, $size){
		$dir = dirname($filename) . '/';
		$file = basename($filename);
		$obj = $this->getMetaFileObject($dir);

		$obj->set($file, 'size', $size);
		$this->_syncMetas();
	}

	/**
	 * @param string $directory
	 *
	 * @return FTPMetaFile
	 */
	public function getMetaFileObject($directory){
		if(!isset($this->metaFiles[$directory])){
			$this->metaFiles[$directory] = new FTPMetaFile($directory, $this);
		}

		return $this->metaFiles[$directory];
	}

	private function _syncMetas(){
		if($this->lastSave + 25 >= DateTime::NowGMT()){
			return;
		}

		$this->lastSave = DateTime::NowGMT();
		foreach($this->metaFiles as $file){
			/** @var FTPMetaFile $file */
			$file->saveMetas();
		}
	}

	/**
	 * Hook to save all metadata files that happen to be open on the open FTP connections.
	 */
	public static function ShutdownHook(){
		foreach(self::$_OpenConnections as $conn){
			/** @var FTPConnection $conn */
			foreach($conn->metaFiles as $file){
				/** @var FTPMetaFile $file */
				$file->saveMetas();
			}
		}
	}
}