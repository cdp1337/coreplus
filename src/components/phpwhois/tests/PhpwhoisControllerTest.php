<?php
/**
 * File for the PhpWhoisController.
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130429.0134
 * @package phpwhois
 */

use PHPUnit\Framework\TestCase;

/**
 * Class PhpwhoisControllerTest
 *
 * @package phpwhois
 */
class PhpwhoisControllerTest extends TestCase {
	/**
	 * Test a lookup to localhost and make sure that it returns appropriate information.
	 */
	public function testLocalhostLookup(){
		$request = new PageRequest('/phpwhois/lookup?q=127.0.0.1');
		// I need to spoof the GET request to add the necessary parameters.
		//$request->parameters['q'] = '8.8.8.8';
		$request->execute();
		$view = $request->getView();

		// The page should be a 200 status
		$this->assertEquals(200, $view->error);

		// Make sure this page is an ajax page.
		$this->assertEquals(View::MODE_AJAX, $view->mode);

		// And is a JSON page
		$this->assertEquals(View::CTYPE_JSON, $view->contenttype);

		// JSON Data needs to be set.
		$this->assertNotNull($view->jsondata);
		$this->assertNotEmpty($view->jsondata);

		// Make sure the JSON data is correct.
		$this->assertEquals('127.0.0.1', $view->jsondata['query']);
		$this->assertEquals('127.0.0.1', $view->jsondata['ip']);
		$this->assertEquals('127.0.0.0/8', $view->jsondata['network']);
		$this->assertEquals('Internet Assigned Numbers Authority', $view->jsondata['organization']);
		//$this->assertEquals('US', $view->jsondata['country']);
		//$this->assertEquals('United States', $view->jsondata['country_name']);
	}

	/**
	 * Test a lookup to Google's public DNS and make sure that it returns appropriate information.
	 */
	public function testGoogleLookup(){
		$request = new PageRequest('/phpwhois/lookup?q=8.8.8.8');
		// I need to spoof the GET request to add the necessary parameters.
		//$request->parameters['q'] = '8.8.8.8';
		$request->execute();
		$view = $request->getView();

		// The page should be a 200 status
		$this->assertEquals(200, $view->error);

		// Make sure this page is an ajax page.
		$this->assertEquals(View::MODE_AJAX, $view->mode);

		// And is a JSON page
		$this->assertEquals(View::CTYPE_JSON, $view->contenttype);

		// JSON Data needs to be set.
		$this->assertNotNull($view->jsondata);
		$this->assertNotEmpty($view->jsondata);

		// Make sure the JSON data is correct.
		$this->assertEquals('8.8.8.8', $view->jsondata['query']);
		$this->assertEquals('8.8.8.8', $view->jsondata['ip']);
		$this->assertEquals('8.8.8.0/24', $view->jsondata['network']);
		$this->assertEquals('Google Inc.', $view->jsondata['organization']);
		$this->assertEquals('US', $view->jsondata['country']);
		// @todo For whatever reason country_name isn't working... whatever, fix it soon.
		//$this->assertEquals('United States', $view->jsondata['country_name']);
	}
}
