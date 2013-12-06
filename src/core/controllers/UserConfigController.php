<?php
/**
 * File for class UserConfigController definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130818.2234
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
 * A short teaser of what UserConfigController does.
 *
 * More lengthy description of what UserConfigController does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for UserConfigController
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
class UserConfigController extends Controller_2_1{
	public function admin() {
		$view    = $this->getView();
		$request = $this->getPageRequest();

		// This is a super-admin-only page!
		if(!\Core\user()->checkAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}

		require_once(ROOT_PDIR . 'core/libs/core/configs/functions.php');

		// Pull all the user configs from the database.
		$userconfigs = UserConfigModel::Find([], null, 'weight');

		// Build a form to handle the config options themselves.
		// These will include password strength, whether or not captcha is enabled, etc.
		$configs = [
			'/user/displayas', '/user/displayname/anonymous', '/user/email/allowchanging', '/user/enableavatar',
			'/user/password/minlength',
			'/user/password/requirecapitals', '/user/password/requiresymbols', '/user/password/requirenumbers',
			'/user/profileedits/requireapproval',
			'/user/register/allowpublic', '/user/register/requireapproval', '/user/register/requirecaptcha',
			'/user/authdrivers',
		];
		$configform = new Form();

		foreach($configs as $key){
			$el = \Core\Configs\get_form_element_from_config(ConfigModel::Construct($key));
			// I don't need this, (Everything from this group will be on the root-level form).
			$el->set('group', null);
			$configform->addElement($el);
		}


		if($request->isPost()){
			// If the request was a post... handle that by running through each config option and looking it up
			// in the POST data.

			$keymap = array_keys($_POST['name']);

			foreach($userconfigs as $config){
				/** @var $config UserConfigModel */
				$k = $config->get('key');

				$config->set('name', $_POST['name'][$k]);
				$config->set('onregistration', (isset($_POST['onregistration'][$k])) );
				$config->set('onedit', (isset($_POST['onedit'][$k])) );
				$config->set('weight', array_search($k, $keymap));

				$config->save();
			}

			// Handle the actual config options too!
			foreach($configs as $key){
				$config = ConfigModel::Construct($key);
				$config->set('value', $_POST['config'][$key]);
				$config->save();
			}

			Core::SetMessage('Saved configuration options successfully', 'success');
			\Core\reload();
		}






		$view->mastertemplate = 'admin';
		$view->title = 'User Options';
		$view->assign('configs', $userconfigs);
		$view->assign('configform', $configform);
	}
}