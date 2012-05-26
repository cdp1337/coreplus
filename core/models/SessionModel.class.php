<?php
/**
 * Defines the schema for the Session table
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
			'maxlength' => 255,
			'required'  => true,
			'null'      => false,
		),
		'user_id'    => array(
			'type'    => Model::ATT_TYPE_INT,
			'default' => 0,
		),
		'ip_addr'    => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 39,
		),
		'data'       => array(
			'type'    => Model::ATT_TYPE_TEXT,
			'default' => null,
			'null'    => true,
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

	// @todo Put your code here.

} // END class ConfigModel extends Model
