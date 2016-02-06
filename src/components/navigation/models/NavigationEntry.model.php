<?php
/**
 * Model for NavigationEntryModel
 *
 * @package Core Plus\Navigation
 * @since 0.1
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2016  Charlie Powell
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
	public static $Schema = [
		'id'           => [
			'type'     => Model::ATT_TYPE_ID,
			'required' => true,
			'null'     => false,
		],
		'navigationid' => [
			'type' => Model::ATT_TYPE_INT,
			'null' => false,
		],
		'parentid'     => [
			'type' => Model::ATT_TYPE_INT,
			'null' => false,
		],
		'type'         => [
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 6,
			'null'      => false,
		],
		'baseurl'      => [
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 255,
			'null'      => false,
		],
		'title'        => [
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'null'      => false,
		],
		'target'       => [
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 16,
			'null'      => false,
		],
		'weight'       => [
			'type' => Model::ATT_TYPE_INT,
			'null' => false,
		],
		'created'      => [
			'type' => Model::ATT_TYPE_CREATED,
			'null' => false,
		],
		'updated'      => [
			'type' => Model::ATT_TYPE_UPDATED,
			'null' => false,
		],
	];

	public static $Indexes = [
		'primary' => ['id'],
	];

	/**
	 * Based on the type of this entry, ie: int or ext, resolve the URL fully.
	 *
	 * @return string
	 */
	public function getResolvedURL() {
		switch ($this->get('type')) {
			case 'int':
				return \Core\resolve_link($this->get('baseurl'));
				break;
			case 'ext':
				if (strpos(substr($this->get('baseurl'), 0, 8), '://') !== false) return $this->get('baseurl');
				else return 'http://' . $this->get('baseurl');
				break;
		}
	}

	/**
	 * Get the access string of the current page, if possible.
	 * This is pulled from the corresponding page, but if it's an external page or manual URL, there won't be any Page to lookup,
	 * so '*' is returned instead.
	 *
	 * @return string
	 */
	public function getAccessString(){
		if($this->get('type') != 'int'){
			// External pages never can support access permissions, I don't know what the external page has!
			return '*';
		}

		$page = PageModel::Construct($this->get('baseurl'));

		if(!$page->exists()){
			// If the page doesn't exist, then there's nothing to go off of either!
			return '*';
		}
		if($page->get('access') == ''){
			// There's a weird bug where sometimes the access cache is empty.
			// In that case, just allow the user to view the page.
			return '*';
		}

		// Otherwise!
		return $page->get('access');
	}

} // END class NavigationEntryModel extends Model
