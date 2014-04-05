<?php
/**
 * File for class Page Search definition in the coreplus project
 * 
 * @package Blog
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20140228.1328
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


/**
 * Page Search Widget provides exactly that, a simple page search.
 * 
 * @package Core
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class PageSearchWidget extends Widget_2_1 {
	public $is_simple = true;

	public $settings = [
		'title' => 'Page Search',
	];

	public function getFormSettings(){

		$settings = [
			[
				'type'        => 'text',
				'name'        => 'title',
				'title'       => 'Displayed Title',
				'description' => 'Displayed title on the page where this widget is added to.',
			],
		];

		return $settings;
	}

	/**
	 * Widget to display a simple site search box
	 */
	public function execute(){
		$view = $this->getView();

		$urlbase = '/page/search';
		$url = Core::ResolveLink($urlbase);

		if(PageRequest::GetSystemRequest()->getBaseURL() == $urlbase && PageRequest::GetSystemRequest()->getParameter('q')){
			$query = PageRequest::GetSystemRequest()->getParameter('q');
		}
		else{
			$query = null;
		}

		$view->assign('title', $this->getSetting('title'));
		$view->assign('url', $url);
		$view->assign('query', $query);
	}
} 