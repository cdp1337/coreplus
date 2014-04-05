<?php
/**
 * Upgrade file to add control links for blogs and to migrated the blog articles to the content application.
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20140228.1049
 * @package Blog
 */

$fac = new ModelFactory('PageModel');
$fac->where('baseurl LIKE /blog/view/%');
foreach($fac->get() as $model){
	/** @var PageModel $model */
	$model->set('component', 'blog');
	$model->set('editurl', str_replace('/blog/view/', '/blog/update/', $model->get('baseurl')));
	$model->set('deleteurl', str_replace('/blog/view/', '/blog/delete/', $model->get('baseurl')));
	$model->save();
}


//
// This is how to update all pages and their necessary values, (useful for a template for other components)
//
//$fac = new ModelFactory('PageModel');
//$fac->where('baseurl LIKE /blog/article/view/%');
//foreach($fac->get() as $model){
//	/** @var PageModel $model */
//	$model->set('component', 'blog');
//	$model->set('editurl', str_replace('/blog/article/view/', '/blog/article/update/', $model->get('baseurl')));
//	$model->set('deleteurl', str_replace('/blog/article/view/', '/blog/article/delete/', $model->get('baseurl')));
//	$model->save();
//}

$fac = new ModelFactory('BlogArticleModel');
foreach($fac->get() as $model){
	/** @var BlogArticleModel $model */

	/** @var PageModel $page */
	$page = $model->getLink('Page');
	$page->setMeta('description', $model->getTeaser());
	$page->setMeta('image', $model->get('image'));
	$page->set('published_status', $model->get('status'));
	$page->set('published', $model->get('published'));

	// Clone this to a content page.
	$content = new ContentModel();

	$content->set('nickname', $model->get('title'));
	$content->set('created', $model->get('created'));
	$content->set('updated', $model->get('updated'));

	$content->save();

	$page->set('component', 'content');
	$page->set('baseurl', '/content/view/' . $content->get('id'));
	$page->set('editurl', '/content/edit/' . $content->get('id'));
	$page->set('deleteurl', '/content/delete/' . $content->get('id'));
	$page->set('page_template', 'blog-article.tpl');
	$page->save();

	$insertable = new InsertableModel();
	$insertable->set('site', $page->get('site'));
	$insertable->set('baseurl', '/content/view/' . $content->get('id'));
	$insertable->set('name', 'body');
	$insertable->set('value', $model->get('body'));
	$insertable->save();

	$model->resetLink('Page');
	$model->delete();
}