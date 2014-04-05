<?php
/**
 * Test the remote file backend
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130530.1925
 * @package Core\Filestore
 */

/**
 * Class FileRemoteTest
 *
 * @package Core\Filestore
 * @see core/libs/core/filestore/File.interface.php
 * @see core/libs/core/filestore/backends/FileRemote.php
 */
class FileRemoteTest extends PHPUnit_Framework_TestCase {

	protected $_testimage;
	protected $_testfile;
	protected $_test404;

	protected function setUp(){
		$this->_testimage = 'http://corepl.us/files/assets/coreplus/img/core-plus.png';
		$this->_testfile = 'https://raw.github.com/nicholasryan/CorePlus/master/README.md';
		$this->_test404 = 'https://raw.github.com/nicholasryan/CorePlus/master/NOTFOUND';
	}

	/**
	 * Unit test to test the construction of a new file object and to ensure it returns the correct data.
	 */
	public function testFactory(){
		$file = \Core\Filestore\Factory::File($this->_testfile);

		$this->assertInstanceOf('\Core\Filestore\File', $file);
		$this->assertInstanceOf('\Core\Filestore\Backends\FileRemote', $file);
		$this->assertTrue($file->exists());
	}

	/**
	 * Test the getFilesize method
	 */
	public function testGetFilesize(){
		$file = new \Core\Filestore\Backends\FileRemote($this->_testfile);

		$this->assertGreaterThan(0, $file->getFilesize());
		$this->assertNotEmpty($file->getFilesize(true));
	}

	/**
	 * Test the getMimetype method
	 */
	public function testGetMimetype(){
		$file = new \Core\Filestore\Backends\FileRemote($this->_testfile);

		$this->assertStringStartsWith('text/', $file->getMimetype());
	}

	/**
	 * Test the getExtension method
	 */
	public function testGetExtension(){
		$file = new \Core\Filestore\Backends\FileRemote($this->_testfile);

		$this->assertEquals('md', $file->getExtension());
	}

	/**
	 * Test the getURL method
	 */
	public function testGetURL(){
		$file = \Core\Filestore\Factory::File($this->_testimage);

		$this->assertNotEmpty($file->getURL());
	}

	/**
	 * Test the getPreviewURL method
	 */
	public function testGetPreviewURL(){
		$file = \Core\Filestore\Factory::File($this->_testimage);

		$this->assertNotEmpty($file->getPreviewURL());
	}

	/**
	 * Test the getFilename method
	 */
	public function testGetFilename(){
		$file = new \Core\Filestore\Backends\FileRemote($this->_testfile);

		// Sending in an empty string should return the path relative to Core.
		$this->assertEquals($this->_testfile, $file->getFilename(''));

		// Sending in null should return the path with the fully resolved path.
		$this->assertEquals($this->_testfile, $file->getFilename());
	}

	/**
	 * The filename should be able to be settable afterwards too!
	 */
	public function testSetFilename(){
		$file = new \Core\Filestore\Backends\FileRemote();
		$file->setFilename($this->_testfile);

		$this->assertEquals($this->_testfile, $file->getFilename());
		$this->assertTrue($file->exists());
	}

	/**
	 * Test the getBasename method
	 */
	public function testGetBasename(){
		$file = new \Core\Filestore\Backends\FileRemote($this->_testfile);

		$this->assertEquals('README.md', $file->getBasename());
		$this->assertEquals('README', $file->getBasename(true));
	}

	/**
	 * Test the getLocalFilename method
	 */
	public function testGetLocalFilename(){
		$file = new \Core\Filestore\Backends\FileRemote($this->_testfile);

		// It should be some /tmp file.... :/
		$this->assertStringStartsWith(\Core\Filestore\get_tmp_path(), $file->getLocalFilename());
	}

	/**
	 * Test the getHash method
	 */
	public function testGetHash() {
		$file = new \Core\Filestore\Backends\FileRemote($this->_testfile);

		// Download the file manually and check.
		$tmpfile = \Core\Filestore\Factory::File('tmp/test-fileremotetest-testgethash.dat');
		$tmpfile->putContents(file_get_contents($this->_testfile));

		$this->assertEquals(md5_file($tmpfile->getFilename()), $file->getHash());

		$tmpfile->delete();
	}

	/**
	 * Test the getFilenameHash method
	 */
	public function testGetFilenameHash() {
		$file = new \Core\Filestore\Backends\FileRemote($this->_testfile);

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
		$file1 = new \Core\Filestore\Backends\FileRemote($this->_testfile);
		$file2 = new \Core\Filestore\Backends\FileRemote($this->_testimage);

		$this->assertFalse($file1->isImage());
		$this->assertTrue($file2->isImage());
	}

	public function testIsText(){
		$file1 = new \Core\Filestore\Backends\FileRemote($this->_testfile);
		$file2 = new \Core\Filestore\Backends\FileRemote($this->_testimage);

		$this->assertTrue($file1->isText());
		$this->assertFalse($file2->isText());
	}

	public function testIsPreviewable(){
		$file = new \Core\Filestore\Backends\FileRemote($this->_testimage);
		$this->assertTrue($file->isPreviewable());
	}

	public function testDisplayPreview(){
		$src = new \Core\Filestore\Backends\FileRemote($this->_testimage);
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
		$file1 = new \Core\Filestore\Backends\FileRemote($this->_testfile);
		$file2 = new \Core\Filestore\Backends\FileRemote($this->_testimage);

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
		$file = new \Core\Filestore\Backends\FileRemote($this->_testfile);

		$this->assertTrue($file->inDirectory('/nicholasryan/CorePlus'));
		$this->assertFalse($file->inDirectory(\Core\Filestore\get_tmp_path()));
	}

	public function testIdenticalTo() {
		$file1 = new \Core\Filestore\Backends\FileRemote($this->_testfile);
		$file2 = \Core\Filestore\Factory::File($this->_testfile);
		$file3 = new \Core\Filestore\Backends\FileRemote($this->_testimage);

		$this->assertTrue($file1->identicalTo($file2));
		$this->assertFalse($file1->identicalTo($file3));
	}

	public function testCopyTo() {
		$file = new \Core\Filestore\Backends\FileRemote($this->_testfile);

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
		$file1 = new \Core\Filestore\Backends\FileRemote($this->_testfile);

		$this->assertNotEmpty($file1->getContents());
	}

	public function testPutContents() {
		// Remote files don't support writing :)
		$contents = 'Some Example Content';

		$file1 = new \Core\Filestore\Backends\FileRemote($this->_testfile);

		// These throw exceptions
		$this->setExpectedException('Exception');
		$this->assertFalse($file1->putContents($contents));
	}

	public function testGetContentsObject() {
		$file1 = new \Core\Filestore\Backends\FileRemote($this->_testimage);
		//$file2 = new \Core\Filestore\Backends\FileRemote('core/tests/ivak_TV_Test_Screen.png.tar.gz');
		//$file3 = new \Core\Filestore\Backends\FileRemote('core/tests/ivak_TV_Test_Screen.png.zip');

		$this->assertInstanceOf('\\Core\\Filestore\\Contents\\ContentUnknown', $file1->getContentsObject());
		//$this->assertInstanceOf('\\Core\\Filestore\\Contents\\ContentTGZ', $file2->getContentsObject());
		//$this->assertInstanceOf('\\Core\\Filestore\\Contents\\ContentZIP', $file3->getContentsObject());
	}

	public function testExists() {
		$file1 = new \Core\Filestore\Backends\FileRemote($this->_testfile);
		$file2 = new \Core\Filestore\Backends\FileRemote($this->_test404);

		$this->assertTrue($file1->exists());
		$this->assertFalse($file2->exists());
	}

	public function testIsReadable() {
		$file1 = new \Core\Filestore\Backends\FileRemote($this->_testfile);
		$file2 = new \Core\Filestore\Backends\FileRemote($this->_test404);

		$this->assertTrue($file1->isReadable());
		$this->assertFalse($file2->isReadable());
	}

	public function testIsWritable() {
		// Remote files are easy :)
		$file1 = new \Core\Filestore\Backends\FileRemote($this->_testfile);
		$this->assertFalse($file1->isWritable());
	}

	public function testGetMTime() {
		$file1 = new \Core\Filestore\Backends\FileRemote($this->_testfile);

		$this->assertFalse($file1->getMTime());
	}
}
