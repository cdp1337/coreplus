<?php

/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 7/21/16
 * Time: 5:07 PM
 */
class XMLLoaderTest extends PHPUnit_Framework_TestCase{
	
	public static $XMLFile;

	/**
	 * This method is called before the first test of this test class is run.
	 *
	 * @since Method available since Release 3.4.0
	 */
	public static function setUpBeforeClass() {
		self::$XMLFile = ROOT_PDIR . 'core/tests/testcomponent.xml';
	}

	/**
	 * Serialize this object, preserving the underlying DOMDocument, (which otherwise wouldn't be perserved).
	 *
	 * @return string
	 */
	public function serialize(){
		
	}

	/**
	 * Magic method called to convert a serialized object back to a valid XMLLoader object.
	 *
	 * @param string $serialized
	 *
	 * @return mixed|void
	 */
	public function unserialize($serialized){
		
	}
	
	public function testLoad(){
		$obj = new XMLLoader();
		// If no root name set, it should return false.
		$this->assertFalse($obj->load());
		
		// Now setting a root name should allow it to go through.
		$obj->setRootName('component');
		
		// This can operate on a filename based approach.
		$obj->setFilename(self::$XMLFile);
		$this->assertTrue($obj->load());
		
		// After loading, the contents should be available.
		$this->assertInstanceOf('DOMDocument', $obj->getDOM());
		$this->assertInstanceOf('DOMElement', $obj->getRootDOM());
	}
	
	public function testLoadFromFile(){
		$obj = new XMLLoader();
		$obj->setRootName('component');
		// And by a file object.
		$file = \Core\Filestore\Factory::File(self::$XMLFile);
		$this->assertTrue($obj->loadFromFile($file));

		// After loading, the contents should be available.
		$this->assertInstanceOf('DOMDocument', $obj->getDOM());
		$this->assertInstanceOf('DOMElement', $obj->getRootDOM());
	}
	
	public function testLoadFromNode(){
		$obj = new XMLLoader();
		
		$node = new DOMElement('thing', 'VAL');

		// Loading cannot happen until the root name has been set.
		$this->assertFalse($obj->loadFromNode($node));
		
		$obj->setRootName('thing');
		$this->assertTrue($obj->loadFromNode($node));
		
		// After loading, the contents should be available.
		$this->assertInstanceOf('DOMDocument', $obj->getDOM());
		$this->assertInstanceOf('DOMElement', $obj->getRootDOM());
		
		$this->assertEquals('VAL', $obj->getRootDOM()->nodeValue);
		$this->assertEquals('thing', $obj->getRootDOM()->nodeName);
	}

	public function testLoadFromString(){
		$obj = new XMLLoader();

		$xml = '<xml><thing>VAL</thing></xml>';

		// Loading cannot happen until the root name has been set.
		$this->assertFalse($obj->loadFromString($xml));

		$obj->setRootName('thing');
		$this->assertTrue($obj->loadFromString($xml));

		// After loading, the contents should be available.
		$this->assertInstanceOf('DOMElement', $obj->getRootDOM());

		$this->assertEquals('VAL', $obj->getRootDOM()->nodeValue);
		$this->assertEquals('thing', $obj->getRootDOM()->nodeName);
	}

	public function testSetSchema(){
		$obj = new XMLLoader();

		$xml = '<xml><thing>VAL</thing></xml>';
		$obj->setRootName('thing');
		$this->assertTrue($obj->loadFromString($xml));

		$obj->setSchema('http://domain.tld');
		
		$this->assertContains('<!DOCTYPE thing PUBLIC "SYSTEM" "http://domain.tld">', $obj->asMinifiedXML());
	}
	
	public function testGetElementsByTagName(){
		$obj = new XMLLoader();
		// If no root name set, it should return false.
		$this->assertFalse($obj->load());

		// Now setting a root name should allow it to go through.
		$obj->setRootName('component');

		// This can operate on a filename based approach.
		$obj->setFilename(self::$XMLFile);
		$this->assertTrue($obj->load());
		
		$this->assertEquals(0, $obj->getElementsByTagName('foo')->length);
		$this->assertEquals(1, $obj->getElementsByTagName('require')->length);
	}

	public function testGetElementByTagName(){
		$obj = new XMLLoader();
		// If no root name set, it should return false.
		$this->assertFalse($obj->load());

		// Now setting a root name should allow it to go through.
		$obj->setRootName('component');

		// This can operate on a filename based approach.
		$obj->setFilename(self::$XMLFile);
		$this->assertTrue($obj->load());

		$this->assertNull($obj->getElementByTagName('foo'));
		$this->assertInstanceOf('DOMElement', $obj->getElementByTagName('require'));
	}
	
	public function testGetElement(){
		$obj = new XMLLoader();
		// If no root name set, it should return false.
		$this->assertFalse($obj->load());

		// Now setting a root name should allow it to go through.
		$obj->setRootName('component');

		// This can operate on a filename based approach.
		$obj->setFilename(self::$XMLFile);
		$this->assertTrue($obj->load());

		$this->assertNull($obj->getElement('foo', false));
		$this->assertInstanceOf('DOMElement', $obj->getElement('foo', true));
		
		// Try a lookup
		// These are all the same thing.
		
		// Fully resolve path! <3
		$this->assertInstanceOf('DOMElement', $obj->getElement('/component/requires/require[@name="core"]', false));
		// Search for the node anywhere
		$this->assertInstanceOf('DOMElement', $obj->getElement('*/require[@name="core"]', false));
		// Legacy support for top-level-only node
		$this->assertInstanceOf('DOMElement', $obj->getElement('//requires/require[@name="core"]', false));
		// Search for the node anywhere.
		$this->assertInstanceOf('DOMElement', $obj->getElement('requires/require[@name="core"]', false));
		$this->assertInstanceOf('DOMElement', $obj->getElement('require[@name="core"]', false));
		$this->assertInstanceOf('DOMElement', $obj->getElement('require[@version]', false));
		$this->assertInstanceOf('DOMElement', $obj->getElement('require[@version="2.4.3"]', false));
		$this->assertInstanceOf('DOMElement', $obj->getElement('require', false));
		
		// Try some lookups that should fail.
		
		// The node "junk" does not exist.
		$this->assertNull($obj->getElement('junk[@name="core"]', false));
		// The node require is not at the top path, this will fail.
		$this->assertNull($obj->getElement('/require', false));
		
		// And try the auto-create feature.
		$node = $obj->getElement('requires/require[@name="thing1"][@version="0.0.1"]', true);
		$this->assertInstanceOf('DOMElement', $node);
		$this->assertContains('<require name="thing1" version="0.0.1"/>', $obj->asMinifiedXML());
		
		// Updating the node should work too!
		$node->setAttribute('version', '1.0.0');
		$this->assertContains('<require name="thing1" version="1.0.0"/>', $obj->asMinifiedXML());
	}
	
	public function testCreateElement(){
		$obj = new XMLLoader();

		$xml = '<xml><thing>VAL</thing></xml>';
		$obj->setRootName('thing');
		$this->assertTrue($obj->loadFromString($xml));

		$this->assertInstanceOf('DOMElement', $obj->getElement('/thing/node[@key="keyval"]/child[@name="/thing1"][@version="@0.0.1"]', true));
		$this->assertEquals('<?xml version="1.0"?><xml><thing>VAL<node key="keyval"><child name="/thing1" version="@0.0.1"/></node></thing></xml>', $obj->asMinifiedXML());
	}
	
	public function testElementToArray(){
		$obj = new XMLLoader();

		$xml = '<xml><thing att="key">VAL</thing></xml>';
		$obj->setRootName('thing');
		$this->assertTrue($obj->loadFromString($xml));

		$this->assertInstanceOf('DOMElement', $obj->getElement('/thing/node[@key="keyval"]/child[@name="/thing1"][@version="@0.0.1"]', true));
		$out = $obj->elementToArray($obj->getRootDOM());
		$this->assertEquals('thing', $out['#NAME']);
		$this->assertEquals('VAL', $out['#VALUE']);
		$this->assertEquals('key', $out['#ATTRIBUTES']['att']);
		$this->assertGreaterThan(0, $out['#CHILDREN']);

		$obj = new XMLLoader();

		$xml = '<xml><thing xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/></xml>';
		$obj->setRootName('thing');
		$this->assertTrue($obj->loadFromString($xml));
		$out = $obj->elementToArray($obj->getRootDOM());
		$this->assertEquals('thing', $out['#NAME']);
		$this->assertNull($out['#VALUE']);
		$this->assertEquals(0, sizeof($out['#ATTRIBUTES']));
		$this->assertNull($out['#CHILDREN']);
	}
}