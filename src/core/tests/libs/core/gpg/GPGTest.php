<?php

/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 11/17/15
 * Time: 3:05 PM
 */
class GPGTest extends PHPUnit_Framework_TestCase {

	public $fingerprint = '4E7330EB2A84D7479B719FF33F20C906B04EFAD6';
	public $key         = 'B04EFAD6';
	public $email       = 'invalid-donotuse@corepl.us';
	public $name        = 'Core Plus Test Key';
	public $comment     = 'Just a test key for phpunit';
	public $bits        = 1536;
	public $enc         = 'RSA';
	public $notGPGFile  = ROOT_PDIR . 'core/tests/ivak_TV_Test_Screen.png';
	public $verifyFile  = ROOT_PDIR . 'core/tests/ivak_TV_Test_Screen.png.gpg';
	public $pubkey      = <<<EOD
-----BEGIN PGP PUBLIC KEY BLOCK-----
Version: GnuPG v1

mM0EVkuSXgEGAM1J5Fuhdnd5KyBJXJbb6sEZQMaEt4pKR9mPmnEmGGb2yS993AzT
uBCn0bVZBHLvRETJVsENO+oYGJi7wv9+zphyFrYtrcKJ11hkHva2KMSQB4FAdQXn
MXVrpaSXc7R69e5R7LOAfnlXOJ4587et2rbJJgg4I4QwsF4gWGvyIbWEqEnCIm4G
XExljwB6ZWSUE8QeK+TEl1U2Y9Bqt410aiYXZMRyAltpvhYAw0cCGCr9txEife9v
wv57uEDSDz9TAwARAQABtE1Db3JlIFBsdXMgVGVzdCBLZXkgKEp1c3QgYSB0ZXN0
IGtleSBmb3IgcGhwdW5pdCkgPGludmFsaWQtZG9ub3R1c2VAY29yZXBsLnVzPoj5
BBMBCAAjBQJWS5JeAhsjBwsJCAcDAgEGFQgCCQoLBBYCAwECHgECF4AACgkQPyDJ
BrBO+tZTSQYApapR7EHjTTS8KuTuZq7P2Ypst5Aysm6TYutiH8vIleZOzxUUKoyw
Jib9NVPJyiXGEFwoCSaKW1zmrJIgcgOaR6jbZI7Pu3323AqGzTKsifo+Y7qWIHLZ
dVVUzZBmmOUy3DOKhKXAtTExfkCANjlcGEv+NuNxe3/KkIIvCD1XiLqA6ZNaBqAj
HgoJn0BYVdz1lVw0GAWrf8ZgJvzqwrqO/fd4GT3pfSv9/sV9r1ERNHK9tlCp2LmO
YytMyWIHK5XSuM0EVkuSXgEGANCHsGri5nN4bM4oLcuUmzyWvbTEOS/qHkJjpWjE
DArLmRpYkKJjjZ/VlS3tCAG7gTnAip6rIO805oYoBP4G7UYBQtqjAjvnqinEM1e/
iZoemQR3okyS0akbNUiUZy95dhtNsIFgufcgtJw2s59bj8JbfCshUlJSdrOX+mYH
duUDeSNP1uz3Igb62gH0lhR5vDJXd6WPb5XQVCIf5dKxCtm+r0Kln8bND0LldKtD
dJpyfgL6T+LqfqjTdOWMmzRTOwARAQABiN8EGAEIAAkFAlZLkl4CGwwACgkQPyDJ
BrBO+tal1QX+Pkg7b46qDpqMSKYJlYTICHUCp2FW69yaMfuwgFmGhys0RE7N1yJ9
HoCo/wFDkOxzIAKIu7uPWzldsoE3DeaTevQ9JAux7vxf10q3dwiGtuwsMe5AFqgP
KH2rW7xiIvvM7V477U71GznPRD6VTPvPOFvqdLNOzJxzg71hUc3K9j+MQoZKvPmX
pvJAhtTR7VKY5D6rsAmrEQKQvX4sUszyjD7ILD6L23AeAG05CmTouoywU3UmPOt3
eb/3g0PSOAoT
=C6rq
-----END PGP PUBLIC KEY BLOCK-----
EOD;
	public $sigDetached = <<<EOD
-----BEGIN PGP SIGNATURE-----
Version: GnuPG v1

iNwEAAECAAYFAlZM0dQACgkQPyDJBrBO+tYLjwYAkSWXV09RttFmdrnmOhQk5ixW
z9q0lQ2+OMRnwPmQlgvuR+Mkcp1s6ZRGtG8/ptKw5gTM14JlaPYg9Dm8s6PAVq0m
t5ZTUW2YmJr5oPYgNJ3RbtWPZ1md5LOjoxGA3iNvth4JRwdz8o4XPh9oCBI9zz3r
sbaJFNw+vHn07s2ACSYVaJOfIDBBGWSvkziWuY3LCxlFfjsgIPM32RnPjYW+QDnb
87r8iIbLNA1SGJPgQ/jeS3HEBuFsiW+U+9YMYkds
=T2b7
-----END PGP SIGNATURE-----
EOD;



	public function testSearchRemoteKeys(){
		$gpg = new \Core\GPG\GPG();
		$keys = $gpg->searchRemoteKeys($this->key);

		$this->assertTrue(is_array($keys), 'GPG->searchRemoteKeys did not return an array!');

		if(sizeof($keys) == 0){
			$this->markTestSkipped('Unable to perform test, GPG key ' . $this->key . ' was not located on remote keyservers!');
		}

		foreach($keys as $k){
			$this->assertInstanceOf('Core\\GPG\\PrimaryKey', $k);
			/** @var Core\GPG\PrimaryKey $k */
			if(($uid = $k->getUID($this->email))){
				// It's the key we're wanting!
				$this->assertEquals($this->key, $k->id_short);
				$this->assertEquals($this->bits, $k->encryptionBits);
				$this->assertEquals($this->enc, $k->encryptionType);

				$this->assertEquals($this->name, $uid->fullname);
				$this->assertEquals($this->email, $uid->email);
				$this->assertEquals($this->comment, $uid->comment);
			}
		}
	}

	/**
	 * Test importing the short version of a key ID
	 *
	 * @throws Exception
	 */
	public function testImportShort(){
		$gpg = new \Core\GPG\GPG();

		/** @var Core\GPG\PrimaryKey $key */
		$key = $gpg->importKey($this->key);

		$this->assertNotNull($key);
		$this->assertInstanceOf('Core\\GPG\\PrimaryKey', $key);

		$this->assertEquals($this->fingerprint, $key->fingerprint);

		// It should break if invalid data is provided
		$this->setExpectedException('Exception');
		$gpg->importKey('#');
	}

	/**
	 * Test importing the full public key text
	 *
	 * @throws Exception
	 */
	public function testImportPublicKey(){
		$gpg = new \Core\GPG\GPG();

		/** @var Core\GPG\PrimaryKey $key */
		$key = $gpg->importKey($this->pubkey);

		$this->assertNotNull($key);
		$this->assertInstanceOf('Core\\GPG\\PrimaryKey', $key);

		$this->assertEquals($this->fingerprint, $key->fingerprint);
	}

	/**
	 * @depends testImportShort
	 */
	public function testPrettyFormatFingerprints(){

		// Given this fingerprint:
		// 4E73 30EB 2A84 D747 9B71  9FF3 3F20 C906 B04E FAD6

		// The default output.
		$this->assertEquals("4E73 30EB 2A84 D747 9B71\n9FF3 3F20 C906 B04E FAD6", \Core\GPG\GPG::FormatFingerprint($this->fingerprint));

		// Plain text as 1 line
		$this->assertEquals("4E73 30EB 2A84 D747 9B71  9FF3 3F20 C906 B04E FAD6", \Core\GPG\GPG::FormatFingerprint($this->fingerprint, false, true));

		// HTML
		$this->assertEquals("4E73 30EB 2A84 D747 9B71<br/>9FF3 3F20 C906 B04E FAD6", \Core\GPG\GPG::FormatFingerprint($this->fingerprint, true));

		// And HTML as one line.
		$this->assertEquals("4E73 30EB 2A84 D747 9B71&nbsp;&nbsp;9FF3 3F20 C906 B04E FAD6", \Core\GPG\GPG::FormatFingerprint($this->fingerprint, true, true));
	}

	/**
	 * Test the encrypt data method
	 *
	 * @depends testImportShort
	 */
	public function testEncryptData(){
		$gpg = new \Core\GPG\GPG();

		$data = 'SSH, Something Super Secret!';
		$output = $gpg->encryptData($data, $this->key);

		$this->assertContains('----BEGIN PGP MESSAGE-----', $output);

		// It should break if invalid data is provided
		$this->setExpectedException('Exception');
		$gpg->encryptData($data, '#');
	}

	/**
	 * Test the verify file method
	 *
	 * @depends testImportShort
	 */
	public function testVerifyFile(){
		$gpg = new \Core\GPG\GPG();

		/** @var \Core\GPG\Signature $sig */
		$sig = $gpg->verifyFileSignature($this->verifyFile);
		$this->assertInstanceOf('Core\\GPG\\Signature', $sig);
		$this->assertTrue($sig->isValid);
		$this->assertEquals('Wed 18 Nov 2015 01:22:49 PM EST', $sig->dateTime);
		$this->assertEquals('4E7330EB2A84D7479B719FF33F20C906B04EFAD6', $sig->fingerprint);
		$this->assertEquals('B04EFAD6', $sig->keyID);
		$this->assertEquals('RSA', $sig->encType);
		$this->assertEquals('invalid-donotuse@corepl.us', $sig->signingEmail);
		$this->assertEquals('Core Plus Test Key', $sig->signingName);

		// It should break if invalid data is provided
		$this->setExpectedException('Exception');
		$gpg->verifyFileSignature($this->notGPGFile);
	}

	/**
	 * Test the verify file method
	 *
	 * @depends testImportShort
	 */
	public function testVerifyString(){
		$gpg = new \Core\GPG\GPG();

		/** @var \Core\GPG\Signature $sig */
		$sig = $gpg->verifyDataSignature($this->sigDetached, $this->comment);
		$this->assertTrue($sig->isValid);
		$this->assertEquals('Wed 18 Nov 2015 02:30:28 PM EST', $sig->dateTime);
		$this->assertEquals('4E7330EB2A84D7479B719FF33F20C906B04EFAD6', $sig->fingerprint);
		$this->assertEquals('B04EFAD6', $sig->keyID);
		$this->assertEquals('RSA', $sig->encType);
		$this->assertEquals('invalid-donotuse@corepl.us', $sig->signingEmail);
		$this->assertEquals('Core Plus Test Key', $sig->signingName);
	}

	/**
	 * Test the verify file method
	 *
	 * @depends testImportShort
	 */
	public function testDeleteKey(){
		$gpg = new \Core\GPG\GPG();
		$gpg->deleteKey($this->fingerprint);
	}
}