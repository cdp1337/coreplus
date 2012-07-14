<?php
/**
 * Model for UserGroupModel
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

class UserGroupModel extends Model {
	
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'name' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 48,
			'null' => false,
			'required' => true,
		),
		'permissions' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'formtype' => 'disabled',
			'comment' => 'json-encoded array of permissions this group has'
		),
		'created' => array(
			'type' => Model::ATT_TYPE_CREATED,
			'null' => false,
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_UPDATED,
			'null' => false,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
		'unique:name' => array('name'),
	);

	/**
	 * Get all the permissions this group as assigned to it.
	 *
	 * @return array
	 */
	public function getPermissions(){
		$p = json_decode($this->get('permissions'), true);

		return $p ? $p : array();
	}

	public function setPermissions($permissions){
		if(sizeof($permissions) == 0){
			$this->set('permissions', '');
		}
		else{
			$this->set('permissions', json_encode($permissions));
		}
	}
	
} // END class UserGroupModel extends Model
