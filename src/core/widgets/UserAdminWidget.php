<?php
/**
 * File for class UserAdminWidget definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130422.1320
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


/**
 * Provides some useful widgets for the admin dashboard.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for UserAdminWidget
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
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class UserAdminWidget extends \Core\Widget {
	/**
	 * Widget to display some of the newest registrations, (if any).
	 */
	public function newestSignups(){
		if(!\Core\user()->checkAccess('p:/user/users/manage')){
			 return '';
		}

		// How far back do I want to search for?
		// 1 month sounds good!
		$date = new CoreDateTime();
		$date->modify('-1 month');
		$searches = UserModel::Find(['created > ' . $date->getFormatted('U')], 10, 'created DESC');

		// No results?  No problem :)
		if(!sizeof($searches)) return '';

		$view = $this->getView();
		$view->assign('enableavatar', (\ConfigHandler::Get('/user/enableavatar')));
		$view->assign('users', $searches);
	}
}