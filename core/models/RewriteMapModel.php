<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 12/3/12
 * Time: 7:44 PM
 *
 * Rewrite Map model, essentially an apache rewrite system for Core.
 *
 * This mimics the page system, with the exception that rewrite urls are the PK here,
 * and there can be multiple baseurls.  Whenever a rewriteurl changes, the previous URL is
 * saved here for lookups that are still using the previous name.
 */
class RewriteMapModel extends Model {
	public static $Schema = array(
		'rewriteurl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'null' => false,
			'validation' => array('this', 'validateRewriteURL'),
			'form' => array(
				'title' => 'Page URL',
				'type' => 'pagerewriteurl',
				'description' => 'Starts with a "/", omit the root web dir.',
			),
		),
		'baseurl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'required' => true,
			'null' => false,
			'form' => array('type' => 'system'),
		),
		'fuzzy' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'comment' => 'If this url is fuzzy or an exact match',
			'null' => false,
			'default' => '0',
			'formtype' => 'system'
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
		'primary' => array('rewriteurl'),
	);
}
