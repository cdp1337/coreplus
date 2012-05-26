<?php
/**
 * Defines the schema for the UpdateSite table
 *
 * @package Core Plus\Core
 * @author Charlie Powell <powellc@powelltechs.com>
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

/**
 * Description of UpdateSiteModel
 *
 * @author powellc
 */
class UpdateSiteModel extends Model{
	
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'url' => array(
			'type' => Model::ATT_TYPE_STRING,
			'required' => true,
			'null' => false
		),
		'enabled' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'null' => false,
			'default' => true
		),
		'username' => array(
			'type' => Model::ATT_TYPE_STRING,
			'required' => false,
			'null' => true
		),
		'password' => array(
			'type' => Model::ATT_TYPE_STRING,
			'required' => false,
			'null' => true
		),
		'created' => array(
			'type' => Model::ATT_TYPE_CREATED
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_UPDATED
		)
	);
	
	public static $Indexes = array(
		'primary' => array('id'),
		'unique:url' => array('url'),
	);


	/**
	 * Test function to see if this update site can be connected to and contains a valid repo.xml.gz file.
	 *
	 * @return boolean
	 */
	public function isValid(){
		$remote = new File_remote_backend();
		$remote->password = $this->get('password');
		$remote->username = $this->get('username');
		$remote->setFilename($this->get('url') . '/repo.xml.gz');

		if(!$remote->exists()){
			return false;
		}

		// I should probably do additional checks here, but eh.....
		return true;
	}
	
}
