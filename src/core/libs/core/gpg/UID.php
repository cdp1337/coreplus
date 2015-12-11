<?php
/**
 * File for class KeyUID definition in the coreplus project
 * 
 * @package Core\GPG
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140319.2056
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

namespace Core\GPG;


/**
 * A short teaser of what KeyUID does.
 *
 * More lengthy description of what KeyUID does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for KeyUID
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
 * @package Core\GPG
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class UID extends Key {
	/**
	 * @var string The full name of this UID
	 */
	public $fullname;

	/** @var string Comment of this UID */
	public $comment;

	/**
	 * @var string The email attached to this UID
	 */
	public $email;

} 