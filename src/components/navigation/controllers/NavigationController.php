<?php
/**
 * Navigation controller, handles the administrative functions for this component.
 *
 * @package Navigation
 * @since 0.1
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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
class NavigationController extends Controller_2_1 {

	public function __construct() {
		$this->accessstring = 'g:admin';
	}

	public function index() {
		$view = $this->getView();

		$f = NavigationModel::Find(null, null, 'name');

		$view->title = 'Navigation Listings';
		$view->assign('navs', $f);
		$view->assign('current_theme', ConfigHandler::Get('/theme/selected') . '?template=' . ConfigHandler::Get('/theme/default_template'));
		$view->templatename = '/pages/navigation/index.tpl';
		$view->addControl('New Navigation Menu', '/Navigation/Create', 'add');
	}


	public function edit() {
		$view = $this->getView();
		$m    = new NavigationModel($this->getPageRequest()->getParameter(0));

		if (!$m->exists()) return View::ERROR_NOTFOUND;

		$form = Form::BuildFromModel($m);
		$form->set('callsmethod', 'NavigationController::_SaveHandler');

		// I only want non-fuzzy pages to display.
		//$views = PageModel::GetPagesAsOptions("fuzzy = 0");
		$views = PageModel::GetPagesAsOptions();

		// Get the entries for this model as well.
		$entries = $m->getLink('NavigationEntry', 'weight ASC');

		$view->title = 'Edit ' . $m->get('name');
		$view->assignVariable('model', $m);
		$view->assignVariable('form', $form);
		$view->assignVariable('pages', $views);
		$view->assignVariable('entries', $entries);
		$view->templatename = '/pages/navigation/edit.tpl';
		$view->addControl('New Navigation Menu', '/Navigation/Create', 'add');
		//$view->addControl('Delete Menu', '/Content/Delete/' . $m->get('id'), 'delete');
		$view->addControl('Navigation Listings', '/Navigation', 'directory');
	}

	public function create() {
		$view = $this->getView();
		$m    = new NavigationModel();

		$form = Form::BuildFromModel($m);
		$form->set('callsmethod', 'NavigationController::_SaveHandler');

		// I only want non-fuzzy pages to display.
		//$views = PageModel::GetPagesAsOptions("fuzzy = 0");
		$views = PageModel::GetPagesAsOptions();

		$view->title        = 'New Navigation Menu';
		$view->templatename = '/pages/navigation/create.tpl';
		$view->assignVariable('model', $m);
		$view->assignVariable('form', $form);
		$view->assignVariable('pages', $views);
		$view->addControl('Navigation Listings', '/Navigation', 'directory');
	}

	public function delete() {
		$view = $this->getView();
		$req  = $this->getPageRequest();
		$mid  = $req->getParameter(0);
		$m    = new NavigationModel($mid);

		if (!$req->isPost()) {
			return View::ERROR_BADREQUEST;
		}

		if (!$m->exists()) {
			return View::ERROR_NOTFOUND;
		}

		$m->delete();

		\core\redirect('/navigation');
	}

	public static function _SaveHandler(Form $form) {

		// Save the model
		$m = $form->getModel();
		$m->save();

		// Save the widget too
		$widget = $m->getLink('Widget');
		$widget->set('title', $m->get('name'));
		$widget->save();

		// Save all the entries
		$counter = 0;
		if (!isset($_POST['entries'])) $_POST['entries'] = array();

		foreach ($_POST['entries'] as $id => $dat) {

			// New entries get an incremented counter and a new model.
			if (strpos($id, 'new') !== false) {
				++$counter;
				$entry = new NavigationEntryModel();
			} // Deleted entries just get the model deleted.
			elseif (strpos($id, 'del-') !== false) {
				$entry = new NavigationEntryModel(substr($id, 4));
				$entry->delete();
				continue;
			}
			// Existing entries also get an incremented counter and the existing model.
			else {
				++$counter;
				$entry = new NavigationEntryModel($id);
			}

			// Set the weight, based on the counter...
			$entry->set('weight', $counter);

			// Make sure it links up to the right navigation...
			$entry->set('navigationid', $m->get('id'));

			// Set the correct parent...
			$entry->set('parentid', $dat['parent']);

			// And the data from the regular form...
			$entry->set('type', $dat['type']);
			$entry->set('baseurl', $dat['url']);
			$entry->set('title', $dat['title']);
			$entry->set('target', $dat['target']);

			$entry->save();

			// I need to update the link of any other element with this as the parent.
			if (strpos($id, 'new') !== false) {
				foreach ($_POST['entries'] as $sk => $sdat) {
					if ($sdat['parent'] == $id) $_POST['entries'][$sk]['parent'] = $entry->get('id');
				}
			}
		}

		return '/Navigation';
	}
}

?>
