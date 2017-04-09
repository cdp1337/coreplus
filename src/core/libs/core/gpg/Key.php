<?php
/**
 * File for class Key definition in the coreplus project
 * 
 * @package Core\GPG
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140319.1923
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

namespace Core\GPG;


/**
 * A short teaser of what Key does.
 *
 * More lengthy description of what Key does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for Key
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
class Key {

	/**
	 * o = Unknown (this key is new to the system)
	 * i = The key is invalid (e.g. due to a missing self-signature)
	 * d = The key has been disabled (deprecated - use the 'D' in field 12 instead)
	 * r = The key has been revoked
	 * e = The key has expired
	 * - = Unknown validity (i.e. no value assigned)
	 * q = Undefined validity
	 * '-' and 'q' may safely be treated as the same value for most purposes
	 * n = The key is valid
	 * m = The key is marginal valid.
	 * f = The key is fully valid
	 * u = The key is ultimately valid.  This often means that the secret key is available, but any key may be marked as ultimately valid.
	 *
	 * @var string
	 */
	public $validity;

	/**
	 * @var int
	 */
	public $encryptionBits;

	/**
	 * @var string
	 */
	public $encryptionType;

	/**
	 * @var string The full ID of this key
	 */
	public $id;

	/**
	 * @var string The short ID of this key
	 */
	public $id_short;

	/**
	 * @var string Serial number of this key
	 */
	public $serial;

	/**
	 * @var string The fingerprint of this key
	 */
	public $fingerprint;

	/**
	 * @var int Created date in UTC time stamp
	 */
	public $created;

	/**
	 * @var int Expiration date in UTC time stamp
	 */
	public $expires;

	const ENCRYPTION_TYPE_RSA = 'RSA';
	const ENCRYPTION_TYPE_DSA = 'DSA';
	const ENCRYPTION_TYPE_ELGAMAL = 'Elgamal';

	/**
	 * Check and see if this key is currently valid and not expired nor revoked.
	 *
	 * @return bool
	 */
	public function isValid(){
		/*
		 * o = Unknown (this key is new to the system)
		 * i = The key is invalid (e.g. due to a missing self-signature)
		 * d = The key has been disabled (deprecated - use the 'D' in field 12 instead)
		 * r = The key has been revoked
		 * e = The key has expired
		 * - = Unknown validity (i.e. no value assigned)
		 * q = Undefined validity
		 * '-' and 'q' may safely be treated as the same value for most purposes
		 * n = The key is valid
		 * m = The key is marginal valid.
		 * f = The key is fully valid
		 * u = The key is ultimately valid.  This often means that the secret key is available, but any key may be marked as ultimately valid.
		 */

		switch($this->validity){
			case 'i':
			case 'd':
			case 'D':
			case 'r':
			case 'e':
				return false;
			default:
				return true;
		}
	}
}