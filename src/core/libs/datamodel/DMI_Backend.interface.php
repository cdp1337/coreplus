<?php
/**
 * Interface that all backends should share.
 *
 * @package Core Plus\Datamodel
 * @since 0.1
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
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

interface DMI_Backend {

	public function execute(Dataset $dataset);

	/**
	 * Check to see if a given table exists without causing an error.
	 *
	 * @param string $tablename
	 * @return boolean
	 */
	public function tableExists($tablename);

	public function createTable($tablename, $schema);

	public function readCount();

	public function writeCount();
}
