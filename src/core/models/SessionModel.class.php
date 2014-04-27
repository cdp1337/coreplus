<?php
/**
 * Defines the schema for the Session table
 *
 * @package Core
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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
 * Model for SessionModel
 *
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 *
 * @author Charlie Powell <charlie@eval.bz>
 * @date 2011-07-24
 */
class SessionModel extends Model {
	public static $Schema = array(
		'session_id' => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 160,
			'required'  => true,
			'null'      => false,
		),
		'user_id'    => array(
			'type'    => Model::ATT_TYPE_UUID,
			'default' => 0,
		),
		'ip_addr'    => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 39,
		),
		'data'       => array(
			'type'    => Model::ATT_TYPE_DATA,
			'default' => null,
			'null'    => true,
		),
		'external_data' => array(
			'type' => Model::ATT_TYPE_DATA,
			'comment' => 'JSON-encoded array of any external data set onto this session.',
			'default' => null,
			'null' => true,
		),
		'created'    => array(
			'type' => Model::ATT_TYPE_CREATED
		),
		'updated'    => array(
			'type' => Model::ATT_TYPE_UPDATED
		)
	);

	public static $Indexes = array(
		'primary' => array('session_id'),
	);

	public function __construct($key = null){
		return parent::__construct($key);
	}

	public function get($k) {
		if ($k == 'data') {
			return $this->getData();
		} else {
			return parent::get($k);
		}
	}

	public function set($k, $v) {
		if ($k == 'data') {
			return $this->setData($v);
		} else {
			parent::set($k, $v);
		}
	}

	/**
	 * Get the data for this session.  Useful for compression :p
	 */
	public function getData() {
		$data     = $this->_data['data'];

		// If the session did not exist before, simply return an empty array.
		if(!$data) return array();

		$unzipped = gzuncompress($data);
		if ($unzipped === false) {
			return $data;
		}
		else {
			return $unzipped;
		}
	}

	/**
	 * Get the JSON-decoded data of the external data on this session.
	 *
	 * @return array
	 */
	public function getExternalData(){
		$ext = $this->get('external_data');

		// Blank values decode as an empty array.
		if($ext == '') return [];

		$json = json_decode($ext, true);

		// Invalid encoded arrays decode as an empty aray.
		if(!$json) return [];

		return $json;
	}

	/**
	 * Set the data for this session.  This will automatically compress the contents.
	 *
	 * @param $data Uncompressed data
	 */
	public function setData($data) {
		$zipped              = gzcompress($data);
		$this->_data['data'] = $zipped;
		// Always cause this to set the dirty flag.
		$this->_dirty = true;
	}

	/**
	 * Set data on this session from an external script or source.
	 *
	 * @param array $data External data to set
	 */
	public function setExternalData($data){
		if(!is_array($data)){
			$this->set('external_data', null);
		}
		elseif(!sizeof($data)){
			$this->set('external_data', null);
		}
		else{
			$this->set('external_data', json_encode($data));
		}
	}
/*
	public function save(){
		// I need to do this here because sessions have a tendency of getting overwritten very quickly.
		// This will sometimes cause "Primary key already exists" errors in the error log
		if($this->exists()){
			return parent::_saveExisting(true);
		}
		else{
			return parent::_saveNew();
		}
	}
*/

}
