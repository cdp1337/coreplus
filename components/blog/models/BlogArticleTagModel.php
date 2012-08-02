<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 7/29/12
 * Time: 9:46 PM
 * To change this template use File | Settings | File Templates.
 */
class BlogArticleTagModel extends Model {
	public static $Schema = array(
		'articleid' => array(
			'type'     => Model::ATT_TYPE_INT,
			'required' => true
		),
		'tag'       => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => '64',
			'required'  => true,
		)
	);

	public static $Indexes = array(
		'primary' => array('articleid', 'tag'),
	);

	public function __construct($key = null) {
		$this->_linked = array(

			'BlogArticle' => array(
				'link' => Model::LINK_BELONGSTOMANY,
				'on'   => array('id' => 'articleid'),
			)
		);

		parent::__construct($key);
	}
}
