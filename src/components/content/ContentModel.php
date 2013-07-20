<?php
/**
 * DESCRIPTION
 *
 * @package Content
 * @since 0.1
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
class ContentModel extends Model{
	
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'site' => array(
			'type' => Model::ATT_TYPE_SITE,
			'formtype' => 'system',
		),
		'nickname' => array(
			'type' => Model::ATT_TYPE_STRING,
			'comment' => 'Cache of the linked page title',
			'required' => false,
			'formtype' => 'disabled',
		),
		'editpermissions' => array(
			'type' => Model::ATT_TYPE_STRING,
			'default' => '!*',
			'form' => array(
				'type' => 'access',
				'title' => 'Edit Permissions',
				'description' => 'Permissions for who is allowed to edit this page',
				'group' => 'Access & Advanced',
			)
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
		'primary' => array('id'),
	);
	
    public function __construct($key = null) {
		$this->_linked = array(
			'Page' => array(
				'link' => Model::LINK_HASONE,
				'on' => 'baseurl',
			),
		);
		
		parent::__construct($key);
	}
	
	public function get($k) {
		$k = strtolower($k);
		switch($k){
			case 'baseurl':
				return '/content/view/' . $this->_data['id'];
				break;
			default:
				return parent::get($k);
		}
	}
}
?>
