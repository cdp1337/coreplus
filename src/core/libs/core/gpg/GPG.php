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
 * ### Compatibility with GnuPG
 *
 * gnupg_​adddecryptkey
 *
 * gnupg_​addencryptkey
 * :    // Incorporated into encryptData and encryptFile.
 *
 * gnupg_​addsignkey
 *
 * gnupg_​cleardecryptkeys
 *
 * gnupg_​clearencryptkeys
 *
 * gnupg_​clearsignkeys
 *
 * gnupg_​decrypt
 *
 * gnupg_​decryptverify
 *
 * gnupg_​encrypt
 * :    $gpg = new Core\GPG\GPG();
 *      $gpg->encryptData($plaintext, $fingerprint);
 *      // OR
 *      $gpg->encryptData($plaintext, [$fingerprint1, $fingerprint2]);
 *
 * gnupg_​encryptsign
 *
 * gnupg_​export
 *
 * gnupg_​geterror
 *
 * gnupg_​getprotocol
 *
 * gnupg_​import
 * :    $gpg = new Core\GPG\GPG();
 *      $gpg->importKey($keydata);
 *
 * gnupg_​init
 * :    new Core\GPG\GPG();
 *
 * gnupg_​keyinfo
 * gnupg_​setarmor
 * gnupg_​seterrormode
 * gnupg_​setsignmode
 * gnupg_​sign
 * gnupg_​verify

0 => &
object(ReflectionMethod)[992]
public 'name' => string 'keyinfo' (length=7)
public 'class' => string 'gnupg' (length=5)
1 => &
object(ReflectionMethod)[1009]
public 'name' => string 'verify' (length=6)
public 'class' => string 'gnupg' (length=5)
2 => &
object(ReflectionMethod)[991]
public 'name' => string 'geterror' (length=8)
public 'class' => string 'gnupg' (length=5)
3 => &
object(ReflectionMethod)[990]
public 'name' => string 'clearsignkeys' (length=13)
public 'class' => string 'gnupg' (length=5)
4 => &
object(ReflectionMethod)[989]
public 'name' => string 'clearencryptkeys' (length=16)
public 'class' => string 'gnupg' (length=5)
5 => &
object(ReflectionMethod)[988]
public 'name' => string 'cleardecryptkeys' (length=16)
public 'class' => string 'gnupg' (length=5)
6 => &
object(ReflectionMethod)[987]
public 'name' => string 'setarmor' (length=8)
public 'class' => string 'gnupg' (length=5)
7 => &
object(ReflectionMethod)[986]
public 'name' => string 'encrypt' (length=7)
public 'class' => string 'gnupg' (length=5)
8 => &
object(ReflectionMethod)[985]
public 'name' => string 'decrypt' (length=7)
public 'class' => string 'gnupg' (length=5)
9 => &
object(ReflectionMethod)[984]
public 'name' => string 'export' (length=6)
public 'class' => string 'gnupg' (length=5)
10 => &
object(ReflectionMethod)[983]
public 'name' => string 'import' (length=6)
public 'class' => string 'gnupg' (length=5)
11 => &
object(ReflectionMethod)[982]
public 'name' => string 'getprotocol' (length=11)
public 'class' => string 'gnupg' (length=5)
12 => &
object(ReflectionMethod)[981]
public 'name' => string 'setsignmode' (length=11)
public 'class' => string 'gnupg' (length=5)
13 => &
object(ReflectionMethod)[980]
public 'name' => string 'sign' (length=4)
public 'class' => string 'gnupg' (length=5)
14 => &
object(ReflectionMethod)[979]
public 'name' => string 'encryptsign' (length=11)
public 'class' => string 'gnupg' (length=5)
15 => &
object(ReflectionMethod)[978]
public 'name' => string 'decryptverify' (length=13)
public 'class' => string 'gnupg' (length=5)
16 => &
object(ReflectionMethod)[977]
public 'name' => string 'addsignkey' (length=10)
public 'class' => string 'gnupg' (length=5)
17 => &
object(ReflectionMethod)[976]
public 'name' => string 'addencryptkey' (length=13)
public 'class' => string 'gnupg' (length=5)
18 => &
object(ReflectionMethod)[975]
public 'name' => string 'adddecryptkey' (length=13)
public 'class' => string 'gnupg' (length=5)
19 => &
object(ReflectionMethod)[974]
public 'name' => string 'deletekey' (length=9)
public 'class' => string 'gnupg' (length=5)
20 => &
object(ReflectionMethod)[973]
public 'name' => string 'gettrustlist' (length=12)
public 'class' => string 'gnupg' (length=5)
21 => &
object(ReflectionMethod)[972]
public 'name' => string 'listsignatures' (length=14)
public 'class' => string 'gnupg' (length=5)
22 => &
object(ReflectionMethod)[971]
public 'name' => string 'seterrormode' (length=12)
public 'class' => string 'gnupg' (length=5)

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

	/** @var null|gnupg Object of the PECL instance if available. */
	private $_gnupg = null;


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

		// If the gnupg library is available, I'd prefer to use that whenever possible due to performance and security reasons.
		if(function_exists('gnupg_init')){
			$this->_gnupg = new \gnupg();
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
			if($k->id == $key || $k->id_short == $key || $k->fingerprint == $key){
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

		// Execute the command to retrieve the raw list of key data
		$output = $this->_exec('--with-colons --fixed-list-mode --with-fingerprint --list-public-keys');

		// Parse the output lines
		$keys = $this->_parseOutputLines(explode("\n", $output['output']));

		// Cache them for next time!
		$this->_localKeys = $keys;

		return $keys;
	}

	/**
	 * Search for a remote key, usually by email.
	 *
	 * This method has no gnupg equivalent!
	 *
	 * @param $query string String to look for on the remote server
	 *
	 * @return array Flat array of the key IDs found from the server.
	 */
	public function searchRemoteKeys($query){
		$output = $this->_execRemote('--with-colons --fixed-list-mode --with-fingerprint --batch --no-tty --search-keys ' . escapeshellarg($query));

		// Parse the output lines
		$keys = $this->_parseOutputLines(explode("\n", $output['output']));

		return $keys;
	}

	/**
	 * Import a key from a remote keyserver
	 *
	 * @alias gnupg_import Implements the gnupg_import functionality
	 *
	 * @param string $key ID, Fingerprint, or full key data of the key to import
	 *
	 * @throws \Exception
	 *
	 * @return PrimaryKey
	 */
	public function importKey($key){
		if(strlen($key) == 8 || strlen($key) == 40){
			// This is an ID or fingerprint, gnupg requires the key data so we'll use the CLI instead.
			$key = strtoupper(preg_replace('/[^a-zA-Z0-9]*/', '', $key));
			$result = $this->_execRemote('--with-colons --fixed-list-mode --with-fingerprint --batch --no-tty --recv-keys ' . escapeshellarg($key));

			if($result['return'] != 0){
				throw new \Exception(trim($result['error']));
			}
		}
		else{
			// CLI-only available and data is provided.
			// This requires some trickery.
			$local = \Core\Filestore\Factory::File('tmp/gpg-import-' . \Core::RandomHex(6) . '.gpg');
			$local->putContents($key);

			// the CLI expects a local file, so now there is one!
			$result = $this->_exec('--with-colons --fixed-list-mode --with-fingerprint --batch --no-tty --import ' . escapeshellarg($local->getFilename()));

			if($result['return'] != 0){
				throw new \Exception(trim($result['error']));
			}

			// Cleanup.
			$local->delete();

			// This result should include a text line with the short form of the GPG key in it.
			$key = preg_replace('/^gpg: key ([A-F0-9]*):.*/s', '$1', $result['error']);
		}

		// Reset the local cache now that the keystore has changed.
		$this->_localKeys = null;

		// And return the newly imported key in its entirety.
		return $this->getKey($key);
	}

	/**
	 * Delete a key from the keyring.
	 *
	 * You must specify the key by its full fingerprint to prevent accidental deletion of multiple keys.
	 *
	 * @param string $fingerprint
	 *
	 * @throws \Exception
	 *
	 * @return bool
	 */
	public function deleteKey($fingerprint){
		$result = $this->_exec('--with-colons --fixed-list-mode --with-fingerprint --batch --no-tty --delete-key ' . escapeshellarg($fingerprint));

		if($result['return'] != 0){
			throw new \Exception(trim($result['error']));
		}

		return true;
	}

	/**
	 * Encrypt a piece of information as either a string or binary data for one or more recipients
	 *
	 * @param string|mixed $data
	 * @param string|array $recipients
	 *
	 * @throws \Exception
	 *
	 * @return string ASCII armoured output of the encrypted data.
	 */
	public function encryptData($data, $recipients){
		if(is_array($recipients)){
			$recipientArguments = '';
			foreach($recipients as $r){
				$recipientArguments .= ' --recipient ' . escapeshellarg($r);
			}
		}
		else{
			$recipientArguments = ' --recipient ' . escapeshellarg($recipients);
		}

		$result = $this->_exec('--batch --no-tty' . $recipientArguments . ' --trust-model always --encrypt -a', $data);

		if($result['return'] != 0){
			// Fatal error
			throw new \Exception(trim($result['error']));
		}

		return $result['output'];
	}

	/**
	 * Verify the signature on a given file
	 *
	 * If only one argument is provided, it is expected that file contains both the file and signature as an attached sig.
	 *
	 * If two arguments are provided, the detached signature is the first argument and the content to verify is the second.
	 *
	 * @throws \Exception
	 *
	 * @param string|\Core\Filestore\File $file       Filename or File object of the file to verify
	 * @param string|\Core\Filestore\File $verifyFile Filename or File object of any detached signature
	 *
	 * @return Signature
	 */
	public function verifyFileSignature($file, $verifyFile = null){
		if($file instanceof \Core\Filestore\File){
			$filename = $file->getFilename();
		}
		else{
			$filename = $file;
		}

		if(!file_exists($filename)){
			throw new \Exception('Requested file does not exist, unable to verify signature!');
		}

		if($verifyFile === null){
			// Standard attached sig
			$result = $this->_exec('--with-fingerprint --batch --no-tty --verify ' . escapeshellarg($filename));
		}
		else{
			// Detached signature
			if($verifyFile instanceof \Core\Filestore\File){
				$sourceFilename = $verifyFile->getFilename();
			}
			else{
				$sourceFilename = $verifyFile;
			}

			$result = $this->_exec('--with-fingerprint --batch --no-tty --verify ' . escapeshellarg($filename) . ' ' . escapeshellarg($sourceFilename));
		}


		// If the result failed, then nothing else to do here.
		if($result['return'] !== 0){
			throw new \Exception($result['error']);
		}

		// Else, the calling script may want to know the results of the verification, eg: the key and date.
		// The metadata here is send to STDERR.  _Shrugs_
		$sig = new Signature();
		$sig->_parseOutputText($result['error']);
		return $sig;
	}

	/**
	 * Verify that some given data has a valid signature.
	 *
	 * Calls verifyFileSignature internally!
	 *
	 * @param string $signature
	 * @param string $content
	 *
	 * @throws \Exception
	 *
	 * @return Signature
	 */
	public function verifyDataSignature($signature, $content){
		// First, write a temporary file to contain the signature.
		$sig = \Core\Filestore\Factory::File('tmp/gpg-verify-' . \Core::RandomHex(6) . '.asc');
		$sig->putContents($signature);

		// And the content
		$con = \Core\Filestore\Factory::File('tmp/gpg-verify-' . \Core::RandomHex(6) . '.dat');
		$con->putContents($content);

		try{
			$result = $this->verifyFileSignature($sig, $con);
		}
		catch(\Exception $e){
			// Cleanup.
			$sig->delete();
			$con->delete();

			throw $e;
		}

		$sig->delete();
		$con->delete();
		return $result;
	}

	/**
	 * @param string     $arguments The command line arguments to run on the gpg binary
	 * @param null|mixed $inputData Any data to send over STDIN
	 *
	 * @return array Array of output from command
	 *
	 * @throws \Exception
	 */
	private function _exec($arguments, $inputData = null){
		$cmd = $this->_executable .
			' --homedir ' . escapeshellarg($this->homedir);

		if($this->ignorePermissionWarnings){
			$cmd .= ' --no-permission-warning';
		}

		$cmd .= ' ' . $arguments;

		$outputPipes = [];
		$descriptorspec = [
			0 => array("pipe", "r"),  // stdin
			1 => array("pipe", "w"),  // stdout
			2 => array("pipe", "w"),  // stderr
		];
		$process = proc_open($cmd, $descriptorspec, $outputPipes);

		if($inputData !== null){
			fwrite($outputPipes[0], $inputData);
			fclose($outputPipes[0]);
		}

		$stdout = stream_get_contents($outputPipes[1]);
		fclose($outputPipes[1]);

		$stderr = stream_get_contents($outputPipes[2]);
		fclose($outputPipes[2]);

		// Close the connection and read the output result.
		$result = proc_close($process);

		return [
			'cmd'    => $cmd,
		    'output' => $stdout,
		    'error'  => $stderr,
		    'return' => $result,
		];
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

	/**
	 * Internal function to parse output lines, (usually from gpg --recv-keys or gpg --list-keys),
	 * into an array of valid Key and SecretKey objects.
	 *
	 * @param array $output
	 *
	 * @return array
	 */
	private function _parseOutputLines($output){
		$keys = [];
		$k    = null; // Last Key

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
					if($k !== null) {
						// Let the previous key handle it!
						$k->parseLine($line);
					}
					break;
			}
		}

		// End of output and there is data?
		if($k !== null){
			$keys[] = $k;
		}


		return $keys;
	}

	/**
	 * Parse an author string, (such as "Name Last (comment about this key) <email@domain.tld>")
	 *
	 * @param $string
	 *
	 * @returns array Returns an array with columns 'name', 'email', and 'comment'.
	 */
	public static function ParseAuthorString($string){
		$ret = ['name' => '', 'email' => '', 'comment' => ''];

		$ret['name'] = substr($string, 0, strpos($string, '<')-1);
		$ret['email'] = substr($string, strlen($ret['name']) + 2, -1);

		if(($b = strpos($ret['name'], '(')) !== false && strpos($ret['name'], ')') !== false){
			$ret['comment'] = trim(substr($ret['name'], $b+1, -1));
			$ret['name'] = trim(substr($ret['name'], 0, $b));
		}

		return $ret;
	}

	/**
	 * Format a given fingerprint in pretty format.
	 *
	 * @param string $fingerprint The raw fingerprint
	 * @param bool   $html        Set to true to return HTML elements instead of plain text.
	 * @param bool   $oneLine     Set to true to return only 1 lines instead of 2.
	 *
	 * @return string Formatted fingerprint
	 */
	public static function FormatFingerprint($fingerprint, $html = false, $oneLine = false){
		// No input?  SIMPLE!
		if(trim($fingerprint) == ''){
			return '';
		}

		// Break up the two lines of the fingerprint.
		$parts = str_split($fingerprint, 20);

		$out = wordwrap($parts[0], 4, ' ', true);
		if($oneLine){
			$out .= $html ? '&nbsp;&nbsp;' : '  ';
		}
		else{
			$out .= $html ? '<br/>' : "\n";
		}
		$out .= wordwrap($parts[1], 4, ' ', true);

		return $out;
	}
} 