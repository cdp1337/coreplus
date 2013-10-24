<?php
/**
 * File for class Dataset definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20131024.1448
 * @copyright Copyright (C) 2009-2013  Author
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


error_log('Dataset* classes are deprecated and will be removed shortly!  Please use Core\\Datamodel\\Dataset* instead!', E_USER_DEPRECATED);

/**
 * A short teaser of what Dataset does.
 *
 * More lengthy description of what Dataset does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for Dataset
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
class Dataset extends Core\Datamodel\Dataset{

}

class DatasetWhere extends Core\Datamodel\DatasetWhere{

}

class DatasetWhereClause extends Core\Datamodel\DatasetWhereClause{

}

class DatasetStream extends Core\Datamodel\DatasetStream{

}