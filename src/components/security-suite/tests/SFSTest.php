<?php
/**
 * The test suite for the StopForumSpam subsystem of the security suite.
 *
 * @package Security-Suite
 * @author Charlie Powell <charlie@eval.bz>
 * Date: 2/11/13
 * Time: 6:19 PM
 */

/**
 *
 */
class SFSTest extends PHPUnit_Framework_TestCase {

	/**
	 * Test to verify that the import is working.  This will verify that the syste can import the zipped CSV from the website.
	 *
	 * Note that the live sfs website is not actually used, but instead a local test zip.
	 */
	public function testImport(){

		// This test file contains two entries:
		// "257.0.0.1","256","2012-12-14 22:49:31"
		// "257.0.0.2","128","2012-12-15 03:23:43"
		// Yes, I know 257. is an invalid IPv4 IP... that's why I'm using it as a test.

		// The import function just prints straight to stdout.  Capture that to get the status.
		$file = ROOT_PDIR . 'components/security-suite/tests/test_listed_ip_1_all.zip';
		$this->setUseOutputBuffering(true);
		SecuritySuite\StopForumSpam::ImportList($file);
		$out = $this->getActualOutput();

		$string = 'Processed 2 records from ' . $file . ' successfully!';
		$this->assertContains($string, $out, 'Checking that 2 records were processed successfully from the test zip');

		// Try to remove them now.
		$record = new sfsBlacklistModel('257.0.0.1');
		$this->assertEquals('256', $record->get('submissions'), 'Checking that record 257.0.0.1 contains 256 submissions');
		$record->delete();
		$this->assertTrue(!$record->exists(), 'Checking that record 257.0.0.1 can be removed');

		$record = new sfsBlacklistModel('257.0.0.2');
		$this->assertEquals('128', $record->get('submissions'), 'Checking that record 257.0.0.2 contains 128 submissions');
		$record->delete();
		$this->assertTrue(!$record->exists(), 'Checking that record 257.0.0.2 can be removed');
	}
}
