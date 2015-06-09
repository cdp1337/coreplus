<?php
/**
 * File for class ContentAdminWidget definition in the coreplus project
 *
 * @package Content
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140405.0251
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
 * A short teaser of what ContentAdminWidget does.
 *
 * More lengthy description of what ContentAdminWidget does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for ContentAdminWidget
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
 * @package Content
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class ContentAdminWidget extends Widget_2_1 {
	/**
	 * Widget to quickly create a new Content page as a draft.
	 */
	public function quickdraft(){
		$view = $this->getView();

		if(!\Core\user()->checkAccess('p:/content/manage_all')){
			// Users who do not have access to manage page content do not get this.
			return '';
		}

		$form = new Form();
		$form->set('orientation', 'vertical');
		$form->set('callsmethod', 'ContentAdminWidget::QuickDraftSave');
		$form->addElement(
			'text',
			[
				'name' => 'title',
				'placeholder' => 'Page or Post Title',
			]
		);
		$form->addElement(
			'textarea',
			[
				'name' => 'content',
				'placeholder' => "What's up?",
				'cols' => 50,
			]
		);
		$form->addElement('submit', ['value' => 'Save Draft']);


		// Load in all the pages on the site that are currently set as draft too, why not? ;)
		$drafts = PageModel::Find(['published_status = draft'], 10, 'updated DESC');

		$view->assign('form', $form);
		$view->assign('drafts', $drafts);
	}

	/**
	 * Form Handler to save a content quick create.
	 *
	 * @param Form $form
	 *
	 * @return string|bool
	 */
	public static function QuickDraftSave(Form $form){

		if(!$form->getElementValue('title')){
			Core::SetMessage('All pages must have titles.', 'error');
			return false;
		}

		/** @var $model ContentModel */
		$model = new ContentModel();
		/** @var $page PageModel Page object for this model, already linked up! */
		$page = $model->getLink('Page');

		// The content nickname is derived from the page title.
		$model->set('nickname', $form->getElementValue('title'));
		$model->save();

		$ins = new InsertableModel();
		$ins->set('site', $page->get('site'));
		$ins->set('baseurl', '/content/view/' . $model->get('id'));
		$ins->set('name', 'body');
		$ins->set('value', '<p>' . nl2br($form->getElementValue('content')) . '</p>');
		$ins->save();

		$page->set('title', $form->getElementValue('title'));
		$page->set('published_status', 'draft');
		$page->set('editurl', '/content/edit/' . $model->get('id'));
		$page->set('deleteurl', '/content/delete/' . $model->get('id'));
		$page->set('component', 'content');
		$page->save();

		return true;
	}
} 