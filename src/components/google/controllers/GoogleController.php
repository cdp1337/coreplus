<?php
/**
 * File for class GoogleController definition in the coreplus-ocs project
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130528.1455
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
 * A short teaser of what GoogleController does.
 *
 * More lengthy description of what GoogleAPIController does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for GoogleController
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
class GoogleController extends Controller_2_1 {
	/**
	 * View to set google API keys and other configuration options.
	 */
	public function configure(){

	}

	public function configure_maps(){

	}

	public function configure_analytics(){
		$view = $this->getView();

		$form = new Form();
		$form->addElement(
			'text',
			[
				'title' => 'Property ID',
				'required' => true,
				'value' => \ConfigHandler::Get('/google-analytics/accountid'),
			]
		);
		$form->addElement('submit', ['name' => 'submit', 'value' => 'Update']);

		$view->assign('form', $form);
	}
}