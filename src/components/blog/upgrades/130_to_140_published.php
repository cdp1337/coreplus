<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 1/17/13
 * Time: 10:20 PM
 * This is the upgrade file from 1.3.0 to 1.4.0
 *
 * Blogs now have a published date.  By default, all published articles should have that timestamp set to the last modified date.
 */


// Since this runs prior to the blog system being enabled, I need to manually include the model files.
require_once(ROOT_PDIR . 'components/blog/models/BlogArticleModel.php');
require_once(ROOT_PDIR . 'components/blog/models/BlogModel.php');

$articles = BlogArticleModel::Find(
	array(
		'status' => 'published',
		'published' => '',
	)
);
foreach($articles as $article){
	/** @var $article BlogArticleModel */
	$article->set('published', $article->get('updated'));
	$article->save();
}
// return