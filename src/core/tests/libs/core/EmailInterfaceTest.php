<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use PHPUnit\Framework\TestCase;

class EmailInterfaceTest extends TestCase {
	
	/**
	 * 
	 * @return array
	 */
	public function backendProvider(){
		$backends = \Core\Email::GetBackends();
		$ret = [];
		foreach($backends as $class => $name){
			$ref = new ReflectionClass($class);
			$ret[] = [$ref->newInstance()];
		}
		
		return $ret;
	}
	
	/**
	 * @dataProvider backendProvider
	 * 
	 * @covers Core\EmailInterface::setTo
	 * @covers Core\EmailInterface::getTo
	 * @covers Core\EmailInterface::addTo
	 * @covers Core\EmailInterface::clearRecipients
	 */
	public function testTo(\Core\EmailInterface $email){
		// Test that setTo works; it should return the instance to allow for chaining.
		$ret = $email->setTo('test@domain.tld', 'Test User');
		$this->assertInstanceOf('\\Core\\EmailInterface', $ret);
		
		// Test that I can retrieve that address.
		$tos = $email->getTo();
		$this->assertInternalType('array', $tos);
		$this->assertCount(1, $tos);
		$this->assertEquals('test@domain.tld', $tos[0][0]);
		$this->assertEquals('Test User', $tos[0][1]);
		
		// addTo should also work, without resetting the to list.
		$email->addTo('test2@domain.tld', 'Test 2 User');
		$tos = $email->getTo();
		$this->assertInternalType('array', $tos);
		$this->assertCount(2, $tos);
		$this->assertEquals('test2@domain.tld', $tos[1][0]);
		$this->assertEquals('Test 2 User', $tos[1][1]);
		
		// Lastly, calling setTo should replace all recipients appropriately.
		$ret = $email->setTo('test3@domain.tld', 'Test 3 User');
		$this->assertInstanceOf('\\Core\\EmailInterface', $ret);
		$tos = $email->getTo();
		$this->assertInternalType('array', $tos);
		$this->assertCount(1, $tos);
		$this->assertEquals('test3@domain.tld', $tos[0][0]);
		$this->assertEquals('Test 3 User', $tos[0][1]);
		
		// This should be able to be reset also.
		$ret = $email->clearRecipients();
		$this->assertInstanceOf('\\Core\\EmailInterface', $ret);
		$tos = $email->getTo();
		$this->assertInternalType('array', $tos);
		$this->assertCount(0, $tos);
	}
	
	/**
	 * @dataProvider backendProvider
	 * 
	 * @covers Core\EmailInterface::addCC
	 * @covers Core\EmailInterface::getCC
	 */
	public function testCC(\Core\EmailInterface $email){
		// Test that addCC works; it should return the instance to allow for chaining.
		$ret = $email->addCC('cc1@domain.tld', 'Test User');
		$this->assertInstanceOf('\\Core\\EmailInterface', $ret);
		
		// Test that I can retrieve that address.
		$tos = $email->getCC();
		$this->assertInternalType('array', $tos);
		$this->assertCount(1, $tos);
		$this->assertEquals('cc1@domain.tld', $tos[0][0]);
		$this->assertEquals('Test User', $tos[0][1]);
		
		// Add another!
		$email->addCC('cc2@domain.tld', 'Test 2 User');
		$tos = $email->getCC();
		$this->assertInternalType('array', $tos);
		$this->assertCount(2, $tos);
		$this->assertEquals('cc2@domain.tld', $tos[1][0]);
		$this->assertEquals('Test 2 User', $tos[1][1]);
		
		// This should be able to be reset also.
		$ret = $email->clearRecipients();
		$this->assertInstanceOf('\\Core\\EmailInterface', $ret);
		$tos = $email->getCC();
		$this->assertInternalType('array', $tos);
		$this->assertCount(0, $tos);
	}
	
	/**
	 * @dataProvider backendProvider
	 * 
	 * @covers Core\EmailInterface::addBCC
	 * @covers Core\EmailInterface::getBCC
	 */
	public function testBCC(\Core\EmailInterface $email){
		// Test that addBCC works; it should return the instance to allow for chaining.
		$ret = $email->addBCC('bcc1@domain.tld', 'Test User');
		$this->assertInstanceOf('\\Core\\EmailInterface', $ret);
		
		// Test that I can retrieve that address.
		$tos = $email->getBCC();
		$this->assertInternalType('array', $tos);
		$this->assertCount(1, $tos);
		$this->assertEquals('bcc1@domain.tld', $tos[0][0]);
		$this->assertEquals('Test User', $tos[0][1]);
		
		// Add another!
		$email->addBCC('bcc2@domain.tld', 'Test 2 User');
		$tos = $email->getBCC();
		$this->assertInternalType('array', $tos);
		$this->assertCount(2, $tos);
		$this->assertEquals('bcc2@domain.tld', $tos[1][0]);
		$this->assertEquals('Test 2 User', $tos[1][1]);
		
		// This should be able to be reset also.
		$ret = $email->clearRecipients();
		$this->assertInstanceOf('\\Core\\EmailInterface', $ret);
		$tos = $email->getBCC();
		$this->assertInternalType('array', $tos);
		$this->assertCount(0, $tos);
	}
	
	/**
	 * @dataProvider backendProvider
	 * 
	 * @covers Core\EmailInterface::setFrom
	 * @covers Core\EmailInterface::getFrom
	 */
	public function testFrom(\Core\EmailInterface $email){
		// Test that setFrom works; it should return the instance to allow for chaining.
		$ret = $email->setFrom('from1@domain.tld', 'Test User');
		$this->assertInstanceOf('\\Core\\EmailInterface', $ret);
		
		// Test that I can retrieve that address.
		$from = $email->getFrom();
		$this->assertInternalType('array', $from);
		$this->assertCount(2, $from);
		$this->assertEquals('from1@domain.tld', $from[0]);
		$this->assertEquals('Test User', $from[1]);
	}
	
	/**
	 * @dataProvider backendProvider
	 * 
	 * @covers Core\EmailInterface::setReplyTo
	 * @covers Core\EmailInterface::addReplyTo
	 * @covers Core\EmailInterface::getReplyTo
	 */
	public function testReplyTo(\Core\EmailInterface $email){
		// Test that setFrom works; it should return the instance to allow for chaining.
		$ret = $email->setReplyTo('no-reply@domain.tld', 'Test User');
		$this->assertInstanceOf('\\Core\\EmailInterface', $ret);
		
		// Test that I can retrieve that address.
		$to = $email->getReplyTo();
		$this->assertInternalType('array', $to);
		$this->assertCount(1, $to);
		$this->assertEquals('no-reply@domain.tld', $to[0][0]);
		$this->assertEquals('Test User', $to[0][1]);
		
		// Add another! (According the RFC spec, this is allowed.)
		$email->addReplyTo('no-reply2@domain.tld', 'Test 2 User');
		$tos = $email->getReplyTo();
		$this->assertInternalType('array', $tos);
		$this->assertCount(2, $tos);
		$this->assertEquals('no-reply2@domain.tld', $tos[1][0]);
		$this->assertEquals('Test 2 User', $tos[1][1]);
	}
	
	/**
	 * @dataProvider backendProvider
	 * 
	 * @covers Core\EmailInterface::setSubject
	 * @covers Core\EmailInterface::getSubject
	 */
	public function testSubject(\Core\EmailInterface $email){
		// Test that setFrom works; it should return the instance to allow for chaining.
		$ret = $email->setSubject('Test Subject');
		$this->assertInstanceOf('\\Core\\EmailInterface', $ret);
		
		$subject = $email->getSubject();
		$this->assertEquals('Test Subject', $subject);
	}
	
	/**
	 * @dataProvider backendProvider
	 * 
	 * @covers Core\EmailInterface::setHeader
	 * @covers Core\EmailInterface::getHeaders
	 */
	public function testCustomHeader(\Core\EmailInterface $email){
		$ret = $email->setHeader('X-Test-Key', 'Some Custom Value');
		$this->assertInstanceOf('\\Core\\EmailInterface', $ret);
		
		$headers = $email->getHeaders();
		// Should be an array with one key, (probably...)
		$this->assertInternalType('array', $headers);
		$this->assertArrayHasKey('X-Test-Key', $headers);
		$this->assertEquals('Some Custom Value', $headers['X-Test-Key']);
		
		// Adding another header of the same key should push it to an array.
		$ret = $email->setHeader('X-Test-Key', 'Some Custom Value 2');
		$this->assertInstanceOf('\\Core\\EmailInterface', $ret);
		
		$headers = $email->getHeaders();
		// Should be an array with one key, (probably...)
		$this->assertInternalType('array', $headers);
		$this->assertArrayHasKey('X-Test-Key', $headers);
		$this->assertInternalType('array', $headers['X-Test-Key']);
	}
	
	/**
	 * @dataProvider backendProvider
	 * 
	 * @covers Core\EmailInterface::getFullEML
	 * @covers Core\EmailInterface::getHeaders
	 */
	public function testEML(\Core\EmailInterface $email){
		$email->setTo('test@domain.tld', 'Test User');
		$email->setSubject('Test Subject Foo');
		$email->setBody('<p>This is some <i>test</i> email.</p>', true);
		$email->setBody('This is some *test* email.', false);
		
		$eml = $email->getFullEML();
		$this->assertInternalType('string', $eml);
		$this->assertRegExp('/^To: Test User <test@domain.tld>$/m', $eml);
		$this->assertRegExp('/^Subject: Test Subject Foo$/m', $eml);
		$this->assertRegExp('/^This is some \*test\* email.$/m', $eml);
		$this->assertRegExp('/^<p>This is some <i>test<\/i> email.<\/p>$/m', $eml);
	}
}