<?php
/**
 * Model for gallery albums
 *
 * @package Gallery
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2012  Charlie Powell
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
class GalleryAlbumModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		/*'paginate' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
			'default' => 15,
			'formtitle' => 'Per-Page Count',
			'formdescription' => 'The number of results per page, set to 0 to disable pagination.'
		),*/
		/*'enabled' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'required' => true,
			'default' => true,
			'formdescription' => 'Disable this album to hide from the public listings.'
		),*/
		'store_type' => array(
			'type' => Model::ATT_TYPE_ENUM,
			'options' => array('public', 'private'),
			'default' => 'public',
			'formdescription' => 'Change the store location for this gallery.
				Public means that images are accessible directly and can be served very quickly.
				Private means that images are only available through the framework and thus more secure, but are slower to be served.',
			'formtype' => 'hidden', // Set this to hidden right now, support for private galleries will be added in the future.
		),
		'editpermissions' => array(
			'type' => Model::ATT_TYPE_STRING,
			'description' => 'Permissions for who is allowed to edit this album',
			'default' => '!*',
			'form' => array(
				'type' => 'access',
				'title' => 'Edit Permissions',
			)
		),
		'uploadpermissions' => array(
			'type' => Model::ATT_TYPE_STRING,
			'description' => 'Permissions for who is allowed to upload new images to this album',
			'default' => '!*',
			'form' => array(
				'type' => 'access',
				'title' => 'Upload Permissions',
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
			'GalleryImage' => array(
				'link' => Model::LINK_HASMANY,
				'on' => array('albumid' => 'id'),
			),
		);

		parent::__construct($key);
	}

	public function get($k) {
		$k = strtolower($k);
		switch($k){
			case 'baseurl':
				return '/gallery/view/' . $this->_data['id'];
				break;
			case 'title':
				return $this->getLink('Page')->get('title');
				break;
			case 'rewriteurl':
				return $this->getLink('Page')->get('rewriteurl');
				break;
			default:
				return parent::get($k);
		}
	}
}
