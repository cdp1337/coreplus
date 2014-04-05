<?php
/**
 * Enter a meaningful file description here!
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130708.1110
 * @package Core
 */

/**
 * Class CoreFunctionsTest
 *
 * @package Core
 */
class CoreFunctionsTest extends PHPUnit_Framework_TestCase {
	/**
	 * Tests that \Core\str_to_url is functioning properly.
	 */
	public function testStrToURL() {
		// spaces and intl characters get translated.
		$this->assertEquals('thors-hammer', \Core\str_to_url('Þors hammer'));

		// and dots
		$this->assertEquals('awesome-hot-imagejpg', \Core\str_to_url('AWESOME höt Image!!!!!!!.JPG'));

		// Unless the second parameter is set to true.
		$this->assertEquals('awesome-hot-image.jpg', \Core\str_to_url('AWESOME höt Image!!!!!!!.JPG', true));
	}
}
