<?php
/**
 * Enter a meaningful file description here!
 * 
 * @author Charlie Powell <charlie@evalagency.com>
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

	/**
	 * Test for the Core::VersionSplit method.  Ensure that correct data is returned.
	 */
	public function testVersionSplit() {
		$tests = [
			[
				'test'      => '1.2.3',
				'major'     => 1,
				'minor'     => 2,
				'point'     => 3,
				'user'      => null,
				'stability' => null,
				'core'      => null,
			],
			[
				'test'      => '4.5.6~fork1',
				'major'     => 4,
				'minor'     => 5,
				'point'     => 6,
				'user'      => '~fork1',
				'stability' => null,
				'core'      => null,
			],
			[
				'test'      => '4.5.6~core1',
				'major'     => 4,
				'minor'     => 5,
				'point'     => 6,
				'user'      => null,
				'stability' => null,
				'core'      => 1,
			],
			[
				'test'      => '1.2.8a2',
				'major'     => 1,
				'minor'     => 2,
				'point'     => 8,
				'user'      => null,
				'stability' => 'a2',
				'core'      => null,
			],
			[
				'test'      => '1.2.8b4',
				'major'     => 1,
				'minor'     => 2,
				'point'     => 8,
				'user'      => null,
				'stability' => 'b4',
				'core'      => null,
			],
			[
				'test'      => '1.2.8rc9',
				'major'     => 1,
				'minor'     => 2,
				'point'     => 8,
				'user'      => null,
				'stability' => 'rc9',
				'core'      => null,
			],
			[
				'test'      => '4.5.6-3',
				'major'     => 4,
				'minor'     => 5,
				'point'     => 6,
				'user'      => null,
				'stability' => null,
				'core'      => 3,
			],
		];

		foreach($tests as $test){
			$split = Core::VersionSplit($test['test']);

			$this->assertEquals($test['major'], $split['major'], 'Checking that major version component is correct for [' . $test['test'] . ']');
			$this->assertEquals($test['minor'], $split['minor'], 'Checking that minor version component is correct for [' . $test['test'] . ']');
			$this->assertEquals($test['point'], $split['point'], 'Checking that point version component is correct for [' . $test['test'] . ']');
			$this->assertEquals($test['user'], $split['user'], 'Checking that user version component is correct for [' . $test['test'] . ']');
			$this->assertEquals($test['stability'], $split['stability'], 'Checking that stability version component is correct for [' . $test['test'] . ']');
			$this->assertEquals($test['core'], $split['core'], 'Checking that core version component is correct for [' . $test['test'] . ']');
		}
	}

	public function testVersionCompare(){
		$tests = [

			['v1' => '5',           'op' => 'gt',    'v2' => '4.99.99'      ], // Major version differences
			['v1' => '1.2.3',       'op' => 'eq',    'v2' => '1.2.3~core1'  ], // Version strings with only 1 user-based query will be equal
			['v1' => '1.2.4',       'op' => 'gt',    'v2' => '1.2.3~core1'  ], // Unless they're not :p
			['v1' => '1.2.4~core2', 'op' => 'lt',    'v2' => '1.2.4~core9'  ], // But if both user strings are requested.
			['v1' => '4.5.0a1',     'op' => 'lt',    'v2' => '4.5.0a2'      ], // Stability versions... a < b < rc < full version
			['v1' => '4.5.0a1',     'op' => 'lt',    'v2' => '4.5.0b1'      ],
			['v1' => '4.5.0b1',     'op' => 'lt',    'v2' => '4.5.0rc2'     ],
			['v1' => '4.5.0rc2',    'op' => 'lt',    'v2' => '4.5.0'        ],
			['v1' => '1.2.3',       'op' => '>',     'v2' => '1.2.0~bpo340' ], // New back ported version strings
			['v1' => '2.2.0~core1', 'op' => 'lt',    'v2' => '2.2.0-2'      ], // This is the same as 2.2.0-1 < 2.2.0-2
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
	
	public function testTimeDurationFormat(){
		// Default 1ns - 1M seconds, should have automatic precision+rounding.
		$this->assertEquals(
			\Core\time_duration_format(0.000000001),
			"1.0000 ns"
		);
		$this->assertEquals(
			\Core\time_duration_format(0.00000001),
			"10.0000 ns"
		);
		$this->assertEquals(
			\Core\time_duration_format(0.0000001),
			"100.0000 ns"
		);
		$this->assertEquals(
			\Core\time_duration_format(0.000001),
			"1.0000 µs"
		);
		$this->assertEquals(
			\Core\time_duration_format(0.00001),
			"10.0000 µs"
		);
		$this->assertEquals(
			\Core\time_duration_format(0.0001),
			"100.0000 µs"
		);
		$this->assertEquals(
			\Core\time_duration_format(0.001),
			"1.0000 ms"
		);
		$this->assertEquals(
			\Core\time_duration_format(0.01),
			"10.0000 ms"
		);
		$this->assertEquals(
			\Core\time_duration_format(0.1),
			"100.0000 ms"
		);
		$this->assertEquals(
			\Core\time_duration_format(1),
			"1.0000 s"
		);
		$this->assertEquals(
			\Core\time_duration_format(10),
			"10.0000 s"
		);
		$this->assertEquals(
			\Core\time_duration_format(100),
			"1 min 40 sec"
		);
		$this->assertEquals(
			\Core\time_duration_format(1000),
			"16 min 40 sec"
		);
		$this->assertEquals(
			\Core\time_duration_format(10000),
			"2 hrs 47 min"
		);
		$this->assertEquals(
			\Core\time_duration_format(100000),
			"1 Day 4 hrs"
		);
		$this->assertEquals(
			\Core\time_duration_format(1000000),
			"11 Days 14 hrs"
		);
		
		// No precision/rounding via ,0 argument.
		// Default 1ns - 1M seconds, should have automatic precision+rounding.
		$this->assertEquals(
			\Core\time_duration_format(0.000000001, 0),
			"1 ns"
		);
		$this->assertEquals(
			\Core\time_duration_format(0.00000001, 0),
			"10 ns"
		);
		$this->assertEquals(
			\Core\time_duration_format(0.0000001, 0),
			"100 ns"
		);
		$this->assertEquals(
			\Core\time_duration_format(0.000001, 0),
			"1 µs"
		);
		$this->assertEquals(
			\Core\time_duration_format(0.00001, 0),
			"10 µs"
		);
		$this->assertEquals(
			\Core\time_duration_format(0.0001, 0),
			"100 µs"
		);
		$this->assertEquals(
			\Core\time_duration_format(0.001, 0),
			"1 ms"
		);
		$this->assertEquals(
			\Core\time_duration_format(0.01, 0),
			"10 ms"
		);
		$this->assertEquals(
			\Core\time_duration_format(0.1, 0),
			"100 ms"
		);
		$this->assertEquals(
			\Core\time_duration_format(1, 0),
			"1 s"
		);
		$this->assertEquals(
			\Core\time_duration_format(10, 0),
			"10 s"
		);
		
		
		// Some complex scenarios.
		$this->assertEquals(
			\Core\time_duration_format(3, 0),
			"3 s"
		);
		$this->assertEquals(
			\Core\time_duration_format(3, 1),
			"3.0 s"
		);
		$this->assertEquals(
			\Core\time_duration_format(3.14159),
			"3.1416 s"
		);
		$this->assertEquals(
			\Core\time_duration_format(63.14159),
			"1 min 3 sec"
		);
		$this->assertEquals(
			\Core\time_duration_format(60),
			"1 Minute"
		);
		$this->assertEquals(
			\Core\time_duration_format(3600),
			"1 Hour"
		);
		$this->assertEquals(
			\Core\time_duration_format(86400),
			"1 Day"
		);
	}
}
