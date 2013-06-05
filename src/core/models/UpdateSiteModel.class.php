<?php
/**
 * Defines the schema for the UpdateSite table
 *
 * @package Core Plus\Core
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

class UpdateSiteModel extends Model {

	/**
	 * Remote file.
	 * @var null|\Core\Filestore\File
	 */
	private $_remotefile = null;

	public static $Schema = array(
		'id'       => array(
			'type'     => Model::ATT_TYPE_ID,
			'required' => true,
			'null'     => false,
		),
		'url'      => array(
			'type'     => Model::ATT_TYPE_STRING,
			'required' => true,
			'null'     => false,
			'form' => array(
				'title' => 'Repository URL',
			),
		),
		/*
		'enabled'  => array(
			'type'    => Model::ATT_TYPE_BOOL,
			'null'    => false,
			'default' => true
		),
		*/
		'username' => array(
			'type'     => Model::ATT_TYPE_STRING,
			'required' => false,
			'null'     => true,
			'form' => array(
				'description' => '[OPTIONAL] - If you require a username to access the repo, set it here.',
			),
		),
		'password' => array(
			'type'     => Model::ATT_TYPE_STRING,
			'required' => false,
			'null'     => true,
			'form' => array(
				'type' => 'password',
				'description' => '[OPTIONAL] - If you require a password to access the repo, set it here.',
			),
		),
		'description' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'formtype' => 'disabled',
			'comment' => 'Cached description from the repo.xml.gz metadata.',
		),
		'created'  => array(
			'type' => Model::ATT_TYPE_CREATED
		),
		'updated'  => array(
			'type' => Model::ATT_TYPE_UPDATED
		)
	);

	public static $Indexes = array(
		'primary'    => array('id'),
		'unique:url' => array('url'),
	);


	/**
	 * Test function to see if this update site can be connected to and contains a valid repo.xml.gz file.
	 *
	 * @return boolean
	 */
	public function isValid() {
		$remote = $this->getFile();

		if (!$remote->exists()) {
			return false;
		}

		// I should probably do additional checks here, but eh.....
		return true;
	}

	/**
	 * Get the remote file for this update site
	 *
	 * @return \Core\Filestore\File
	 */
	public function getFile(){
		if($this->_remotefile === null){
			$this->_remotefile           = new \Core\Filestore\Backends\FileRemote();
			$this->_remotefile->password = $this->get('password');
			$this->_remotefile->username = $this->get('username');
			$this->_remotefile->setFilename($this->get('url') . '/repo.xml.gz');
		}

		return $this->_remotefile;
	}

}
