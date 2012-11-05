<?php

abstract class GalleryFormHandler{

	/**
	 * Save just the gallery listing page itself.  This doesn't actually have any administrable data
	 * associated to it, other than the page.
	 *
	 * @static
	 * @param Form $form
	 */
	public static function SaveListing(Form $form){
		$model = $form->getModel();
		$model->save();

		$insertables = $form->getElementByName('insertables');
		$insertables->set('baseurl', '/gallery');
		$insertables->save();

		// w00t
		return $model->getResolvedURL();
	}

	/**
	 * Save a new or existing album
	 *
	 * @static
	 * @param Form $form
	 * @return mixed
	 */
	public static function SaveAlbum(Form $form){

		$model = $form->getModel();
		// Ensure that everything is marked as updated...
		$model->set('updated', Time::GetCurrent());
		//var_dump($model); die();
		$model->save();

		/** @var $page PageModel */
		$page = $form->getElementByName('page')->getModel();
		$page->setFromForm($form, 'page');
		$page->set('fuzzy', 1);
		$page->set('baseurl', '/gallery/view/' . $model->get('id'));
		$page->set('updated', Time::GetCurrent());
		$page->save();

		$insertables = $form->getElementByName('insertables');
		$insertables->set('baseurl', '/gallery/view/' . $model->get('id'));
		$insertables->save();

		// w00t
		return $page->getResolvedURL();
	}
}