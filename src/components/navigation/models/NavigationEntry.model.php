<?php
/**
 * Model for NavigationEntryModel
 *
 * @package Core Plus\Navigation
 * @since 0.1
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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
class NavigationEntryModel extends Model {
	public static $Schema = array(
		'id'           => array(
			'type'     => Model::ATT_TYPE_ID,
			'required' => true,
			'null'     => false,
		),
		'navigationid' => array(
			'type' => Model::ATT_TYPE_INT,
			'null' => false,
		),
		'parentid'     => array(
			'type' => Model::ATT_TYPE_INT,
			'null' => false,
		),
		'type'         => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 6,
			'null'      => false,
		),
		'baseurl'      => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 255,
			'null'      => false,
		),
		'title'        => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'null'      => false,
		),
		'target'       => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 16,
			'null'      => false,
		),
		'weight'       => array(
			'type' => Model::ATT_TYPE_INT,
			'null' => false,
		),
		'created'      => array(
			'type' => Model::ATT_TYPE_CREATED,
			'null' => false,
		),
		'updated'      => array(
			'type' => Model::ATT_TYPE_UPDATED,
			'null' => false,
		),
	);

	public static $Indexes = array(
		'primary' => array('id'),
	);

	/**
	 * Based on the type of this entry, ie: int or ext, resolve the URL fully.
	 *
	 * @return string
	 */
	public function getResolvedURL() {
		switch ($this->get('type')) {
			case 'int':
				return Core::ResolveLink($this->get('baseurl'));
				break;
			case 'ext':
				if (strpos(substr($this->get('baseurl'), 0, 8), '://') !== false) return $this->get('baseurl');
				else return 'http://' . $this->get('baseurl');
				break;
		}
	}

} // END class NavigationEntryModel extends Model
