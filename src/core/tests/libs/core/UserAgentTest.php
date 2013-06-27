<?php
/**
 * Enter a meaningful file description here!
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130625.1749
 *
 */

class UserAgentTest extends PHPUnit_Framework_TestCase {
	public function testAgents(){
		$tests = [
			[
				'ua' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.110 Safari/537.36',
				'browser' => 'Chrome',
				'ismobile' => false,
				'engine' => 'WebKit',
				'platform' => 'Win7',
			],
			[
				'ua' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:21.0) Gecko/20100101 Firefox/21.0',
				'browser' => 'Firefox',
				'ismobile' => false,
				'engine' => 'Gecko',
				'platform' => 'Win7',
			],
			[
				'ua' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_3) AppleWebKit/536.29.13 (KHTML, like Gecko) Version/6.0.4 Safari/536.29.13',
				'browser' => 'Safari',
				'ismobile' => false,
				'engine' => 'WebKit',
				'platform' => 'MacOSX',
			],
			[
				'ua' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:21.0) Gecko/20100101 Firefox/21.0',
				'browser' => 'Firefox',
				'ismobile' => false,
				'engine' => 'Gecko',
				'platform' => 'Linux',
			],
			[
				'ua' => 'Mozilla/5.0 (iPad; CPU OS 6_1_3 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10B329 Safari/8536.25',
				'browser' => 'Safari',
				'ismobile' => true,
				'engine' => 'WebKit',
				'platform' => 'iOS',
			],
			[
				'ua' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 6_1_3 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10B329 Safari/8536.25',
				'browser' => 'Safari',
				'ismobile' => true,
				'engine' => 'WebKit',
				'platform' => 'iOS',
			],
			[
				'ua' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.110 Safari/537.36',
				'browser' => 'Chrome',
				'ismobile' => false,
				'engine' => 'WebKit',
				'platform' => 'Linux',
			],
			[
				'ua' => 'Mozilla/5.0 (Linux; Android 4.1.1; GT-I9300 Build/JRO03C) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30',
				'browser' => 'Android',
				'ismobile' => true,
				'engine' => 'WebKit',
				'platform' => 'Android',
			],
			[
				'ua' => 'Mozilla/5.0 (Android; Mobile; rv:18.0) Gecko/18.0 Firefox/18.0',
				'browser' => 'Firefox',
				'ismobile' => true,
				'engine' => 'Gecko',
				'platform' => 'Android',
			],
		];

		foreach($tests as $test){
			$ua = new \Core\UserAgent($test['ua']);

			$this->assertEquals($test['browser'], $ua->browser, 'Browser match failed for UA ' . $test['ua']);
			$this->assertEquals($test['ismobile'], $ua->isMobile(), 'IsMobile match failed for UA ' . $test['ua']);
			$this->assertEquals($test['engine'], $ua->rendering_engine_name, 'Engine match failed for UA ' . $test['ua']);
			$this->assertEquals($test['platform'], $ua->platform, 'Platform match failed for UA ' . $test['ua']);
		}
	}
}
