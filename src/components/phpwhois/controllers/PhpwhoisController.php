<?php
/**
 * File for class PhpwhoisController definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130424.2342
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
 * A short teaser of what PhpwhoisController does.
 *
 * More lengthy description of what PhpwhoisController does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for PhpwhoisController
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
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class PhpwhoisController extends Controller_2_1{
	public function lookup(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$query = $request->getParameter('q');

		$view->contenttype = View::CTYPE_JSON;
		$view->mode = View::MODE_AJAX;
		$view->record = false;

		$result = Whois::Lookup($query);

		$view->jsondata = [
			'query'        => $query,
			'ip'           => $result->getIP(),
			'network'      => $result->getNetwork(),
			'organization' => $result->getOrganization(),
			'country'      => $result->getCountry(),
			'country_name' => $result->getCountryName(),
			'flag_sm'      => $result->getCountryIcon('20x20'),
			'flag_lg'      => $result->getCountryIcon('100x100'),
		];
	}
}