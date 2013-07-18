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
		// Update the current user so it has admin access.
		\Core\user()->set('admin', true);

		// Setup some variables that will be used throughout this method.
		$title = 'New Test Blog Article';
		$randomsnippet = 'Random-Snippet-' . Core::RandomHex(10);
		$lorem = new BaconIpsumGenerator();

		$body = $lorem->getParagraph(1);
		// Tack on the random snipped I'll be looking for.
		$body .= $lorem->getParagraphsAsMarkup(8, $randomsnippet);

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
		$form->getElement('model[image]')->set('value', 'public/blog/blog-test-image.png');
		$form->getElement('model[body]')->set('value', $body);

		// Copy in the image
		$src = \Core\Filestore\Factory::File(ROOT_PDIR . 'components/blog/tests/blog-test-image.png');
		/** @var $dest \Core\Filestore\File */
		$dest = \Core\Filestore\Factory::File('public/blog/blog-test-image.png');

		$src->copyTo($dest, true);

		// make sure that it exists

		$this->assertTrue($dest->exists(), 'Checking that files can be copied into the public filestore');

		// And submit this form to the handler.
		// On a successful submission, it should be simply the URL of the blog.
		$formsubmission = call_user_func_array($form->get('callsmethod'), array($form));
		if($formsubmission === false){
			throw new Exception(implode("\n", $form->getErrors()));
		}

		// Go to the parent listing page and find this entry.
		$request = new PageRequest($blog->get('rewriteurl'));
		$return = $request->execute();
		$this->assertEquals(200, $return->error);
		$html = $return->fetch();

		$this->assertContains($title, $html);
		$this->assertContains('itemtype="http://schema.org/BlogPosting"', $html);

		preg_match_all('#<div[^>]*itemtype="http://schema.org/BlogPosting"[^>]*>.*<a[^>]*href="(.*)"[^>]*>(.*)</a>#msU', $html, $matches);
		// Title should now have three keys, with at least one value each.

		$this->assertNotEmpty($matches[1]);
		$this->assertNotEmpty($matches[2]);

		// This node contains the URL.
		$foundurl = $matches[1][0];
		$foundtitle = trim($matches[2][0]);

		// Make sure the url contains the site url.
		$this->assertStringStartsWith(ROOT_URL, $foundurl);
		// And trim it off.  This is because PageRequest expects that the url is already trimmed.
		$foundurl = '/' . substr($foundurl, strlen(ROOT_URL));

		$this->assertEquals($title, $foundtitle);

		//$this->assertStringStartsWith('/blog/article/view/', $formsubmission, 'Checking that blog article creation was successful');

		// Go to the page and make sure that it loads up!
		$request = new PageRequest($foundurl);
		$return = $request->execute();
		$this->assertEquals(200, $return->error, 'Checking that public blog article exists');

		$html = $return->fetch();
		$this->assertContains($title, $html, 'Checking that the public blog article page contains the correct title');
		$this->assertContains($randomsnippet, $html, 'Checking that the public blog article page contains the correct body');
		$this->assertContains('blog-test-image', $html, 'Checking that the public blog article page contains the correct image');
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

