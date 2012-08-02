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
			'maxlength' => '64'
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
			)
		),
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
		'status'      => array(
			'type'    => Model::ATT_TYPE_ENUM,
			'options' => array('published', 'draft'),
			'default' => 'published'
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
			'BlogArticleTag' => array(
				'link' => Model::LINK_HASMANY,
				'on'   => array('articleid' => 'id'),
			)
		);

		parent::__construct($key);
	}

	public function get($k) {
		$k = strtolower($k);
		switch ($k) {
			case 'baseurl':
				return '/blog/view/' . $this->_data['blogid'] . '/' . $this->_data['id'];
			case 'rewriteurl':
				return $this->getLink('Blog')->get('rewriteurl') . '/' . $this->_data['id'] . '-' . \Core\str_to_url($this->_data['title']);
			default:
				return parent::get($k);
		}
	}
}
