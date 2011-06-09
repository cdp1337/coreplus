<?php
/**
 * Model for PageModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class PageModel extends Model {
	public static $Schema = array(
		'baseurl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'required' => true,
			'null' => false,
		),
		'rewriteurl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'null' => false,
		),
		'parenturl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'null' => false,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'default' => null,
			'comment' => '[Cached] Title of the page',
			'null' => true,
		),
		'metas' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'comment' => '[Cached] Serialized array of metainformation',
			'null' => false,
		),
		'theme_template' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'default' => null,
			'null' => true,
		),
		'page_template' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'default' => null,
			'null' => true,
		),
		'access' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 512,
			'comment' => '[Cached] Access string of the page',
			'null' => false,
		),
		'fuzzy' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'comment' => '[Cached] If this url is fuzzy or an exact match',
			'null' => false,
		),
		'widget' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'comment' => 'If this page can be viewed as a widget',
			'null' => false,
		),
		'admin' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'comment' => 'If this page is an administration page',
			'null' => false,
		),
		'created' => array(
			'type' => Model::ATT_TYPE_CREATED,
			'null' => false,
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_UPDATED,
			'null' => false,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('baseurl'),
		'unique:rewrite_url' => array('rewriteurl'),
	);

	// @todo Put your code here.

} // END class PageModel extends Model
