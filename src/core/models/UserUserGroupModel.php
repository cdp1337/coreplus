<?php
/**
 * Model for UserUserGroupModel
 * 
 * @package User
 * @since 1.9
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

class UserUserGroupModel extends Model {

	public static $Schema = array(
		'user_id' => array(
			'type' => Model::ATT_TYPE_UUID_FK,
			'required' => true,
			'null' => false,
			'link' => [
				'model' => 'User',
				'type' => Model::LINK_BELONGSTOONE,
				'on' => 'id',
			],
		),
		'group_id' => array(
			'type' => Model::ATT_TYPE_UUID_FK,
			'required' => true,
			'null' => false,
			'link' => [
				'model' => 'UserGroup',
				'type' => Model::LINK_BELONGSTOONE,
				'on' => 'id',
			],
		),
		'context' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => '40',
			'default' => '',
			'comment' => 'If this user group is tied to a specific context, the Model base name is here.',
			'form' => array(
				'type' => 'select',
				'description' => 'If this group will be used to apply permissions in specific contexts, select it here. The specific context object is selected on the respective user edit page under their groups.',
			)
		),
		'context_pk' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => '200',
			'default' => '',
			'comment' => 'The PK of the context for this group, if applicable.'
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
		'primary' => array('user_id', 'group_id', 'context', 'context_pk'),
	);
}
