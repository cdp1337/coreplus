<?php
class BlogTest extends PHPUnit_Framework_TestCase {

	public static $TestBlogID = null;

	/**
	 * Create a new blog
	 */
	public function testCreateBlog(){
		// Setup some variables that will be used throughout this method.
		$random = Core::RandomHex(6);
		$title = 'New Test Blog ' . Core::RandomHex(6);

		// Update the current user so it has admin access.
		\Core\user()->set('admin', true);

		$request = new PageRequest('/blog/create');

		// Run the method, it should give me a 200 since the user is now an admin.
		$return = $request->execute();
		$this->assertEquals(200, $return->error, 'Checking that admin users can create blogs');

		// The returned data should have a "form" available.  This is the actual creation form.
		/** @var $form Form */
		$form = $return->getVariable('form');
		$this->assertInstanceOf('Form', $form, 'Checking that the form is set from the blog create controller');

		// Set some variables on the form
		$form->getElement('page[title]')->set('value', $title);
		$form->getElement('page[rewriteurl]')->set('value', '/blogtest-' . $random);

		// And submit this form to the handler.
		// On a successful submission, it should be simply the URL of the blog.
		$formsubmission = call_user_func_array($form->get('callsmethod'), array($form));
		$this->assertStringStartsWith('/blog/view/', $formsubmission, 'Checking that form creation was successful');

		self::$TestBlogID = substr($formsubmission, 11); // This will be just the number at the end of the baseurl, ie: the id.

		// Make sure that this blog exists!
		$blog = new BlogModel(self::$TestBlogID);
		$this->assertTrue($blog->exists(), 'Checking that blog model creation was successful');

		// And make sure that the page was created.
		$page = new PageModel('/blog/view/' . self::$TestBlogID);
		$this->assertTrue($page->exists(), 'Checking that blog page creation was successful');

		// Go to the page and make sure that it loads up!
		$request = new PageRequest('/blog/view/' . self::$TestBlogID);
		$return = $request->execute();
		$this->assertEquals(200, $return->error, 'Checking that public blog page exists');

		$html = $return->fetch();
		$this->assertContains($title, $html, 'Checking that the public blog page contains the correct title');
	}

	/**
	 * Test the creation of a blog article based off the newly created blog
	 *
	 * @depends testCreateBlog
	 */
	public function testCreateBlogArticle(){
		// Setup some variables that will be used throughout this method.
		$title = 'New Test Blog Article';
		$randomsnippet = 'Random-Snippet-' . Core::RandomHex(10);
		$body = <<< EOD
<p>
	Lorem ipsum dolor sit amet, $randomsnippet consectetur adipiscing elit. Pellentesque sodales eros quis purus auctor sit amet
	placerat tortor imperdiet. Quisque ullamcorper sodales quam eu dictum. Etiam rutrum, mi vitae auctor luctus,
	metus sem consequat urna, eu rutrum magna purus non arcu. Integer sem massa, euismod id tincidunt sit amet,
	porttitor sed mauris. Etiam rhoncus magna quis nibh fringilla id volutpat justo fermentum.
	Praesent pretium porta lectus ut consequat. Sed rutrum massa ut mauris vestibulum sodales.
	Proin vehicula volutpat diam vitae gravida. Curabitur ut ipsum turpis. Cras lorem leo, ullamcorper quis venenatis a,
	mollis a tellus. Proin facilisis scelerisque lectus ut porta. Integer cursus purus id magna pretium pellentesque
	suscipit lectus sagittis. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.
	Duis nec libero diam, hendrerit dignissim mi. Morbi vitae molestie dolor. Vivamus placerat, nunc id lobortis lobortis,
	velit orci cursus lectus, eget elementum orci justo sit amet erat.
</p>
<p>
	Curabitur a quam eget dolor luctus pharetra vel et felis. Sed vitae venenatis nisi. Nam imperdiet hendrerit mi,
	et dictum velit varius at. In in lacus dolor. Donec rutrum interdum felis, vitae malesuada nunc condimentum vel.
	Mauris ut dui eu risus viverra ullamcorper nec vel magna. Morbi varius mi eget turpis lobortis pulvinar.
	Fusce consectetur interdum ante, vitae aliquet purus vehicula et. Proin pulvinar porta elementum.
	Curabitur auctor vulputate ipsum. Phasellus et urna eget lacus sagittis bibendum quis ac mauris.
</p>
EOD;
		$blog = new BlogModel(self::$TestBlogID);

		$request = new PageRequest('/blog/article/create/' . self::$TestBlogID);

		$return = $request->execute();
		$this->assertEquals(200, $return->error, 'Checking that article creation returns a valid page');

		// The returned data should have a "form" available.  This is the actual creation form.
		/** @var $form Form */
		$form = $return->getVariable('form');
		$this->assertInstanceOf('Form', $form, 'Checking that the form is set from the blog article create controller');

		// Set some variables on the form
		$form->getElement('page[title]')->set('value', $title);
		$form->getElement('page[rewriteurl]')->set('value', $blog->get('rewriteurl') . '/' . \Core\str_to_url($title));
		$form->getElement('model[image]')->set('value', 'blog-test-image.png');
		$form->getElement('model[body]')->set('value', $body);

		// Copy in the image
		$src = new File_local_backend(ROOT_PDIR . 'components/blog/tests/blog-test-image.png');
		/** @var $dest File_Backend */
		$dest = \Core\file('public/blog/blog-test-image.png');

		$src->copyTo($dest, true);

		// make sure that it exists

		$this->assertTrue($dest->exists(), 'Checking that files can be copied into the public filestore');

		// And submit this form to the handler.
		// On a successful submission, it should be simply the URL of the blog.
		$formsubmission = call_user_func_array($form->get('callsmethod'), array($form));
		if($formsubmission === false){
			throw new Exception(implode("\n", $form->getErrors()));
		}
		$this->assertStringStartsWith('/blog/article/view/', $formsubmission, 'Checking that blog article creation was successful');

		// Go to the page and make sure that it loads up!
		$request = new PageRequest($formsubmission);
		$return = $request->execute();
		$this->assertEquals(200, $return->error, 'Checking that public blog article exists');

		$html = $return->fetch();
		$this->assertContains($title, $html, 'Checking that the public blog article page contains the correct title');
		$this->assertContains($randomsnippet, $html, 'Checking that the public blog article page contains the correct body');
		$this->assertContains('blog-test-image.png', $html, 'Checking that the public blog article page contains the correct image');
	}

	/**
	 * Remove the objects created in the database.
	 */
	public static function tearDownAfterClass(){
		if(self::$TestBlogID){
			// Remove it!
			$blog = new BlogModel(self::$TestBlogID);
			$blog->delete();
		}
	}
}

