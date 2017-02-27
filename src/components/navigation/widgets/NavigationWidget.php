<?php
/**
 * Navigation widget, handles displaying the navigation menus.
 *
 * @package Navigation
 * @since 0.1
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2016  Charlie Powell
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

class NavigationWidget extends \Core\Widget {
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
		$sortedentries = [];
		// First level children
		foreach ($entries as $k => $e) {
			if (!$e->get('parentid')) {

				$classes = [];
				$classes[] = Core\str_to_url($e->get('title')) . '-link';
				if($e->get('baseurl') == $currenturl) $classes[] = 'active';

				if(\Core\user()->checkAccess($e->getAccessString())){
					// There's a weird bug where sometimes the access cache is empty.
					// In that case, just allow the user to view the page.
					$sortedentries[] = [
						'obj' => $e,
						'children' => [],
						'classes' => $classes
					];
				}

				unset($entries[$k]);
			}
		}

		// One level deep
		if (sizeof($entries)) {
			foreach ($sortedentries as $sk => $se) {
				foreach ($entries as $k => $e) {
					if ($e->get('parentid') == $se['obj']->get('id')) {

						if(\Core\user()->checkAccess($e->getAccessString())){
							// There's a weird bug where sometimes the access cache is empty.
							// In that case, just allow the user to view the page.
							$classes   = [];
							$classes[] = Core\str_to_url($e->get('title')) . '-link';
							if($e->get('baseurl') == $currenturl) {
								$classes[] = 'active';

								// also set active class on the parent so frontenders don't rage :)
								$sortedentries[ $sk ]['classes'][] = 'active';
							}

							// Add the "more" class to the parent.
							$sortedentries[ $sk ]['classes'][] = 'more';

							$sortedentries[ $sk ]['children'][] = [
								'obj'      => $e,
								'children' => [],
								'classes'  => $classes
							];
						}
						unset($entries[ $k ]);
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

							if(\Core\user()->checkAccess($e->getAccessString())){
								$classes = [];
								$classes[] = Core\str_to_url($e->get('title')) . '-link';
								if($e->get('baseurl') == $currenturl) {
									$classes[] = 'active';

									// also set active class on the top-most nav parent so frontenders don't rage :)
									$sortedentries[$sk]['children'][$subsk]['class'][] =  'active';
								}

								// Add the "more" class to the parent.
								$sortedentries[$sk]['children'][$subsk]['class'][] = 'more';

								$sortedentries[$sk]['children'][$subsk]['children'][] = [
									'obj' => $e,
									'children' => [],
									'classes' => $classes
								];
							}
							unset($entries[$k]);
						}
					}
				}
			}
		}

		foreach($sortedentries as $k => $el){
			$this->_transposeClass($sortedentries[$k]);
		}

		$view->title        = $m->get('title');
		$view->access       = $m->get('access');
		//$view->templatename = '/widgets/navigation/view.tpl';
		$view->assignVariable('model', $m);
		$view->assignVariable('entries', $sortedentries);
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
			$pages = PageModel::Find(['admin = 1', 'baseurl != /admin'], null, 'title');
		} else {
			// Give me all the siblings of that baseurl.
			$pages = PageModel::Find(['parenturl' => $baseurl], null, 'title');
		}

		$entries = [];
		foreach ($pages as $page) {
			$entries[] = ['obj' => $page, 'children' => [], 'class' => ''];
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
			$pages = PageModel::Find(['admin = 1', 'baseurl != /admin'], null, 'title');
		} else {
			// Give me all the siblings of that baseurl.
			$pages = PageModel::Find(['parenturl' => $baseurl, 'selectable' => 1], null, 'title');
		}

		$entries = [];
		foreach ($pages as $page) {
			if ($page->get('baseurl') == $model->get('baseurl')) {
				$subpages   = PageModel::Find(['parenturl' => $model->get('baseurl'), 'selectable' => 1], null, 'title');
				$subentries = [];
				foreach ($subpages as $subpage) {
					$subentries[] = ['obj' => $subpage, 'children' => [], 'class' => ''];
				}
				$entries[] = ['obj' => $page, 'children' => $subentries, 'class' => 'active'];
			} else {
				$entries[] = ['obj' => $page, 'children' => [], 'class' => ''];
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
		$pages = PageModel::Find(['parenturl' => $baseurl, 'selectable' => 1], null, 'title');

		$entries = [];
		foreach ($pages as $page) {
			$subpages   = PageModel::Find(['parenturl' => $page->get('baseurl'), 'selectable' => 1], null, 'title');
			$subentries = [];
			foreach ($subpages as $subpage) {
				$subentries[] = ['obj' => $subpage, 'children' => [], 'class' => ''];
			}
			$entries[] = ['obj' => $page, 'children' => $subentries, 'class' => 'active'];
		}

		$view->assign('entries', $entries);
	}

	/**
	 * Get the path for the preview image for this widget.
	 *
	 * Should be an image of size 210x70, 210x140, or 210x210.
	 *
	 * @return string
	 */
	public function getPreviewImage(){
		$instance = $this->getWidgetInstanceModel();
		$baseurl = $instance ? $instance->get('baseurl') : null;
		$base = 'assets/images/previews/templates/widgets/navigation/';

		switch($baseurl){
			case '/navigation/siblings':
				return $base . 'navigation-siblings.png';
			case '/navigation/children':
				return $base . 'navigation-children.png';
			case '/navigation/siblingsandchildren':
				return $base . 'navigation-children_siblings.png';
			default:
				if(strpos($baseurl, '/navigation/view') === 0){
					return $base . 'navigation.png';
				}
				else{
					//var_dump($baseurl);
					return '';
				}
		}
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