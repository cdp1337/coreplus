<?php
/**
 * Admin menu widget
 *
 * Displays every "admin" level page in the system, (if the user has access)
 *
 * @package Core Plus\Core
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

class AdminMenuWidget extends Widget_2_1 {

	// API Version 1.0 of the widget system.
	public function execute(){
		return $this->view();
	}

	// API Version 2.1 of the widget system.
	public function view(){
		$v = $this->getView();

		$pages = PageModel::Find(array('admin' => '1'));
		$groups = array();
		$flatlist = array();
		foreach($pages as $p){
			if(!\Core\user()->checkAccess($p->get('access'))) continue;
			if($p->get('title') == "Administration") {
				$p->set('title', trim(str_replace("Administration", "Admin", $p->get('title'))) );
			} else {
				$p->set('title', trim(str_replace("Admin","", str_replace("Administration", "", $p->get('title'))) ) );
			}
			if($p->get('title') == "System Configuration") {
				$p->set('title', "System Config");
			}
			if($p->get('title') == "Navigation Listings") {
				$p->set('title', "Navigation");
			}
			if($p->get('title') == "Content Page Listings") {
				$p->set('title', "Content Pages");
			}

			$group = $p->get('admin_group') ? $p->get('admin_group') : 'Admin';

			// Some group tweaks ;)
			$group = str_replace('and', '&', $group);

			if(!isset($groups[$group])){
				$groups[$group] = array();
			}

			// The new grouped pages
			$groups[$group][ $p->get('title') ] = $p;
			// And the flattened list to support legacy templates.
			$flatlist[ $p->get('title') ] = $p;
		}

		ksort($flatlist);
		ksort($groups);

		foreach($groups as $gname => $dat){
			ksort($groups[$gname]);
		}

		$v->templatename = 'widgets/adminmenu/view.tpl';
		$v->assign('pages', $flatlist);
		$v->assign('groups', $groups);

		return $v;
	}
}
