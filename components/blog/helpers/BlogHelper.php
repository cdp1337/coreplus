<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 7/29/12
 * Time: 10:13 PM
 * To change this template use File | Settings | File Templates.
 */
abstract class BlogHelper {
	/**
	 * Helper function to save blog pages, both new and existing.
	 *
	 * @static
	 *
	 * @param Form $form
	 */
	public static function BlogFormHandler(Form $form) {
		$model = $form->getModel();
		$page  = $model->getLink('Page');

		foreach ($form->getElements() as $el) {
			$n = $el->get('name');

			if (strpos($n, 'page[') === 0) {
				$page->set(substr($n, 5, -1), $el->get('value'));
			}
		}
		$page->set('fuzzy', '1'); // Needs to be fuzzy since it supports children
		$model->save();
		return $model->get('baseurl');
	}

	/**
	 * Helper function to save a blog article, both new and existing.
	 *
	 * @static
	 *
	 * @param Form $form
	 */
	public static function BlogArticleFormHandler(Form $form) {
		$article = $form->getModel();
		$article->save();

		return $article->get('baseurl');
	}
}
