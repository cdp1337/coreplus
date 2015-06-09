<?php
/**
 * @todo Enter a meaningful file description here!
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20141204.1839
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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

 

class PageModelTest extends PHPUnit_Framework_TestCase {
	public function testAdmin(){

		// Basic Construct should return one result.
		/** @var PageModel $page */
		$page = PageModel::Construct('/admin');

		$this->assertInstanceOf('Model', $page);
		$this->assertInstanceOf('PageModel', $page);

		if(!$page->exists()){
			$this->markTestSkipped('/admin page does not exist, Core must not be installed.');
			return;
		}

		// Test that getAsArray works as expected.
		$asArray = $page->getAsArray();
		$this->assertInternalType('array', $asArray);
		$this->assertGreaterThan(0, sizeof($asArray));

		// And JSON
		$asJSON = $page->getAsJSON();
		$this->assertInternalType('string', $asJSON);
		$this->assertGreaterThan(0, strlen($asJSON));
		$this->assertInternalType('array', json_decode($asJSON, true));


		$this->assertArrayHasKey('title', $asArray);
		$this->assertArrayHasKey('title', $page);
		$this->assertEquals($asArray['title'], $page->get('title'));

		$this->assertArrayHasKey('expires', $asArray);
		$this->assertArrayHasKey('expires', $page);
		$this->assertEquals($asArray['expires'], $page->get('expires'));


		// Test that getLink behaves as expected.
		$this->assertNull($page->getLink('nonexistent'));

		// Pages also have the following linked records,
		// insertables, pagemetas, and rewrites.
		$this->assertInternalType('array', $page->getLink('Insertable'));
		$this->assertInternalType('array', $page->getLink('PageMeta'));
		$this->assertInternalType('array', $page->getLink('RewriteMap'));


		// Ensure that creating meta files works
		$meta_name = 'phpunit-test';
		$meta_val  = 'This is a test meta field from phpunit';
		$page->setMeta($meta_name, $meta_val);

		// Saving the page should also save this new meta.
		$this->assertTrue($page->save());

		// Verify this meta from a separate object
		// (because Construct will use the cached copy)
		$page2 = new PageModel('/admin');

		$this->assertTrue($page2->exists());

		$this->assertEquals($meta_val, $page2->getMetaValue($meta_name));

		// Remove the field from the original object now.
		$page->setMeta($meta_name, null);

		// Test that removing it does not remove the object's data until the parent is saved though!
		$meta = new PageMetaModel($page->get('site'), $page->get('baseurl'), $meta_name, '');
		$this->assertTrue($meta->exists());

		$this->assertTrue($page->save());

		$meta = new PageMetaModel($page->get('site'), $page->get('baseurl'), $meta_name, '');
		$this->assertFalse($meta->exists());
	}

	public function testSchema(){
		$schema = PageModel::GetSchema();

		$this->assertInternalType('array', $schema);

		/** @var PageModel $page */
		$page = PageModel::Construct('/admin');
		$schema2 = $page->getKeySchemas();

		// The two schemas should be identical sizes, (they should contain identical content).
		$this->assertEquals(sizeof($schema), sizeof($schema2));

		$asArray = $page->getAsArray();
		foreach($asArray as $k => $val){
			$this->assertArrayHasKey($k, $schema, 'PageModel schema does not contain key ' . $k);

			// Ensure that this schema element has all the required fields.
			$this->assertArrayHasKey('type', $schema[$k], 'PageModel schema ' . $k . ' does not contain a type');
			$this->assertArrayHasKey('maxlength', $schema[$k], 'PageModel schema ' . $k . ' does not contain a maxlength');
			$this->assertArrayHasKey('default', $schema[$k], 'PageModel schema ' . $k . ' does not contain a default field');
			$this->assertArrayHasKey('comment', $schema[$k], 'PageModel schema ' . $k . ' does not contain a comment');
			$this->assertArrayHasKey('null', $schema[$k], 'PageModel schema ' . $k . ' does not contain a null field');
			$this->assertArrayHasKey('encrypted', $schema[$k], 'PageModel schema ' . $k . ' does not contain an encrypted field');
			$this->assertArrayHasKey('required', $schema[$k], 'PageModel schema ' . $k . ' does not contain a required field');
			$this->assertArrayHasKey('options', $schema[$k], 'PageModel schema ' . $k . ' does not contain an options field');
			$this->assertArrayHasKey('title', $schema[$k], 'PageModel schema ' . $k . ' does not contain a title');

			// If the default is null, then null must be true.
			if($schema[$k]['default'] === null){
				$this->assertTrue($schema[$k]['null']);
			}
		}
	}
}
 