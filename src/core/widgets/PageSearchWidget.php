<?php
/**
 * File for class Page Search definition in the coreplus project
 * 
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140228.1328
 * @copyright Copyright (C) 2009-2017  Charlie Powell
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
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class PageSearchWidget extends \Core\Widget {
	public $is_simple = true;
	
	public $displaySettings = [
		[
			'name' => 'title',
			'title' => 'Title',
			'description' => 'Set the title to display above/before the search box, leave blank to omit any text.',
		],
		[
			'name' => 'placeholder',
			'title' => 'Placeholder Text',
			'value' => 'Search',
			'description' => 'Set the text to display inside the search box as placeholder text.',
		],
	];

	/*public $settings = [
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
	}*/

	/**
	 * Get the path for the preview image for this widget.
	 *
	 * Should be an image of size 210x70, 210x140, or 210x210.
	 *
	 * @return string
	 */
	public function getPreviewImage(){
		return 'assets/images/previews/templates/widgets/pagesearch/page-search-execute.png';
	}

	/**
	 * Widget to display a simple site search box
	 */
	public function execute(){
		$view = $this->getView();

		$urlbase = '/page/search';
		$url = \Core\resolve_link($urlbase);

		if(PageRequest::GetSystemRequest()->getBaseURL() == $urlbase && PageRequest::GetSystemRequest()->getParameter('q')){
			$query = PageRequest::GetSystemRequest()->getParameter('q');
		}
		else{
			$query = null;
		}
		
		$displaySettings = [];
		foreach($this->displaySettings as $dat){
			$displaySettings[ $dat['name'] ] = $dat['value'];
		}

		$view->assign('title', $this->getSetting('title'));
		$view->assign('url', $url);
		$view->assign('query', $query);
		$view->assign('display_settings', $displaySettings);
	}
} 