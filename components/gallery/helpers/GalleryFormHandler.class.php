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

		$page = $model->getLink('Page');
		$page->setFromForm($form, 'page');
		$page->set('fuzzy', 1);

		// Update the model cache data
		$model->set('title', $page->get('title'));

		//var_dump($model); die();
		$model->save();

		$insertables = $form->getElementByName('insertables');
		$insertables->set('baseurl', '/gallery/view/' . $model->get('id'));
		$insertables->save();

		// w00t
		return $page->getResolvedURL();
	}

	public static function SaveWidgetHandler(Form $form){
		$id = $form->getElement('id')->get('value');
		// ID can be null, that just means it's a new widget!

		if(!$id){
			// Generate an id!
			$id = Core::GenerateUUID();
		}


		$model = new WidgetModel('/gallery/view/' . $id);
		$model->set('title', $form->getElement('title')->get('value'));
		$model->setSetting('album', $form->getElement('album')->get('value'));
		$model->setSetting('count', $form->getElement('count')->get('value'));
		$model->setSetting('order', $form->getElement('order')->get('value'));
		$model->setSetting('dimensions', $form->getElement('dimensions')->get('value'));
		$model->setSetting('uselightbox', $form->getElement('uselightbox')->get('value'));

		$model->save();

		return '/galleryadmin/widgets';
	}
}