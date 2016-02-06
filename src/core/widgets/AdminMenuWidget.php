<?php
/**
 * Admin menu widget
 *
 * Displays every "admin" level page in the system, (if the user has access)
 *
 * @package Core
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
			$groups['SUDO'] = [
				'title' => 'SUDO',
				'href' => '',
				'children' => [
					'Exit SUDO Mode' => $p,
				],
			];
			$flatlist[ 'Exit SUDO Mode' ] = $p;
		}


		if(\Core\user()){
			foreach($pages as $p){
				/** @var PageModel $p */
				if(!\Core\user()->checkAccess($p->get('access'))) continue;

				// Pages can define which sub-menu they get grouped under.
				// The 'Admin' submenu is the default.
				$group = $p->get('admin_group') ? $p->get('admin_group') : 't:STRING_ADMIN';
				// Support i18n here!
				if(strpos($group, 't:') === 0){
					$group = t(substr($group, 2));
				}

				if(!isset($groups[$group])){
					$groups[$group] = [
						'title'    => $group,
						'href'     => '',
						'children' => [],
					];
				}

				if($p->get('baseurl') == '/admin'){
					// Admin gets special treatment.
					$groups[t('STRING_ADMIN')]['href'] = '/admin';
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

				$title = $p->get('title');
				// Support i18n here!
				if(strpos($title, 't:') === 0){
					$title = t(substr($title, 2));
				}

				if(isset($groups[$title])){
					// Link the main group to this page instead of an empty link.
					// This removes duplicate links such as the group "User" and page "User".
					$groups[$title]['href'] = $p->get('rewriteurl');
				}
				else{
					// The new grouped pages
					$groups[$group]['children'][ $title ] = $p;
					// And the flattened list to support legacy templates.
					$flatlist[ $title ] = $p;
				}
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
			ksort($groups[$gname]['children']);
		}

		// Build a list of languages that can be set by the user.
		$locales = \Core\i18n\I18NLoader::GetLocalesAvailable();
		$selected = \Core\i18n\I18NLoader::GetUsersLanguage();
		$languages = [];
		if(sizeof($locales) > 1){
			// There is at least 1 language available on the system, YAY!
			foreach($locales as $localeKey => $localeDat){
				if(($pos = strpos($localeKey, '_')) !== false){
					// This locale contains an underscore, that means it has a corresponding country!
					// These are what we want to display to the end user.
					$country = substr($localeKey, $pos+1);

					// Here I am retrieving the language and dialect in the native dialect if at all possible.
					// This is because if you as a user only can read your native language and your browser renders something different,
					// then you want to be able to read what you're switching it to.
					$str1 = new \Core\i18n\I18NString($localeDat['lang']);
					$str1->setLanguage($localeKey);
					$localeTitle = $str1->getTranslation();
					if($localeDat['dialect']){
						$str2 = new \Core\i18n\I18NString($localeDat['dialect']);
						$str2->setLanguage($localeKey);
						$localeTitle .= ' (' . $str2->getTranslation() . ')';
					}

					$languages[] = [
						'key'      => $localeKey,
					    'title'    => $localeTitle,
					    'country'  => $country,
					    'image'    => 'assets/images/iso-country-flags/' . strtolower($country) . '.png',
					    'selected' => $localeKey == $selected,
					];
				}
			}
		}

		$v->templatename = 'widgets/adminmenu/view.tpl';
		$v->assign('pages', $flatlist);
		$v->assign('groups', $groups);
		$v->assign('languages', $languages);

		return $v;
	}
}
