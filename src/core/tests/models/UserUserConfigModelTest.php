<?php
/**
 * @todo Enter a meaningful file description here!
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20141204.2129
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

use PHPUnit\Framework\TestCase;
 

class UserUserConfigModelTest extends TestCase {
	public function testLinkedRecord1(){
		// Just get one UserUserConfigModel
		$model = UserUserConfigModel::Find([], 1);

		if(!$model){
			$this->markTestSkipped('No users found on the system, is Core installed?');
			return;
		}

		// Get the user by the linked key
		$user = $model->getLink('user_id');
		$this->assertInstanceOf('UserModel', $user);
	}
}
 