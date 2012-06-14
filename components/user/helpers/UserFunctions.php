<?php
/**
 * Provides common user functionality, such as registration form generation and any logic required by
 * both widget and controller.
 *
 * @package
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
 * @return Form
 */
function get_registration_form(){
	$form = new \Form();
	$form->set('callsMethod', 'UserController::_RegisterHandler');
	// Because the user system may not use a traditional Model for the backend, (think LDAP),
	// I cannot simply do a setModel() call here.
	$form->addElement('text', array('name' => 'email', 'title' => 'Email', 'required' => true));
	$form->addElement('password', array('name' => 'pass', 'title' => 'Password', 'required' => true));
	$form->addElement('password', array('name' => 'pass2', 'title' => 'Confirm', 'required' => true));

	$fac = \UserConfigModel::Find(array('onregistration' => 1));
	foreach($fac as $f){
		$el = \FormElement::Factory($f->get('formtype'));
		$el->set('name', 'option[' . $f->get('key') . ']');
		$el->set('title', $f->get('name'));
		$el->set('value', $f->get('default_value'));

		switch($f->get('formtype')){
			case 'file':
				$el->set('basedir', 'public/user/');
				break;
			case 'select':
				$opts = array_map('trim', explode('|', $f->get('options')));
				$el->set('options', $opts);
				break;
		}

		$form->addElement($el);
		//var_dump($f);
	}

	$form->addElement('submit', array('value' => 'Register'));

	// Do something with /user/register/requirecaptcha

	// @todo Implement a hook handler here for UserPreRegisterForm

	return $form;
}