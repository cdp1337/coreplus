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

class ThemeTemplateChangeModel extends Model {
	/**
	 * Schema definition for ThemeEditorItemModel
	 *
	 * @static
	 * @var array
	 */
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID
		),
		'filename' => array(
			'type' => Model::ATT_TYPE_STRING,
			'required' => true,
			'formtype' => 'system',
		),
		'content' => array(
			'type' => Model::ATT_TYPE_DATA,
			'required' => false,
			'form' => array(
				'type' => 'textarea',
			),
		),
		'content_md5' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 32,
			'comment' => 'An MD5 of the content, (used for external change verification checks)',
			'formtype' => 'disabled',
		),
		'comment' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'form' => array(
				'title' => 'Change Comment',
				'description' => 'If you want to comment about this change, enter something short and meaningful here.',
			),
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_UPDATED,
			'null' => false,
		),
	);

	/**
	 * Index definition for ThemeTemplateChangeModel
	 *
	 * @static
	 * @var array
	 */
	public static $Indexes = array(
		'primary' => array('id'),
	);
}