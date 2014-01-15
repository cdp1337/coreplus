<?php
/**
 * File for class JSHelper definition in the coreplus project
 * 
 * @package phpwhois
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130425.0302
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

namespace phpwhois;


/**
 * A short teaser of what JSHelper does.
 *
 * More lengthy description of what JSHelper does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for JSHelper
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
 * @package phpwhois
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
abstract class JSHelper {
	public static function LoadFancyIP() {
		// I need jquery ui first.
		\JQuery::IncludeJQueryUI();

		// And Core.Strings
		\Core::_AttachCoreStrings();

		// Add the styles
		\Core\view()->addStylesheet('assets/css/phpwhois/fancy_ip.css');
		// And the script itself
		\Core\view()->addScript('assets/js/phpwhois/fancy_ip.js');

		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}
}