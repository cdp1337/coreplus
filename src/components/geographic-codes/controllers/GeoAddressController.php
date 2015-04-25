<?php
/**
 * File for class GeoAddressController definition in the coreplus project
 *
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20131104.1541
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


/**
 * A short teaser of what GeoAddressController does.
 *
 * More lengthy description of what GeoAddressController does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for GeoAddressController
 * <h4>Example 1</h4>
 * <p>Description 1</p>
 * <code>
 * // Some code for example 1
 * $a = $b;
 * </code>
 *
 *
 * <h4>Example 2</h4>
 * <p>Description 2</p>
 * <code>
 * // Some code for example 2
 * $b = $a;
 * </code>
 *
 *
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class GeoAddressController extends Controller_2_1{

	/**
	 * View to get the provinces for a given country
	 */
	public function getprovinces(){
		$view             = $this->getView();
		$request          = $this->getPageRequest();
		$selected_country = $request->getParameter(0) ? $request->getParameter(0) : REMOTE_COUNTRY;

		$view->mode = View::MODE_AJAX;
		$view->contenttype = View::CTYPE_JSON;

		$provinces = GeoProvinceModel::Find(['country = ' . $selected_country]);
		// Convert the provinces to JSON data so the javascript
		// can use it as if it had loaded from the server.
		$provincejs = [];
		foreach($provinces as $p){
			/** @var GeoProvinceModel $p */
			$provincejs[] = $p->getAsArray();
		}

		$view->jsondata = $provincejs;
	}
}