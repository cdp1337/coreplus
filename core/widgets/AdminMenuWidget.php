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
	public function execute(){
		$v = $this->getView();

		$pages = PageModel::Find(array('admin' => '1'));
		$viewable = array();
		foreach($pages as $p){
			if(!Core::User()->checkAccess($p->get('access'))) continue;
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

			$viewable[] = $p;
		}

		$v->templatename = 'widgets/adminmenu.tpl';
		$v->assignVariable('pages', $viewable);

		return $v;
	}
}

?>
