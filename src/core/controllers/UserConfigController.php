<?php
/**
 * File for class UserConfigController definition in the coreplus project
 *
 * @package Core\User
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130818.2234
 * @copyright Copyright (C) 2009-2016  Charlie Powell
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
 * @package Core\User
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class UserConfigController extends Controller_2_1{
	/**
	 * The main configuration for any user option on the site.
	 *
	 * Displayed under the "Configure" menu.
	 *
	 * @return int
	 */
	public function admin() {
		$view    = $this->getView();
		$request = $this->getPageRequest();

		// This is a super-admin-only page!
		if(!\Core\user()->checkAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}
		
		$userConfigs = [];
		$userSchema = UserModel::GetSchema();
		foreach($userSchema as $k => $dat){
			if(
				$dat['type'] == Model::ATT_TYPE_UUID ||
				$dat['type'] == Model::ATT_TYPE_UUID_FK ||
				$dat['type'] == Model::ATT_TYPE_ID ||
				$dat['type'] == Model::ATT_TYPE_ID_FK ||
				(isset($dat['formtype']) && $dat['formtype'] == 'disabled') ||
				(isset($dat['form']) && isset($dat['form']['type']) && $dat['form']['type'] == 'disabled')
			){
				// Skip these columns.
				continue;
			}
			
			$title = t('STRING_MODEL_USERMODEL_' . strtoupper($k));
			
			$userConfigs[$k] = $title;
		}
		
		// Pull a list of options currently enabled for both registration and edit.
		$onReg = [];
		$onEdits = [];
		
		$curReg = explode('|', ConfigHandler::Get('/user/register/form_elements'));
		$curEdits = explode('|', ConfigHandler::Get('/user/edit/form_elements'));
		
		foreach($curReg as $k){
			if(isset($userConfigs[$k])){
				// It's a valid key in the current application!
				$onReg[] = [
					'key' => $k,
					'checked' => true,
					'title' => $userConfigs[$k],
				];
			}
		}
		foreach($curEdits as $k){
			if(isset($userConfigs[$k])){
				// It's a valid key in the current application!
				$onEdits[] = [
					'key' => $k,
					'checked' => true,
					'title' => $userConfigs[$k],
				];
			}
		}
		
		foreach($userConfigs as $k => $title) {
			// If any key isn't in either curReg and curEdit, tack it to the end of the respective array.
			if(!in_array($k, $curReg)) {
				$onReg[] = [
					'key'     => $k,
					'checked' => false,
					'title'   => $title,
				];
			}
			if(!in_array($k, $curEdits)) {
				$onEdits[] = [
					'key'     => $k,
					'checked' => false,
					'title'   => $title,
				];
			}
		}

		// Build a form to handle the config options themselves.
		// These will include password strength, whether or not captcha is enabled, etc.
		$configs = [
			'/user/displayas', '/user/displayname/anonymous', '/user/email/allowchanging', '/user/enableavatar',
			'/user/password/minlength',
			'/user/password/requirecapitals', '/user/password/requiresymbols', '/user/password/requirenumbers',
			'/user/profileedits/requireapproval',
			'/user/register/allowpublic', '/user/register/requireapproval', '/user/register/requirecaptcha',
		];
		$configform = new Form();

		foreach($configs as $key){
			$el = ConfigHandler::GetConfig($key)->getAsFormElement();
			// I don't need this, (Everything from this group will be on the root-level form).
			$el->set('group', null);
			$configform->addElement($el);
		}

		$authbackends = ConfigHandler::Get('/user/authdrivers');
		if(!$authbackends){
			$authbackendsenabled = [];
		}
		else{
			$authbackendsenabled = explode('|', $authbackends);
		}

		$authbackends = [];
		$available = [];
		foreach(Core::GetComponents() as $c){
			/** @var Component_2_1 $c */
			$available = array_merge($available, $c->getUserAuthDrivers());
		}

		foreach($authbackendsenabled as $k){
			if(!isset($available[$k])){
				continue;
			}

			$classname = $available[$k];

			if(!class_exists($classname)){
				continue;
			}
			try{
				/** @var \Core\User\AuthDriverInterface $class */
				$class = new $classname();
			}
			catch(Exception $e){
				continue;
			}

			$authbackends[] = [
				'name' => $k,
				'class' => $classname,
				'title' => $class->getAuthTitle(),
				'enabled' => true,
			];

			unset($available[$k]);
		}


		foreach($available as $k => $classname){
			if(!class_exists($classname)){
				continue;
			}

			try{
				/** @var \Core\User\AuthDriverInterface $class */
				$class = new $classname();
			}
			catch(Exception $e){
				continue;
			}

			$authbackends[] = [
				'name' => $k,
				'class' => $classname,
				'title' => $class->getAuthTitle(),
				'enabled' => false,
			];
		}


		if($request->isPost()){
			$onEditSelected = (isset($_POST['onedit'])) ? implode('|', $_POST['onedit']) : '';
			$onRegSelected  = (isset($_POST['onregister'])) ? implode('|', $_POST['onregister']) : '';
			$authSelected   = (isset($_POST['authbackend'])) ? implode('|', $_POST['authbackend']) : '';

			if($authSelected == ''){
				\Core\set_message('At least one auth backend is required, re-enabling datastore.', 'info');
				$authSelected = 'datastore';
			}
			
			ConfigHandler::Set('/user/register/form_elements', $onRegSelected);
			ConfigHandler::Set('/user/edit/form_elements', $onEditSelected);
			ConfigHandler::Set('/user/authdrivers', $authSelected);

			// Handle the actual config options too!
			foreach($configs as $key){
				ConfigHandler::Set($key, $_POST['config'][$key]);
			}

			\Core\set_message('Saved configuration options successfully', 'success');
			\Core\reload();
		}

		$view->mastertemplate = 'admin';
		$view->title = 'User Options';
		$view->assign('configform', $configform);
		$view->assign('auth_backends', $authbackends);
		$view->assign('on_register_elements', $onReg);
		$view->assign('on_edit_elements', $onEdits);
	}
}