<?php
/**
 * Enter a meaningful file description here!
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130625.1749
 *
 */

class UserAgentTest extends PHPUnit_Framework_TestCase {
	public function testAgents(){
		$tests = [
			[
				'ua'            => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2202.3 Safari/537.36',
				'browser'       => 'Chrome',
				'version'       => '40.0',
				'major_ver'     => '40',
				'minor_ver'     => '0',
				'ismobile'      => false,
				'isbot'         => false,
				'engine'        => 'WebKit',
				'platform'      => 'Linux',
				'platform_ver'  => '',
				'platform_arch' => 'x86',
				'platform_bits' => '64',
			],
			[
				'ua'            => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.110 Safari/537.36',
				'browser'       => 'Chrome',
				'version'       => '27.0',
				'major_ver'     => '27',
				'minor_ver'     => '0',
				'ismobile'      => false,
				'isbot'         => false,
				'engine'        => 'WebKit',
				'platform'      => 'Windows',
				'platform_ver'  => '7',
				'platform_arch' => '',
				'platform_bits' => '64',
			],
			[
				'ua'            => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:21.0) Gecko/20100101 Firefox/21.0',
				'browser'       => 'Firefox',
				'version'       => '21.0',
				'major_ver'     => '21',
				'minor_ver'     => '0',
				'ismobile'      => false,
				'isbot'         => false,
				'engine'        => 'Gecko',
				'platform'      => 'Windows',
				'platform_ver'  => '7',
				'platform_arch' => '',
				'platform_bits' => '64',
			],
			[
				'ua'            => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_3) AppleWebKit/536.29.13 (KHTML, like Gecko) Version/6.0.4 Safari/536.29.13',
				'browser'       => 'Safari',
				'version'       => '6.0',
				'major_ver'     => '6',
				'minor_ver'     => '0',
				'ismobile'      => false,
				'isbot'         => false,
				'engine'        => 'WebKit',
				'platform'      => 'MacOSX',
				'platform_ver'  => '10.8.3',
				'platform_arch' => 'x86',
				'platform_bits' => '',
			],
			[
				'ua'            => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:21.0) Gecko/20100101 Firefox/21.0',
				'browser'       => 'Firefox',
				'version'       => '21.0',
				'major_ver'     => '21',
				'minor_ver'     => '0',
				'ismobile'      => false,
				'isbot'         => false,
				'engine'        => 'Gecko',
				'platform'      => 'Ubuntu',
				'platform_ver'  => '',
				'platform_arch' => 'x86',
				'platform_bits' => '64',
			],
			[
				'ua'            => 'Mozilla/5.0 (iPad; CPU OS 6_1_3 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10B329 Safari/8536.25',
				'browser'       => 'Safari',
				'version'       => '6.0',
				'major_ver'     => '6',
				'minor_ver'     => '0',
				'ismobile'      => true,
				'isbot'         => false,
				'engine'        => 'WebKit',
				'platform'      => 'iOS',
				'platform_ver'  => '6.1.3',
				'platform_arch' => '',
				'platform_bits' => '',
			],
			[
				'ua'            => 'Mozilla/5.0 (iPhone; CPU iPhone OS 6_1_3 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10B329 Safari/8536.25',
				'browser'       => 'Safari',
				'version'       => '6.0',
				'major_ver'     => '6',
				'minor_ver'     => '0',
				'ismobile'      => true,
				'isbot'         => false,
				'engine'        => 'WebKit',
				'platform'      => 'iOS',
				'platform_ver'  => '6.1.3',
				'platform_arch' => '',
				'platform_bits' => '',
			],
			[
				'ua'            => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.110 Safari/537.36',
				'browser'       => 'Chrome',
				'version'       => '27.0',
				'major_ver'     => '27',
				'minor_ver'     => '0',
				'ismobile'      => false,
				'isbot'         => false,
				'engine'        => 'WebKit',
				'platform'      => 'Linux',
				'platform_ver'  => '',
				'platform_arch' => 'x86',
				'platform_bits' => '64',
			],
			[
				'ua'            => 'Mozilla/5.0 (Linux; Android 4.1.1; GT-I9300 Build/JRO03C) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30',
				'browser'       => 'Android',
				'version'       => '4.0',
				'major_ver'     => '4',
				'minor_ver'     => '0',
				'ismobile'      => true,
				'isbot'         => false,
				'engine'        => 'WebKit',
				'platform'      => 'Android',
				'platform_ver'  => '4.1.1',
				'platform_arch' => '',
				'platform_bits' => '',
			],
			[
				'ua'            => 'Mozilla/5.0 (Mobile; rv:26.0) Gecko/26.0 Firefox/26.0',
				'browser'       => 'Firefox',
				'version'       => '26.0',
				'major_ver'     => '26',
				'minor_ver'     => '0',
				'ismobile'      => true,
				'isbot'         => false,
				'engine'        => 'Gecko',
				'platform'      => 'FirefoxOS',
				'platform_ver'  => '',
				'platform_arch' => '',
				'platform_bits' => '',
			],
			[
				'ua'            => 'Googlebot/2.1 ( http://www.googlebot.com/bot.html) ',
				'browser'       => 'Googlebot',
				'version'       => '2.1',
				'major_ver'     => '2',
				'minor_ver'     => '1',
				'ismobile'      => false,
				'isbot'         => true,
				'engine'        => 'WebKit',
				'platform'      => 'unknown',
			    'platform_ver'  => '',
			    'platform_arch' => '',
			    'platform_bits' => '',
			],
		];

		foreach($tests as $test){
			$ua = new \Core\UserAgent($test['ua']);

			$this->assertEquals($test['browser'], $ua->browser, 'Browser match failed for UA ' . $test['ua']);
			$this->assertEquals($test['version'], $ua->version, 'Version match failed for UA ' . $test['ua']);
			$this->assertEquals($test['major_ver'], $ua->major_ver, 'Version (Major String) match failed for UA ' . $test['ua']);
			$this->assertEquals($test['minor_ver'], $ua->minor_ver, 'Version (Minor String) match failed for UA ' . $test['ua']);

			$this->assertEquals($test['ismobile'], $ua->isMobile(), 'IsMobile match failed for UA ' . $test['ua']);
			$this->assertEquals($test['isbot'], $ua->isBot(), 'IsBot match failed for UA ' . $test['ua']);

			$this->assertEquals($test['engine'], $ua->rendering_engine_name, 'Engine match failed for UA ' . $test['ua']);

			$this->assertEquals($test['platform'], $ua->platform, 'OS match failed for UA ' . $test['ua']);
			$this->assertEquals($test['platform_ver'], $ua->platform_version, 'OS (Version) match failed for UA ' . $test['ua']);
			$this->assertEquals($test['platform_arch'], $ua->platform_architecture, 'OS (Architecture) match failed for UA ' . $test['ua']);
			$this->assertEquals($test['platform_bits'], $ua->platform_bits, 'OS (Bits) match failed for UA ' . $test['ua']);
		}
	}
}
