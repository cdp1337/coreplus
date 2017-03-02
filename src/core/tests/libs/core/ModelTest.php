<?php
/**
 * @todo Enter a meaningful file description here!
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20141204.1831
 * @copyright Copyright (C) 2009-2016  Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/agpl-3.0.txt.
 */

use PHPUnit\Framework\TestCase;

 

class ModelTest extends TestCase {
	public function testValidationNotBlank(){
		$this->assertEquals(1, preg_match(Model::VALIDATION_NOTBLANK, 'Blah'));
		$this->assertEquals(0, preg_match(Model::VALIDATION_NOTBLANK, ''));
	}

	public function testValidationURL(){
		$this->assertEquals(1, preg_match(Model::VALIDATION_URL, 'http://blah.tld'));
		$this->assertEquals(1, preg_match(Model::VALIDATION_URL, 'http://something.meuseum'));
		$this->assertEquals(1, preg_match(Model::VALIDATION_URL, 'https://something.meuseum'));
		$this->assertEquals(1, preg_match(Model::VALIDATION_URL, 'http://sub.www2.something.tld'));
		$this->assertEquals(1, preg_match(Model::VALIDATION_URL, 'ftp://127.0.0.1'));
		$this->assertEquals(1, preg_match(Model::VALIDATION_URL, 'ssh://10.10.10.10'));
		$this->assertEquals(0, preg_match(Model::VALIDATION_URL, 'www.foo.com'));
	}

	public function testValidationURLWeb(){
		$this->assertEquals(1, preg_match(Model::VALIDATION_URL_WEB, 'http://blah.tld'));
		$this->assertEquals(1, preg_match(Model::VALIDATION_URL_WEB, 'http://something.meuseum'));
		$this->assertEquals(1, preg_match(Model::VALIDATION_URL, 'https://something.meuseum'));
		$this->assertEquals(1, preg_match(Model::VALIDATION_URL_WEB, 'http://sub.www2.something.tld'));
		$this->assertEquals(0, preg_match(Model::VALIDATION_URL_WEB, 'ftp://127.0.0.1'));
		$this->assertEquals(0, preg_match(Model::VALIDATION_URL_WEB, 'ssh://10.10.10.10'));
		$this->assertEquals(0, preg_match(Model::VALIDATION_URL_WEB, 'www.foo.com'));
	}
}
 