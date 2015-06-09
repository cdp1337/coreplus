<?php
/**
 * File for class GPG definition in the coreplus project
 * 
 * @package Core\GPG
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140319.1912
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

namespace Core\GPG;


/**
 * A short teaser of what GPG does.
 *
 * More lengthy description of what GPG does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for GPG
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
 * @package Core\GPG
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class GPG {

	/**
	 * @var string The remote keyserver to be used by this instance.
	 */
	public $keyserver = 'hkp://pool.sks-keyservers.net';

	public $keyserverOptions = [
		'timeout' => 6,
	];

	/**
	 * @var string Directory of the home directory
	 */
	public $homedir;

	/**
	 * @var bool Set to false to enable strict file permissions of the home directory
	 */
	public $ignorePermissionWarnings = true;

	/**
	 * @var string The specific location of the GPG executable on this server
	 */
	private $_executable;

	/**
	 * @var array|null Cache of local keys, in the event that multiple calls to listKeys are done.
	 */
	private $_localKeys;


	/**
	 * @throws \Exception
	 */
	public function __construct(){
		if(!is_dir(GPG_HOMEDIR)){
			// Try to create it?
			if(is_writable(dirname(GPG_HOMEDIR))){
				// w00t
				mkdir(GPG_HOMEDIR);
			}
			else{
				throw new \Exception(GPG_HOMEDIR . ' does not exist and could not be created!');
			}
		}
		elseif(!is_writable(GPG_HOMEDIR)){
			throw new \Exception(GPG_HOMEDIR . ' is not writable!');
		}

		$this->homedir = GPG_HOMEDIR;

		$this->_executable = exec('which gpg 2>/dev/null');

		if(!$this->_executable){
			throw new \Exception('Unable to locate the gpg executable on the server.  Please ensure that it is installed!');
		}
	}

	/**
	 * Retrieve a key from the local store.
	 *
	 * @param $key string Key ID to retrieve
	 *
	 * @return PrimaryKey|null
	 */
	public function getKey($key){
		$keys = $this->listKeys();

		foreach($keys as $k){
			/** @var Key $k */
			if($k->id == $key || $k->id_short == $key){
				return $k;
			}
		}

		return null;
	}

	/**
	 * List the local keys installed on the system
	 *
	 * @return array Returns an array of GPG\Keys.
	 */
	public function listKeys(){

		if($this->_localKeys !== null){
			// GOGO CACHE
			return $this->_localKeys;
		}

		$output = $this->_exec('--list-public-keys --with-colons --fixed-list-mode --with-fingerprint');
		$keys = array();
		$k = null;

		foreach($output as $line){
			$type = substr($line, 0, 4);

			switch($type){
				case 'gpg:':
					// This is a GPG comment, skip it.
					continue;
					break;
				case 'tru:':
					// This is a trust definition, skip it for now.
					continue;
					break;
				case 'pub:':
					if($k !== null){
						// This is a new key.
						// Save the previous one.
						$keys[] = $k;
					}

					// This is a new key object.
					$k = new PublicKey();
					$k->parseLine($line);
					break;
				case 'sec:':
					if($k !== null){
						// This is a new key.
						// Save the previous one.
						$keys[] = $k;
					}

					// This is a new key object.
					$k = new SecretKey();
					$k->parseLine($line);
					break;
				default:
					// Let the previous key handle it!
					$k->parseLine($line);
					break;
			}
		}

		// End of output?
		$keys[] = $k;

		$this->_localKeys = $keys;

		return $keys;
	}

	/**
	 * Search for a remote key, usually by email.
	 *
	 * @param $query string String to look for on the remote server
	 *
	 * @return array Flat array of the key IDs found from the server.
	 */
	public function searchRemoteKeys($query){
		$output = $this->_execRemote('--with-colons --fixed-list-mode --with-fingerprint --batch --no-tty --search-keys ' . escapeshellarg($query));

		$keys = array();

		foreach($output as $line){
			$type = substr($line, 0, 4);

			// I'm only interested in the primary pub keys here.
			if($type != 'pub:') continue;

			//offset of 5 characters to grab the pos of the second colon in the string...effectively skip the first colon.
			$offset = strpos($line, ':', 5);

			// grab the 8 characters leading to the second colon in the string (the pubkey)
			$key = substr($line, $offset - 8, 8);

			$keys[] = $key;
		}

		return $keys;
	}

	/**
	 * @param string $key
	 *
	 * @return PrimaryKey
	 */
	public function importKey($key){
		$key = strtoupper(preg_replace('/[^a-zA-Z0-9]*/', '', $key));
		$this->_execRemote('--with-colons --fixed-list-mode --with-fingerprint --batch --no-tty --recv-keys ' . escapeshellarg($key));

		// Reset the local cache now that the keystore has changed.
		$this->_localKeys = null;

		// And return the newly imported key in its entirety.
		return $this->getKey($key);
	}

	/**
	 * Verify that a given signature was in fact signed by the private counter part of the key and is matched the content provided.
	 *
	 * @param $signature
	 * @param $content
	 * @param $key
	 *
	 * @return bool
	 */
	public function verifyDetachedSignature($signature, $content, $key){
		// First, write a temporary file to contain the signature.
		$sig = \Core\Filestore\Factory::File('tmp/gpg-verify-' . \Core::RandomHex(6) . '.asc');
		$sig->putContents($signature);

		// And the content
		$con = \Core\Filestore\Factory::File('tmp/gpg-verify-' . \Core::RandomHex(6) . '.dat');
		$con->putContents($content);

		$result = $this->_exec('--verify ' . escapeshellarg($sig->getFilename()) . ' ' . escapeshellarg($con->getFilename()), true);

		// Cleanup.
		$sig->delete();
		$con->delete();

		// Line 1 should end with the key, if it doesn't, then it's not valid!
		if(substr($result[0], -8) != $key){
			return false;
		}

		// And Line 2 should contain either Good or BAD.
		if(substr($result[1], 0, 8) == 'gpg: BAD'){
			return false;
		}

		// More specifically, line 2 should say good signature.
		if(strpos($result[1], 'gpg: Good signature from') !== false){
			return true;
		}
		else{
			return false;
		}
	}

	/**
	 * @param string $arguments
	 * @param bool $redirect_stderr Redirect stderr to stdout
	 *
	 * @return array Array of output from command
	 *
	 * @throws \Exception
	 */
	private function _exec($arguments, $redirect_stderr = false){
		$cmd = $this->_executable .
			' --homedir ' . escapeshellarg($this->homedir);

		if($this->ignorePermissionWarnings){
			$cmd .= ' --no-permission-warning';
		}

		$cmd .= ' ' . $arguments;

		if($redirect_stderr){
			// Redirect STDERR to STDOUT.
			$cmd .= ' 2>&1';
		}


		$output = [];
		$return_var = 1;
		exec($cmd, $output, $return_var);
		//if($return_var !== 0){
		//	throw new \Exception('Command did not return successfully. ' . implode("\n", $output));
		//}

		return $output;
	}

	/**
	 * Execute gpg against a remote server, (adds the necessary options)
	 *
	 * @param string $arguments
	 *
	 * @return array Array of output from command
	 *
	 * @throws \Exception
	 */
	private function _execRemote($arguments){

		// Tack on the necessary arguments for communicating against a remote server.
		$remoteargs = ' --keyserver ' . escapeshellarg($this->keyserver);

		$opts = [];
		foreach($this->keyserverOptions as $k => $o){
			$opts[] = $k . '=' . $o;
		}
		if(sizeof($opts)){
			$remoteargs .= ' --keyserver-options ' . implode(',', $opts);
		}

		return $this->_exec($remoteargs . ' ' . $arguments);
	}
} 