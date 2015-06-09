<?php
/**
 * File for class GalleryWidgetController definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130808.1133
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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
 * A short teaser of what GalleryWidgetController does.
 *
 * More lengthy description of what GalleryWidgetController does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for GalleryWidgetController
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
class GalleryWidgetController extends Controller_2_1{
	/**
	 * Page to display and manage all gallery widgets.
	 */
	public function admin(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->checkAccess('p:/gallery/manage_all')){
			return View::ERROR_ACCESSDENIED;
		}

		$factory = new ModelFactory('WidgetModel');
		$factory->where('baseurl LIKE /gallery/view/%');
		$factory->order('title');
		$widgets = $factory->get();

		$view->title = 'Gallery Widgets';
		$view->assign('can_manage_theme', \Core\user()->checkAccess('g:admin'));
		$view->assign('widgets', $widgets);

		$view->addControl('Create Gallery Widget', '/gallerywidget/update', 'add');
	}

	/**
	 * Direct alias of update.
	 */
	public function create(){
		return $this->update();
	}

	/**
	 * Page for creating and updating a gallery widget
	 */
	public function update(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->checkAccess('p:/gallery/manage_all')){
			return View::ERROR_ACCESSDENIED;
		}

		if($request->getParameter(0)){
			$model = new WidgetModel('/gallery/view/' . $request->getParameter(0));
		}
		else{
			$model = new WidgetModel();
		}

		// The settings and their default values
		$defaults = array(
			'album' => '',
			'count' => 5,
			'order' => 'weight',
			'dimensions' => '100x75',
			'uselightbox' => false,
		);
		$settings = array();
		foreach($defaults as $key => $def){
			$settings[$key] = $model->getSetting($key) ? $model->getSetting($key) : $def;
		}


		$isnew = !$model->exists();
		$form = new Form();
		$form->set('callsmethod', 'GalleryFormHandler::SaveWidgetHandler');

		$form->addElement('system', array('name' => 'id', 'value' => $request->getParameter(0)));

		$form->addElement(
			'text',
			array(
				'name' => 'title',
				'required' => true,
				'title' => 'Widget Title',
				'value' => $model->get('title'),
				'description' => 'Just the identifying title used on admin pages.',
			)
		);

		// The options herein are pic the gallery to display from,
		// pick how many images to show,
		// and order to retrieve them.

		$albums = GalleryAlbumModel::Find(null, null, 'title');
		$albumopts = array('' => 'All Galleries');
		foreach($albums as $album){
			$albumopts[ $album->get('id') ] = $album->get('title');
		}
		$form->addElement(
			'select',
			array(
				'name' => 'album',
				'value' => $settings['album'],
				'title' => 'Gallery Album',
				'options' => $albumopts
			)
		);

		$form->addElement(
			'select',
			array(
				'name' => 'count',
				'title' => 'Number of thumbnails',
				'value' => $settings['count'],
				'options' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20),
			)
		);

		$form->addElement(
			'select',
			array(
				'name' => 'order',
				'title' => 'Order By',
				'value' => $settings['order'],
				'options' => array('weight' => 'Standard Order', 'created desc' => 'Date (Newest First)', 'random' => 'Random'),
			)
		);

		$form->addElement(
			'text',
			array(
				'name' => 'dimensions',
				'title' => 'Thumbnail Dimensions',
				'value' => $settings['dimensions'],
				'description' => 'Enter the desired thumbnail dimensions, in the format of (for example), 100x75, width then height separated by an "x" and no spaces.'
			)
		);

		if(Core::IsComponentAvailable('jquery-lightbox')){
			$form->addElement(
				'checkbox',
				array(
					'name' => 'uselightbox',
					'checked' => $settings['uselightbox'],
					'value' => 1,
					'title' => 'Use Lightbox',
					'description' => 'Check to open images in a lightbox window for quick previewing.'
				)
			);
		}
		else{
			$form->addElement('hidden', array('name' => 'uselightbox', 'value' => 0));
		}


		$form->addElement('submit', array('value' => ($isnew ? 'Create' : 'Update') . ' Widget'));

		$view->templatename = 'pages/gallerywidget/update.tpl';
		$view->mastertemplate = 'admin';
		$view->title = ($isnew ? 'Create' : 'Update') . ' Gallery Widget';
		$view->assign('form', $form);

		$view->addControl('Gallery Widgets', '/gallerywidget/admin', 'directory');
	}

	public function delete(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->checkAccess('p:/gallery/manage_all')){
			return View::ERROR_ACCESSDENIED;
		}

		if($request->getParameter(0)){
			$model = new WidgetModel('/gallery/view/' . $request->getParameter(0));
		}
		else{
			$model = new WidgetModel();
		}

		if(!$request->isPost()){
			return View::ERROR_BADREQUEST;
		}

		if(!$model->exists()){
			return View::ERROR_NOTFOUND;
		}

		$model->delete();
		\Core\go_back();
	}
}