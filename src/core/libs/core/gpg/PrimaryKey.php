<?php
/**
 * File for class PrimaryKey definition in the coreplus project
 * 
 * @package Core\GPG
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140319.2122
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

namespace Core\GPG;


/**
 * A short teaser of what PrimaryKey does.
 *
 * More lengthy description of what PrimaryKey does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for PrimaryKey
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
class PrimaryKey extends Key {

	/**
	 * @var array Array of UID keys on this primary key
	 */
	public $uids = [];

	/**
	 * @var array Array of sub keys on this primary key
	 */
	public $subkeys = [];
	
	/** @var null|array Cache of photos on the filesystem for this primary key. */
	private $_photos = null;

	/**
	 * Check and see if this key is currently valid and not expired nor revoked.
	 *
	 * Optionally, pass in an email address to check the key's specific UID and see if that subkey is valid.
	 *
	 * @param string|null $email Optional email to verify that needs to be attached to this key.
	 *
	 * @return bool
	 */
	public function isValid($email = null){
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
		}

		if($email){
			// Also check for that subkey.
			$uid = $this->getUID($email);
			if(!$uid){
				// UID not attached, means it's not valid!
				return false;
			}
			if(!$uid->isValid()){
				return false;
			}
		}

		// Otherwise, all checks passed.
		return true;
	}

	/**
	 * @param $email
	 *
	 * @return UID|null
	 */
	public function getUID($email){
		foreach($this->uids as $u){
			/** @var UID $u */
			if($u->email == $email){
				return $u;
			}
		}
		return null;
	}

	/**
	 * Get an array containing all the photos for this public key
	 * 
	 * @return array
	 */
	public function getPhotos(){
		if($this->_photos === null){
			$this->_photos = [];
			// By specifying these options, the contents are extracted automatically and the filename is rendered.
			$gpg = new GPG();
			$o = $gpg->_exec('--list-keys --list-options show-photos --photo-viewer "echo %I >&2" ' . $this->fingerprint . ' 1>/dev/null ');
			
			if($o['return'] === 0){
				// In this case, stderr should contain the output.
				$files = explode("\n", $o['error']);

				foreach($files as $f){
					if($f){
						$this->_photos[] = \Core\Filestore\Factory::File($f);
					}
				}
			}
		}
		
		return $this->_photos;
	}

	/**
	 * Parse a line from --with-colons output.
	 *
	 * <pre>
	 * pub:f:1024:17:6C7EE1B8621CC013:899817715:1055898235::m:::scESC:
	 * fpr:::::::::ECAF7590EB3443B5C7CF3ACB6C7EE1B8621CC013:
	 * uid:f::::::::Werner Koch <wk@g10code.com>:
	 * uid:f::::::::Werner Koch <wk@gnupg.org>:
	 * sub:f:1536:16:06AD222CADF6A6E1:919537416:1036177416:::::e:
	 * fpr:::::::::CF8BCC4B18DE08FCD8A1615906AD222CADF6A6E1:
	 * sub:r:1536:20:5CE086B5B5A18FF4:899817788:1025961788:::::esc:
	 * fpr:::::::::AB059359A3B81F410FCFF97F5CE086B5B5A18FF4:
	 *
	 * The double --with-fingerprint prints the fingerprint for the subkeys
	 * too. --fixed-list-mode is the modern listing way printing dates in
	 * seconds since Epoch and does not merge the first userID with the pub
	 * record; gpg2 does this by default and the option is a dummy.
	 *
	 *
	 * 1. Field:  Type of record
	 * pub = public key
	 * crt = X.509 certificate
	 * crs = X.509 certificate and private key available
	 * sub = subkey (secondary key)
	 * sec = secret key
	 * ssb = secret subkey (secondary key)
	 * uid = user id (only field 10 is used).
	 * uat = user attribute (same as user id except for field 10).
	 * sig = signature
	 * rev = revocation signature
	 * fpr = fingerprint: (fingerprint is in field 10)
	 * pkd = public key data (special field format, see below)
	 * grp = keygrip
	 * rvk = revocation key
	 * tru = trust database information
	 * spk = signature subpacket
	 *
	 * 2. Field:  A letter describing the calculated validity. This is a single
	 * letter, but be prepared that additional information may follow
	 * in some future versions. (not used for secret keys)
	 * o = Unknown (this key is new to the system)
	 * i = The key is invalid (e.g. due to a missing self-signature)
	 * d = The key has been disabled
	 * (deprecated - use the 'D' in field 12 instead)
	 * r = The key has been revoked
	 * e = The key has expired
	 * - = Unknown validity (i.e. no value assigned)
	 * q = Undefined validity
	 * '-' and 'q' may safely be treated as the same
	 * value for most purposes
	 * n = The key is valid
	 * m = The key is marginal valid.
	 * f = The key is fully valid
	 * u = The key is ultimately valid.  This often means
	 * that the secret key is available, but any key may
	 * be marked as ultimately valid.
	 *
	 * If the validity information is given for a UID or UAT
	 * record, it describes the validity calculated based on this
	 * user ID.  If given for a key record it describes the best
	 * validity taken from the best rated user ID.
	 *
	 * For X.509 certificates a 'u' is used for a trusted root
	 * certificate (i.e. for the trust anchor) and an 'f' for all
	 * other valid certificates.
	 *
	 * 3. Field:  length of key in bits.
	 *
	 * 4. Field:  Algorithm:	1 = RSA
	 * 16 = Elgamal (encrypt only)
	 * 17 = DSA (sometimes called DH, sign only)
	 * 20 = Elgamal (sign and encrypt - don't use them!)
	 * (for other id's see include/cipher.h)
	 *
	 * 5. Field:  KeyID
	 *
	 * 6. Field:  Creation Date (in UTC).  For UID and UAT records, this is
	 * the self-signature date.  Note that the date is usally
	 * printed in seconds since epoch, however, we are migrating
	 * to an ISO 8601 format (e.g. "19660205T091500").  This is
	 * currently only relevant for X.509.  A simple way to detect
	 * the new format is to scan for the 'T'.
	 *
	 * 7. Field:  Key or user ID/user attribute expiration date or empty if none.
	 *
	 * 8. Field:  Used for serial number in crt records (used to be the Local-ID).
	 * For UID and UAT records, this is a hash of the user ID contents
	 * used to represent that exact user ID.  For trust signatures,
	 * this is the trust depth seperated by the trust value by a
	 * space.
	 *
	 * 9. Field:  Ownertrust (primary public keys only)
	 * This is a single letter, but be prepared that additional
	 * information may follow in some future versions.  For trust
	 * signatures with a regular expression, this is the regular
	 * expression value, quoted as in field 10.
	 *
	 * 10. Field:  User-ID.  The value is quoted like a C string to avoid
	 * control characters (the colon is quoted "\x3a").
	 * For a "pub" record this field is not used on --fixed-list-mode.
	 * A UAT record puts the attribute subpacket count here, a
	 * space, and then the total attribute subpacket size.
	 * In gpgsm the issuer name comes here
	 * An FPR record stores the fingerprint here.
	 * The fingerprint of an revocation key is stored here.
	 *
	 * 11. Field:  Signature class as per RFC-4880.  This is a 2 digit
	 * hexnumber followed by either the letter 'x' for an
	 * exportable signature or the letter 'l' for a local-only
	 * signature.  The class byte of an revocation key is also
	 * given here, 'x' and 'l' is used the same way.  IT is not
	 * used for X.509.
	 *
	 * 12. Field:  Key capabilities:
	 * e = encrypt
	 * s = sign
	 * c = certify
	 * a = authentication
	 * A key may have any combination of them in any order.  In
	 * addition to these letters, the primary key has uppercase
	 * versions of the letters to denote the _usable_
	 * capabilities of the entire key, and a potential letter 'D'
	 * to indicate a disabled key.
	 *
	 * 13. Field:  Used in FPR records for S/MIME keys to store the
	 * fingerprint of the issuer certificate.  This is useful to
	 * build the certificate path based on certificates stored in
	 * the local keyDB; it is only filled if the issuer
	 * certificate is available. The root has been reached if
	 * this is the same string as the fingerprint. The advantage
	 * of using this value is that it is guaranteed to have been
	 * been build by the same lookup algorithm as gpgsm uses.
	 * For "uid" records this lists the preferences in the same
	 * way the gpg's --edit-key menu does.
	 * For "sig" records, this is the fingerprint of the key that
	 * issued the signature.  Note that this is only filled in if
	 * the signature verified correctly.  Note also that for
	 * various technical reasons, this fingerprint is only
	 * available if --no-sig-cache is used.
	 *
	 * 14. Field   Flag field used in the --edit menu output:
	 *
	 * 15. Field   Used in sec/sbb to print the serial number of a token
	 * (internal protect mode 1002) or a '#' if that key is a
	 * simple stub (internal protect mode 1001)
	 * 16. Field:  For sig records, this is the used hash algorithm:
	 * 2 = SHA-1
	 * 8 = SHA-256
	 * (for other id's see include/cipher.h)
	 *
	 * All dates are displayed in the format yyyy-mm-dd unless you use the
	 * option --fixed-list-mode in which case they are displayed as seconds
	 * since Epoch.  More fields may be added later, so parsers should be
	 * prepared for this. When parsing a number the parser should stop at the
	 * first non-number character so that additional information can later be
	 * added.
	 *
	 * If field 1 has the tag "pkd", a listing looks like this:
	 * pkd:0:1024:B665B1435F4C2 .... FF26ABB:
	 * !  !   !-- the value
	 * !  !------ for information number of bits in the value
	 * !--------- index (eg. DSA goes from 0 to 3: p,q,g,y)
	 * </pre>
	 *
	 * @param string $line
	 */
	public function parseLine($line){
		$parts = explode(':', $line);

		switch($parts[0]){
			case 'pub':
			case 'sec':
				// Primary key, either public or secret.
				if(sizeof($parts) == 13){
					self::_ParseKeyLine13($parts, $this);
				}
				else{
					self::_ParseKeyLine7($parts, $this);
				}

				break;
			case 'fpr':
				// Fingerprint
				$this->fingerprint = $parts[9];
				break;
			case 'uid':
				// UID sub key
				$uid = new UID();
				if(sizeof($parts) == 11){
					self::_ParseSubUIDLine11($parts, $uid);
				}
				else{
					self::_ParseSubUIDLine5($parts, $uid);
				}
				$this->uids[] = $uid;

				break;
			case 'uat':
				// Skip user attributes.
				break;
			case 'sub':
				$sub = new Key();
				if(sizeof($parts) == 13){
					self::_ParseKeyLine13($parts, $sub);
				}
				else{
					self::_ParseKeyLine7($parts, $sub);
				}
				$this->subkeys[] = $sub;
				break;
			/*
* crt = X.509 certificate
* crs = X.509 certificate and private key available
* sub = subkey (secondary key)
* ssb = secret subkey (secondary key)
* uid = user id (only field 10 is used).
* uat = user attribute (same as user id except for field 10).
* sig = signature
* rev = revocation signature
* fpr = fingerprint: (fingerprint is in field 10)
* pkd = public key data (special field format, see below)
* grp = keygrip
* rvk = revocation key
* tru = trust database information
* spk = signature subpacket
			 */
		}
	}

	protected static function _ParseKeyLine13($parts, Key $key){
		/*
		 * (0-index of keys)
		 * 0.  Field:  Type of record
		 * 1.  Field:  A letter describing the calculated validity.
		 * 2.  Field:  length of key in bits.
		 * 3.  Field:  Algorithm:
		 * 4.  Field:  KeyID
		 * 5.  Field:  Creation Date (in UTC).
		 * 6.  Field:  Key or user ID/user attribute expiration date or empty if none.
		 * 7.  Field:  Used for serial number in crt records (used to be the Local-ID).
		 * 8.  Field:  Ownertrust (primary public keys only)
		 * 9.  Field:  User-ID.
		 * 10. Field:  Signature class as per RFC-4880.
		 * 11. Field:  Key capabilities
		 * 12. Field:  Used in FPR records for S/MIME keys to store the fingerprint of the issuer certificate.
		 * 13. Field:  Flag field used in the --edit menu output:
		 * 14. Field:  Used in sec/sbb to print the serial number
		 * 15. Field:  For sig records, this is the used hash algorithm:
		 */
		//$this->type = $parts[0];
		$key->validity = $parts[1];
		$key->encryptionBits = $parts[2];

		switch($parts[3]){
			case '1':
				$key->encryptionType = Key::ENCRYPTION_TYPE_RSA;
				break;
			case '16':
				$key->encryptionType = Key::ENCRYPTION_TYPE_ELGAMAL;
				break;
			case '17':
				$key->encryptionType = Key::ENCRYPTION_TYPE_DSA;
				break;
			case '20':
				$key->encryptionType = Key::ENCRYPTION_TYPE_ELGAMAL;
				break;
		}

		$key->id = $parts[4];
		$key->id_short = substr($parts[4], -8);
		$key->created = $parts[5];
		$key->expires = $parts[6];
	}

	/**
	 * Parse a colon-delimited string for a 7-part data field, (usually from remote sources).
	 *
	 * @param     $parts
	 */
	protected static function _ParseKeyLine7($parts, Key $key){
		/*
		 * (0-index of keys)
		 * 0.  Field:  Type of record
		 * 1.  Field:  KeyID
		 * 2.  Field:  Algorithm:
		 * 3.  Field:  length of key in bits.
		 * 4.  Field:  Creation Date (in UTC).
		 * 5.  Field:  ???
		 * 6.  Field:  ???
		 */
		//$this->type = $parts[0];
		$key->encryptionBits = $parts[3];

		switch($parts[2]){
			case '1':
				$key->encryptionType = Key::ENCRYPTION_TYPE_RSA;
				break;
			case '16':
				$key->encryptionType = Key::ENCRYPTION_TYPE_ELGAMAL;
				break;
			case '17':
				$key->encryptionType = Key::ENCRYPTION_TYPE_DSA;
				break;
			case '20':
				$key->encryptionType = Key::ENCRYPTION_TYPE_ELGAMAL;
				break;
		}

		$key->id = $parts[1];
		$key->fingerprint = $parts[1]; // On remote servers, they rarely give the fingerprint.
		$key->id_short = substr($parts[1], -8);
		$key->created = $parts[4];
		//$key->expires = $parts[6];
	}

	protected static function _ParseSubUIDLine11($parts, UID $uid){
		$uid->validity = $parts[1];
		$uid->created = $parts[5];
		$uid->expires = $parts[6];
		$uid->serial = $parts[7];

		$split = GPG::ParseAuthorString($parts[9]);
		$uid->fullname = $split['name'];
		$uid->email    = $split['email'];
		$uid->comment  = $split['comment'];
	}

	protected static function _ParseSubUIDLine5($parts, UID $uid){
		/*
		 * (0-index of keys)
		 * 0.  Field:  Type of record
		 * 1.  Field:  Key name and/or email
		 * 2.  Field:  Creation Date (in UTC).
		 * 3.  Field:  ???
		 * 4.  Field:  ???
		 */
		//$uid->validity = $parts[1];
		$uid->created = $parts[2];
		//$uid->expires = $parts[6];
		//$uid->serial = $parts[7];
		$split = GPG::ParseAuthorString($parts[1]);
		$uid->fullname = $split['name'];
		$uid->email    = $split['email'];
		$uid->comment  = $split['comment'];
	}

} 