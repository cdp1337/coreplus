<?php
/**
 * DESCRIPTION
 *
 * @package User
 * @since 1.9
 * @author Charlie Powell <charlie@evalagency.com>
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

class UserLoginWidget extends \Core\Widget {

	/**
	 * @deprecated 2012.06.13
	 */
	public function execute() {
		$v = $this->getView();
		
		$u = Core::User();
		
		$v->assign('user', $u);
		$v->assign('loggedin', $u->exists());
		$v->assign('allowregister', ConfigHandler::Get('/user/register/allowpublic'));
	}
	
}
