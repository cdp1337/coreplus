<?php
/**
 * DESCRIPTION
 *
 * @package Content
 * @since 0.1
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/agpl-3.0.txt.
 */

class ContentController extends Controller_2_1 {
	public function index(){
		
		$view = $this->getView();
		
		if(!$this->setAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}
		
		$f = ContentModel::Find(null, null, null);
		
		$view->templatename = '/pages/content/index.tpl';
		$view->title = 'Content Page Listings';
		$view->assignVariable('pages', $f);
		$view->addControl('Add Page', '/Content/Create', 'add');
	}
	
    public function view(){
		// I'm calling checkAcess here because the cached access string is canonical in this case.
		$page = $this->getPageModel();
		$view = $this->getView();
		
		if(!$this->setAccess($page->get('access'))){
			return View::ERROR_ACCESSDENIED;
		}
		
		$m = new ContentModel($page->getParameter(0));

		if(!$m->exists()) return View::ERROR_NOTFOUND;

		$view->assignVariable('model', $m);
		$view->templatename = '/pages/content/view.tpl';
		View::AddMeta('<meta http-equiv="last-modified" content="' . Time::FormatGMT($m->get('updated'), Time::TIMEZONE_GMT, Time::FORMAT_FULLDATETIME) . '" />');
		
		if(\Core\user()->checkAccess('g:admin')){
			$view->addControl('Add Page', '/Content/Create', 'add');
			$view->addControl('Edit Page', '/Content/Edit/' . $m->get('id'), 'edit');
			$view->addControl('Delete Page', '/Content/Delete/' . $m->get('id'), 'delete');
			$view->addControl('All Content Pages', '/Content', 'directory');
		}
	}

	public function edit(){
		$view = $this->getView();
		$page = $this->getPageRequest();
		
		if(!$this->setAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
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

		$view->templatename = '/pages/content/edit.tpl';
		$view->title = 'Edit ' . $m->get('title');
		$view->assignVariable('model', $m);
		$view->assignVariable('form', $form);
		
		$view->addControl('Add Page', '/Content/Create', 'add');
		$view->addControl('View Page', '/Content/View/' . $m->get('id'), 'view');
		$view->addControl('Delete Page', '/Content/Delete/' . $m->get('id'), 'delete');
		$view->addControl('All Content Pages', '/Content', 'directory');
	}

	public function create(){
		
		$view = $this->getView();
		
		if(!$this->setAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}
		
		$m = new ContentModel();
		
		$form = Form::BuildFromModel($m);
		$form->set('callsmethod', 'ContentController::_SaveHandler');
		
		$form->addElement('pagemeta', array('name' => 'page'));
		
		$form->addElement('pageinsertables', array('name' => 'insertables', 'baseurl' => '/Content/View/new'));
		
		// Tack on a submit button
		$form->addElement('submit', array('value' => 'Create'));

		
		$view->templatename = '/pages/content/create.tpl';
		$view->title = 'New Content Page';
		$view->assignVariable('model', $m);
		$view->assignVariable('form', $form);
		
		$view->addControl('All Content Pages', '/Content', 'directory');
	}
	
	public static function _SaveHandler(Form $form){
		
		$model = $form->getModel();
		// Ensure that everything is marked as updated...
		$model->set('updated', Time::GetCurrent());
		//var_dump($model); die();
		$model->save();
		
		$page = $form->getElementByName('page')->getModel();
		$page->set('baseurl', '/Content/View/' . $model->get('id'));
		$page->set('updated', Time::GetCurrent());
		$page->save();
		
		$insertables = $form->getElementByName('insertables');
		$insertables->set('baseurl', '/Content/View/' . $model->get('id'));
		$insertables->save();
		
		// w00t
		return $page->getResolvedURL();
	}
	
	public function delete(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		// This is a POST-only page.
		if(!$request->isPost()){
			return View::ERROR_BADREQUEST;
		}
		
		$m = new ContentModel($request->getParameter(0));

		if(!$m->exists()) return View::ERROR_NOTFOUND;

		$view->templatename = '/pages/content/delete.tpl';
		$view->title = 'Confirm Delete ' . $m->get('title');
		$view->assignVariable('model', $m);
		
		$view->addControl('Add Page', '/Content/Create', 'add');
		$view->addControl('View Page', '/Content/View/' . $m->get('id'), 'view');
		$view->addControl('Edit Page', '/Content/Edit/' . $m->get('id'), 'edit');
		$view->addControl('All Content Pages', '/Content', 'directory');
	}
}
?>
