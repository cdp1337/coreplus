<?php
/**
 * Model for PageModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-08 20:43:40
 */
class PageModel extends Model {
	public static $Schema = array(
		'baseurl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'required' => true,
		),
		'rewriteurl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
		),
		'parenturl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'comment' => '[Cached] Title of the page',
		),
		'metas' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'comment' => '
					[Cached] Serialized array of metainformation
				',
		),
		'theme_template' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
		),
		'page_template' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
		),
		'access' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 512,
			'comment' => '
					[Cached] Access string of the page
				',
		),
		'fuzzy' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'comment' => '
					[Cached] If this url is fuzzy or an exact match
				',
		),
		'widget' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'comment' => '
					If this page can be viewed as a widget
				',
		),
		'admin' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'comment' => '
					If this page is an administration page
				',
		),
		'created' => array(
			'type' => Model::ATT_TYPE_INT,
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_INT,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('baseurl'),
		'unique:rewrite_url' => array('rewriteurl'),
	);

	// @todo Put your code here.

} // END class PageModel extends Model
