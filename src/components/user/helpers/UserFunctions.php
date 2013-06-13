<?php
/**
 * Provides common user functionality, such as registration form generation and any logic required by
 * both widget and controller.
 *
 * @package User
 * @since 2.0
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
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


namespace User;

/**
 * Get the form object for registrations.
 *
 * @return \Form
 */
function get_registration_form(){
	return get_form(null);
}

/**
 * Get the form object for editing users.
 *
 * @param \User_Backend $user
 *
 * @return \Form
 */
function get_edit_form(\User_Backend $user){
	return get_form($user);
}

/**
 * @param \User_Backend|null $user
 *
 * @return \Form
 */
function get_form($user = null){
	$form = new \Form();
	if($user === null) $user = \User::Factory();

	$type = ($user->exists()) ? 'edit' : 'registration';

	if($type == 'registration'){
		$form->set('callsMethod', 'UserHelper::RegisterHandler');
	}
	else{
		$form->set('callsMethod', 'UserHelper::UpdateHandler');
		$form->addElement('system', array('name' => 'id', 'value' => $user->get('id')));
	}

	// Because the user system may not use a traditional Model for the backend, (think LDAP),
	// I cannot simply do a setModel() call here.

	// Only enable email changes if the current user is an admin or it's new.
	if($type == 'registration' || \Core\user()->checkAccess('p:user_manage')){
		$form->addElement('text', array('name' => 'email', 'title' => 'Email', 'required' => true, 'value' => $user->get('email')));
	}

	// Tack on the active option if the current user is an admin.
	if(\Core\user()->checkAccess('p:user_manage')){
		$form->addElement(
			'checkbox',
			array(
				'name' => 'active',
				'title' => 'Active',
				'checked' => $user->get('active'),
			)
		);

	}

	// Passwords are for registrations only, at least here.
	if($type == 'registration'){
		// If the user is a manager, the new account can be created with an empty password.
		// That user will get the prompt to set an initial password on login via the forgot password logic.
		$passrequired = \Core\user()->checkAccess('p:user_manage') ? false : true;
		$form->addElement(
			'password',
			array('name' => 'pass', 'title' => 'Password', 'required' => $passrequired)
		);
		$form->addElement(
			'password',
			array('name' => 'pass2', 'title' => 'Confirm', 'required' => $passrequired)
		);
	}

	// Avatar is for existing accounts or admins.
	if($type == 'edit' || \Core\user()->checkAccess('p:user_manage')){
		// Only if enabled.
		if(\ConfigHandler::Get('/user/enableavatar')){
			$form->addElement('file', array('name' => 'avatar', 'title' => 'Avatar Image', 'basedir' => 'public/user', 'accept' => 'image/*', 'value' => $user->get('avatar')));
		}
	}

	// For non-admins, the factory depends on the registration type as well.
	if(\Core\user()->checkAccess('p:user_manage')){
		$fac = \UserConfigModel::Find(array(), null, 'weight ASC');
	}
	elseif($type == 'registration'){
		$fac = \UserConfigModel::Find(array('onregistration' => 1), null, 'weight ASC');
	}
	else{
		$fac = \UserConfigModel::Find(array('onedit' => 1), null, 'weight ASC');
	}

	foreach($fac as $f){
		$key = $f->get('key');
		$val = ($user->get($key) === null) ? $f->get('default_value') : $user->get($key);

		$el = \FormElement::Factory($f->get('formtype'));
		$el->set('name', 'option[' . $key . ']');
		$el->set('title', $f->get('name'));
		$el->set('value', $val);
		if($f->get('required')) $el->set('required', true);

		switch($f->get('formtype')){
			case 'file':
				$el->set('basedir', 'public/user/');
				break;
			case 'checkboxes':
			case 'select':
			case 'radio':
				$opts = array_map('trim', explode('|', $f->get('options')));
				$el->set('options', $opts);
				break;
			case 'checkbox':
				$el->set('value', 1);
				$el->set('checked', ($val ? true : false));
				break;
		}

		$form->addElement($el);
		//var_dump($f);
	}

	if(\Core\user()->checkAccess('g:admin')){
		$form->addElement(
			'checkbox',
			array(
				'name' => 'admin',
				'title' => 'Admin Account',
				'checked' => $user->get('admin'),
			)
		);
	}

	// Tack on the group registration if the current user is an admin.
	if(\Core\user()->checkAccess('p:user_manage')){
		// Find all the groups currently on the site.

		$where = array();
		if(\Core::IsComponentAvailable('enterprise') && \MultiSiteHelper::IsEnabled()){
			$where['site'] = \MultiSiteHelper::GetCurrentSiteID();
		}

		$groups = \UserGroupModel::Find($where, null, 'name');

		if(sizeof($groups)){
			$groupopts = array();
			foreach($groups as $g){
				$groupopts[$g->get('id')] = $g->get('name');
			}

			$form->addElement(
				'checkboxes',
				array(
					'name' => 'groups[]',
					'title' => 'Group Memebership',
					'options' => $groupopts,
					'value' => $user->getGroups()
				)
			);
		}

	}

	// If the config is enabled and the current user is guest...
	if($type == 'registration' && \ConfigHandler::Get('/user/register/requirecaptcha') && !\Core\user()->exists()){
		$form->addElement('captcha');
	}

	$form->addElement(
		'submit',
		[
			'value' => (($type == 'registration') ? 'Register' : 'Update'),
			'name' => 'submit',
		]
	);

	// @todo Implement a hook handler here for UserPreRegisterForm

	return $form;
}