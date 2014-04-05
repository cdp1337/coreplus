<?php
/**
 * Admin menu widget
 *
 * Displays every "admin" level page in the system, (if the user has access)
 *
 * @package Core
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

		if(isset($_SESSION['user_sudo'])){
			$p = new PageModel('/user/sudo');
			$p->set('title', 'Exit SUDO Mode');
			$groups['SUDO']['Exit SUDO Mode'] = $p;
			$flatlist[ 'Exit SUDO Mode' ] = $p;
		}


		if(\Core\user()){
			foreach($pages as $p){
				/** @var PageModel $p */
				if(!\Core\user()->checkAccess($p->get('access'))) continue;

				// Pages can define which sub-menu they get grouped under.
				// The 'Admin' submenu is the default.
				$group = $p->get('admin_group') ? $p->get('admin_group') : 'Admin';

				// Some group tweaks ;)
				$group = str_replace('and', '&', $group);

				if(!isset($groups[$group])){
					$groups[$group] = [
						'title'    => $group,
						'href'     => '',
						'children' => [],
					];
				}

				if($p->get('baseurl') == '/admin'){
					// Admin gets special treatment.
					$groups['Admin']['href'] = '/admin';
					continue;
				}

				switch($p->get('title')){
					case 'System Configuration':
						$p->set('title', "System Config");
						break;
					case 'Navigation Listings':
						$p->set('title', "Navigation");
						break;
					case 'Content Page Listings':
						$p->set('title', "Content Pages");
						break;
					default:
						$p->set(
							'title',
							trim( str_replace(['Administration', 'Admin'],'', $p->get('title')) )
						);
				}


				// The new grouped pages
				$groups[$group]['children'][ $p->get('title') ] = $p;
				// And the flattened list to support legacy templates.
				$flatlist[ $p->get('title') ] = $p;
			}

			// This is a hack to make sure that users can view the /admin link if they can view other admin pages.
			/*if(sizeof($flatlist) && !isset($groups['Admin']['Dashboard'])){
				$p = new PageModel('/admin');
				$p->set('title', 'Dashboard');
				$groups['Admin']['Dashboard'] = $p;
			}*/
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
