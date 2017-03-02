<?php
/**
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 */
class BlogArticleModel extends Model {
	public static $Schema = array(
		'id'          => array(
			'type' => Model::ATT_TYPE_UUID,
		),
		'blogid'      => array(
			'type' => Model::ATT_TYPE_UUID_FK,
			'form' => array('type' => 'system'),
		),
		'authorid'    => array(
			'type' => Model::ATT_TYPE_UUID_FK,
			'form' => array('type' => 'system'),
		),
		'guid' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => '128',
			'comment' => 'External feeds have a GUID attached to this article.',
			'formtype' => 'disabled',
		),
		'link' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => '256',
			'comment' => 'External feeds have a link back to the original article.',
			'formtype' => 'disabled',
		),
		'title'       => array(
			'type'      => Model::ATT_TYPE_STRING,
			'required'  => true,
			'maxlength' => '256',
			'comment' => 'This is cached from the Page title.',
		),
		'image'       => array(
			'type'     => Model::ATT_TYPE_STRING,
			'required' => false,
			'form'     => array(
				'type'        => 'file',
				'accept'      => 'image/*',
				'basedir'     => 'public/blog',
				'description' => 'An optional image to showcase for this article.',
				'group'       => 'Basic',
				'browsable'   => true,
			)
		),
		'body'        => array(
			'type'     => Model::ATT_TYPE_TEXT,
			'required' => true,
			'form'     => array(
				'type'        => 'wysiwyg',
				'description' => 'The main body of this blog article.',
				'group'       => 'Basic',
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
		/*'status'      => array(
			'type'    => Model::ATT_TYPE_ENUM,
			'options' => array('published', 'draft'),
			'default' => 'draft',
			'form' => array(
				'description' => 'Set this to "draft" to make it visible to editors and admins
					only.  Useful for working on an article across multiple sessions while 
					keeping not-ready content hidden from public users.',
				'group' => 'Publish Settings',
				'grouptype' => 'tabs',
			)
		),*/
		'fb_account_id' => array(
			'type' => Model::ATT_TYPE_STRING,
			'formtype' => 'hidden',
		),
		'fb_post_id' => array(
			'type' => Model::ATT_TYPE_STRING,
			'formtype' => 'hidden',
		),
		/*'published' => array(
			'type' => Model::ATT_TYPE_INT,
			'form' => array(
				'title' => 'Published Date',
				'type' => 'datetime',
				'description' => 'Leave this blank for default published time, or set it to a desired date/time to set the published time.  Note, you CAN set this to a future date to set the article as published at that time, however doing so will disable the facebook publishing ability.',
				'group' => 'Publish Settings',
				'grouptype' => 'tabs',
			),
			'comment' => 'The published date',
		),*/
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

	public static $HasSearch = true;

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

	public function get($k, $format = null) {
		$k = strtolower($k);
		switch ($k) {
			case 'baseurl':
				//return '/blog/view/' . $this->_data['blogid'] . '/' . $this->_data['id'];
				return '/blog/article/view/' . $this->_data['id'];
			case 'rewriteurl':
				//return $this->getLink('Blog')->get('rewriteurl') . '/' . $this->_data['id'] .
				//  '-' . \Core\str_to_url($this->_data['title']);
				return $this->getLink('Page')->get('rewriteurl', $format);
			default:
				return parent::get($k, $format);
		}
	}

	public function set($k, $v){
		switch($k){
			case 'status':
				// Update the published date if it's status has changed.
				if($v == $this->get($k)) return false;

				if($v == 'published' && !$this->get('published')) {
					parent::set('published', Time::GetCurrentGMT());
				}
				elseif($v == 'draft' && $this->get('published')) {
					parent::set('published', '');
				}

				// And resume!
				return parent::set($k, $v);
			case 'published':
				// make sure this is a valid timestamp!
				if($v != '' && !is_numeric($v)){
					$time = strtotime($v);
					return parent::set($k, $time);
				}
				else{
					return parent::set($k, $v);
				}
			default:
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
	 * @return Core\Filestore\File|null
	 */
	public function getImage(){
		if($this->get('image')) {
			$f = \Core\Filestore\Factory::File($this->get('image'));
			return $f;
		}
		else{
			return null;
		}
	}

	/**
	 * Get the author of this article
	 *
	 * @return null|User_Backend
	 */
	public function getAuthor() {
		$author = UserModel::Construct($this->get('authorid'));
		return $author;
	}

	/**
	 * Get the resolved link for this blog article.  Will be remote if it's a remote article.
	 *
	 * @return string
	 */
	public function getResolvedLink(){
		if($this->get('link')){
			return $this->get('link');
		}
		else{
			return \Core\resolve_link($this->get('baseurl'));
		}
	}

	/**
	 * Get if this article is published AND not set to a future published date.
	 *
	 * @return bool
	 */
	public function isPublished(){
		return ($this->get('status') == 'published' && $this->get('published') <= CoreDateTime::Now('U', Time::TIMEZONE_GMT));
	}
}
