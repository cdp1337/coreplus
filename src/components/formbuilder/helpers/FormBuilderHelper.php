<?php
/**
 * Class file for FormBuilderHelper
 *
 * @package FormBuilder
 * @author Nicholas Hinsch <nicholas@eval.bz>
 */

abstract class FormBuilderHelper {
	/**
	 * Helper function to save custom form pages, both new and existing.
	 *
	 * @static
	 *
	 * @param Form $form
	 * @return string Redirect URL
	 */
	public static function FormBuilderFormHandler(Form $form) {
		$model = $form->getModel();
		/** @var PageModel $page */
		$page = $form->getModel('page');
		$page->set('fuzzy', '1'); // Needs to be fuzzy since it supports children
		$isnew = !$model->exists();

		$model->save();

		$page->set('component', 'formbuilder');
		$page->set('editurl', '/formbuilder/update/' . $model->get('id'));
		$page->set('deleteurl', '/formbuilder/delete/' . $model->get('id'));
		$page->save();

		// Clear the page cache
		$page->purgePageCache();

		if($isnew){
			Core::SetMessage('Created custom form successfully!', 'success');
			return $page->get('baseurl');
		}
		else{
			Core::SetMessage('Updated custom form successfully!', 'success');
			return 'back';
		}
	}


}