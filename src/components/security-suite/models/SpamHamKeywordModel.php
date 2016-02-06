<?php
/**
 * File for class SpamHamModel definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140412.1537
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


/**
 * A short teaser of what SpamHamModel does.
 *
 * More lengthy description of what SpamHamModel does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for SpamHamModel
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
class SpamHamKeywordModel extends Model {
	public static $Schema = array(
		'keyword' => [
			'type' => Model::ATT_TYPE_STRING,
			'required' => true,
		],
		'score' => [
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
			'default' => 1,
		]
	);

	public static $Indexes = array(
		'primary' => ['keyword'],
	);
} 