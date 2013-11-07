<?php
/**
 * Main content controller; handles both frontend and administrative utilities.
 *
 * @package Content
 * @since 0.1
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2013  Charlie Powell
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
		
		if(!$this->setAccess('p:content_manage')){
			return View::ERROR_ACCESSDENIED;
		}
		
		$f = ContentModel::Find(null, null, 'nickname');
		
		$view->templatename = '/pages/content/index.tpl';
		$view->title = 'Content Page Listings';
		$view->assignVariable('pages', $f);
		$view->addControl('Add Page', '/Content/Create', 'add');
	}
	
    public function view(){
		// I'm calling check access here because the cached access string is canonical in this case.
		$page    = $this->getPageModel();
		$view    = $this->getView();

		if(!$this->setAccess($page->get('access'))){
			return View::ERROR_ACCESSDENIED;
		}

		/** @var $m ContentModel */
		$m = ContentModel::Construct($page->getParameter(0));

		if(!$m->exists()) return View::ERROR_NOTFOUND;

		$editor  = (\Core\user()->checkAccess($m->get('editpermissions')) || \Core\user()->checkAccess('p:content_manage'));
		$manager = \Core\user()->checkAccess('p:content_manage');

		$page = $m->getLink('Page');
		//$template = ($page->get('page_template')) ? 'view/' . $page->get('page_template') : 'view.tpl';

		$view->assignVariable('model', $m);
		//$view->templatename = '/pages/content/' . $template;
		$view->updated = $m->get('updated');

		if($manager) $view->addControl('Add Page', '/Content/Create', 'add');
		if($editor)  $view->addControl('Edit Page', '/Content/Edit/' . $m->get('id'), 'edit');
		if($manager) $view->addControl('Delete Page', '/Content/Delete/' . $m->get('id'), 'delete');
		if($manager) $view->addControl('All Content Pages', '/Content', 'directory');
	}

	public function edit(){
		$view     = $this->getView();
		$request  = $this->getPageRequest();
		$model    = new ContentModel($request->getParameter(0));

		if(!$model->exists()) return View::ERROR_NOTFOUND;

		$editor  = (\Core\user()->checkAccess($model->get('editpermissions')) || \Core\user()->checkAccess('p:content_manage'));
		$manager = \Core\user()->checkAccess('p:content_manage');

		if(!($editor || $manager)){
			return View::ERROR_ACCESSDENIED;
		}

		$page = $model->getLink('Page');

		$form = new Form();
		$form->set('callsmethod', 'ContentController::_SaveHandler');

		$form->addModel($page, 'page');
		$form->addModel($model, 'model');

		// Tack on a submit button
		$form->addElement('submit', array('value' => 'Update'));

		// Editors have certain permissions here, namely limited.
		if($editor && !$manager){
			$form->removeElement('model[nickname]');
			$form->removeElement('model[editpermissions]');
			$form->removeElement('page[rewriteurl]');
			$form->removeElement('page[parenturl]');
		}

		$view->templatename = '/pages/content/edit.tpl';
		$view->title        = 'Edit ' . $model->get('title');
		$view->assignVariable('model', $model);
		$view->assignVariable('form', $form);

		if ($manager) $view->addControl('Add Page', '/Content/Create', 'add');
		$view->addControl('View Page', '/Content/View/' . $model->get('id'), 'view');
		if ($manager) $view->addControl('Delete Page', '/Content/Delete/' . $model->get('id'), 'delete');
		if ($manager) $view->addControl('All Content Pages', '/Content', 'directory');
	}

	public function create() {

		$view = $this->getView();

		if (!$this->setAccess('p:content_manage')) {
			return View::ERROR_ACCESSDENIED;
		}

		$model = new ContentModel();
		$page = $model->getLink('Page');
		//$page = new PageModel('/content/view/new');

		$form = new Form();
		$form->set('callsmethod', 'ContentController::_SaveHandler');

		$form->addModel($page, 'page');
		$form->addModel($model, 'model');

		// Tack on a submit button
		$form->addElement('submit', array('value' => 'Create'));


		$view->templatename = '/pages/content/create.tpl';
		$view->title        = 'New Content Page';
		$view->assignVariable('model', $model);
		$view->assignVariable('form', $form);

		$view->addControl('All Content Pages', '/content', 'directory');
	}

	public static function _SaveHandler(Form $form) {

		/** @var $model ContentModel */
		$model = $form->getModel('model');
		/** @var $page PageModel Page object for this model, already linked up! */
		$page = $form->getModel('page');

		// The content nickname is derived from the page title.
		$model->set('nickname', $page->get('title'));
		$model->save();

		// w00t
		return $page->getResolvedURL();
	}

	public function delete() {
		$view    = $this->getView();
		$request = $this->getPageRequest();

		// This is a POST-only page.
		if (!$request->isPost()) {
			return View::ERROR_BADREQUEST;
		}

		if (!$this->setAccess('p:content_manage')) {
			return View::ERROR_ACCESSDENIED;
		}

		$m = new ContentModel($request->getParameter(0));

		if (!$m->exists()) return View::ERROR_NOTFOUND;
		$m->delete();
		\core\redirect('/content');
	}
}
