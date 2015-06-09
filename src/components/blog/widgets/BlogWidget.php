<?php
/**
 * Provides the top-level Blog listing widget.
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
 * Display a widget of the various blogs on the site.
 *
 * @package Blog
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class BlogWidget extends Widget_2_1 {
	public $is_simple = true;

	public $settings = [
		'title' => 'Blogs',
		'sort'  => 'newest',
		'count' => 5,
	];

	public function getFormSettings(){

		$settings = [
			[
				'type'        => 'text',
				'name'        => 'title',
				'title'       => 'Displayed Title',
				'description' => 'Displayed title on the page where this widget is added to.',
			],
			[
				'type'        => 'select',
				'name' => 'count',
				'title' => 'Number of results',
				'options' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20),
			],
			[
				'type'        => 'select',
				'name' => 'sort',
				'title' => 'Sort By',
				'options' => array('newest' => 'Newest', 'popular' => 'Most Popular', 'random' => 'Random'),
			]
		];

		return $settings;
	}

	/**
	 * Get the path for the preview image for this widget.
	 *
	 * Should be an image of size 210x70, 210x140, or 210x210.
	 *
	 * @return string
	 */
	public function getPreviewImage(){
		// Extend this method in your class and return the path you need.
		// Optional.
		return 'assets/images/previews/blog/templates/widgets/blog/blog-listing.png';
	}

	public function execute(){
		$view = $this->getView();

		$fac = new ModelFactory('PageModel');
		$fac->where('baseurl LIKE /blog/view/%');
		$fac->where('published_status = published');
		$fac->where('published <= ' . \Core\Date\DateTime::NowGMT());
		$fac->limit($this->getSetting('count'));
		switch($this->getSetting('sort')){
			case 'newest':
				$fac->order('published DESC');
				break;
			case 'popular':
				$fac->order('popularity DESC');
				break;
			case 'random':
				$fac->order('RAND()');
				break;
		}

		if(!$fac->count()){
			// If there are no results found, then do not display the widget.
			return '';
		}

		$view->assign('sort', $this->getSetting('sort'));
		$view->assign('title', $this->getSetting('title'));
		$view->assign('links', $fac->get());
	}
} 