<?php
/**
 * File for class BlogWidget definition in the coreplus project
 * 
 * @package Blog
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20140228.1328
 * @copyright Copyright (C) 2009-2013  Author
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
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class BlogSearchWidget extends Widget_2_1 {
	public $is_simple = true;

	public $settings = [
		'title' => 'Blog Articles',
		'blog'  => '',
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
		];

		return $settings;
	}

	/**
	 * Widget to display a simple blog search box
	 */
	public function execute(){
		$view = $this->getView();

		if($this->getSetting('blog')){
			$urlbase = '/blog/view/' . $this->getSetting('blog');
		}
		else{
			$urlbase = '/blog';
		}
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