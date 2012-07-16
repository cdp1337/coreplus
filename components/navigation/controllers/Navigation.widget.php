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
		$view = $this->getView();
		$m    = new NavigationModel($this->getParameter(0));

		if (!$m->exists()) return View::ERROR_NOTFOUND;

		// Get the entries for this model as well.
		$entries = $m->getLink('NavigationEntry', 'weight ASC');

		//var_dump($entries);
		// View won't quite just have a flat list of entries, as they need to be checked and sorted
		// into a nested array.
		$sortedentries = array();
		// First level children
		foreach ($entries as $k => $e) {
			if (!$e->get('parentid')) {
				$sortedentries[] = array('obj' => $e, 'children' => array());
				unset($entries[$k]);
			}
		}
		// One level deep
		if (sizeof($entries)) {
			foreach ($sortedentries as $sk => $se) {
				foreach ($entries as $k => $e) {
					if ($e->get('parentid') == $se['obj']->get('id')) {
						$sortedentries[$sk]['children'][] = array('obj' => $e, 'children' => array());
						unset($entries[$k]);
					}
				}
			}
		}
		// Two levels deep
		// this would be so much simplier if the menu was in DOM format... :/
		if (sizeof($entries)) {
			foreach ($sortedentries as $sk => $se) {
				foreach ($se['children'] as $subsk => $subse) {
					foreach ($entries as $k => $e) {
						if ($e->get('parentid') == $subse['obj']->get('id')) {
							$sortedentries[$sk]['children'][$subsk]['children'][] = array('obj' => $e, 'children' => array());
							unset($entries[$k]);
						}
					}
				}
			}
		}


		// @todo Check page permissions

		$view->title        = $m->get('title');
		$view->access       = $m->get('access');
		$view->templatename = '/pages/navigation/view.tpl';
		$view->assignVariable('model', $m);
		$view->assignVariable('entries', $sortedentries);
		/*
		$view->addControl('New Navigation Menu', '/Navigation/Create', 'add');
		$view->addControl('Edit Page', '/Content/Edit/' . $m->get('id'), 'edit');
		$view->addControl('Delete Page', '/Content/Delete/' . $m->get('id'), 'delete');
		$view->addControl('All Content Pages', '/Content', 'directory');
		*/
	}
}