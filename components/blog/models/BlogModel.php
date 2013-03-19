<?php

class BlogModel extends Model {
	public static $Schema = array(
		'id'                         => array(
			'type' => Model::ATT_TYPE_ID,
		),
		'site'                       => array(
			'type'     => Model::ATT_TYPE_SITE,
			'formtype' => 'system',
		),
		'manage_articles_permission' => array(
			'type'    => Model::ATT_TYPE_STRING,
			'default' => '!*',
			'form'    => array('type' => 'access', 'title' => 'Article Management Permission', 'description' => 'Which groups can add, edit, and remove blog articles in this blog.'),
		),
	);

	public static $Indexes = array(
		'primary' => array('id'),
	);

	public function __construct($key = null) {
		$this->_linked = array(
			'Page'        => array(
				'link' => Model::LINK_HASONE,
				'on'   => 'baseurl',
			),
			'BlogArticle' => array(
				'link' => Model::LINK_HASMANY,
				'on'   => array('blogid' => 'id'),
			),
		);

		parent::__construct($key);
	}

	public function get($k) {
		$k = strtolower($k);
		switch ($k) {
			case 'baseurl':
				return '/blog/view/' . $this->_data['id'];
			case 'access':
			case 'created':
			case 'title':
			case 'rewriteurl':
			case 'updated':
				return $this->getLink('Page')->get($k);
			default:
				return parent::get($k);
		}
	}
}