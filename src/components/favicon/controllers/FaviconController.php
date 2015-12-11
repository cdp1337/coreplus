<?php
/**
 * Class file for the controller FaviconController
 *
 * @package Favicon
 * @author Charlie Powell <charlie@evalagency.com>
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
		$form->set('callsmethod', 'AdminController::_ConfigSubmit');
		$form->addElement(ConfigHandler::GetConfig('/favicon/image')->getAsFormElement());
		$form->addElement('submit', ['value' => t('STRING_SAVE')]);

		$view->title = 'Site Favicon';
		$view->assign('current', $image);
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

		// Fix Bug #562, Favicon "none" option.
		// Do not render anything if no icon is selected.
		if(!$image){
			return;
		}
		if(!$file->exists()){
			return;
		}

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
}