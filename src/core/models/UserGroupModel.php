<?php
/**
 * Model for UserGroupModel
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

class UserGroupModel extends Model {
	
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_UUID,
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
			'validation' => array('this', '_validateName'),
			'form' => array(
				'description' => 'The name for this group displayed in the admin and user interface.',
			)
		),
		'context' => array(
			'type' => Model::ATT_TYPE_STRING,
			'default' => '',
			'comment' => 'If this group is for a specific context, the Model base name is here.',
			'form' => array(
				'type' => 'select',
				'description' => 'If this group will be used to apply permissions in specific contexts, select it here. The specific context object is selected on the respective user edit page under their groups.',
			)
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

	public function __construct($id = null){

		$this->_linked['UserUserGroup'] = [
			'link' => Model::LINK_HASMANY,
			'on' => ['group_id' => 'id'],
		];

		parent::__construct($id);
	}

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

			// Verify that the permissions being set match this group's context!
			$allperms = Core::GetPermissions();
			$thiscontext = $this->get('context');
			foreach($permissions as $k => $perm){
				if(!isset($allperms[$perm])){
					// Skip invalid permissions!
					unset($permissions[$k]);
				}
				elseif($allperms[$perm]['context'] != $thiscontext){
					// Skip non-matching context permissions.
					unset($permissions[$k]);
				}
				// Else, it's fine!
			}

			// Don't forget to re-index the keys.... just because.
			$permissions = array_values($permissions);

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

	/**
	 * Import the given data into the destination Model.
	 *
	 * @param array   $data            Indexed array of records to import/merge from the external source.
	 * @param array   $options         Any options required for the import, such as merge, key, etc.
	 * @param boolean $output_realtime Set to true to output the log in real time as the import happens.
	 *
	 * @throws Exception
	 *
	 * @return \Core\ModelImportLogger
	 */
	public static function Import($data, $options, $output_realtime = false) {
		$log = new \Core\ModelImportLogger('User Group Importer', $output_realtime);

		$merge = isset($options['merge']) ? $options['merge'] : true;
		$pk    = isset($options['key']) ? $options['key'] : null;

		if(!$pk) {
			throw new Exception(
				'Import requires a "key" field on options containing the primary key to compare against locally.'
			);
		}

		foreach($data as $dat) {

			if($pk == 'name' || $pk == 'id' || $pk == 'ldap_dn') {
				// These are the only two fields on the User object itself.
				$group = UserGroupModel::Find([$pk . ' = ' . $dat[ $pk ]], 1);
			}
			else {
				$group = UserGroupModel::Find(['name = ' . $dat['name']], 1);
			}

			$status_type = $group ? 'Updated' : 'Created';

			if($group && !$merge) {
				$log->duplicate('Skipped group ' . $group->getLabel() . ', already exists and merge not requested');
				// Skip to the next record.
				continue;
			}

			if(!$group) {
				// All incoming groups must have a name!
				if(!isset($dat['name'])) {
					$log->error('Unable to import groups without a name!');
					// Skip to the next record.
					continue;
				}

				// New user!
				$group = new UserGroupModel();
			}
			// No else needed, else is there IS a valid $user object and it's setup ready to go.


			// Handle all the properties for this user!
			foreach($dat as $key => $val){
				// Default Behaviour,
				// save the key into whatever field it was set to go to.
				$group->set($key, $val);
			}

			try {
				$status = $group->save();
			}
			catch(Exception $e) {
				$log->error($e->getMessage());
				// Skip to the next.
				continue;
			}

			if($status) {
				$log->success($status_type . ' group ' . $group->getLabel() . ' successfully!');
			}
			else {
				$log->skip('Skipped group ' . $group->getLabel() . ', no changes detected.');
			}
		}

		$log->finalize();

		return $log;
	}
	
} // END class UserGroupModel extends Model
