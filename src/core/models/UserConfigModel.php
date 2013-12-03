<?php
/**
 * User configuration model
 *
 * @package User
 * @since 1.9
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2013  Charlie Powell
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

class UserConfigModel extends Model{
	public static $Schema = array(
		'key' => array(
			'type' => Model::ATT_TYPE_STRING,
			'required' => true,
			'null' => false,
			'maxlength' => 64,
		),
		'default_name' => array(
			'type' => Model::ATT_TYPE_STRING,
			'required' => false,
			'comment' => 'The default name/title',
		),
		'name' => array(
			'type' => Model::ATT_TYPE_STRING,
			'required' => false,
			'comment' => 'The name/title displayed on the system',
		),
		'formtype' => array(
			'type' => Model::ATT_TYPE_STRING,
			'required' => false,
			'default' => 'text'
		),
		'default_value' => array(
			'type' => Model::ATT_TYPE_TEXT
		),
		'options' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'required' => false,
			'null' => true
		),
		'default_weight' => array(
			'type' => Model::ATT_TYPE_INT,
			'default' => 0,
		),
		'weight' => array(
			'type' => Model::ATT_TYPE_INT,
			'default' => 0,
		),
		'default_onregistration' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => true
		),
		'onregistration' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => true
		),
		'default_onedit' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => true
		),
		'onedit' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => true
		),
		'searchable' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => 0,
		),
		'required' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => 0,
		),
		/*'system' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => false
		),*/
		'validation' => array(
			'type' => Model::ATT_TYPE_STRING,
			'formtype' => 'disabled',
			'comment' => 'Class or function to call on validation',
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
		'primary' => array('key'),
		'searchable' => array('searchable'),
	);
}
