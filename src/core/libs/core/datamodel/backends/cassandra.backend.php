<?php

/**
 * Cassandra datamodel backend system
 * 
 * -- EXPERIMENTAL! --
 * 
 * @package Core Plus\Datamodel
 * @since 0.1
 * @author Charlie Powell <charlie@eval.bz>
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


// give me the driver for this system!
require_once(__DMI_PDIR . 'drivers/phpcassa/connection.php');
require_once(__DMI_PDIR . 'drivers/phpcassa/columnfamily.php');
require_once(__DMI_PDIR . 'drivers/phpcassa/uuid.php');


class DMI_cassandra_backend implements DMI_Backend {
	protected $_conn = false;
	
	public function connect($host, $user, $pass, $database) {
		//$this->_conn = new ConnectionWrapper($database, $host);
		
		// @todo Add support for multiple servers.
		$servers = array($host, '9160');
		$this->_conn = new ConnectionPool($database, $servers);
		
		return ($this->_conn);
	}
	
	public function tableExists($tablename){
		try{
			$cf = new ColumnFamily($this->_conn, $tablename);
			//$cf->get('1');
			return true;
		}
		catch(Exception $e){
			return false;
		}
	}
	
	public function execute(Core\Datamodel\Dataset $dataset){
		try{
			switch($dataset->_mode){
				case Core\Datamodel\Dataset::MODE_GET:
					return $this->_executeGet($dataset);
					break;
			}
		}
		catch(Exception $e){
			$class = get_class($e);
			switch($class){
				case 'cassandra_NotFoundException':
					$errno = DMI_Exception::ERRNO_NODATASET;
					$error = "Columnfamily '" . $name . "' doesn't exist";
					break;
				default:
					$errno = DMI_Exception::ERRNO_UNKNOWN;
					$error = '';
					break;
			}
			
			throw new DMI_Exception($error, 0, null, $errno);
		}
	}
	
	public function createTable($tablename, $schema){
		// Cassandra can't actually create tables :/
		// @todo something...
	}
	
	private function _executeGet(Core\Datamodel\Dataset $dataset){
		$name = $dataset->_name;
		// Is this name prefixed by the DB_PREFIX variable?
		if(strpos($name, DB_PREFIX) === false) $name = DB_PREFIX . $name;

		$cf = new ColumnFamily($this->_conn, $name);
		$out = $cf->get_range();
		
		foreach($out as $k => $v){
			var_dump($k, $v);
		}

		var_dump($dat, $dataset);
		die();
	}
}

?>
