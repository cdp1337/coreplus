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
		'site' => array(
			'type' => Model::ATT_TYPE_SITE,
			'default' => 0,
			'formtype' => 'system',
			'comment' => 'The site id in multisite mode, (or 0 otherwise)',
		),
		'name' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 48,
			'null' => false,
			'required' => true,
			'validation' => array('this', '_validateName')
		),
		'permissions' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'formtype' => 'disabled',
			'comment' => 'json-encoded array of permissions this group has'
		),
		'default' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => false,
			'form' => array(
				'title' => 'Default Group',
				'description' => 'Is this a default user group for new account sign-ups?',
			),
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
		'unique:name' => array('site', 'name'),
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

	public function _validateName($key){
		// there are a few reserved keywords for the name.
		switch(strtolower($key)){
			case 'admin':
			case 'anonymous':
			case 'authenticated':
				return false;
			default:
				return true;
		}
	}
	
} // END class UserGroupModel extends Model
