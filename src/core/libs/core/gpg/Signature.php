<?php
/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 11/18/15
 * Time: 1:43 PM
 */

namespace Core\GPG;


class Signature {
	/** @var bool T/F if this is a valid signature */
	public $isValid = false;
	/** @var string Full RFC2822 version of the data (DoW d Mon Year Time TZ) */
	public $dateTime;
	/** @var string Full fingerprint of this signature */
	public $fingerprint;
	/** @var string Short key ID of the signing key */
	public $keyID;
	/** @var string Encryption type of the signing key */
	public $encType;
	/** @var string Email of the signing key, (only first line) */
	public $signingEmail;
	/** @var string Full Name of the signing key, (only first line) */
	public $signingName;

	public function _parseOutputText($text){
		/* EXAMPLE INPUT:
gpg: Signature made Wed 18 Nov 2015 01:22:49 PM EST using RSA key ID B04EFAD6
gpg: Good signature from "Core Plus Test Key (Just a test key for phpunit) <invalid-donotuse@corepl.us>"
Primary key fingerprint: 4E73 30EB 2A84 D747 9B71  9FF3 3F20 C906 B04E FAD6

-- OR --

gpg: Signature made Wed Apr  2 03:36:20 2014 EDT
gpg:                using DSA key DB0AD0EBB2BEDCCB
gpg: Note: trustdb not writable
gpg: Good signature from "Charlie Powell <charlie@evalagency.com>" [unknown]
gpg:                 aka "Charlie Powell <powellc@powelltechs.com>" [unknown]
gpg:                 aka "Charlie Powell <charlie@eval.bz>" [unknown]
gpg:                 aka "[jpeg image of size 4506]" [unknown]
gpg:                 aka "[jpeg image of size 3568]" [unknown]
		 */

		preg_match('/gpg: Signature made ([a-zA-Z 0-9:+-]*).*using ([A-Z]*) (key ID|key) ([A-F0-9]*).*gpg: Good signature from "([^"]*)".*Primary key fingerprint: ([A-F0-9 ]*).*/s', $text, $matches);

		$this->isValid      = (strpos('gpg: Good Signature from', $text) === false);
		$this->dateTime     = $matches[1];
		$this->fingerprint  = str_replace(' ', '', $matches[6]); // Remove spaces/formatting.
		$this->keyID        = $matches[4];
		$this->encType      = $matches[2];
		$split              = GPG::ParseAuthorString($matches[5]);
		$this->signingEmail = $split['email'];
		$this->signingName  = $split['name'];
	}
}