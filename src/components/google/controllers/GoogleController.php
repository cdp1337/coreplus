<?php
/**
 * File for class GoogleController definition in the coreplus-ocs project
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130528.1455
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
		require_once(ROOT_PDIR . 'core/libs/core/configs/functions.php');

		$view = $this->getView();
		$request = $this->getPageRequest();

		$configs = [
			'general' => [
				'title' => 'General',
			    'configs' => ['/google/services/public_api_key'],
			],
			'analytics' => [
				'title' => 'Analytics',
				'configs' => ['/google-analytics/accountid', '/google/tagmanager/tagid'],
			],
			'maps' => [
				'title' => 'Maps',
				'configs' => ['/googlemaps/enterprise/privatekey', '/googlemaps/enterprise/clientname'],
			],
			'cse' => [
				'title' => 'Custom Search',
				'configs' => ['/google/cse/key'],
			]
		];

		$form = new Form();
		$form->set('callsmethod', 'GoogleController::ConfigureSave');

		foreach($configs as $gk => $gdat){
			$group = new FormTabsGroup(['name' => $gk, 'title' => $gdat['title']]);
			foreach($gdat['configs'] as $c){
				/** @var ConfigModel $config */
				$config = ConfigHandler::GetConfig($c);
				$group->addElement($config->getAsFormElement());
			}
			$form->addElement($group);
		}

		$form->addElement('submit', ['name' => 'submit', 'value' => 'Update Settings']);

		$view->title = 'Google Keys and Apps ';
		$view->assign('form', $form);
	}

	public static function ConfigureSave(Form $form) {
		foreach($form->getElements() as $el){
			/** @var $el FormElement */
			$n = $el->get('name');

			// I only want config options here.
			if(strpos($n, 'config[') !== 0) continue;

			// Trim off the "config[]" wrapper.
			$k = substr($n, 7, -1);
			ConfigHandler::Set($k, $el->get('value'));
		}

		Core::SetMessage('Saved configuration options', 'success');
		return true;
	}
}