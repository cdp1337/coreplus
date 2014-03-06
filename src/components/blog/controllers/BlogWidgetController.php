<?php
/**
 * File for class BlogWidgetController definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20140303.0921
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
 * A short teaser of what BlogWidgetController does.
 *
 * More lengthy description of what BlogWidgetController does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for BlogWidgetController
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
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class BlogWidgetController extends Controller_2_1 {

	public function articles_update(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->checkAccess('p:/blog/manage_all')){
			return View::ERROR_ACCESSDENIED;
		}

		$model = new WidgetModel('/blog/articles/' . $request->getParameter(0));
		if(!$model->exists()){
			return View::ERROR_NOTFOUND;
		}

		return $this->_articles_create_update($model);
	}

	public function articles_create(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->checkAccess('p:/blog/manage_all')){
			return View::ERROR_ACCESSDENIED;
		}

		$id = Core::GenerateUUID();
		$model = new WidgetModel('/blog/articles/' . $id);

		return $this->_articles_create_update($model);
	}

	private function _articles_create_update(WidgetModel $model){
		$view = $this->getView();

		// The settings and their default values

		$settings = array();
		foreach($defaults as $key => $def){
			// Load in each saved value, if there is one set.
			$settings[$key] = $model->getSetting($key) !== null ? $model->getSetting($key) : $def;
		}


		$isnew = !$model->exists();
		$form = new Form();
		$form->set('callsmethod', 'BlogWidgetControler::ArticlesSave');

		$form->addElement('system', array('name' => 'id', 'value' => $model->get('id')));

		$form->addElement(
			'text',
			array(
				'name' => 'title',
				'required' => true,
				'title' => 'Admin Title',
				'value' => $model->get('title'),
				'description' => 'Just the identifying title used on admin pages.',
			)
		);

		// The options herein are pic the blog to display from
		// and pick how many articles to show



		$form->addElement('submit', array('value' => ($isnew ? 'Create' : 'Update') . ' Widget'));

		$view->templatename = 'pages/blogwidget/create_update.tpl';
		$view->mastertemplate = 'admin';
		$view->title = ($isnew ? 'Create' : 'Update') . ' Blog Articles Widget';
		$view->assign('form', $form);
	}

	public static function ArticlesSave(Form $form){
		$id = $form->getElement('id')->get('value');

		$model = new WidgetModel('/blog/articles/' . $id);
		$model->set('editurl', '/blogwidget/articles/update/' . $id);
		$model->set('deleteurl', '/blogwidget/articles/delete/' . $id);
		$model->set('title', $form->getElement('title')->get('value'));
		$model->setSetting('album', $form->getElement('album')->get('value'));
		$model->setSetting('count', $form->getElement('count')->get('value'));
		$model->setSetting('order', $form->getElement('order')->get('value'));
		$model->setSetting('dimensions', $form->getElement('dimensions')->get('value'));
		$model->setSetting('uselightbox', $form->getElement('uselightbox')->get('value'));

		$model->save();

		return 'back';
	}
} 