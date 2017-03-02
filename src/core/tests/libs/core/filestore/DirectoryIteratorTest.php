<?php

namespace Core\Filestore;

use Core\Filestore\Backends\DirectoryLocal;
use PHPUnit\Framework\TestCase;

class DirectoryIteratorTest extends TestCase {

	/**
	 * @return DirectoryLocal
	 */
	public function testGetDirectory(){
		$dir = new DirectoryLocal('core/tests');

		$this->assertInstanceOf('Core\\Filestore\\Backends\\DirectoryLocal', $dir);

		return $dir;
	}

	/**
	 * @depends testGetDirectory
	 */
	public function testFilesOnly($dir){
		$it = new DirectoryIterator($dir);
		$it->findFiles = true;
		$it->findDirectories = false;
		$it->recursive = false;

		$this->assertGreaterThan(0, sizeof($it->scan()));

		foreach($it->scan() as $f){
			$this->assertInstanceOf('Core\\Filestore\\Backends\\FileLocal', $f);
		}
	}

	/**
	 * @depends testGetDirectory
	 */
	public function testDirectoriesOnly($dir){
		$it = new DirectoryIterator($dir);
		$it->findFiles = false;
		$it->findDirectories = true;
		$it->recursive = false;

		$this->assertGreaterThan(0, sizeof($it->scan()));

		foreach($it->scan() as $f){
			$this->assertInstanceOf('Core\\Filestore\\Backends\\DirectoryLocal', $f);
		}
	}

	/**
	 * @depends testGetDirectory
	 */
	public function testRecursive($dir){
		$it1 = new DirectoryIterator($dir);
		$it1->findFiles = true;
		$it1->findDirectories = true;
		$it1->recursive = false;

		$it2 = new DirectoryIterator($dir);
		$it2->findFiles = true;
		$it2->findDirectories = true;
		$it2->recursive = true;

		$size1 = sizeof($it1->scan());
		$this->assertGreaterThan(0, $size1);

		$size2 = sizeof($it2->scan());
		$this->assertGreaterThan(0, $size2);
		$this->assertGreaterThan($size1, $size2);
	}

	/**
	 * @depends testGetDirectory
	 */
	public function testExtensions($dir){
		$it = new DirectoryIterator($dir);
		$it->findFiles = true;
		$it->findDirectories = false;
		$it->recursive = true;
		$it->findExtensions[] = 'png';
		$it->findExtensions[] = 'zip';

		$pngfound = false;
		$zipfound = false;

		foreach($it->scan() as $f){
			/** @var Backends\FileLocal $f */
			$this->assertInstanceOf('Core\\Filestore\\Backends\\FileLocal', $f);
			if($f->isImage()){
				$this->assertEquals('png', $f->getExtension());
				$pngfound = true;
			}
			else{
				$this->assertEquals('zip', $f->getExtension());
				$zipfound = true;
			}
		}

		$this->assertTrue($pngfound, 'PNG file found within tests');
		$this->assertTrue($zipfound, 'ZIP file found within tests');
	}

	/**
	 * @depends testGetDirectory
	 */
	public function testIgnores($dir){
		$it = new DirectoryIterator($dir);
		$it->findFiles = true;
		$it->findDirectories = true;
		$it->recursive = true;
		$it->ignores[] = 'ivak_TV_Test_Screen.png';

		$this->assertGreaterThan(0, sizeof($it->scan()));

		foreach($it->scan() as $f){
			/** @var Backends\FileLocal|Backends\DirectoryLocal $f */
			$this->assertNotEquals('ivak_TV_Test_Screen.png', $f->getBasename());
		}
	}

	/**
	 * @depends testGetDirectory
	 */
	public function testPregMatch($dir){
		$it = new DirectoryIterator($dir);
		$it->findFiles = true;
		$it->findDirectories = true;
		$it->recursive = true;
		$it->pregMatch = '#^ivak_TV_Test_Screen.*#';

		$this->assertGreaterThan(0, sizeof($it->scan()));

		foreach($it->scan() as $f){
			/** @var Backends\FileLocal|Backends\DirectoryLocal $f */
			$this->assertEquals(1, preg_match('/^ivak_TV_Test_Screen.*/', $f->getBasename()));
		}
	}
}