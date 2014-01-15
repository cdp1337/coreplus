<?php
/**
 * File for class Updater definition in the coreplus project
 * 
 * @package geocode
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130425.0007
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

namespace geocode;


/**
 * A short teaser of what Updater does.
 *
 * More lengthy description of what Updater does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for Updater
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
 * @package geocode
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
abstract class Updater {
	/**
	 * Utility to update all the local databases with the newest internet sources.
	 */
	public static function UpdateDatabases($quiet = true) {
		$databases = array(
			// This file provides country - ISO-3166-1-a-2 translation, (2-digit codes)
			array(
				'title' => 'Country Name and ISO-2 Codes',
				'source' => 'http://www.iso.org/iso/home/standards/country_codes/country_names_and_code_elements_txt.htm',
				'dest' => 'country_name_iso_2.txt',
			)
		);

		foreach($databases as $db){
			if(!$quiet) echo 'Retrieving ' . $db['title'] . '...';
			$src = \Core\Filestore\Factory::File($db['source']);
			$src->copyTo('tmp/geographic-codes/' . $db['dest']);
		}
	}
}