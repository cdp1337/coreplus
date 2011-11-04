<?php
/**
 * // enter a good description here
 * 
 * @package Core
 * @since 2011.06
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl.html>
 * This system is licensed under the GNU LGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/lgpl-3.0.html>, 
 * and please contribute back to the community :)
 */

/**
 * DB.class.php
 * 
 * @package CAE-libraries
 * @subpackage DB
 * @version 1.0.0-dev
 * @author powellc
 *
 */

class DB implements ISingleton{
	
	private static $instance = null;
	
	/**
	 * 
	 * @var ADOConnection
	 */
	public $connection;
	
	public $counter = 0;
	
	private function __construct(){
		$connectionSettings = ConfigHandler::LoadConfigFile("db");
		
		switch($connectionSettings['type']){
			case 'cassandra':
				require_once(ROOT_PDIR . 'core/libs/phpcassa-0.7.a.4/connection.php');
				require_once(ROOT_PDIR . 'core/libs/core/ADODB_cassandra.class.php');
				$this->connection = new ADODB_cassandra();
				$this->connection->connect($connectionSettings['server'], null, null, $connectionSettings['name']);
				//var_dump($this->connection); die();
				break;
			default:
				$dsn = "{$connectionSettings['type']}://"
				 . "{$connectionSettings['user']}:{$connectionSettings['pass']}"
				 . "@{$connectionSettings['server']}"
				 . "/{$connectionSettings['name']}"
				 . "?persist&fetchmode=ASSOC";
				$this->connection =& ADONewConnection($dsn);
				
				break;
		}
		
		
		// No go?
		if(!$this->connection) return;
		
		// Caching?
		// @todo Make this a config option.
		//$this->connection->memCache = true;
		//$this->connection->memCacheHost = 'localhost';
		//$this->connection->memCacheCompress = false;
	}
	
	
	public static function GetConnection(){ return DB::singleton()->connection; }
	public static function GetConn(){ return DB::singleton()->connection; }
	
	/**
	 * 
	 * @return DB
	 */
	public static function Singleton(){
		if(is_null(DB::$instance)){
			DB::$instance = new DB();
		}
		return DB::$instance;
	}
	
	public static function GetInstance(){ return self::singleton(); }
	
	/**
	 * Execute SQL 
	 *
	 * @param sql	 SQL statement to execute, or possibly an array holding prepared statement ($sql[0] will hold sql text)
	 * @param [inputarr]	holds the input data to bind to. Null elements will be set to null.
	 * @return		ADORecordSet | false
	 */
	public static function Execute($sql, $inputarr = false){
		$db = DB::Singleton();
		$db->counter++;
		//echo $sql . '<br/>';
		// @todo Make this toggleable.
		//return $db->connection->CacheExecute(3600, $sql, $inputarr);
		return $db->connection->Execute($sql, $inputarr);
	}
	
	public static function qstr($string){
		return DB::singleton()->connection->qstr($string);
	}
	
	public static function Insert_ID($table = '', $column = ''){
		return DB::singleton()->connection->Insert_ID($table, $column);
	}

	public static function Error(){
		return DB::Singleton()->connection->ErrorMsg();
	}
	
	/**
	 * Basic function to check if a given table exists without causing an error.
	 * Useful for those installation scripts that alter the db schema.
	 * 
	 */
	public static function TableExists($tablename){
		$q = "SHOW TABLES";
		$rs = DB::Execute($q);
		foreach($rs as $row){
			if($row[0] == $tablename) return true;
		}
		// Nope?  Well...
		return false;
	}
	
	/**
	 * Create an SQL string for a given table and hash
	 * @param $table string
	 * @param $isNew boolean | 'auto'
	 * @param $hash array
	 * @param $primaryKeys array
	 * @return unknown_type
	 */
	public static function CreateSQLFromHash($table, $isNew, $hashTable, $primaryKeys = array()){
		
		if($isNew === true){
			// If isNew is true
			$query = "INSERT INTO `$table` ";
			$q1 = ''; $q2 = '';
			foreach($hashTable as $key => $val){
				$q1 .= ($q1 == '')? $key : ', ' . $key;
				$q2 .= (($q2 == '')? '' : ', ') . DB::qstr($val);
			}
			return "$query \n($q1) \nVALUES \n($q2)";
		}
		elseif($isNew === false){
			$query = "UPDATE `$table` SET ";
			
			$q1 = '';
			foreach($hashTable as $key => $val){
				$q1 .= (($q1 == '')? '' : ", \n") . "`$key` = " . DB::qstr($val);
			}
			
			$q2 = '';
			foreach($primaryKeys as $pKey => $pVal){
				$q2 .= ($q2 == '')? "WHERE `$pKey` = " . DB::qstr($pVal) : " AND `$pKey` = " . DB::qstr($pVal);
			}
			
			return "$query \n$q1 \n$q2";
		}
		elseif(strtolower($isNew) == 'auto'){
			$query = "INSERT INTO `$table`";
			$q1 = ''; $q2 = ''; $q3 = '';
			foreach(array_merge($hashTable, $primaryKeys) as $key => $val){
				$q1 .= (($q1 == '')? '' : ", \n") . "`$key`";
				$q2 .= (($q2 == '')? '' : ", \n") . DB::qstr($val);
			}
			
			//$query .= "\n($q1) \nVALUES \n($q2) " . "ON DUPLICATE KEY UPDATE ";
			
			foreach($hashTable as $key => $val){
				$q3 .= (($q3 == '')? '' : ", \n") . "`$key` = " . DB::qstr($val);
			}
			
			return "$query \n($q1) \nVALUES \n($q2) \nON DUPLICATE KEY UPDATE \n$q3";
		}
		else{
			return false; // What do I do?!?
		}
	}
	
}

// I need adodb to be loaded first!
require_once(ROOT_PDIR . 'core/libs/adodb5/adodb.inc.php');

// This class needs to be instantiated before anything can use the database.
//	Just ignore any hookHandles, as this should be available before libraries_loaded is sent.
DB::singleton();
