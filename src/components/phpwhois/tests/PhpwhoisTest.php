<?php
/**
 * File for the PhpWhoisTest.
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130429.0134
 * @package phpwhois
 */

use PHPUnit\Framework\TestCase;

if(!class_exists('phpwhois\\Whois')){
	require_once(__DIR__ . '/../src/phpwhois/whois.main.php');
}

/**
 * Class PhpwhoisTest
 *
 * @package phpwhois
 */
class PhpwhoisTest extends TestCase {
	/**
	 * Test google.com
	 */
	public function test_google_com_Lookup(){
		$whois = new phpwhois\Whois();
		$result = $whois->lookup('google.com');

		$this->assertEquals('google.com', $result['regrinfo']['domain']['name']);
		$this->assertEquals('2020-09-14', $result['regrinfo']['domain']['expires']);
		$this->assertGreaterThan(0, sizeof($result['regrinfo']['domain']['nserver']));

		$this->assertEquals('Google Inc.', $result['regrinfo']['owner']['organization']);

		$this->assertEquals('1600 Amphitheatre Parkway', $result['regrinfo']['admin']['address']['street']);
		$this->assertEquals('Mountain View', $result['regrinfo']['admin']['address']['city']);
		$this->assertEquals('CA', $result['regrinfo']['admin']['address']['state']);
		$this->assertEquals('94043', $result['regrinfo']['admin']['address']['pcode']);
		$this->assertEquals('US', $result['regrinfo']['admin']['address']['country']);

		$this->assertEquals('DNS Admin', $result['regrinfo']['tech']['name']);

		$this->assertEquals('yes', $result['regrinfo']['registered']);

		$this->assertEquals('MARKMONITOR INC.', $result['regyinfo']['registrar']);
	}

	public function test_8_8_8_8_Lookup(){
		$whois = new phpwhois\Whois();
		$result = $whois->lookup('8.8.8.8');

		$this->assertEquals('LVLT-GOGL-8-8-8', $result['regrinfo']['network']['name']);
		$this->assertEquals('8.8.8.0 - 8.8.8.255', $result['regrinfo']['network']['inetnum']);

		$this->assertEquals('Google Inc.', $result['regrinfo']['owner']['organization']);

		$this->assertEquals('Google Inc', $result['regrinfo']['tech']['name']);
	}

	/**
	 * Test eval.bz
	 */
	public function test_eval_bz_Lookup(){
		$whois = new phpwhois\Whois();
		$result = $whois->lookup('eval.bz');
var_dump($result);
		$this->assertEquals('eval.bz', $result['regrinfo']['domain']['name']);
		$this->assertEquals('2016-04-13', $result['regrinfo']['domain']['expires']);
		$this->assertGreaterThan(0, sizeof($result['regrinfo']['domain']['nserver']));

		$org = strtolower($result['regrinfo']['owner']['organization']);
		$this->assertEquals('eval agency', $org);

		$this->assertEquals('175 S 3RD ST', $result['regrinfo']['admin']['address']['street']);
		$this->assertEquals('Columbus', $result['regrinfo']['admin']['address']['city']);
		$this->assertEquals('OH', $result['regrinfo']['admin']['address']['state']);
		$this->assertEquals('43215', $result['regrinfo']['admin']['address']['pcode']);
		$this->assertEquals('US', $result['regrinfo']['admin']['address']['country']);

		$this->assertEquals('Charlie Powell', $result['regrinfo']['tech']['name']);
		$this->assertEquals('domains@evalagency.com', $result['regrinfo']['tech']['email']);

		$this->assertEquals('yes', $result['regrinfo']['registered']);

		$this->assertNotEmpty($result['regyinfo']['registrar']);
	}

	/**
	 * Test eval.agency
	 */
	public function test_eval_agency_Lookup(){
		$whois = new phpwhois\Whois();
		$result = $whois->lookup('eval.agency');

		$this->assertEquals('eval.agency', $result['regrinfo']['domain']['name']);
		$this->assertEquals('2015-05-22', $result['regrinfo']['domain']['expires']);
		$this->assertGreaterThan(0, sizeof($result['regrinfo']['domain']['nserver']));

		$org = strtolower($result['regrinfo']['owner']['organization']);
		$this->assertEquals('eval agency', $org);

		$this->assertEquals('175 S 3RD ST', $result['regrinfo']['admin']['address']['street']);
		$this->assertEquals('Columbus', $result['regrinfo']['admin']['address']['city']);
		$this->assertEquals('OH', $result['regrinfo']['admin']['address']['state']);
		$this->assertEquals('43215', $result['regrinfo']['admin']['address']['pcode']);
		$this->assertEquals('US', $result['regrinfo']['admin']['address']['country']);

		$this->assertEquals('Charlie Powell', $result['regrinfo']['tech']['name']);
		$this->assertEquals('domains@evalagency.com', $result['regrinfo']['tech']['email']);

		$this->assertEquals('yes', $result['regrinfo']['registered']);

		$this->assertNotEmpty($result['regyinfo']['registrar']);
	}

	/**
	 * Test corepl.us
	 */
	public function test_corepl_us_Lookup(){

		$this->_check(
			'corepl.us',
			[
				'expires'      => '2015-07-21',
				'organization' => 'eVAL, Ltd.',
				'street'       => '396 South Washington Ave',
				'city'         => 'Columbus',
				'state'        => 'OH',
				'pcode'        => '43215',
				'country'      => 'US',
				'name'         => 'Charlie Powell',
				'email'        => 'domains@evalagency.com',
			]
		);
	}

	public function test_nic_aero_lookup(){
		$this->_check(
			'nic.aero',
			[
				'expires'      => null,
				'organization' => 'SITA SC',
				'street'       => '26 chemin de Joinville',
				'city'         => 'Geneva',
				'state'        => 'GE',
				'pcode'        => 'CH-1216',
				'country'      => 'CH',
				'name'         => 'Afilias Limited',
				'email'        => 'support@afilias.info',
			]
		);
	}

	protected function _check($domain, $expected){
		$whois = new phpwhois\Whois();
		$result = $whois->lookup($domain);

		$this->assertEquals($domain, $result['regrinfo']['domain']['name'], $domain . ' domain-name check did not match');

		if($expected['expires']){
			$this->assertEquals($expected['expires'], $result['regrinfo']['domain']['expires'], $domain . ' domain-expires check did not match');
		}

		$this->assertGreaterThan(0, sizeof($result['regrinfo']['domain']['nserver']), $domain . ' domain-nserver check did not return any nameservers');

		$this->assertEquals($expected['organization'], $result['regrinfo']['owner']['organization'], $domain . ' owner-organization check did not match');

		$street = $result['regrinfo']['admin']['address']['street'];
		if(is_array($street)){
			$this->assertEquals($expected['street'], $street[0], $domain . ' admin-address-street check did not match');
		}
		else{
			$this->assertEquals($expected['street'], $street, $domain . ' admin-address-street check did not match');
		}

		$this->assertEquals($expected['city'], $result['regrinfo']['admin']['address']['city'], $domain . ' admin-address-city check did not match');
		$this->assertEquals($expected['state'], $result['regrinfo']['admin']['address']['state'], $domain . ' admin-address-state check did not match');
		$this->assertEquals($expected['pcode'], $result['regrinfo']['admin']['address']['pcode'], $domain . ' admin-address-pcode check did not match');
		$this->assertEquals($expected['country'], $result['regrinfo']['admin']['address']['country'], $domain . ' admin-address-country check did not match');

		$this->assertEquals($expected['name'], $result['regrinfo']['tech']['name'], $domain . ' tech-name check did not match');
		$this->assertEquals($expected['email'], $result['regrinfo']['tech']['email'], $domain . ' domain-email check did not match');

		$this->assertEquals('yes', $result['regrinfo']['registered'], $domain . ' domain-name check did not match');

		$this->assertNotEmpty($result['regyinfo']['registrar'], $domain . ' domain-name check did not match');
	}
}
