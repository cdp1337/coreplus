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

	/**
	 * Widget to display a simple blog search box
	 */
	public function execute(){
		$view = $this->getView();

		$url = Core::ResolveLink('/blog');

		if(PageRequest::GetSystemRequest()->getBaseURL() == '/blog' && PageRequest::GetSystemRequest()->getParameter('q')){
			$query = PageRequest::GetSystemRequest()->getParameter('q');
		}
		else{
			$query = null;
		}

		$view->assign('url', $url);
		$view->assign('query', $query);
	}
} 