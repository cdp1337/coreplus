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
		
		// @todo Check page permissions
		
		//$f = PageModel::Find(array("baseurl LIKE '/Content/View/%'"));
		//$f = ContentModel::Find(null, null, 'title');
		$f = ContentModel::Find(null, null, null);
		
		$page->title = 'Content Page Listings';
		$page->assignVariable('pages', $f);
		
		$page->addControl('Add Page', '/Content/Create', 'add');
	}
	
    public static function View(View $page){
		if(!$page->setAccess('g:admin')){
			return;
		}
		
		$m = new ContentModel($page->getParameter(0));

		if(!$m->exists()) return View::ERROR_NOTFOUND;


		$page->title = $m->get('title');
		$page->metas['description'] = $m->get('description');
		$page->metas['keywords'] = $m->get('keywords');
		$page->access = $m->get('access');
		$page->assignVariable('model', $m);
		
		$page->addControl('Add Page', '/Content/Create', 'add');
		$page->addControl('Edit Page', '/Content/Edit/' . $m->get('id'), 'edit');
		$page->addControl('Delete Page', '/Content/Delete/' . $m->get('id'), 'delete');
		$page->addControl('All Content Pages', '/Content', 'directory');
	}

	public static function Edit(View $page){
		if(!$page->setAccess('g:admin')){
			return;
		}
		
		$m = new ContentModel($page->getParameter(0));

		if(!$m->exists()) return View::ERROR_NOTFOUND;
		
		$form = Form::BuildFromModel($m);
		$form->set('callsmethod', 'ContentController::_SaveHandler');

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

		//$p = new PageModel();
		//$p->set('rewriteurl', '/foo');
		//var_dump($p); 
		//die();
		
		$form = Form::BuildFromModel($m);
		$form->set('callsmethod', 'ContentController::_SaveHandler');
		
		$form->addElement('pagemeta', array('name' => 'page'));
		
		$form->addElement('pageinsertables', array('name' => 'insertables', 'baseurl' => '/Content/View/new'));
		
		//$this->addElement('pageinsertables', array('name' => 'insertables', 'baseurl' => $this->get('baseurl')));
		
		//$form->addElement(new FormElementPageGroup(array('name' => 'thispage', 'baseurl' => '/Content/View/1')));
		//var_dump($form); die();
		// Some file upload option (a test really)
		//$form->addElement(FormElement::Factory('file', array('title' => 'File Foo', 'name' => 'fileupload', 'basedir' => 'public/test12', 'browsable' => false)));

		// Tack on a submit button
		$form->addElement('submit', array('value' => 'Create'));

		
		$page->title = 'New Content Page';
		$page->assignVariable('model', $m);
		$page->assignVariable('form', $form);
		
		$page->addControl('All Content Pages', '/Content', 'directory');
	}
	
	public static function _SaveHandler(Form $form){
		
		$page = $form->getElementByName('page');
		$insertables = $form->getElementByName('insertables');
		
		
		var_dump($page, $insertables, $form); die();

		// Save the model
		$m = $form->getModel();
		// These pages are widget-able.
		//$m->getLink('Page')->set('widget', true);
		//$m->save();
		//return $m->get('baseurl');
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
