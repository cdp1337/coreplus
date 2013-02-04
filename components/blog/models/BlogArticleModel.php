<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 7/29/12
 * Time: 9:32 PM
 * To change this template use File | Settings | File Templates.
 */
class BlogArticleModel extends Model {
	public static $Schema = array(
		'id'          => array(
			'type' => Model::ATT_TYPE_ID,
		),
		'blogid'      => array(
			'type' => Model::ATT_TYPE_INT,
			'form' => array('type' => 'system'),
		),
		'authorid'    => array(
			'type' => Model::ATT_TYPE_INT,
			'form' => array('type' => 'system'),
		),
		'title'       => array(
			'type'      => Model::ATT_TYPE_STRING,
			'required'  => true,
			'maxlength' => '64',
			'comment' => 'This is cached from the Page title.',
		),
		'image'       => array(
			'type'     => Model::ATT_TYPE_STRING,
			'required' => false,
			'form'     => array(
				'type'        => 'file',
				'accept'      => 'image/*',
				'basedir'     => 'public/blog',
				'description' => 'An optional image to showcase for this article.'
			)
		),
		'body'        => array(
			'type'     => Model::ATT_TYPE_TEXT,
			'required' => true,
			'form'     => array(
				'type' => 'wysiwyg',
				'description' => 'The main body of this blog article.'
			)
		),
		/*
		'description' => array(
			'type'     => Model::ATT_TYPE_TEXT,
			'required' => false,
			'form'     => array(
				'description' => 'Optional description or preview snippet of the article.'
			)
		),
		'keywords'    => array(
			'type'     => Model::ATT_TYPE_TEXT,
			'required' => false,
		),
		*/
		'status'      => array(
			'type'    => Model::ATT_TYPE_ENUM,
			'options' => array('published', 'draft'),
			'default' => 'published',
			'form' => array(
				'description' => 'Set this to "draft" to make it visible to editors and admins only.  Useful for working on an article across multiple sessions while keeping not-ready content hidden from public users.'
			)
		),
		'fb_account_id' => array(
			'type' => Model::ATT_TYPE_STRING,
			'formtype' => 'hidden',
		),
		'fb_post_id' => array(
			'type' => Model::ATT_TYPE_STRING,
			'formtype' => 'hidden',
		),
		'published' => array(
			'type' => Model::ATT_TYPE_INT,
			'formtype' => 'disabled',
			'comment' => 'The published date',
		),
		'created'     => array(
			'type' => Model::ATT_TYPE_CREATED,
			'null' => false,
		),
		'updated'     => array(
			'type' => Model::ATT_TYPE_UPDATED,
			'null' => false,
		),
	);

	public static $Indexes = array(
		'primary' => array('id'),
	);

	public function __construct($key = null) {
		$this->_linked = array(
			'Blog'           => array(
				'link' => Model::LINK_BELONGSTOONE,
				'on'   => array('id' => 'blogid'),
			),
			'User'           => array(
				'link' => Model::LINK_BELONGSTOONE,
				'on'   => array('id' => 'authorid'),
			),
			'Page' => array(
				'link' => Model::LINK_HASONE,
				'on' => 'baseurl',
			)
		);

		parent::__construct($key);
	}

	public function get($k) {
		$k = strtolower($k);
		switch ($k) {
			case 'baseurl':
				//return '/blog/view/' . $this->_data['blogid'] . '/' . $this->_data['id'];
				return '/blog/article/view/' . $this->_data['id'];
			case 'rewriteurl':
				//return $this->getLink('Blog')->get('rewriteurl') . '/' . $this->_data['id'] . '-' . \Core\str_to_url($this->_data['title']);
				return $this->getLink('Page')->get('rewriteurl');
			default:
				return parent::get($k);
		}
	}

	public function set($k, $v){
		if($k == 'status'){
			// Update the published date if it's status has changed.
			if($v == $this->get($k)) return false;

			if($v == 'published'){
				$this->set('published', Time::GetCurrentGMT());
			}
			else{
				$this->set('published', '');
			}

			// And resume!
			return parent::set($k, $v);
		}
		else{
			return parent::set($k, $v);
		}
	}

	/**
	 * Get a teaser or snippet of this article.
	 * This will return at most 500 characters of the body or the description.
	 */
	public function getTeaser(){
		$text = $this->get('description') ? $this->get('description') : $this->get('body');

		// Remove HTML
		$text = strip_tags($text);

		// And whitespace
		$text = trim($text);

		// And if it's more than so many characters...
		$text = substr($text, 0, 500);

		return $text;
	}

	/**
	 * Get the image object or null
	 *
	 * @return File_local_backend|null
	 */
	public function getImage(){
		if($this->get('image')){
			$f = new File_local_backend('public/blog/' . $this->get('image'));
			return $f;
		}
		else{
			return null;
		}
	}
}
