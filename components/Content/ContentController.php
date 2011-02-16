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
		$f = ContentModel::Find(null, null, 'title');
		
		$page->title = 'Content Page Listings';
		$page->assignVariable('pages', $f);
		
		$page->addControl('Add Page', '/Content/Create', 'add');
	}
	
    public static function View(View $page){
		$m = new ContentModel($page->getParameter(0));

		if(!$m->exists()) return View::ERROR_NOTFOUND;

		// @todo Check page permissions

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
		$m = new ContentModel($page->getParameter(0));

		if(!$m->exists()) return View::ERROR_NOTFOUND;
		
		$form = Form::BuildFromModel($m);
		$form->set('callsmethod', 'ContentController::_SaveHandler');

		// Tack on a submit button
		$form->addElement('submit', array('value' => 'Update'));

		// @todo Check page permissions
		
		$page->title = 'Edit ' . $m->get('title');
		$page->assignVariable('model', $m);
		$page->assignVariable('form', $form);
		
		$page->addControl('Add Page', '/Content/Create', 'add');
		$page->addControl('View Page', '/Content/View/' . $m->get('id'), 'view');
		$page->addControl('Delete Page', '/Content/Delete/' . $m->get('id'), 'delete');
		$page->addControl('All Content Pages', '/Content', 'directory');
	}

	public static function Create(View $page){
		$m = new ContentModel();

		$form = Form::BuildFromModel($m);
		$form->set('callsmethod', 'ContentController::_SaveHandler');

		// Tack on a submit button
		$form->addElement('submit', array('value' => 'Create'));

		// @todo Check page permissions

		$page->title = 'New Content Page';
		$page->assignVariable('model', $m);
		$page->assignVariable('form', $form);
		
		$page->addControl('All Content Pages', '/Content', 'directory');
	}
	
	public static function _SaveHandler(Form $form){
		// @todo Check page permissions

		// Save the model
		$m = $form->getModel();
		// These pages are widget-able.
		$m->getLink('Page')->set('widget', true);
		$m->save();
		return $m->get('baseurl');
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
