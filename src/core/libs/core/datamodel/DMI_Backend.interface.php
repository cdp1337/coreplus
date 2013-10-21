<?php
/**
 * Interface that all backends should share.
 *
 * @package Core\Datamodel
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

	/**
	 * Execute a given Dataset object on this backend
	 *
	 * @param Dataset $dataset
	 *
	 * @throws DMI_Exception
	 */
	public function execute(Dataset $dataset);

	/**
	 * Check to see if a given table exists without causing an error.
	 *
	 * @param string $tablename
	 *
	 * @return bool
	 */
	public function tableExists($tablename);

	/**
	 * Create a table on this backend with the provided ModelSchema.
	 *
	 * @param string $tablename
	 * @param ModelSchema $schema
	 *
	 * @return bool
	 */
	public function createTable($tablename, $schema);

	/**
	 * Modify a table to match a new schema.
	 *
	 * This is used to keep the database in sync with the code upon upgrades, installations and reinstalls.
	 *
	 * @param string $table
	 * @param ModelSchema $newschema
	 *
	 * @return bool
	 * @throws DMI_Exception
	 * @throws DMI_Query_Exception
	 */
	public function modifyTable($table, $newschema);

	/**
	 * Get a flat array of table names currently available on this backend.
	 *
	 * @return array
	 */
	public function showTables();

	/**
	 * Get the number of reads that have been performed on this page load.
	 *
	 * @return int
	 */
	public function readCount();

	/**
	 * Get the number of writes that have been performed on this page load.
	 *
	 * @return int
	 */
	public function writeCount();

	/**
	 * Get the query log for this backend.
	 *
	 * @return array
	 */
	public function queryLog();
}
