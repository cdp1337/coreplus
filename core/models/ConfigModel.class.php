<?php
/**
 * Defines the schema for the Config table
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
 * Model for ConfigModel
 *
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 *
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class ConfigModel extends Model {
	public static $Schema = array(
		'key'           => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 255,
			'required'  => true,
			'null'      => false,
		),
		'type'          => array(
			'type'    => Model::ATT_TYPE_ENUM,
			'options' => array('string', 'int', 'boolean', 'enum', 'set'),
			'default' => 'string',
			'null'    => false,
		),
		'default_value' => array(
			'type'    => Model::ATT_TYPE_TEXT,
			'default' => null,
			'null'    => true,
		),
		'value'         => array(
			'type'    => Model::ATT_TYPE_TEXT,
			'default' => null,
			'null'    => true,
		),
		'options'       => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 511,
			'default'   => null,
			'null'      => true,
		),
		'description'   => array(
			'type'    => Model::ATT_TYPE_TEXT,
			'default' => null,
			'null'    => true,
		),
		'mapto'         => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 32,
			'default'   => null,
			'comment'   => 'The define constant to map the value to on system load.',
			'null'      => true,
		),
		'created'       => array(
			'type' => Model::ATT_TYPE_CREATED
		),
		'updated'       => array(
			'type' => Model::ATT_TYPE_UPDATED
		)
	);

	public static $Indexes = array(
		'primary' => array('key'),
	);

	/**
	 * Get either the set value or the default value if that is null.
	 *
	 * This value will also be typecasted to the correct type.
	 *
	 * @return mixed
	 */
	public function getValue() {
		$v = $this->get('value');
		if ($v === null) $v = $this->get('default');

		switch ($this->get('type')) {
			case 'int':
				return (int)$v;
			case 'boolean':
				return ($v == '1' || $v == 'true') ? true : false;
			case 'set':
				return array_map('trim', explode('|', $v));
			default:
				return $v;
		}
	}

} // END class ConfigModel extends Model
