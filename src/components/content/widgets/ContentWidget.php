<?php
/**
 * File for class ContentWidget definition in the Core Plus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20150121.2343
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
 * A short teaser of what ContentWidget does.
 *
 * More lengthy description of what ContentWidget does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for ContentWidget
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
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class ContentWidget extends \Core\Widget {
	public $is_simple = true;
	
	public $title = 'Content Widget';

	public $settings = [
		[
			'type'        => 'text',
			'name'        => 'title',
			'title'       => 'Displayed Title',
			'description' => 'Displayed title on the page where this widget is added to.',
			'value'       => 'Content Title',
		],
		[
			'type'  => 'wysiwyg',
			'name'  => 'content',
			'title' => 'Widget Content',
			'value' => '',
		],
	];

	public function getFormSettings(){
		return $this->settings;
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
		return 'assets/images/previews/templates/widgets/content/custom-content-area.png';
	}

	/**
	 * Widget to display a simple site search box
	 */
	public function execute(){
		$view = $this->getView();
		
		if(false && \Core\user()->checkAccess('g:admin')){
			/*if(($wi = $this->getWidgetInstanceModel())){
				$this->addControl('Edit Display Options', '/widget/instance/update/' . $wi->get('id'), 'desktop');
			}*/
			///widget/update?baseurl=/content/execute/0537-d831de842e7-3044
			var_dump($this); die();
			$this->addControl('Settings', '/widget/update?baseurl=' . $m->get('id'), 'edit');
		}

		$view->assign('title', $this->getSetting('title'));
		$view->assign('content', \Core\parse_html($this->getSetting('content')));
	}
}