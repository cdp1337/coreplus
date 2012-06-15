<?php
/**
 * Standard datamodel-backed user system
 *
 * @package User
 * @since 1.9
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

class User_datamodel_Backend extends User implements User_Backend{
	
	public function checkPassword($password) {
		$hasher = new PasswordHash(15);
		return $hasher->checkPassword($password, $this->_getModel()->get('password'));
	}
	
	/**
	 * Utilize the builtin datamodel systems to look for a facebook user 
	 * that matches the requested clause.
	 * 
	 * @param type $where
	 * @param type $limit
	 * @param type $order
	 * 
	 * @return User_datamodel_Backend 
	 */
	public static function Find($where = array()){
		// Tack on the facebook backend requirement.
		$where['backend'] = 'datamodel';
		
		$res = new self();
		$res->_find($where);
		
		return $res;
	}
	
	
	public static function Register($email, $password, $attributes = array()){
		$ub = new self();
		
		$ub->set('password', $password);
		$ub->set('email', $email);
		//$ub->generateNewApiKey();
		
		// Save the extended attributes or 'UserConfig' options too!
		foreach($attributes as $k => $v){
			$ub->set($k, $v);
		}
		
		// whee!
		$ub->save();
		
		return $ub;
	}
}
