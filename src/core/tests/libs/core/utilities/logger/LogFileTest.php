<?php
/**
 * Test file to ensure that logs can be written and are correctly written.
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20131231.1718
 * @package Core\Utilities\Logger
 */

use PHPUnit\Framework\TestCase;

class LogFileTest extends TestCase {
	/**
	 * Test that a message can be written to a log file.
	 *
	 * @return \Core\Utilities\Logger\LogFile The log file created.
	 */
	public function testWrite(){
		$type = 'testphpunit';
		$msg  = \BaconIpsumGenerator::Make_a_Sentence();
		$code = '/test/' . Core::RandomHex(6);

		// First, I'll test the functional method.
		\Core\Utilities\Logger\append_to($type, $msg, $code);

		// Now a file should exist called testphpunit.log that contains the line above.
		$this->assertTrue(file_exists(ROOT_PDIR . 'logs/' . $type . '.log'));
		$contents = file_get_contents(ROOT_PDIR . 'logs/' . $type . '.log');
		$this->assertContains($msg, $contents);
		$this->assertContains($code, $contents);

		// And now the class method, (should be identical).
		$type = 'testphpunit';
		$msg  = \BaconIpsumGenerator::Make_a_Sentence();
		$code = '/test/' . Core::RandomHex(6);

		// First, I'll test the functional method.
		$log = new \Core\Utilities\Logger\LogFile($type);
		$log->write($msg, $code);

		// Now a file should exist called testphpunit.log that contains the line above.
		$this->assertTrue($log->exists());
		$contents = $log->getContents();
		$this->assertContains($msg, $contents);
		$this->assertContains($code, $contents);

		return $log;
	}

	/**
	 * Test that archiving a log works.
	 *
	 * @depends testWrite
	 */
	public function testArchive(\Core\Utilities\Logger\LogFile $log){
		// Archive will throw exceptions if there were any problems.
		$log->archive();

		return $log;
	}

	/**
	 * Remove the test log file left on the filesystem.
	 *
	 * @depends testArchive
	 */
	public function testDelete(\Core\Utilities\Logger\LogFile $log){
		// Delete will throw exceptions if there were any problems.
		$log->delete();
	}
}
 