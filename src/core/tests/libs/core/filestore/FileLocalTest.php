<?php
/**
 * Test the local file backend
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130530.1925
 * @package Core\Filestore
 */

/**
 * Class FileLocalTest
 *
 * @package Core\Filestore
 * @see core/libs/core/filestore/File.interface.php
 * @see core/libs/core/filestore/backends/FileLocal.php
 */
class FileLocalTest extends PHPUnit_Framework_TestCase {

	/**
	 * Test the getFilesize method
	 */
	public function testGetFilesize(){
		$file = new \Core\Filestore\Backends\FileLocal('core/tests/updater-testdocument.txt');

		$this->assertGreaterThan(0, $file->getFilesize());
		$this->assertNotEmpty($file->getFilesize(true));
	}

	/**
	 * Test the getMimetype method
	 */
	public function testGetMimetype(){
		$file0 = new \Core\Filestore\Backends\FileLocal('core/tests/updater-testdocument.txt');
		$file1 = new \Core\Filestore\Backends\FileLocal('core/tests/ivak_TV_Test_Screen.png');
		$file2 = new \Core\Filestore\Backends\FileLocal('core/tests/ivak_TV_Test_Screen.png.gz');
		$file3 = new \Core\Filestore\Backends\FileLocal('core/tests/ivak_TV_Test_Screen.png.tar.gz');
		$file4 = new \Core\Filestore\Backends\FileLocal('core/tests/ivak_TV_Test_Screen.png.zip');

		$this->assertStringStartsWith('text/', $file0->getMimetype());
		$this->assertEquals('image/png', $file1->getMimetype());
		$this->assertEquals('application/x-gzip', $file2->getMimetype());
		$this->assertStringStartsWith('application/x-gzip', $file3->getMimetype());
		$this->assertStringStartsWith('application/zip', $file4->getMimetype());
	}

	/**
	 * Test the getExtension method
	 */
	public function testGetExtension(){
		$file = new \Core\Filestore\Backends\FileLocal('core/tests/updater-testdocument.txt');

		$this->assertEquals('txt', $file->getExtension());
	}

	/**
	 * Test the getURL method
	 */
	public function testGetURL(){
		$file = \Core\Filestore\Factory::File('asset/images/logo.png');

		if(!$file instanceof \Core\Filestore\Backends\FileLocal){
			$this->markTestSkipped('asset files are not local files, skipping getURL');
		}
		else{
			$this->assertStringStartsWith(ROOT_URL, $file->getURL());
		}
	}

	/**
	 * Test the getPreviewURL method
	 */
	public function testGetPreviewURL(){
		$file = \Core\Filestore\Factory::File('asset/images/logo.png');

		if(!$file instanceof \Core\Filestore\Backends\FileLocal){
			$this->markTestSkipped('asset files are not local files, skipping getPreviewURL');
		}
		else{
			$this->assertStringStartsWith(ROOT_URL, $file->getPreviewURL());
		}
	}

	/**
	 * Test the getFilename method
	 */
	public function testGetFilename(){
		$file = new \Core\Filestore\Backends\FileLocal('core/tests/updater-testdocument.txt');

		// Sending in an empty string should return the path relative to Core.
		$this->assertStringStartsWith('core/tests/', $file->getFilename(''));

		// Sending in null should return the path with the fully resolved path.
		$this->assertStringStartsWith(ROOT_PDIR, $file->getFilename());
	}

	/**
	 * The filename should be able to be settable afterwards too!
	 */
	public function testSetFilename(){
		$file = new \Core\Filestore\Backends\FileLocal();
		$file->setFilename('core/tests/updater-testdocument.txt');

		$this->assertEquals(ROOT_PDIR . 'core/tests/updater-testdocument.txt', $file->getFilename());
		$this->assertTrue($file->exists());
	}

	/**
	 * Test the getBasename method
	 */
	public function testGetBasename(){
		$file = new \Core\Filestore\Backends\FileLocal('core/tests/updater-testdocument.txt');

		$this->assertEquals('updater-testdocument.txt', $file->getBasename());
		$this->assertEquals('updater-testdocument', $file->getBasename(true));
	}

	/**
	 * Test the getLocalFilename method
	 */
	public function testGetLocalFilename(){
		$file = new \Core\Filestore\Backends\FileLocal('core/tests/updater-testdocument.txt');

		// For local files, it's the same :)
		$this->assertEquals($file->getFilename(), $file->getLocalFilename());
	}

	/**
	 * Test the getHash method
	 */
	public function testGetHash() {
		$file = new \Core\Filestore\Backends\FileLocal('core/tests/updater-testdocument.txt');

		$this->assertEquals(md5_file(ROOT_PDIR . 'core/tests/updater-testdocument.txt'), $file->getHash());
	}

	/**
	 * Test the getFilenameHash method
	 */
	public function testGetFilenameHash() {
		$file = new \Core\Filestore\Backends\FileLocal('core/tests/updater-testdocument.txt');

		// For local files, this should be base64:[b64-of-full-filename]
		$this->assertEquals('base64:' . base64_encode($file->getFilename()), $file->getFilenameHash());
	}

	/**
	 * Test the delete method
	 */
	public function testDelete() {
		$file = \Core\Filestore\Factory::File('tmp/test-filelocaltest-testdelete.dat');

		// I need to write something to it so it exists!
		// (this is usually the case)
		if(!$file->exists()){
			$file->putContents('This is some test data!');
		}

		// Still doesn't exist?
		$this->assertTrue($file->exists());
		// And delete it
		$this->assertTrue($file->delete());
		// And now it really shouldn't exist, (again).
		$this->assertFalse($file->exists());
	}

	public function testRename() {
		$file = \Core\Filestore\Factory::File('tmp/test-filelocaltest-testrename.dat');

		// I need to write something to it so it exists!
		// (this is usually the case)
		if(!$file->exists()){
			$file->putContents('This is some test data!');
		}

		// Still doesn't exist?
		$this->assertTrue($file->exists());

		// The basename should be testrename.
		$this->assertEquals('test-filelocaltest-testrename.dat', $file->getBasename());

		$this->assertTrue($file->rename('test-filelocaltest-testrename2.dat'));

		// And the filename should have changed
		$this->assertEquals('test-filelocaltest-testrename2.dat', $file->getBasename());

		// And verify that this new file exists
		$file2 = \Core\Filestore\Factory::File('tmp/test-filelocaltest-testrename2.dat');
		$this->assertTrue($file2->exists());

		// And cleanup
		$file->delete();
	}

	public function testIsImage(){
		$file1 = new \Core\Filestore\Backends\FileLocal('core/tests/updater-testdocument.txt');
		$file2 = new \Core\Filestore\Backends\FileLocal('core/tests/ivak_TV_Test_Screen.png');

		$this->assertFalse($file1->isImage());
		$this->assertTrue($file2->isImage());
	}

	public function testIsText(){
		$file1 = new \Core\Filestore\Backends\FileLocal('core/tests/updater-testdocument.txt');
		$file2 = new \Core\Filestore\Backends\FileLocal('core/tests/ivak_TV_Test_Screen.png');

		$this->assertTrue($file1->isText());
		$this->assertFalse($file2->isText());
	}

	public function testIsPreviewable(){
		$file = new \Core\Filestore\Backends\FileLocal('core/tests/ivak_TV_Test_Screen.png');
		$this->assertTrue($file->isPreviewable());
	}

	public function testDisplayPreview(){
		$src = new \Core\Filestore\Backends\FileLocal('core/tests/ivak_TV_Test_Screen.png');
		$dst = \Core\Filestore\Factory::File('tmp/filelocaltest-displaypreview.dat');

		// Start capturing the output
		ob_start();
		$src->displayPreview('32x32', false);
		$dat = ob_get_clean();

		$this->assertNotEmpty($dat);

		// Quickest way to verify this is an actual image... write it to another file and check that!
		$dst->putContents($dat);

		// Make sure it wrote
		$this->assertTrue($dst->exists());
		// And is a non-zero size
		$this->assertGreaterThan(0, $dst->getFilesize());
		// And is an image
		$this->assertTrue($dst->isImage());
		// Finally, clean it up.
		$this->assertTrue($dst->delete());
	}

	public function testGetMimetypeIconURL() {
		$file1 = new \Core\Filestore\Backends\FileLocal('core/tests/updater-testdocument.txt');
		$file2 = new \Core\Filestore\Backends\FileLocal('core/tests/ivak_TV_Test_Screen.png');

		$this->assertContains('text-plain', $file1->getMimetypeIconURL());
		$this->assertStringStartsWith(ROOT_URL, $file1->getMimetypeIconURL());

		$this->assertContains('image-png', $file2->getMimetypeIconURL());
		$this->assertStringStartsWith(ROOT_URL, $file2->getMimetypeIconURL());
	}

	public function testGetQuickPreviewFile() {
		$file1 = new \Core\Filestore\Backends\FileLocal('core/tests/ivak_TV_Test_Screen.png');

		$preview = $file1->getQuickPreviewFile();

		// Preview File needs to return a valid file.
		$this->assertInstanceOf('Core\\Filestore\\Backends\\FileLocal', $preview);
		// That's publicly visible.
		$this->assertEquals('public', $preview->_type);
	}

	public function testGetPreviewFile() {
		$file1 = new \Core\Filestore\Backends\FileLocal('core/tests/ivak_TV_Test_Screen.png');

		$preview = $file1->getPreviewFile();

		// Preview File needs to return a valid file.
		$this->assertInstanceOf('Core\\Filestore\\Backends\\FileLocal', $preview);
		// That's publicly visible.
		$this->assertEquals('public', $preview->_type);
		// And it must exist.
		$this->assertTrue($preview->exists());
	}

	public function testInDirectory() {
		$file = new \Core\Filestore\Backends\FileLocal('core/tests/updater-testdocument.txt');

		$this->assertTrue($file->inDirectory(ROOT_PDIR));
		$this->assertTrue($file->inDirectory(ROOT_PDIR . 'core/tests'));
		$this->assertFalse($file->inDirectory(\Core\Filestore\get_tmp_path()));
	}

	public function testIdenticalTo() {
		$file1 = new \Core\Filestore\Backends\FileLocal('core/tests/updater-testdocument.txt');
		$file2 = \Core\Filestore\Factory::File('core/tests/updater-testdocument.txt');
		$file3 = new \Core\Filestore\Backends\FileLocal('core/tests/ivak_TV_Test_Screen.png');

		$this->assertTrue($file1->identicalTo($file2));
		$this->assertFalse($file1->identicalTo($file3));
	}

	public function testCopyTo() {
		$file = new \Core\Filestore\Backends\FileLocal('core/tests/updater-testdocument.txt');

		// I should be able to copy to a filename.
		// this gets resolved to a local file.
		$copy = $file->copyTo('tmp/tests-filelocaltest-testcopyto.dat');
		$this->assertInstanceOf('\\Core\\Filestore\\File', $copy);
		$this->assertTrue($copy->exists());
		$this->assertTrue($copy->delete());

		// And it should be able to copy to a local file object.
		$copy = new \Core\Filestore\Backends\FileLocal('tmp/tests-filelocaltest-testcopyto.dat');
		$this->assertFalse($copy->exists());
		$file->copyTo($copy);
		$this->assertTrue($copy->exists());
		$this->assertTrue($copy->delete());
	}

	public function testCopyFrom() {
		$copy = Core\Filestore\Factory::File('tmp/tests-filelocaltest-testcopyfrom.dat');
		if($copy->exists()){
			$this->assertTrue($copy->delete(), 'Delete files in tmp/');
		}
		$file = new \Core\Filestore\Backends\FileLocal('core/tests/updater-testdocument.txt');

		// I should be able to copy from a filename.
		// this gets resolved to a local file.
		$copy->copyFrom($file);
		$this->assertInstanceOf('\\Core\\Filestore\\File', $copy);
		$this->assertTrue($copy->exists());
		$this->assertTrue($copy->identicalTo($file));
		$this->assertTrue($copy->delete());
	}

	public function testGetContents() {
		$file1 = new \Core\Filestore\Backends\FileLocal('core/tests/updater-testdocument.txt');

		$this->assertNotEmpty($file1->getContents());
	}

	public function testPutContents() {
		$contents = 'Some Example Content';

		$file1 = \Core\Filestore\Factory::File('tmp/test-filelocaltest-putcontents.dat');

		$this->assertTrue($file1->putContents($contents));
		$this->assertTrue($file1->exists());
		$this->assertEquals($contents, $file1->getContents());
		$this->assertTrue($file1->delete());
	}

	public function testGetContentsObject() {
		$file1 = new \Core\Filestore\Backends\FileLocal('core/tests/ivak_TV_Test_Screen.png.gz');
		$file2 = new \Core\Filestore\Backends\FileLocal('core/tests/ivak_TV_Test_Screen.png.tar.gz');
		$file3 = new \Core\Filestore\Backends\FileLocal('core/tests/ivak_TV_Test_Screen.png.zip');

		$this->assertInstanceOf('\\Core\\Filestore\\Contents\\ContentGZ', $file1->getContentsObject());
		$this->assertInstanceOf('\\Core\\Filestore\\Contents\\ContentTGZ', $file2->getContentsObject());
		$this->assertInstanceOf('\\Core\\Filestore\\Contents\\ContentZIP', $file3->getContentsObject());
	}

	public function testExists() {
		$file1 = new \Core\Filestore\Backends\FileLocal('core/tests/updater-testdocument.txt');
		$file2 = new \Core\Filestore\Backends\FileLocal('core/tests/updater-testdocument-doesntoexist.txt');

		$this->assertTrue($file1->exists());
		$this->assertFalse($file2->exists());
	}

	public function testIsReadable() {
		$file1 = new \Core\Filestore\Backends\FileLocal('core/tests/updater-testdocument.txt');
		$file2 = new \Core\Filestore\Backends\FileLocal('core/tests/updater-testdocument-doesntoexist.txt');

		$this->assertTrue($file1->isReadable());
		$this->assertFalse($file2->isReadable());
	}

	public function testIsWritable() {
		$file = new \Core\Filestore\Backends\FileLocal('core/tests/ivak_TV_Test_Screen.png');
		$this->assertTrue($file->isWritable());
	}

	public function testGetMTime() {
		$file1 = new \Core\Filestore\Backends\FileLocal('core/tests/updater-testdocument.txt');

		$this->assertGreaterThan(100, $file1->getMTime());
	}

	/**
	 * Test that a file can be sent to the user agent via the File interface.
	 */
	public function testSendToUserAgent(){
		$file = new \Core\Filestore\Backends\FileLocal('core/tests/ivak_TV_Test_Screen.png');

		ob_start();
		$file->sendToUserAgent(true);
		$contents = ob_get_clean();

		$headers = \Core\view()->headers;

		$this->assertArrayHasKey('Content-Disposition', $headers);
		$this->assertArrayHasKey('Cache-Control', $headers);
		$this->assertArrayHasKey('Content-Transfer-Encoding', $headers);
		$this->assertArrayHasKey('Content-Length', $headers);

		$this->assertEquals('image/png', \Core\view()->contenttype);

		$this->assertNotEmpty($contents);
	}

	public function testImageResizing(){
		// Start with this 1024x768 image.
		$file = new \Core\Filestore\Backends\FileLocal('core/tests/ivak_TV_Test_Screen.png');

		// Basic resize will scale large images down but will not scale small images up.
		$test = $file->getPreviewFile('32x32');
		$this->assertTrue($test->isImage());
		$dimensions = getimagesize($test->getFilename());
		$this->assertEquals(32, $dimensions[0]);
		$this->assertEquals(24, $dimensions[1]);

		// Basic resize will scale down images if one of the dimensions is greater than the requested.
		$test = $file->getPreviewFile('800x800');
		$this->assertTrue($test->isImage());
		$dimensions = getimagesize($test->getFilename());
		$this->assertEquals(800, $dimensions[0]);
		$this->assertEquals(600, $dimensions[1]);

		// Basic resize will NOT scale up an image.
		$test = $file->getPreviewFile('2048x2048');
		$this->assertTrue($test->isImage());
		$dimensions = getimagesize($test->getFilename());
		$this->assertEquals(1024, $dimensions[0]);
		$this->assertEquals(768, $dimensions[1]);

		// Force-Constraint forces the constraint regardless of original image source.
		$test = $file->getPreviewFile('32x32!');
		$this->assertTrue($test->isImage());
		$dimensions = getimagesize($test->getFilename());
		$this->assertEquals(32, $dimensions[0]);
		$this->assertEquals(32, $dimensions[1]);

		// Force-Constraint forces the constraint regardless of original image source.
		$test = $file->getPreviewFile('1040x1040!');
		$this->assertTrue($test->isImage());
		$dimensions = getimagesize($test->getFilename());
		$this->assertEquals(1040, $dimensions[0]);
		$this->assertEquals(1040, $dimensions[1]);

		// Fill area will scale up to match the smallest dimension.
		$test = $file->getPreviewFile('800x800^');
		$this->assertTrue($test->isImage());
		$dimensions = getimagesize($test->getFilename());
		$this->assertEquals(1067, $dimensions[0]);
		$this->assertEquals(800, $dimensions[1]);

		// Fill area will scale up to match the smallest dimension.
		$test = $file->getPreviewFile('1200x1200^');
		$this->assertTrue($test->isImage());
		$dimensions = getimagesize($test->getFilename());
		$this->assertEquals(1600, $dimensions[0]);
		$this->assertEquals(1200, $dimensions[1]);

		// Fill area will scale up to match the smallest dimension.
		$test = $file->getPreviewFile('32x32^');
		$this->assertTrue($test->isImage());
		$dimensions = getimagesize($test->getFilename());
		$this->assertEquals(42, $dimensions[0]);
		$this->assertEquals(32, $dimensions[1]);
	}
}
