<?php
/**
 * Class file for the controller FaviconController
 *
 * @package Favicon
 * @author Charlie Powell <charlie@eval.bz>
 */
class FaviconController extends Controller_2_1 {
	// Each controller can have many views, each defined by a different method.
	// These methods should be regular public functions that DO NOT begin with an underscore (_).
	// Any method that begins with an underscore or is static will be assumed as an internal method
	// and cannot be called externally via a url.

	public function admin(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->checkAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}

		$image = ConfigHandler::Get('/favicon/image');

		$form = new Form();
		$form->set('callsmethod', 'FaviconController::AdminHandler');
		$form->addElement(
			'file',
			[
				'name' => 'image',
				'title' => 'Image',
				'accept' => 'image/*',
				'basedir' => 'public/favicon/',
				'value' => $image,
			]
		);
		$form->addElement('submit', ['value' => 'Save']);

		$view->title = 'Site Favicon';
		$view->assign('form', $form);
	}

	/**
	 * Simple method to handle any legacy call to favicon.ico
	 */
	public function index() {
		$view = $this->getView();

		$image = ConfigHandler::Get('/favicon/image');
		$file = \Core\Filestore\Factory::File($image);

		$view->contenttype = 'image/png';
		$view->record = false;
		$view->mode = View::MODE_NOOUTPUT;

		$file->displayPreview( '32x32!');
	}

	/**
	 * Hook to add in the necessary favicon ViewMeta attribute into the View.
	 *
	 * The ViewMeta_favicon handles the rest of the magic.
	 *
	 * @param View $view
	 */
	public static function PageHook(View $view) {
		$view->addMetaName('favicon', null);
	}

	public static function AdminHandler(Form $form) {
		ConfigHandler::Set('/favicon/image', $form->getElement('image')->get('value'));

		return true;
	}
}