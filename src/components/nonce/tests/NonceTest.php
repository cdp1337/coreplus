<?php
/**
 * File for the NonceTest test suite.
 *
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130412.1031
 * @package Nonce
 */

/**
 * Class NonceTest
 *
 * Tests the Nonce system to ensure it's operating as expected.
 *
 * @package Nonce
 */
class NonceTest extends PHPUnit_Framework_TestCase {
	/**
	 * Test the creation and validation of a good nonce.
	 *
	 * This will use the shortcut methods to perform the operations.
	 */
	public function testQuickCreateValidNonce(){
		$randomdata = Core::RandomHex(90);
		// Make sure core::randomhex gives me the correct number of characters.
		$this->assertEquals(90, strlen($randomdata));

		$key = NonceModel::Generate('10 minutes', $randomdata);
		$this->assertTrue($key != '' && strlen($key) >= 40);

		// Do something blah
		// ..... and check it!
		$this->assertTrue(NonceModel::ValidateAndUse($key, $randomdata));
	}

	/**
	 * Test to make sure that binary garble won't mess up the hash.
	 */
	public function testCrapHashData(){
		$fp = fopen('/dev/urandom','rb');
		if ($fp !== FALSE) {
			$binary = fread($fp, 16);
			fclose($fp);
		}

		// Generate a nonce with this data
		$key = NonceModel::Generate('10 minutes', $binary);
		$this->assertTrue($key != '' && strlen($key) >= 40);

		// And retrieve it to check.
		$nonce = new NonceModel($key);
		// The data in should NOT be the data out
		$this->assertFalse($nonce->get('hash') == $binary);
		// But it *should* match validation still.
		$this->assertTrue($nonce->isValid($binary));
	}

	/**
	 * Create a nonce with an expired data and make sure that it's not valid on checking.
	 */
	public function testExpiredNonce(){
		// Yes, you can technically create an already-expired nonce.
		$key = NonceModel::Generate('-1 minute');

		$this->assertFalse(NonceModel::ValidateAndUse($key));
	}

	/**
	 * Create an expired nonce and then manually run cleanup.
	 * This will ensure that expried nonce keys won't clutter the database providing the crons are running.
	 */
	public function testCleanup(){
		// Yes, you can technically create an already-expired nonce.
		$key = NonceModel::Generate('-1 minute');

		// Make sure it saved
		$nonce = new NonceModel($key);
		$this->assertTrue($nonce->exists());
		// But is invalid
		$this->assertFalse($nonce->isValid());

		// Run the cleanup
		// Remember, this doesn't actually generate any output, so I need to lookup this key again.
		NonceModel::Cleanup();

		$nonce = new NonceModel($key);
		// And NOW it should not exist.
		$this->assertFalse($nonce->exists());
	}

	/**
	 * Make sure that the keys generated are URL save, (all lower case, no weird characters, etc).
	 *
	 * Repeat this several times, just to make sure ;)
	 */
	public function testURLSafe(){
		// Yes, you can technically create an already-expired nonce.
		$key = NonceModel::Generate('-1 minute');

		// Make sure that it's the same.
		$encoded = urlencode($key);
		$this->assertEquals($key, $encoded);


		// Yes, you can technically create an already-expired nonce.
		$key = NonceModel::Generate('-1 minute');

		// Make sure that it's the same.
		$encoded = urlencode($key);
		$this->assertEquals($key, $encoded);


		// Yes, you can technically create an already-expired nonce.
		$key = NonceModel::Generate('-1 minute');

		// Make sure that it's the same.
		$encoded = urlencode($key);
		$this->assertEquals($key, $encoded);


		// Yes, you can technically create an already-expired nonce.
		$key = NonceModel::Generate('-1 minute');

		// Make sure that it's the same.
		$encoded = urlencode($key);
		$this->assertEquals($key, $encoded);
	}

	/**
	 * Test that capitalization doesn't bork up the nonce.
	 */
	public function testCapitalizationTolerance() {
		$nonce = NonceModel::Generate('10 seconds');

		// ucase it!
		$nonce = strtoupper($nonce);

		// And is it still valid?
		$this->assertTrue(NonceModel::ValidateAndUse($nonce));
	}

	/**
	 * This test is to check and make sure that the nonce system is typecast tolerant.
	 *
	 * This causes issue because Core models are not strict in their datatypes.
	 * ie: an int may be 123 or "123", and to Core they're both the same.
	 */
	public function testStrictTypecastTolerance() {
		$d1 = [
			'something' => '123',
		];
		$d2 = [
			'something' => 123,
		];

		$nonce = NonceModel::Generate('12 seconds', $d1);
		$this->assertTrue(NonceModel::ValidateAndUse($nonce, $d2));
	}
}
