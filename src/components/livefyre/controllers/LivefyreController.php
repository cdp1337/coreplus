<?php
/**
 * File for class LivefyreController definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130402.1411
 */


/**
 * Class LivefyreController description
 */
class LivefyreController extends Controller_2_1{
	public function index(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->checkAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}

		if($request->isPost()){
			// Update/save the site id.
			ConfigHandler::Set('/livefyre/siteid', $_POST['siteid']);
			Core::SetMessage('Set Site ID Successfully!', 'success');
			\Core\reload();
		}

		// Pull the configuration options to see if livefyre is currently setup.
		$siteid = ConfigHandler::Get('/livefyre/siteid');

		// Generate the form to either set or update the siteid.
		$form = new Form();
		$form->set('method', 'POST');
		$form->addElement('text', ['name' => 'siteid', 'title' => 'Site ID', 'value' => $siteid]);

		$view->assign('siteid', $siteid);
		$view->assign('url', ROOT_URL_NOSSL);
		$view->assign('form', $form);

		// Setup instructions:
		// http://www.livefyre.com/install/
	}
}
