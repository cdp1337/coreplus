<?php
/**
 * Navigation widget, handles displaying the navigation menus.
 *
 * @package Core Plus\Navigation
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

class NavigationWidget extends Widget_2_1 {
	public function view() {
		$view       = $this->getView();
		$m          = NavigationModel::Construct($this->getParameter(0));
		$current    = PageRequest::GetSystemRequest();
		$currenturl = $current->getBaseURL(); // Used to indicate the "active" link.

		if (!$m->exists()) return View::ERROR_NOTFOUND;

		// Get the entries for this model as well.
		$entries = $m->getLink('NavigationEntry', 'weight ASC');

		// View won't quite just have a flat list of entries, as they need to be checked and sorted
		// into a nested array.
		$sortedentries = array();
		// First level children
		foreach ($entries as $k => $e) {
			if (!$e->get('parentid')) {

				$classes = array();
				if($e->get('baseurl') == $currenturl) $classes[] = 'active';

				$sortedentries[] = array(
					'obj' => $e,
					'children' => array(),
					'classes' => $classes
				);
				unset($entries[$k]);
			}
		}

		// One level deep
		if (sizeof($entries)) {
			foreach ($sortedentries as $sk => $se) {
				foreach ($entries as $k => $e) {
					if ($e->get('parentid') == $se['obj']->get('id')) {

						$classes = array();
						if($e->get('baseurl') == $currenturl) $classes[] = 'active';

						// Add the "more" class to the parent.
						$sortedentries[$sk]['classes'][] = 'more';

						$sortedentries[$sk]['children'][] = array(
							'obj' => $e,
							'children' => array(),
							'classes' => $classes
						);
						unset($entries[$k]);
					}
				}
			}
		}

		// Two levels deep
		// this would be so much simpler if the menu was in DOM format... :/
		if (sizeof($entries)) {
			foreach ($sortedentries as $sk => $se) {
				foreach ($se['children'] as $subsk => $subse) {
					foreach ($entries as $k => $e) {
						if ($e->get('parentid') == $subse['obj']->get('id')) {

							$classes = array();
							if($e->get('baseurl') == $currenturl) $classes[] = 'active';

							// Add the "more" class to the parent.
							$sortedentries[$sk]['children'][$subsk]['class'][] = 'more';

							$sortedentries[$sk]['children'][$subsk]['children'][] = array(
								'obj' => $e,
								'children' => array(),
								'classes' => $classes
							);
							unset($entries[$k]);
						}
					}
				}
			}
		}

		foreach($sortedentries as $k => $el){
			$this->_transposeClass($sortedentries[$k]);
		}


		// @todo Check page permissions

		$view->title        = $m->get('title');
		$view->access       = $m->get('access');
		$view->templatename = '/widgets/navigation/view.tpl';
		$view->assignVariable('model', $m);
		$view->assignVariable('entries', $sortedentries);
		/*
		$view->addControl('New Navigation Menu', '/Navigation/Create', 'add');
		$view->addControl('Edit Page', '/Content/Edit/' . $m->get('id'), 'edit');
		$view->addControl('Delete Page', '/Content/Delete/' . $m->get('id'), 'delete');
		$view->addControl('All Content Pages', '/Content', 'directory');
		*/
	}

	/**
	 * This is a widget to display siblings on a given page.
	 *
	 * The page is dynamic based on the currently viewed page.
	 *
	 * @return int
	 */
	public function siblings() {
		$view    = $this->getView();
		$current = PageRequest::GetSystemRequest();
		$model   = $current->getPageModel();
		if (!$model) return '';
		$baseurl = $model->get('parenturl');
		if (!$baseurl) return '';

		if ($model->get('admin')) {
			$pages = PageModel::Find(array('admin = 1', 'baseurl != /admin'), null, 'title');
		} else {
			// Give me all the siblings of that baseurl.
			$pages = PageModel::Find(array('parenturl' => $baseurl), null, 'title');
		}

		$entries = array();
		foreach ($pages as $page) {
			$entries[] = array('obj' => $page, 'children' => array(), 'class' => '');
		}

		$view->assign('entries', $entries);
	}

	/**
	 * This is a widget to display siblings AND the active page's children on a given page.
	 *
	 * The page is dynamic based on the currently viewed page.
	 *
	 * @return int
	 */
	public function siblingsandchildren() {
		$view    = $this->getView();
		$current = PageRequest::GetSystemRequest();
		$model   = $current->getPageModel();
		if (!$model) return '';
		$baseurl = $model->get('parenturl');

		// Top level pages don't get their siblings displayed.
		if (!$baseurl) return '';

		if ($model->get('admin')) {
			$pages = PageModel::Find(array('admin = 1', 'baseurl != /admin'), null, 'title');
		} else {
			// Give me all the siblings of that baseurl.
			$pages = PageModel::Find(array('parenturl' => $baseurl, 'selectable' => 1), null, 'title');
		}

		$entries = array();
		foreach ($pages as $page) {
			if ($page->get('baseurl') == $model->get('baseurl')) {
				$subpages   = PageModel::Find(array('parenturl' => $model->get('baseurl'), 'selectable' => 1), null, 'title');
				$subentries = array();
				foreach ($subpages as $subpage) {
					$subentries[] = array('obj' => $subpage, 'children' => array(), 'class' => '');
				}
				$entries[] = array('obj' => $page, 'children' => $subentries, 'class' => 'active');
			} else {
				$entries[] = array('obj' => $page, 'children' => array(), 'class' => '');
			}
		}

		$view->assign('entries', $entries);
	}

	/**
	 * This is a widget to display children of the current page
	 *
	 * The page is dynamic based on the currently viewed page.
	 *
	 * @return int
	 */
	public function children() {
		$view    = $this->getView();
		$current = PageRequest::GetSystemRequest();
		$model   = $current->getPageModel();
		if (!$model) return '';
		$baseurl = $model->get('baseurl');

		// Give me all the siblings of that baseurl.
		$pages = PageModel::Find(array('parenturl' => $baseurl, 'selectable' => 1), null, 'title');

		$entries = array();
		foreach ($pages as $page) {
			$subpages   = PageModel::Find(array('parenturl' => $page->get('baseurl'), 'selectable' => 1), null, 'title');
			$subentries = array();
			foreach ($subpages as $subpage) {
				$subentries[] = array('obj' => $subpage, 'children' => array(), 'class' => '');
			}
			$entries[] = array('obj' => $page, 'children' => $subentries, 'class' => 'active');
		}

		$view->assign('entries', $entries);
	}



	private function _transposeClass(&$el){
		$el['class'] = implode(' ', array_unique($el['classes']));
		if(sizeof($el['children'])){
			foreach($el['children'] as $k => $subel){
				$this->_transposeClass($el['children'][$k]);
			}
		}
	}
}