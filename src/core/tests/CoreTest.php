<?php
class CoreTest extends PHPUnit_Framework_TestCase {

	/**
	 * Test for the Core::VersionSplit method.  Ensure that correct data is returned.
	 */
	public function testVersionSplit() {
		$tests = [
			['major' => 1, 'minor' => 2, 'point' => 3, 'user' => null, 'stability' => null],
			['major' => 4, 'minor' => 5, 'point' => 6, 'user' => '~core3', 'stability' => null],
			['major' => 1, 'minor' => 2, 'point' => 8, 'user' => null, 'stability' => 'a2'],
			['major' => 1, 'minor' => 2, 'point' => 8, 'user' => null, 'stability' => 'b4'],
			['major' => 1, 'minor' => 2, 'point' => 8, 'user' => null, 'stability' => 'rc9'],
		];

		foreach($tests as $test){
			$version = $test['major'] . '.' . $test['minor'] . '.' . $test['point'] . $test['user'] . $test['stability'];
			$split = Core::VersionSplit($version);

			$this->assertEquals($test['major'], $split['major'], 'Checking that major version component is correct');
			$this->assertEquals($test['minor'], $split['minor'], 'Checking that minor version component is correct');
			$this->assertEquals($test['point'], $split['point'], 'Checking that point version component is correct');
			if($test['user']){
				$this->assertEquals($test['user'], $split['user'], 'Checking that user version component is correct');
			}
			if($test['stability']){
				$this->assertEquals($test['stability'], $split['stability'], 'Checking that stability version component is correct');
			}
		}
	}

	public function testVersionCompare(){
		$tests = [
			// Major version differences
			['v1' => '5', 'op' => 'gt', 'v2' => '4.99.99'],
			// Version strings with only 1 user-based query will be equal
			['v1' => '1.2.3', 'op' => 'eq', 'v2' => '1.2.3~core1'],
			// Unless they're not :p
			['v1' => '1.2.4', 'op' => 'gt', 'v2' => '1.2.3~core1'],
			// But if both user strings are requested.
			['v1' => '1.2.4~core2', 'op' => 'lt', 'v2' => '1.2.4~core9'],
			// Stability versions... a < b < rc < full version
			['v1' => '4.5.0a1', 'op' => 'lt', 'v2' => '4.5.0a2'],
			['v1' => '4.5.0a1', 'op' => 'lt', 'v2' => '4.5.0b1'],
			['v1' => '4.5.0b1', 'op' => 'lt', 'v2' => '4.5.0rc2'],
			['v1' => '4.5.0rc2', 'op' => 'lt', 'v2' => '4.5.0'],
		];

		foreach($tests as $test){
			$result = Core::VersionCompare($test['v1'], $test['v2'], $test['op']);
			$this->assertTrue($result, 'Checking that ' . $test['v1'] . ' is ' . $test['op'] . ' ' . $test['v2']);
		}
	}

	public function testCompareValues() {
		// float 5.20 and float 5.20000 are the same value.
		$this->assertTrue(\Core\compare_values(5.20, 5.20000));
		$this->assertTrue(\Core\compare_values(5.20, '5.20000'));

		// (string) "0" and (int) 0 are the same value.
		$this->assertTrue(\Core\compare_values('0', 0));

		// (boolean) false and (int) 0 are not the same value.
		$this->assertTrue(!\Core\compare_values(false, 0));
	}

	public function testCompareStrings(){
		$this->assertTrue(!\Core\compare_strings(1234, '01234'));
		$this->assertTrue(!\Core\compare_strings('1234', '01234'));
		$this->assertTrue(\Core\compare_strings(1234, '1234'));
	}
}
