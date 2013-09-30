<?php
/**
 * @package Nonce
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
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

/**
 * Nonce model and helper methods.
 *
 * Contains the root Model object for Nonces as well as the utilities for using Nonces.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * <h4>To generate a basic nonce in the database</h4>
 * <p>A basic creation of a verifiable-unique key.  This expires by default in two hours from creation.</p>
 * <code>
 * // And this will be a guaranteed unique and pseudo-cryptographically random key.
 * $my_unique_key = NonceModel::Generate();
 * </code>
 *
 *
 * <h4>Vaidate a saved nonce (Short Version)</h4>
 * <p>A basic example of verifying and validating a saved nonce key.</p>
 * <code>
 * // This is retrieved from another source, it's a string
 * // $my_unique_key = "123-random-key"
 *
 * if(NonceModel::ValidateAndUse($my_unique_key)){
 *   // Do something awesome
 * }
 * else{
 *   // FAIL!  Alert the user.
 * }
 * </code>
 *
 *
 * <h4>Validate a saved nonce (Long Version)</h4>
 * <p>Slightly longer version of verifying and validating a saved nonce key.</p>
 * <code>
 * // This is retrieved from another source, it's a string
 * // $my_unique_key = "123-random-key"
 *
 * // Get the instance of a NonceModel object.
 * $nonce = NonceModel::Construct($my_unique_key);
 *
 * // Is it valid?
 * if(!$nonce->isValid(){
 *   // do something on error
 * }
 * else{
 *   // Yay, do something on success
 *   // And mark this nonce as used
 *   $nonce->markUsed();
 * }
 * </code>
 *
 *
 * <h4>Using secure hashes</h4>
 * <p>
 * Sometimes you <i>really</i> want to validate that this nonce is meant to be attached to
 * exactly what you want it to be.
 * </p>
 * <code>
 * // I have an array of data that is attached to this object, like an email, user id, download id,
 * // etc.  With that, I can either send in the data verbatim, (providing it's small enough), or simply
 * // make a basic hash with it.
 * // $thedata = ['id' => 123]
 *
 * // Generate a nonce with this hash attached to it.
 * // And I'm going to make it valid for 1 day.
 * $noncekey = NonceModel::Generate('1 day', $hash);
 *
 * // Do some other logic, get some coffee, etc.
 * // ...
 * // ...
 *
 * // Now later on in the day, let's say $thedata contains the same data as above.
 * if(NonceModel::ValidateAndUse($noncekey, $thedata)){
 *   // yay, do something über secure or what not.
 * }
 * </code>
 *
 * <h4>Storing Data</h4>
 * <p>
 * A nonce can be generated with a payload attached to it that's returned on validation.
 * This is useful if you want to store some bit of information securely, and retrieve it on another pageload, say a custom form submission.
 * </p>
 *
 * <code>
 * $supersecretdata = ['something', 'blah', 'more stuffs'];
 * $noncekey = NonceModel::Generate('2 hours', null, $supersecretdata);
 *
 * // The $noncekey is now literally a key to that data.  Anyone with it can retrieve the data.
 * // So later on in the session...
 * $nonce = new Nonce($noncekey);
 * $savedsecretdata = $nonce->get('data');
 * // $supersecretdata == $savedsecretdata
 * </code>
 *
 * @package Nonce
 * @author Charlie Powell <charlie@eval.bz
 *
 */
class NonceModel extends Model {
	/**
	 * Schema definition for NonceModel
	 *
	 * @static
	 * @var array
	 */
	public static $Schema = array(
		'key' => array(
			'type' => Model::ATT_TYPE_STRING,
			'required' => true,
		),
		'expires' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
		),
		'hash' => array(
			'type' => Model::ATT_TYPE_STRING,
			'required' => false,
			'maxlength' => 64,
			'comment' => 'An optional hash usable to verify this is matching exactly what is expected',
		),
		'hash_version' => array(
			'type' => Model::ATT_TYPE_INT,
			'default' => '1',
		),
		'data' => array(
			'type'      => Model::ATT_TYPE_DATA,
			'comment'   => 'Large column space for JSON, serialized, or any other data',
			'encrypted' => true,
		)
	);

	/**
	 * Index definition for NonceModel
	 *
	 * @static
	 * @var array
	 */
	public static $Indexes = array(
		'primary' => array('key'),
	);

	public function get($k){
		if($k == 'data'){
			$try = unserialize(parent::get($k));
			if(!$try) return parent::get($k);
			else return $try;
		}
		else{
			return parent::get($k);
		}
	}

	/**
	 * Check and see if this nonce is valid and has not expired yet.
	 *
	 * @param string $hash The optional check to confirm that this is exactly the nonce you're expecting.
	 * @return bool
	 */
	public function isValid($hash = null){
		// Nonces are not valid if they're not recorded!
		if(!$this->exists()) return false;
		// Expired ones aren't valid either!
		if($this->get('expires') < CoreDateTime::Now('U', Time::TIMEZONE_GMT)) return false;

		// Only check the hash if it's requested.
		if($hash){
			switch($this->get('hash_version')){
				case 1:
					if($this->_generateHashValue1($hash) != $this->get('hash')) return false;
				break;
				case 2:
					if($this->_generateHashValue2($hash) != $this->get('hash')) return false;
				break;
				default:
					/// Ummm...
					return false;
			}
		}

		return true;
	}

	/**
	 * Mark this nonce key as used.
	 * (Actually this just deletes the record from the database)
	 */
	public function markUsed(){
		$this->delete();
		// And purge out some of the previous info.
		$this->set('key', null);
		$this->set('hash', null);
	}

	/**
	 * Set the hash for this nonce.
	 * Will automatically convert any data or overlength'd text to a sha2 string.
	 *
	 * @param string|null|mixed $data The string, number, or data as a whole to hash.
	 */
	public function setHash($data = null){
		$newhash = $this->_generateHashValue2($data);
		parent::set('hash', $newhash);
		parent::set('hash_version', 2);
	}

	/**
	 * Generate the hashed value of a given data, (Version 1)
	 *
	 * @param string|null|mixed $data The string, number, or data as a whole to hash.
	 * @return string
	 */
	private function _generateHashValue1($data){
		if($data === null){
			// Null hashes simply get saved as empty strings.
			return '';
		}
		elseif(is_scalar($data) && strlen($data) <= 32 && preg_match('/^[a-zA-Z0-9]*$/', $data)){
			// String data or numeric data that are < 32 characters long can be saved directly as-is.
			return $data;
		}
		else{
			// Hash it to make it fit.
			$data = hash('sha256', serialize($data));
			return $data;
		}
	}

	/**
	 * Generate the hashed value of a given data, (Version 2)
	 *
	 * @param string|null|mixed $data The string, number, or data as a whole to hash.
	 * @return string
	 */
	private function _generateHashValue2($data){
		if($data === null){
			// Null hashes simply get saved as empty strings.
			return '';
		}
		elseif(is_scalar($data) && strlen($data) <= 32 && preg_match('/^[a-zA-Z0-9]*$/', $data)){
			// String data or numeric data that are < 32 characters long can be saved directly as-is.
			return $data;
		}
		elseif(is_scalar($data)){
			// String or numeric data can simply be hashed directly.
			$data = hash('sha256', $data);
			return $data;
		}
		elseif(is_array($data)){
			// Arrays can be transposed to a string.
			// This is to allow ['key' => 123] to be validated with ['key' => '123'].
			$d = '';
			foreach($data as $k => $v){
				$d .= $k . ':';
				if(is_scalar($v)){
					$d .= $v;
				}
				else{
					$d .= serialize($v);
				}
				$d .= ';';
			}
			$data = hash('sha256', $d);
			return $data;
		}
		else{
			// Hash it to make it fit.
			$data = hash('sha256', serialize($data));
			return $data;
		}
	}

	/**
	 * Use /dev/urandom to generate a pseudo-random key for this nonce.
	 */
	private function _generateAndSetKey(){
		// This will guarantee that a key is unique.
		// A UUID is based on the current server, (in the group), date, and a small amount of entropy.
		$key = Core::GenerateUUID();

		// But since this is designed to be somewhat secure... I want to be a little more cryptographically secure.
		$fp = fopen('/dev/urandom','rb');
		if ($fp !== FALSE) {
			$bits = fread($fp, 16);
			fclose($fp);
		}
		// Convert that to ASCII
		$bits_b64 = base64_encode($bits);
		// Damn "==" of base64 :/
		$bits_b64 = substr($bits_b64, 0, -2);
		// And append.
		$key .= $bits_b64;

		// And convert spaces and other invalid characters to a random digit.
		$randombit = Core::RandomHex(2);
		$key = str_replace(['+', ' ', '\\', '/', ], $randombit, $key);

		// make sure it's all lowercase... URLs don't like capital letters!
		$key = strtolower($key);

		$this->set('key', $key);
	}

	/**
	 * Shorthand function to generate and save a valid Nonce key.
	 *
	 * @param string            $expires A human-readable string of an expire time in the future, ie: 2 seconds, 10 days, etc.
	 * @param string|null|mixed $hash    Can be used to validate this nonce from an external verification check.
	 * @param mixed             $data    Any data that is needed to be stored along with this nonce.
	 * @return string
	 */
	public static function Generate($expires = '2 hours', $hash = null, $data = null){
		$nonce = new NonceModel();
		$nonce->_generateAndSetKey();
		$nonce->setHash($hash);

		// Determine the datetime that this nonce expires.
		$date = new CoreDateTime();
		$date->modify($expires);
		$nonce->set('expires', $date->getFormatted('U', Time::TIMEZONE_GMT));

		$nonce->set('data', serialize($data));
		$nonce->save();

		return $nonce->get('key');
	}

	/**
	 * Shorthand function to validate and "mark as used" a Nonce key.
	 * @param string            $key  The nonce key to validate
	 * @param string|null|mixed $hash An optional hash to validate also
	 *
	 * @return boolean
	 */
	public static function ValidateAndUse($key, $hash = null){
		/** @var $nonce NonceModel */
		$nonce = NonceModel::Construct(strtolower($key));
		if($nonce->isValid($hash)){
			$nonce->markUsed();
			return true;
		}
		else{
			return false;
		}
	}

	/**
	 * Shorthand function to lookup a nonce from the database.
	 *
	 * @param $key
	 * @return NonceModel
	 */
	public static function LookupKey($key){
		$key = strtolower($key);
		$search = NonceModel::Find(['key' => $key], 1);

		return $search;
	}

	/**
	 * Function to cleanup expired nonce keys and data.
	 * This is meant to be called in a cron, be it hourly, daily, etc.
	 *
	 * @return bool Exit status
	 */
	public static function Cleanup(){
		$date = new CoreDateTime();
		$expired = self::Find(['expires > 0', 'expires <= ' . $date->getFormatted('U', Time::TIMEZONE_GMT)]);
		foreach($expired as $e){
			$e->delete();
		}
		return true;
	}
}