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


/**
 * Simple model for storing which templates have which optional stylesheet enabled.
 */
class TemplateCssModel extends Model{
	public static $Schema = array(
		'template' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 100,
			'required' => true,
		),
		'css_asset' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 100,
			'required' => true,
		),
		'enabled' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => 0
		)
	);

	public static $Indexes = array(
		'primary' => array('template', 'css_asset'),
	);
}
