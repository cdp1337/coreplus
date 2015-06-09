<?php
/**
 * File for class Helper definition in the locator-platform project
 * 
 * @package Google\Maps
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20141109.2304
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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

namespace Google\Maps;


/**
 * A short teaser of what Helper does.
 *
 * More lengthy description of what Helper does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for Helper
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
 * @package Google\Maps
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
abstract class Helper {
	public static function Load() {
		$key = \ConfigHandler::Get('/google/services/public_api_key');

		\Core\view()->addScript('<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=' . $key . '"></script>', 'head');

		return true;
	}
} 