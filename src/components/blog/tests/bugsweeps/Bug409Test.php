<?php
/**
 * Test file to ensure that the blog RSS feed validates by w3c.
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130712.1210
 * @package Blog
 */


/**
 * Class Bug409Test
 *
 * Ensure that RSS Feeds validate with W3C.
 *
 * @package Blog
 */
class Bug409Test extends PHPUnit_Framework_TestCase {

	/**
	 * @var BlogModel
	 */
	protected $blog;

	/**
	 * @var BlogArticleModel
	 */
	protected $article;

	protected function setUp(){
		// Setup some variables that will be used throughout this method.
		$title = 'Blog Bug 409 [' . Core::RandomHex(6) . ']';

		$this->blog = new BlogModel();
		$this->blog->set('title', $title);

		// Make sure the page model has been loaded into this model too.
		$page = $this->blog->getLink('Page');
		$page->set('title', $title);

		$this->blog->save();

		$bacon = new BaconIpsumGenerator();

		// Create an article with an invalid title.
		$this->article = new BlogArticleModel();
		$this->article->setFromArray(
			[
				'blogid' => $this->blog->get('id'),
				'title'  => 'Sömé "ḮnvÁlid" & \'Bad\' T¹tle!¡',
				'body'   => $bacon->getParagraphsAsMarkup(),
				'status' => 'published',
			]
		);
		$this->article->save();
	}

	/**
	 * Test that I can load the RSS page and that it returns valid XML.
	 * The XMLLoader will take care of the validation, since it should be a valid document anyway.
	 */
	public function testRSSPage(){
		// Get the RSS feed and download it to a local file.
		$rewriteurl = $this->blog->get('rewriteurl');
		$this->assertNotEmpty($rewriteurl);

		// Go to the page and make sure that it loads up!
		$request = new PageRequest($rewriteurl . '.rss');
		$return = $request->execute();
		$this->assertEquals(200, $return->error);

		$markup = $return->fetch();
		$this->assertNotEmpty($markup);

		// DEVELOPMENT DEBUG
		//echo $markup; // DEBUG //

		$xml = new XMLLoader();
		$xml->setRootName('rss');

		// If it's invalid markup, this load will throw an error, causing phpunit to return an error :)
		// If the bug is fixed, this will not throw any errors.
		$xml->loadFromString($markup);

		$parsedmarkup = $xml->asMinifiedXML();
		$this->assertNotEmpty($parsedmarkup);
	}

	/**
	 * Test that I can load the ATOM page and that it returns valid XML.
	 * The XMLLoader will take care of the validation, since it should be a valid document anyway.
	 */
	public function testATOMPage(){
		// Get the RSS feed and download it to a local file.
		$rewriteurl = $this->blog->get('rewriteurl');
		$this->assertNotEmpty($rewriteurl);

		// Go to the page and make sure that it loads up!
		$request = new PageRequest($rewriteurl . '.atom');
		$return = $request->execute();
		$this->assertEquals(200, $return->error);

		$markup = $return->fetch();
		$this->assertNotEmpty($markup);

		// DEVELOPMENT DEBUG
		//echo $markup; // DEBUG //

		$xml = new XMLLoader();
		$xml->setRootName('feed');

		// If it's invalid markup, this load will throw an error, causing phpunit to return an error :)
		// If the bug is fixed, this will not throw any errors.
		$xml->loadFromString($markup);

		$parsedmarkup = $xml->asMinifiedXML();
		$this->assertNotEmpty($parsedmarkup);
	}

	protected function tearDown(){
		$this->blog->delete();
		$this->article->delete();
	}
}
