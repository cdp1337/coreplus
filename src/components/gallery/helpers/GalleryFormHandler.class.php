<?php

abstract class GalleryFormHandler{

	/**
	 * Save a new or existing album
	 *
	 * @static
	 * @param Form $form
	 * @return mixed
	 */
	public static function SaveAlbum(Form $form){

		$model = $form->getModel('model');
		/** @var $page PageModel */
		$page  = $form->getModel('page');
		$page->set('fuzzy', 1);

		// Update the model cache data
		$model->set('title', $page->get('title'));

		//var_dump($model); die();
		$model->save();

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