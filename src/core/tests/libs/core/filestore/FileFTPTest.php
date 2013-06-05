<?php
/**
 * Test the FTP file backend
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130530.1925
 * @package PackageName
 * 
 * Created with JetBrains PhpStorm.
 */

/**
 * Class FileFTPTest
 *
 * @see core/libs/core/filestore/File.interface.php
 * @see core/libs/core/filestore/backends/FileFTP.php
 */
class FileFTPTest extends PHPUnit_Framework_TestCase {

	protected $_ftp;

	protected function setUp(){
		$this->_ftp = \Core\FTP();
	}

	/**
	 * Test the getFilesize method
	 */
	public function testGetFilesize(){
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

		$file = new \Core\Filestore\Backends\FileFTP('core/tests/updater-testdocument.txt');

		$this->assertGreaterThan(0, $file->getFilesize());
		$this->assertNotEmpty($file->getFilesize(true));
	}

	/**
	 * Test the getMimetype method
	 */
	public function testGetMimetype(){
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

		$file = new \Core\Filestore\Backends\FileFTP('core/tests/updater-testdocument.txt');

		$this->assertStringStartsWith('text/', $file->getMimetype());
	}

	/**
	 * Test the getExtension method
	 */
	public function testGetExtension(){
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

		$file = new \Core\Filestore\Backends\FileFTP('core/tests/updater-testdocument.txt');

		$this->assertEquals('txt', $file->getExtension());
	}

	/**
	 * Test the getURL method
	 */
	public function testGetURL(){
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

		$file = \Core\Filestore\Factory::File('asset/images/logo.png');

		if(!$file instanceof \Core\Filestore\Backends\FileFTP){
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
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

		$file = \Core\Filestore\Factory::File('asset/images/logo.png');

		if(!$file instanceof \Core\Filestore\Backends\FileFTP){
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
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

		$file = new \Core\Filestore\Backends\FileFTP('core/tests/updater-testdocument.txt');

		// Sending in an empty string should return the path relative to Core.
		$this->assertStringStartsWith('core/tests/', $file->getFilename(''));

		// Sending in null should return the path with the fully resolved path.
		$this->assertStringStartsWith(ROOT_PDIR, $file->getFilename());
	}

	/**
	 * The filename should be able to be settable afterwards too!
	 */
	public function testSetFilename(){
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

		$file = new \Core\Filestore\Backends\FileFTP();
		$file->setFilename('core/tests/updater-testdocument.txt');

		$this->assertEquals(ROOT_PDIR . 'core/tests/updater-testdocument.txt', $file->getFilename());
		$this->assertTrue($file->exists());
	}

	/**
	 * Test the getBasename method
	 */
	public function testGetBasename(){
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

		$file = new \Core\Filestore\Backends\FileFTP('core/tests/updater-testdocument.txt');

		$this->assertEquals('updater-testdocument.txt', $file->getBasename());
		$this->assertEquals('updater-testdocument', $file->getBasename(true));
	}

	/**
	 * Test the getLocalFilename method
	 */
	public function testGetLocalFilename(){
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

		$file = new \Core\Filestore\Backends\FileFTP('core/tests/updater-testdocument.txt');

		// For local files, it's the same :)
		$this->assertEquals($file->getFilename(), $file->getLocalFilename());
	}

	/**
	 * Test the getHash method
	 */
	public function testGetHash() {
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

		$file = new \Core\Filestore\Backends\FileFTP('core/tests/updater-testdocument.txt');

		$this->assertEquals(md5_file(ROOT_PDIR . 'core/tests/updater-testdocument.txt'), $file->getHash());
	}

	/**
	 * Test the getFilenameHash method
	 */
	public function testGetFilenameHash() {
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

		$file = new \Core\Filestore\Backends\FileFTP('core/tests/updater-testdocument.txt');

		// For local files, this should be base64:[b64-of-full-filename]
		$this->assertEquals('base64:' . base64_encode($file->getFilename()), $file->getFilenameHash());
	}

	/**
	 * Test the delete method
	 */
	public function testDelete() {
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

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
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

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
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

		$file1 = new \Core\Filestore\Backends\FileFTP('core/tests/updater-testdocument.txt');
		$file2 = new \Core\Filestore\Backends\FileFTP('core/tests/ivak_TV_Test_Screen.png');

		$this->assertFalse($file1->isImage());
		$this->assertTrue($file2->isImage());
	}

	public function testIsText(){
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

		$file1 = new \Core\Filestore\Backends\FileFTP('core/tests/updater-testdocument.txt');
		$file2 = new \Core\Filestore\Backends\FileFTP('core/tests/ivak_TV_Test_Screen.png');

		$this->assertTrue($file1->isText());
		$this->assertFalse($file2->isText());
	}

	public function testIsPreviewable(){
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

		$file = new \Core\Filestore\Backends\FileFTP('core/tests/ivak_TV_Test_Screen.png');
		$this->assertTrue($file->isPreviewable());
	}

	public function testDisplayPreview(){
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

		$src = new \Core\Filestore\Backends\FileFTP('core/tests/ivak_TV_Test_Screen.png');
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
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

		$file1 = new \Core\Filestore\Backends\FileFTP('core/tests/updater-testdocument.txt');
		$file2 = new \Core\Filestore\Backends\FileFTP('core/tests/ivak_TV_Test_Screen.png');

		$this->assertContains('text-plain', $file1->getMimetypeIconURL());
		$this->assertStringStartsWith(ROOT_URL, $file1->getMimetypeIconURL());

		$this->assertContains('image-png', $file2->getMimetypeIconURL());
		$this->assertStringStartsWith(ROOT_URL, $file2->getMimetypeIconURL());
	}

	public function testGetQuickPreviewFile() {
		// @todo Finish this
		$this->markTestIncomplete('@todo Finish this');
	}

	public function testGetPreviewFile() {
		// @todo Finish this
		$this->markTestIncomplete('@todo Finish this');
	}

	public function testInDirectory() {
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

		$file = new \Core\Filestore\Backends\FileFTP('core/tests/updater-testdocument.txt');

		$dir = dirname($file->getFilename());

		$this->assertTrue($file->inDirectory($dir));
		$this->assertFalse($file->inDirectory(\Core\Filestore\get_tmp_path()));
	}

	public function testIdenticalTo() {
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

		$file1 = new \Core\Filestore\Backends\FileFTP('core/tests/updater-testdocument.txt');
		$file2 = \Core\Filestore\Factory::File('core/tests/updater-testdocument.txt');
		$file3 = new \Core\Filestore\Backends\FileFTP('core/tests/ivak_TV_Test_Screen.png');

		$this->assertTrue($file1->identicalTo($file2));
		$this->assertFalse($file1->identicalTo($file3));
	}

	public function testCopyTo() {
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

		$file = new \Core\Filestore\Backends\FileFTP('core/tests/updater-testdocument.txt');

		// I should be able to copy to a filename.
		// this gets resolved to a local file.
		$copy = $file->copyTo('tmp/tests-fileremotetest-testcopyto.dat');
		$this->assertInstanceOf('\\Core\\Filestore\\File', $copy);
		$this->assertTrue($copy->exists());
		$this->assertTrue($copy->delete());

		// And it should be able to copy to a local file object.
		$copy = new \Core\Filestore\Backends\FileLocal('tmp/tests-fileremotetest-testcopyto.dat');
		$this->assertFalse($copy->exists());
		$file->copyTo($copy);
		$this->assertTrue($copy->exists());
		$this->assertTrue($copy->delete());
	}

	public function testCopyFrom() {
		// @todo Finish this
		$this->markTestIncomplete('@todo Finish this');
	}

	public function testGetContents() {
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

		$file1 = new \Core\Filestore\Backends\FileFTP('core/tests/updater-testdocument.txt');

		$this->assertNotEmpty($file1->getContents());
	}

	public function testPutContents() {
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

		$contents = 'Some Example Content';

		$file1 = \Core\Filestore\Factory::File('tmp/test-filelocaltest-putcontents.dat');

		$this->assertTrue($file1->putContents($contents));
		$this->assertTrue($file1->exists());
		$this->assertEquals($contents, $file1->getContents());
		$this->assertTrue($file1->delete());
	}

	public function testGetContentsObject() {
		// @todo Finish this
		$this->markTestIncomplete('@todo Finish this');
	}

	public function testExists() {
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

		$file1 = new \Core\Filestore\Backends\FileFTP('core/tests/updater-testdocument.txt');
		$file2 = new \Core\Filestore\Backends\FileFTP('core/tests/updater-testdocument-doesntoexist.txt');

		$this->assertTrue($file1->exists());
		$this->assertFalse($file2->exists());
	}

	public function testIsReadable() {
		if(!$this->_ftp) $this->markTestSkipped('FTP disabled, skipping tests');

		$file1 = new \Core\Filestore\Backends\FileFTP('core/tests/updater-testdocument.txt');
		$file2 = new \Core\Filestore\Backends\FileFTP('core/tests/updater-testdocument-doesntoexist.txt');

		$this->assertTrue($file1->isReadable());
		$this->assertFalse($file2->isReadable());
	}

	public function testIsWritable() {
		// @todo Finish this
		$this->markTestIncomplete('@todo Finish this');
	}

	public function testGetMTime() {
		// @todo Finish this
		$this->markTestIncomplete('@todo Finish this');
	}
}
