<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 1/17/13
 * Time: 10:20 PM
 * This is the upgrade file from 1.2.1 to 1.3.0.
 *
 * The blog system in 1.3.0 and later utilizes a dedicated Page entry for each blog entry.  This helps to have more
 * fine grain control over each entry's settings and data, as well as making use of the insertable system.
 */

// Create a page for each BlogArticle!
// Since this runs prior to the blog system being enabled, I need to manually include the model files.
require_once(ROOT_PDIR . 'components/blog/models/BlogArticleModel.php');
require_once(ROOT_PDIR . 'components/blog/models/BlogModel.php');

$articles = BlogArticleModel::Find();
foreach($articles as $article){
	/** @var $article BlogArticleModel */

	/** @var $blog BlogModel */
	$blog = $article->getLink('Blog');
	/** @var $page PageModel */
	$page = $article->getLink('Page');

	$page->setFromArray(
		array(
			'title' => $article->get('title'),
			'rewriteurl' => $blog->get('rewriteurl') . '/' . $article->get('id') . '-' . \Core\str_to_url($article->get('title')),
			'parenturl' => $blog->get('baseurl'),
			'fuzzy' => 0,
			'admin' => 0,
		)
	);

	if($article->get('authorid')){
		$user = User::Construct($article->get('authorid'));
		$page->setMeta('author', $user->getDisplayName());
		$page->setMeta('authorid', $user->get('id'));
	}

	$page->setMeta('description', $article->get('description'));
	$page->setMeta('keywords', $article->get('keywords'));

	$page->save();
}
// return