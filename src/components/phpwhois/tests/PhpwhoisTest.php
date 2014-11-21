<?php
/**
 * File for the PhpWhoisController.
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130429.0134
 * @package phpwhois
 */

/**
 * Class PhpwhoisTest
 *
 * @package phpwhois
 */
class PhpwhoisTest extends PHPUnit_Framework_TestCase {
	/**
	 * Test google.com
	 */
	public function test_google_com_Lookup(){
		$whois = new phpwhois\Whois();
		$result = $whois->lookup('google.com');
		$this->assertEquals('US', $result->getCountry());
		$this->assertEquals('Google Inc.', $result->getOrganization());

		$address = $result->getAddress();

		$this->assertArrayHasKey('street', $address);
		$this->assertEquals('Mountain View', $address['city']);
		$this->assertEquals('CA', $address['state']);
		$this->assertEquals('94043', $address['pcode']);
		$this->assertEquals('US', $address['country']);
	}

	/**
	 * Test eval.bz
	 */
	public function test_eval_bz_Lookup(){
		$whois = new phpwhois\Whois();
		$result = $whois->lookup('eval.bz');
		$this->assertEquals('EVAL, LTD.', $result->getOrganization());

		$address = $result->getAddress();

		$this->assertArrayHasKey('street', $address);
		$this->assertEquals('COLUMBUS', $address['city']);
		$this->assertEquals('OH', $address['state']);
		$this->assertEquals('43215', $address['pcode']);
		$this->assertEquals('US', $address['country']);
	}

	/**
	 * Test eval.agency
	 */
	public function test_eval_agency_Lookup(){
		$whois = new phpwhois\Whois();
		$result = $whois->lookup('eval.agency');
		$this->assertEquals('EVAL, LTD.', $result->getOrganization());

		$address = $result->getAddress();

		$this->assertArrayHasKey('street', $address);
		$this->assertEquals('COLUMBUS', $address['city']);
		$this->assertEquals('OH', $address['state']);
		$this->assertEquals('43215', $address['pcode']);
		$this->assertEquals('US', $address['country']);
	}

	/**
	 * Test corepl.us
	 */
	public function test_corepl_us_Lookup(){
		$whois = new phpwhois\Whois();
		$result = $whois->lookup('corepl.us');
		$this->assertEquals('EVAL, LTD.', $result->getOrganization());

		$address = $result->getAddress();

		$this->assertArrayHasKey('street', $address);
		$this->assertEquals('COLUMBUS', $address['city']);
		$this->assertEquals('OH', $address['state']);
		$this->assertEquals('43215', $address['pcode']);
		$this->assertEquals('US', $address['country']);
	}
}
