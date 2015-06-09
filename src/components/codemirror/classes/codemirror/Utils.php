<?php
/**
 * File for class Utils definition in the coreplus project
 * 
 * @package CodeMirror
 * @author Charlie Powell <charlie@evalagency.com>
 * @author Nick Hinsch <nicholas@evalagency.com>
 * @date 20130509.1449
 * @copyright Copyright (C) 2009-2014  Author
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

namespace CodeMirror;


/**
 * A short teaser of what Utils does.
 *
 * More lengthy description of what Utils does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for Utils
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
 * @package CodeMirror
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
abstract class Utils {
	/**
	 * Include the base codemirror dependencies.
	 *
	 * @return bool
	 */
	public static function IncludeCodeMirror() {

		\Core\view()->addScript('assets/libs/codemirror/lib/codemirror.js');
		\Core\view()->addStylesheet('assets/libs/codemirror/lib/codemirror.css');
		\Core\view()->addStylesheet('assets/css/codemirror.css'); // This is one that can get overridden with custom themes.

		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}

	public static function IncludeCSS() {
		return self::_IncludeMode('css');
	}

	public static function IncludeSQL() {
		return self::_IncludeMode('sql');
	}

	public static function IncludeHTML() {
		self::_IncludeMode('css');
		self::_IncludeMode('xml');
		self::_IncludeMode('javascript');
		return self::_IncludeMode('htmlmixed');
	}

	public static function IncludeHTTP() {
		return self::_IncludeMode('http');
	}

	public static function IncludeJS() {
		return self::_IncludeMode('javascript');
	}

	public static function IncludeMD() {
		return self::_IncludeMode('markdown');
	}

	public static function IncludePHP() {
		self::_IncludeMode('css');
		self::_IncludeMode('javascript');
		self::_IncludeMode('htmlmixed');
		return self::_IncludeMode('php');
	}

	public static function IncludeSmarty() {
		self::_IncludeMode('css');
		self::_IncludeMode('javascript');
		self::_IncludeMode('htmlmixed');
		self::_IncludeMode('php');
		return self::_IncludeMode('smarty');
	}

	private static function _IncludeMode($mode) {
		self::IncludeCodeMirror();

		\Core\view()->addScript('libs/codemirror/mode/' . $mode . '/' . $mode . '.js');
		return true;
	}
}