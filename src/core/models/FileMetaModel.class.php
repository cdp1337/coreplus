<?php
/**
 * 
 *
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2017  Charlie Powell
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


class FileMetaModel extends Model {
	public static $Schema = array(
		'file' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 192,
			'required' => true,
			'null' => false,
			'form' => array('type' => 'system'),
		),
		'meta_key' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 36,
			'required' => true,
			'comment' => 'The key of this meta tag',
		),
		'meta_value' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'required' => true,
			'comment' => 'Machine version of the value of this meta tag',
		),
		'meta_value_title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 256,
			'required' => true,
			'comment' => 'Human readable version of the value of this meta tag',
		),
	);

	public static $Indexes = array(
		'primary' => array('file', 'meta_key', 'meta_value'),
	);
}
