<?php
/**
 * Interface that all backends should share.
 *
 * @package Core\Datamodel
 * @since 0.1
 * @author Charlie Powell <charlie@evalagency.com>
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

namespace Core\Datamodel;

interface BackendInterface {

	/**
	 * Execute a given Dataset object on this backend
	 *
	 * @param Dataset $dataset
	 *
	 * @throws \DMI_Exception
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
	 * Create a table on this backend with the provided Schema.
	 *
	 * @param string $table  Table name to be created
	 * @param Schema $schema Schema to create table with
	 *
	 * @return bool
	 *
	 * @throws \DMI_Exception
	 */
	public function createTable($table, Schema $schema);

	/**
	 * Modify a table to match a new schema.
	 *
	 * This is used to keep the database in sync with the code upon upgrades, installations and reinstalls.
	 *
	 * @param string $table  Table name to be created
	 * @param Schema $schema Schema to match
	 *
	 * @return bool
	 * @throws \DMI_Exception
	 * @throws \DMI_Query_Exception
	 */
	public function modifyTable($table, Schema $schema);

	/**
	 * Drop a table from the system.
	 *
	 * @param $table
	 *
	 * @return bool
	 * @throws \DMI_Exception
	 */
	public function dropTable($table);
	
	/**
	 * Describe the schema of a given table
	 *
	 * @param string $table Table name to query
	 * @return Schema
	 */
	public function describeTable($table);

	/**
	 * Get a flat array of table names currently available on this backend.
	 *
	 * @return array
	 */
	public function showTables();

}
