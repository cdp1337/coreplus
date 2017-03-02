<?php
/**
 * Test suite for preserving legacy functionality on the Time and CoreDateTime objects.
 *
 * This is a short set of tests, merely here to test the minimal functionality of the legacy Time methods.
 *
 * These will be deprecated as of 3.1 in favour of the Core\Date systems.
 */

use PHPUnit\Framework\TestCase;

class LegacyDateTimeTest extends TestCase {
	public function testTimeGetCurrentGMT(){
		$ref = new DateTime(null, new DateTimeZone('UTC'));

		$this->assertEquals($ref->format('U'), \Time::GetCurrentGMT('U'));

		$this->assertEquals($ref->format('Y-m-d'), \Time::GetCurrentGMT('Y-m-d'));
	}

	public function testTimeGetCurrent(){
		$ref = new DateTime(null, new DateTimeZone(date_default_timezone_get()));

		$this->assertEquals($ref->format('U'), \Time::GetCurrent(\Core\Date\Timezone::TIMEZONE_DEFAULT, 'U'));

		$this->assertEquals($ref->format('Y-m-d'), \Time::GetCurrent(\Core\Date\Timezone::TIMEZONE_DEFAULT, 'Y-m-d'));
	}

	public function testTimeGetRelativeAsString(){
		$ref = new DateTime(null, new DateTimeZone('UTC'));

		if($ref->format('h') < 10){
			$ref->modify('+2 hours');
		}
		else{
			$ref->modify('-2 hours');
		}

		$this->assertStringStartsWith('Today at ', \Time::GetRelativeAsString($ref->format('U')));
	}

	public function testTimeFormatGMT(){
		$ref = new DateTime(null, new DateTimeZone('UTC'));
		$localref = new DateTime(null, new DateTimeZone(date_default_timezone_get()));

		$this->assertEquals($localref->format('U'), \Time::FormatGMT($ref->format('U'), date_default_timezone_get()));
	}
}
 