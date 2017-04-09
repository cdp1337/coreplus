<?php
/**
 * 
 * 
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140228.1328
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

class PageWidget extends \Core\Widget {
	/**
	 * This is a widget to display siblings on a given page.
	 *
	 * The page is dynamic based on the currently viewed page.
	 *
	 * @return int
	 */
	public function siblingsnavigation() {
		return 'Please use /navigation/siblings instead.';
	}

	/**
	 * This is a widget to display siblings AND the active page's children on a given page.
	 *
	 * The page is dynamic based on the currently viewed page.
	 *
	 * @return int
	 */
	public function siblingsandchildrennavigation() {
		return 'Please use /navigation/siblingsandchildren instead.';
	}
}