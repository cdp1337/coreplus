<?php
/**
 * @todo      Enter a meaningful file description here!
 *
 * @package   HTMLToMD\Elements
 * @author    Charlie Powell <charlie@evalagency.com>
 * @date      20151017.1858
 * @copyright Copyright (C) 2009-2016  Charlie Powell
 * @license   GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
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

namespace HTMLToMD\Elements;

use HTMLToMD\Converter;

interface ElementInterface {
	public function __construct(\DOMNode $node, Converter $converter);

	/**
	 * @return string
	 */
	public function convert();

	/**
	 * @param string $attribute
	 * @param mixed $default
	 *
	 * @return null|string
	 */
	public function getAttribute($attribute, $default = null);

	/**
	 * Get the page footer text, (if any).
	 *
	 * @return string
	 */
	public function getPageFooter();
}