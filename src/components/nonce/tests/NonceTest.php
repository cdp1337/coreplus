<?php
/**
 * Enter a meaningful file description here!
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130412.1031
 * @package PackageName
 * 
 * Created with JetBrains PhpStorm.
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
}
