<?php
/**
 * The user access string form element, used in both the traditional form system and the ajax version.
 *
 * @package User
 * @since 1.9
 * @author Charlie Powell <charlie@evalagency.com>
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

namespace Core\Forms;

/**
 * Handles the form element for access strings.
 */
class AccessStringInput extends FormElement {
	/**
	 * Holds the base name of this form group
	 * @var string
	 */
	private $_targetname = null;

	public function __construct($atts = null) {
		parent::__construct($atts);

		// Some defaults
		$this->_attributes['class'] = 'formelement formaccessstringinput formradioinput';
	}

	/**
	 * Standard render function for this form element
	 *
	 * @return string
	 */
	public function render() {
		static $renderedcount = 0;

		$renderedcount++;
		$this->_targetname = '_formaccessstring' . $renderedcount . \Core\random_hex(8);

		$v               = trim($this->get('value'));
		$checked         = 'advanced';
		$advanced_groups = array();
		$type            = 'whitelist';

		if ($v == '*') {
			$checked = 'basic_anyone';
		} elseif ($v == '!*') {
			$checked = 'basic_admin';
		}
		elseif (!$v) {
			// Blank value
			$checked = 'advanced';
		}
		elseif ($v == 'g:anonymous') {
			$checked = 'basic_anonymous';
		}
		elseif ($v == 'g:authenticated') {
			$checked = 'basic_authenticated';
		}
		elseif ($v == 'none') {
			// Allow for a blank value.
			$checked = null;
			$type    = null;
		}
		else {
			// Determine the sub groups checked.
			$checked = 'advanced';
			$parts   = array_map('trim', explode(';', $v));
			foreach ($parts as $p) {
				if ($p == '*') {
					// If a wildcard is present, mark the groups as ones to blacklist.
					$type = 'blacklist';
					continue;
				}
				list($t, $tv) = explode(':', $p);
				// Trim off the '!' in front of it, it'll be picked up by the presence of the '*' at the end.
				if ($tv{0} == '!') $tv = substr($tv, 1);
				$advanced_groups[] = $tv;
			}
		}

		$groups = array();

		// Tack on the system groups.
		$anongroup = new \UserGroupModel();
		$anongroup->setFromArray(
			array(
				'id'   => 'anonymous',
				'name' => 'Anonymous Users'
			)
		);

		$authgroup = new \UserGroupModel();
		$authgroup->setFromArray(
			array(
				'id'   => 'authenticated',
				'name' => 'Authenticated Users'
			)
		);
		$groups[] = $anongroup;
		$groups[] = $authgroup;

		// Find all the groups currently on the site.
		$groups = array_merge($groups, \UserGroupModel::Find(null, null, 'name'));
		foreach ($groups as $k => $v) {
			if (in_array($v->get('id'), $advanced_groups)) $v['checked'] = true;
		}


		$tpl = \Core\Templates\Template::Factory($this->getTemplateName());
		$tpl->assign('element', $this);
		$tpl->assign('groups', $groups);
		$tpl->assign('dynname', $this->_targetname);
		$tpl->assign('main_checked', $checked);
		$tpl->assign('advanced_type', $type);

		return $tpl->fetch();
	}

	/**
	 * Get the rendered value directly from the POST value
	 * Returns the access string as a single string value.
	 *
	 * @param array $src
	 *
	 * @return string
	 */
	public function lookupValueFrom(&$src) {

		// I'll let the parent do all the work.
		$val = parent::lookupValueFrom($src);

		// In addition, I have to sift through the rest of the options for this system.
		switch ($val) {
			case 'basic_anyone':
				return '*';
			case 'basic_admin':
				return '!*';
			case 'basic_anonymous':
				return 'g:anonymous';
			case 'basic_authenticated':
				return 'g:authenticated';
			case '':
				return null;
		}

		// No?  alright, must be advanced...
		if (!isset($src[$this->_targetname . '_type'])) $src[$this->_targetname . '_type'] = 'whitelist';

		$bool   = ($src[$this->_targetname . '_type'] == 'whitelist') ? '' : '!';
		$groups = array();

		// Get each group selected, (if the user selected any)
		if(isset($src[$this->_targetname]) && is_array($src[$this->_targetname])){
			foreach ($src[$this->_targetname] as $g) {
				$groups[] = 'g:' . $bool . $g;
			}
		}
		// And the white/black list itself.
		if ($src[$this->_targetname . '_type'] != 'whitelist') $groups[] = '*';

		return implode(';', $groups);
	}
}