<?php
/**
 * File for class BlogWidget definition in the coreplus project
 * 
 * @package Blog
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
 * A short teaser of what BlogWidget does.
 *
 * More lengthy description of what BlogWidget does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for BlogWidget
 * <h4>Example 1</h4>
 * <p>Description 1</p>
 * <code>
 * // Some code for example 1
 * $a = $b;
 * </code>
 *
 *
 * <h4>Example 2</h4>
 * <p>Description 2</p>
 * <code>
 * // Some code for example 2
 * $b = $a;
 * </code>
 *
 * 
 * @package Blog
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class BlogArticleWidget extends \Core\Widget {
	public $is_simple = true;

	public $settings = [
		'title' => 'Blog Articles',
		'sort'  => 'newest',
		'blog'  => '',
		'count' => 5,
	];

	public function getFormSettings(){

		$pages = PageModel::Find(['baseurl LIKE /blog/view/%'], null, 'title');
		$opts = array('' => 'All Blogs');
		foreach($pages as $page){
			$id = substr($page->get('baseurl'), 11);
			$opts[ $id ] = $page->get('title');
		}

		$settings = [
			[
				'type'        => 'text',
				'name'        => 'title',
				'title'       => 'Displayed Title',
				'description' => 'Displayed title on the page where this widget is added to.',
			],
			[
				'type'        => 'select',
				'name'        => 'blog',
				'title'       => 'Blog',
				'options'     => $opts,
				'description' => 'Choose a specific blog if you wish to retrieve posts from a specific blog.',
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

	public function execute(){
		$view = $this->getView();

		$fac = new ModelFactory('PageModel');
		if($this->getSetting('blog')){
			$fac->where('parenturl = /blog/view/' . $this->getSetting('blog'));
		}
		$fac->where('parenturl LIKE /blog/view/%');
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

		$view->assign('count', $this->getSetting('count'));
		$view->assign('sort', $this->getSetting('sort'));
		$view->assign('title', $this->getSetting('title'));
		// The template is expecting an array, if count is 1, only a single Model is returned from the factory.
		$view->assign('links', $this->getSetting('count') == 1 ? [$fac->get()] : $fac->get());
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
		$base = 'assets/images/previews/templates/widgets/blogarticle/execute/';

		$wi = $this->getWidgetInstanceModel();
		$template = $wi === null ? '' : $wi->get('display_template');

		switch($template){
			case 'everything-small.tpl':
				return $base . 'everything-small.png';
			case 'everything-large.tpl':
				return $base . 'everything-large.png';
			case 'unordered-list.tpl':
				return $base . 'unordered-list.png';
			case 'unordered-list-with-date.tpl':
				return $base . 'unordered-list-with-date.png';
			case 'unordered-list-with-thumbnail.tpl':
				return $base . 'unordered-list-with-thumbnail.png';
			default:
				return $base . 'unordered-list.png';
		}
	}
} 