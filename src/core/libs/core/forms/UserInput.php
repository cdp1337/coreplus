<?php
/**
 * File for class FormUserInput definition in the coreplus project
 *
 * @package \Core\User
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140407.2212
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

namespace Core\Forms;



/**
 * A short teaser of what FormUserInput does.
 *
 * More lengthy description of what FormUserInput does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 * <h4>Standard Usage</h4>
 * <code>
 * $form->addElement(
 *     'user',
 *     [
 *         'name' => 'user',
 *         'title' => 'User',
 *         'description' => 'Search by user name or email!',
 *     ]
 * );
 * </code>
 *
 * <h4>Retrieve All Users</h4>
 * <p>
 *     This is used to retrieve both active AND inactive users.
 *     By default, inactive users are omitted as this is meant to be used for providing the admin with a list of only
 *     active users, such as post authors or the such.  However, by providing the "data-include-inactive" flag with the
 *     parameters, inactive users will be included in the results.
 * </p>
 *
 * <code>
 * $form->addElement(
 *     'user',
 *     [
 *         'name' => 'user',
 *         'title' => 'User',
 *         'description' => 'Search by user name or email!',
 *         'data-include-inactive' => 1,
 *     ]
 * );
 * </code>
 *
 * @package \Core\User
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class UserInput extends FormElement {
	public function __construct($atts = null){
		parent::__construct($atts);

		// Some defaults
		$defaults = [
			'class' => 'formelement formuserinput',
		];
		foreach($defaults as $k => $v){
			if(!isset($this->_attributes[$k])){
				$this->_attributes[$k] = $v;
			}
		}

		$this->_validattributes = array(
			'accesskey', 'autocomplete', 'dir', 'disabled',
			'lang', 'maxlength', 'placeholder',
			'required', 'size', 'tabindex', 'width', 'height',
			'style'
		);
	}

	/**
	 * Render this form element and return the HTML content.
	 *
	 * @return string
	 */
	public function render() {
		$file = $this->getTemplateName();

		$tpl = \Core\Templates\Template::Factory($file);

		if($this->get('value')){
			/** @var UserModel $user */
			$user = \UserModel::Construct($this->get('value'));
		}
		else{
			$user = null;
		}

		$tpl->assign('element', $this);
		$tpl->assign('can_lookup', \Core\user()->checkAccess('p:/user/search/autocomplete'));
		$tpl->assign('username', ($user ? $user->getDisplayName() : ''));

		return $tpl->fetch();
	}

	/**
	 * This set explicitly handles the value, and has the extended logic required
	 *  for error checking and validation.
	 *
	 * @param string $value The value to set
	 * @return boolean
	 */
	public function setValue($value) {

		$valid = $this->validate($value);
		if($valid !== true){
			$this->_error = $valid;
			return false;
		}

		if(!$value){
			// If it doesn't have a value, then GREAT!
			// The above validation handles if a value is required.
			$this->_attributes['value'] = '';
			return true;
		}

		// This determines what data will be passed in.
		// If the current user can lookup, then it'll be the ID of the user.
		// If the current user can't, then it'll be the full user name of of the user.
		$canlookup = \Core\user()->checkAccess('p:/user/search/autocomplete');

		if(!$canlookup){
			// I need to lookup the username here.
			$uuc = \UserUserConfigModel::Find(['key = username', 'value = ' . $value], 1);
			if(!$uuc){
				$this->_error = 'Invalid user specified!';
				return false;
			}

			$value = $uuc->get('user_id');
		}

		$this->_attributes['value'] = $value;
		return true;
	}
} 