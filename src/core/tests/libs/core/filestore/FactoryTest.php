<?php
/**
 * Test file for the Filestore\Factory class.
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130829.1146
 * @package Core\Filestore
 *
 */

class FactoryTest extends PHPUnit_Framework_TestCase {

	public function testFileAsset(){
		$asset = \Core\Filestore\Factory::File('asset/js/core.js');
		// Verify that this is a valid object.
		$this->assertInstanceOf('\Core\Filestore\File', $asset);

		// Make sure that the cache is working too.
		// This is used by consecutive calls to the File object with the same resource.
		$asset = \Core\Filestore\Factory::File('asset/js/core.js');
		// Verify that this is a valid object.
		$this->assertInstanceOf('\Core\Filestore\File', $asset);
	}

	public function testFilePublic(){
		$src = new \Core\Filestore\Backends\FileLocal('core/tests/ivak_TV_Test_Screen.png');

		$dst = \Core\Filestore\Factory::File('public/tests/ivak_TV_Test_Screen.png');
		// Verify that this is a valid object.
		$this->assertInstanceOf('\Core\Filestore\File', $dst);

		// Make sure it's writable... just because :p
		$src->copyTo($dst);
		$this->assertTrue($dst->exists());
	}

	public function testFilePrivate(){
		$src = new \Core\Filestore\Backends\FileLocal('core/tests/ivak_TV_Test_Screen.png');

		$dst = \Core\Filestore\Factory::File('private/tests/ivak_TV_Test_Screen.png');
		// Verify that this is a valid object.
		$this->assertInstanceOf('\Core\Filestore\File', $dst);

		// Make sure it's writable... just because :p
		$src->copyTo($dst);
		$this->assertTrue($dst->exists());
	}
}
