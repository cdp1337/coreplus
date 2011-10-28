<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ContentController
 *
 * @author powellc
 */
class ContentController extends Controller {
	public static function Index(View $page){
		if(!$page->setAccess('g:admin')){
			return;
		}
		
		$f = ContentModel::Find(null, null, null);
		
		$page->title = 'Content Page Listings';
		$page->assignVariable('pages', $f);
		
		$page->addControl('Add Page', '/Content/Create', 'add');
	}
	
    public static function View(View $page){
		// I'm calling checkAcess here because the cached access string is canonical in this case.
		if(!$page->checkAccess()){
			return;
		}
		
		$m = new ContentModel($page->getParameter(0));

		if(!$m->exists()) return View::ERROR_NOTFOUND;

		$page->assignVariable('model', $m);
		
		if(Core::User()->checkAccess('g:admin')){
			$page->addControl('Add Page', '/Content/Create', 'add');
			$page->addControl('Edit Page', '/Content/Edit/' . $m->get('id'), 'edit');
			$page->addControl('Delete Page', '/Content/Delete/' . $m->get('id'), 'delete');
			$page->addControl('All Content Pages', '/Content', 'directory');
		}
	}

	public static function Edit(View $page){
		if(!$page->setAccess('g:admin')){
			return;
		}
		
		$m = new ContentModel($page->getParameter(0));

		if(!$m->exists()) return View::ERROR_NOTFOUND;
		
		$form = Form::BuildFromModel($m);
		$form->set('callsmethod', 'ContentController::_SaveHandler');
		
		$form->addElement('pagemeta', array('name' => 'page', 'baseurl' => '/Content/View/' . $m->get('id')));
		
		$form->addElement('pageinsertables', array('name' => 'insertables', 'baseurl' => '/Content/View/' . $m->get('id')));
//var_dump($form->getElementByName('page'), $form->getElementByName('page')->getModel()); die();
		// Tack on a submit button
		$form->addElement('submit', array('value' => 'Update'));

		$page->title = 'Edit ' . $m->get('title');
		$page->assignVariable('model', $m);
		$page->assignVariable('form', $form);
		
		$page->addControl('Add Page', '/Content/Create', 'add');
		$page->addControl('View Page', '/Content/View/' . $m->get('id'), 'view');
		$page->addControl('Delete Page', '/Content/Delete/' . $m->get('id'), 'delete');
		$page->addControl('All Content Pages', '/Content', 'directory');
	}

	public static function Create(View $page){
		
		if(!$page->setAccess('g:admin')){
			return;
		}
		
		$m = new ContentModel();
		
		$form = Form::BuildFromModel($m);
		$form->set('callsmethod', 'ContentController::_SaveHandler');
		
		$form->addElement('pagemeta', array('name' => 'page'));
		
		$form->addElement('pageinsertables', array('name' => 'insertables', 'baseurl' => '/Content/View/new'));
		
		// Tack on a submit button
		$form->addElement('submit', array('value' => 'Create'));

		
		$page->title = 'New Content Page';
		$page->assignVariable('model', $m);
		$page->assignVariable('form', $form);
		
		$page->addControl('All Content Pages', '/Content', 'directory');
	}
	
	public static function _SaveHandler(Form $form){
		
		$model = $form->getModel();
		//var_dump($model); die();
		$model->save();
		
		$page = $form->getElementByName('page')->getModel();
		$page->set('baseurl', '/Content/View/' . $model->get('id'));
		$page->save();
		
		$insertables = $form->getElementByName('insertables');
		$insertables->set('baseurl', '/Content/View/' . $model->get('id'));
		$insertables->save();
		
		// w00t
		return $page->getResolvedURL();
	}
	
	public static function Delete(View $page){
		$m = new ContentModel($page->getParameter(0));

		if(!$m->exists()) return View::ERROR_NOTFOUND;
		
		if($page->getParameter(1) == 'confirm'){
			$m->delete();
			Core::Redirect('/Content');
		}
		
		$page->title = 'Confirm Delete ' . $m->get('title');
		$page->assignVariable('model', $m);
		
		$page->addControl('Add Page', '/Content/Create', 'add');
		$page->addControl('View Page', '/Content/View/' . $m->get('id'), 'view');
		$page->addControl('Edit Page', '/Content/Edit/' . $m->get('id'), 'edit');
		$page->addControl('All Content Pages', '/Content', 'directory');
	}
}
?>
