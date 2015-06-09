<?php
/**
 * Defines the schema for the Component table
 *
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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
 * Model for ComponentModel
 *
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 *
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 2011-06-09 01:14:48
 */
class ComponentModel extends Model {
	public static $Schema = array(
		'name'    => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 48,
			'required'  => true,
			'null'      => false,
		),
		'version' => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 24,
			'null'      => false,
		),
		'enabled' => array(
			'type'    => Model::ATT_TYPE_BOOL,
			'default' => '1',
			'null'    => false,
		),
	);

	public static $Indexes = array(
		'primary' => array('name'),
	);

	// @todo Put your code here.

} // END class ComponentModel extends Model
