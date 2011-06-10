<?php
/**
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 *
 * Copyright (C) 2010  Charlie Powell <powellc@powelltechs.com>
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
 *
 * @package [packagename]
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date [date]
 */

class Model {

	const ATT_TYPE_STRING = 'string';
	const ATT_TYPE_TEXT = 'text';
	const ATT_TYPE_INT = 'int';
	const ATT_TYPE_FLOAT = 'float';
	const ATT_TYPE_BOOL = 'boolean';
	const ATT_TYPE_ENUM = 'enum';
	const ATT_TYPE_ID = '__id';
	const ATT_TYPE_UPDATED = '__updated';
	const ATT_TYPE_CREATED = '__created';
	
	// Regex to match anything not blank.
	const VALIDATION_NOTBLANK = "/^.+$/";
	// Regex to match email addresses.
	// @see http://www.regular-expressions.info/email.html
	const VALIDATION_EMAIL = "/[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/";

	/**
	 * @var string The table name corresponding to this Model.
	 */
	protected $_tablename = null;
	
	/**
	 * @var array Associative array of the data in this corresponding record.
	 */
	protected $_data = array();

	/**
	 * @var array The primary key column of the corresponding table.
	 */
	//protected $_pkcolumns;

	/**
	 * @var string The AI column of this table.
	 */
	//protected $_aicolumn;

	/**
	 * @var string The "created" column of the corresponding table.
	 */
	//protected $_createdcolumn;

	/**
	 * @var string The "Updated" column of the corresponding table.
	 */
	//protected $_updatedcolumn;

	/**
	 * @var boolean Dirty flag, if true the data in the model has been changed.
	 */
	protected $_dirty = false;

	/**
	 * @var boolean Flag to signify if this record exists in the database.
	 */
	protected $_exists = false;

	/**
	 * @var SQLBuilderSelect The builder for the query.
	 */
	protected $_sqlbuilder;

	/**
	 * @var array The columns and their structure.
	 * @deprecated
	 */
	protected $_columns;
	
	public static $Schema = array();
	
	public static $Indexes = array();
	
	private static $_ModelDataCache = array();
	
	protected $_linked = array();
	
	protected $_cacheable = true;
	
	const LINK_HASONE = 'one';
	const LINK_HASMANY = 'many';


	
	public function __construct($key = null){
		// Build a query to describe the table.
		// @todo Sometime tie this into a cache system!
		//$this->_data = array();
		//$this->_exists = false;
		//$this->_pkcolumns = array();
		//$this->_columns = array();
		
		
		/*
		if(!isset(Model::$_ModelStructureCache[$this->getTableName()])){
			
			$l =& Model::$_ModelStructureCache[$this->getTableName()];
			$l = array('data' => array(), 'pkcolumns' => array(), 'columns' => array(), 'createdcolumn' => $this->_createdcolumn, 'updatedcolumn' => $this->_updatedcolumn, 'aicolumn' => $this->_aicolumn);
		
			$q = "DESCRIBE `" . $this->getTableName() . "`";
			$rs = DB::Execute($q);
			foreach($rs as $row){
				//var_dump($row);
				$l['data'][$row['Field']] = null;

				// Primary key column?
				if($row['Key'] == 'PRI') $l['pkcolumns'][] = $row['Field'];

				// "Created" column?
				if(!$l['createdcolumn'] && $row['Field'] == 'created' && $row['Type'] == 'int(11)') $l['createdcolumn'] = $row['Field'];

				// "Updated" column?
				if(!$l['updatedcolumn'] && $row['Field'] == 'updated' && $row['Type'] == 'int(11)') $l['updatedcolumn'] = $row['Field'];

				// AI column?
				if($row['Extra'] == 'auto_increment') $l['aicolumn'] = $row['Field'];

				// Save this column definition too.
				// This is useful for the meta functions down the road.
				if($row['Field'] == 'access' && $row['Type'] == 'varchar(512)'){
					// Interpuret this as an access string.
					$type = 'accessstring';
					$maxlen = 512;
				}
				elseif(strpos($row['Type'], 'int(') !== false){
					$type = 'int';
					$maxlen = substr($row['Type'], 4, -1);
				}
				elseif(strpos($row['Type'], 'varchar(') !== false){
					$type = 'string';
					$maxlen = substr($row['Type'], 8, -1);
				}
				elseif($row['Type'] == 'text'){
					$type = 'text';
					$maxlen = null;
				}
				elseif($row['Type'] == "enum('0','1')" || $row['Type'] == "enum('1','0')"){
					$type = 'boolean';
					$maxlen = null;
				}
				elseif(strpos($row['Type'], 'enum(') !== false){
					$type = 'enum';
					$maxlen = null;
					$opt = explode(',', substr($row['Type'], 5, -1));
					foreach($opt as $k => $v) $opt[$k] = substr($v, 1, -1); // Opts will be surrounded by single quotes.
				}
				else{
					echo "<pre class='xdebug-var-dump'>Unsupported column definition: " . $row['Type'] . "</pre>";
					$type = 'other';
					$maxlen = null;
				}
				$name = ucwords(str_replace('_', ' ', $row['Field']));
				$primary = ($row['Key'] == 'PRI');
				$unique = ($row['Key'] == 'PRI' || $row['Key'] == 'UNI');
				$ai = ($row['Extra'] == 'auto_increment');
				$null = ($row['Null'] == 'YES');

				$l['columns'][$row['Field']] = array('name' => $name, 'type' => $type, 'maxlength' => $maxlen, 'primary' => $primary, 'unique' => $unique, 'autoinc' => $ai, 'allownull' => $null);
				if($type == 'enum') $l['columns'][$row['Field']]['opts'] = $opt;
			}
		} // if(!isset(Model::$_ModelStructureCache[$this->getTableName()]))
		*/
		
		// Load in the structure from the cache.
		/*$l =& Model::$_ModelStructureCache[$this->getTableName()];
		$this->_data = $l['data'];
		$this->_pkcolumns = $l['pkcolumns'];
		$this->_createdcolumn = $l['createdcolumn'];
		$this->_updatedcolumn = $l['updatedcolumn'];
		$this->_aicolumn = $l['aicolumn'];
		$this->_columns = $l['columns'];
		*/
		
		// Check the index (primary), and the incoming data.  If it matches, load it up!
		$i = self::$Indexes;
		
		if(isset($i['primary']) && func_num_args() == sizeof($i['primary'])){
			foreach($i['primary'] as $k => $v){
				$this->_data[$v] = func_get_arg($k);
			}
		}

		$this->load();
	}

	public function load(){

		// I need to check the pks first.
		// If they're not set I can't load anything from the database.
		$i = self::$Indexes;
		
		if(isset($i['primary']) && sizeof($i['primary'])){
			foreach($i['primary'] as $k){
				if($this->get($k) === null) return;
			}
		}
		
		if($this->_cacheable){
			$cachekey = $this->_getCacheKey();
			$cache = Core::Cache()->get($cachekey);
			
			// do something if cache succeeds....
		}
		
		// Enable cache, w00t!
		if(!isset(Model::$_ModelDataCache[$this->getTableName()])) Model::$_ModelDataCache[$this->getTableName()] = array();
		$cachekey = '';
		if(sizeof($this->_pkcolumns)){
			foreach($this->_pkcolumns as $v){
				$cachekey .= (($cachekey == '')? '' : '-') . $this->_data[$v];
			}
		}
		
		if(!isset(Model::$_ModelDataCache[$this->getTableName()][$cachekey])){
			$builder = $this->getSQLBuilder();

			if(sizeof($this->_pkcolumns)){
				foreach($this->_pkcolumns as $v){
					if($this->_data[$v]) $builder->where(array($v => $this->_data[$v]));
				}
			}

			$rs = $builder->execute();
			if(!$rs) throw new Exception(DB::Error());

			Model::$_ModelDataCache[$this->getTableName()][$cachekey] = $rs->fields;
		}


		if(!Model::$_ModelDataCache[$this->getTableName()][$cachekey]){
			$this->_dirty = true;
			return;
		}

		$this->_loadFromRecord(Model::$_ModelDataCache[$this->getTableName()][$cachekey]);
	}

	public function save(){
		
		// Only do the same operation if it's been changed.
		if($this->_dirty){
			// Add support for automatic timestamps.
			if($this->_updatedcolumn) $this->set($this->_updatedcolumn, Time::GetCurrentGMT() );
			
			// @todo Should this be in the set instead?
			// Update all NULL values to blank for values that aren't allowed to be null.
			foreach($this->_data as $k => $v){
				if($this->_columns[$k]['type'] == 'boolean'){
					// If null is allowed and either null or a blank string, set those as null.
					if($this->_columns[$k]['allownull'] && ($v === null || $v === '')) $this->_data[$k] = null;
					// Otherwise use PHP to evaluate any possible "true" condition to simply "1".
					else $this->_data[$k] = ($v)? '1' : '0'; 
				}
				elseif($v === null && !$this->_columns[$k]['allownull']){
					$this->_data[$k] = '';
				}
			}

			if($this->isnew()){
				$ret = $this->_saveNew();
			}
			else{
				$ret = $this->_saveExisting();
			}
			
			if(!$ret){
				// @todo Make a DBI exception.
				throw new Exception(DB::Error());
			}
		}
		
		
		// Go through any linked tables and ensure that they're saved as well.
		foreach($this->_linked as $k => $l){
			if(!(isset($l['records']) || $this->_dirty)) continue; // No need to save if it was never loaded.
			
			$models = (is_array($l['records']))? $l['records'] : array($l['records']);
			
			foreach($models as $model){
				// Ensure all linked fields still match up.  Something may have been changed in the parent.
				$model->setFromArray($this->_getLinkWhereArray($k));
				$model->save();
			}
		}
	}

	private function _saveNew(){
		$builder = new SQLBuilderInsert();

		$builder->from($this->getTableName());

		// Add support for automatic timestamps.
		if($this->_createdcolumn) $this->set($this->_createdcolumn, Time::GetCurrentGMT() );

		foreach($this->_data as $k => $v){
			// Skip the AI column.
			if($k == $this->_aicolumn) continue;
			
			$builder->set($k, $v);
		}
		
		if($builder->execute()){
			// Set the insert id appropriately.
			if($this->_aicolumn) $this->_data[$this->_aicolumn] = DB::Insert_ID ();

			$this->_dirty = false;
			$this->_exists = true;
			return true;
		}
		else{
			return false;
		}
	}

	private function _saveExisting(){
		$builder = new SQLBuilderUpdate();

		$builder->from($this->getTableName());

		if(sizeof($this->_pkcolumns)){
			foreach($this->_pkcolumns as $v){
				$builder->where(array($v => $this->_data[$v]));
			}
		}

		$builder->limit(1);

		foreach($this->_data as $k => $v){
			if(!in_array($k, $this->_pkcolumns)) $builder->set($k, $v);
		}

		if($builder->execute()){
			$this->_dirty = false;
			$this->_exists = true;
			return true;
		}
		else{
			return false;
		}
	}

	public function _loadFromRecord($record){
		$this->setFromArray($record);

		// And since this is supposed to be the initial load method... toggle the appropriate flags.
		$this->_dirty = false;
		$this->_exists = true;
	}
	
	public function delete(){
		if($this->exists()){
			// Delete the data from the database.
			$builder = new SQLBuilderDelete();
			
			$builder->from($this->getTableName());
			
			if(sizeof($this->_pkcolumns)){
				foreach($this->_pkcolumns as $v){
					$builder->where(array($v => $this->_data[$v]));
				}
			}

			$builder->limit(1);
			
			if($builder->execute()){
				$this->_dirty = false;
				$this->_exists = false;
			}
		}
		
		// Blank out any dependent records based on links.
		foreach($this->_linked as $k => $l){
			
			// I can do this without actually calling them.
			$c = $this->_getLinkClassName($k);
			$model = new $c();
			
			$subbuilder = new SQLBuilderDelete();
			$subbuilder->from($model->getTableName());
			$subbuilder->where($this->_getLinkWhereArray($k));
			$subbuilder->execute();
			
			if(isset($this->_linked[$k]['records'])) unset($this->_linked[$k]['records']);
		}
	}

	public function set($k, $v){
		if(array_key_exists($k, $this->_data)){
			if($this->_data[$k] == $v) return false; // No change needed.
			
			// Set the propagation FIRST, that way I have the old key in memory to lookup.
			$this->_setLinkKeyPropagation($k, $v);

			$this->_data[$k] = $v;
			$this->_dirty = true;
		}
	}
	
	/**
	 * Go through any linked tables and update them if the linking key has been changed.
	 * @param type $key 
	 */
	protected function _setLinkKeyPropagation($key, $newval){
		foreach($this->_linked as $lk => $l){
			$dolink = false;
			// I can't use the getLinkWhereArray function, because that will resolve the key.
			// I need the key itself.
			if(!isset($l['on'])){
				// @todo automatic linking
			}
			elseif(is_array($l['on'])){
				foreach($l['on'] as $k => $v){
					if(is_numeric($k) && $v == $key) $dolink = true;
					elseif(!is_numeric($k) && $k == $key) $dolink = true;
				}
			}
			else{
				if($l['on'] == $key) $dolink = true;
			}
			
			// $dolink should now be true/false.  If true I need to load that linked table and set the new key.
			if(!$dolink) continue;
			
			// Get the data and update it!
			$links = $this->getLink($lk);
			if(!is_array($links)) $links = array($links);
			
			foreach($links as $model){
				$model->set($key, $newval);
			}
		}
	}
	
	protected function _getLinkClassName($linkname){
		// Determine the class.
		$c = (isset($this->_linked[$linkname]['class']))? $this->_linked[$linkname]['class'] : $linkname . 'Model';

		if(!is_subclass_of($c, 'Model')) return null; // @todo Error Handling
		
		return $c;
	}
	
	protected function _getLinkWhereArray($linkname){
		if(!isset($this->_linked[$linkname])) return null; // @todo Error Handling
		
		// Build a standard where criteria that can be used throughout this function.
		$wheres = array();

		if(!isset($this->_linked[$linkname]['on'])){
			return null; // @todo automatic linking.
		}
		elseif(is_array($this->_linked[$linkname]['on'])){
			foreach($this->_linked[$linkname]['on'] as $k => $v){
				if(is_numeric($k)) $wheres[$v] = $this->get($v);
				else $wheres[$k] = $this->get($v);
			}
		}
		else{
			$k = $this->_linked[$linkname]['on'];
			$wheres[$k] = $this->get($k);
		}
		
		return $wheres;
	}
	
	public function getLink($linkname, $order = null){
		if(!isset($this->_linked[$linkname])) return null; // @todo Error Handling
		
		// Try to keep these in cache, so when they change I'll be able to save them on the parent's save function.
		if(!isset($this->_linked[$linkname]['records'])){
			$c = $this->_getLinkClassName($linkname);

			$f = new ModelFactory($c);
			if($this->_linked[$linkname]['link'] == Model::LINK_HASONE) $f->limit(1);
			
			$wheres = $this->_getLinkWhereArray($linkname);
			$f->where($wheres);
			if($order) $f->order($order);
			
			$this->_linked[$linkname]['records'] = $f->get();
			
			// Ensure that it's a valid record and not null.  If it's a LINK_ONE, the factory will return null if it doesn't exist.
			if($this->_linked[$linkname]['records'] === null){
				$this->_linked[$linkname]['records'] = new $c();
				foreach($wheres as $k => $v){
					$this->_linked[$linkname]['records']->set($k, $v);
				}
			}
		}
		
		return $this->_linked[$linkname]['records'];
	}
	
	/**
	 * In 1-to-1 mode, this returns either the single record matched or nothing at all.
	 * In 1-to-M mode, this returns an attached object with the requested search keys, either bound or new.
	 * @param type $linkname
	 * @param type $searchkeys 
	 */
	public function findLink($linkname, $searchkeys = array()){
		$l = $this->getLink($linkname);
		if($l === null) return null;
		
		// aka 1-to-1 mode.
		if(!is_array($l)){
			$f = true;
			foreach($searchkeys as $k => $v){
				if($l->get($k) != $v){
					$f = false;
					break;
				}
			}
			return ($f)? $l : false;
		}
		else{
			foreach($l as $model){
				$f = true;
				foreach($searchkeys as $k => $v){
					if($model->get($k) != $v){
						$f = false;
						break;
					}
				}
				if($f) return $model;
			}
			
			// Still here?  Guess it didn't find it.
			// Create the element, attach it and return that!
			$c = $this->_getLinkClassName($linkname);
			/** @var Model $model **/
			$model = new $c();
			$model->setFromArray($this->_getLinkWhereArray($linkname));
			$model->setFromArray($searchkeys);
			$model->load();
			$this->_linked[$linkname]['records'][] = $model;
			return $model;
		}
		// Search through each of these and find a matching 
	}

	public function setFromArray($array){
		foreach($array as $k => $v){
			$this->set($k, $v);
		}
	}

	public function get($k){
		if(array_key_exists($k, $this->_data)){
			return $this->_data[$k];
		}
		else{
			return null;
		}
	}

	// Moving this to a static function
	/*
	public function getTableName(){
		if(!$this->_tablename){
			// Calculate the table class.

			// It's based on the main class's name.
			$tbl = get_class($this);

			// If it ends in Model... trim that bit off.  It's assumed that the prefix is what we want.
			if(preg_match('/Model$/', $tbl)) $tbl = substr($tbl, 0, -5);

			// Replace any capitalized letters with a _[letter].
			$tbl = preg_replace('/([A-Z])/', '_$1', $tbl);

			// Of course this would produce something similar to _Foo_Mep_Blah.. don't need the beginning _.
			if($tbl{0} == '_') $tbl = substr($tbl, 1);

			// And lowercase.
			$tbl = strtolower($tbl);

			// Prepend the DB_PREFIX and save!
			$this->_tablename = DB_PREFIX . $tbl;
		}
		return $this->_tablename;
	}
	*/

	public function getColumnStructure(){
		return $this->_columns;
	}

	public function getSQLBuilder(){
		if(!$this->_sqlbuilder){
			$this->_sqlbuilder = new SQLBuilderSelect();
			$this->_sqlbuilder
				->from($this->getTableName())
				->select('*')
				->limit(1);
		}

		return $this->_sqlbuilder;
	}

	public function exists(){
		return $this->_exists;
	}

	public function isnew(){
		return !$this->_exists;
	}
	
	
	protected function _getCacheKey(){
		if(!$this->_cacheable) return false;
		if(!(isset($i['primary']) && sizeof($i['primary']))) return false;
		
		$cachekeys = array();
		foreach($i['primary'] as $k){
			$val = $this->get($k);
			if($val === null) $val = 'null';
			elseif($val === false) $val = 'false';
			$cachekeys[] = $val;
		}
		
		return 'DATA:' . self::GetTableName() . ':' . implode('-', $cachekeys);
	}
	


	/*****************    Factory-Related Static Methods *******************/

	/**
	 * Factory shortcut function to do a search for the specific records.
	 *
	 * @param Array || string $where
	 */
	public static function Find($where, $limit = null, $order = null){
		$fac = new ModelFactory(get_called_class());
		$fac->where($where);
		$fac->limit($limit);
		return $fac->get();
	}
	
	
	
	/*******************   Other Static Methods *************************/
	public static function GetTableName(){
		static $_tablename;
		if(!$_tablename){
			// Calculate the table class.

			// It's based on the main class's name.
			$tbl = get_called_class();
			//$tbl = get_class($this);

			// If it ends in Model... trim that bit off.  It's assumed that the prefix is what we want.
			if(preg_match('/Model$/', $tbl)) $tbl = substr($tbl, 0, -5);

			// Replace any capitalized letters with a _[letter].
			$tbl = preg_replace('/([A-Z])/', '_$1', $tbl);

			// Of course this would produce something similar to _Foo_Mep_Blah.. don't need the beginning _.
			if($tbl{0} == '_') $tbl = substr($tbl, 1);

			// And lowercase.
			$tbl = strtolower($tbl);

			// Prepend the DB_PREFIX and save!
			$_tablename = DB_PREFIX . $tbl;
		}
		return $_tablename;
	}
	
	public static function GetSchema(){
		// Because the "Model" class doesn't have a schema... that's up to classes that extend it.
		$m = get_called_class();
		
		return $m::$Schema;
	}
	
	public static function GetIndexes(){
		//// Because the "Model" class doesn't have a schema... that's up to classes that extend it.
		$m = get_called_class();
		
		return $m::$Indexes;
	}
}



class ModelFactory{

	/**
	 * @var Model An instance of the model for lookup-reasons.
	 */
	private $_model;

	public function __construct($model){
		
		// Allow a Model object to be passed in.
		if($model instanceof Model) $this->_model = $model;
		else $this->_model = new $model();

		if(!$this->_model instanceof Model) throw new Exception ($model . ' is not an instance of Model!');

		// Reset the "limit" option set from the Model.
		// The model is designed to get ONLY 1 record, so it needs to be cleared.
		$this->_model->getSQLBuilder()->limit(false);
	}

	public function where($where){
		// Due to the fact that I need to support an arbitrary number of arguments....
		if(func_num_args() == 1){
			$this->_model->getSQLBuilder()->where($where);
		}
		else{
			// @todo Retool this so it does not need to rely on "eval"...
			$execargs = '$where';
			$args = array();
			foreach(func_get_args() as $k => $a){
				if($k == 0) continue; // Skip the first argument... already taken into account.
				$args[$k] = $a;
				$execargs .= ', $args[' . $k . ']';
			}
			$builder = $this->_model->getSQLBuilder();
			eval('$builder->where(' . $execargs . ');');
		}
	}
	
	public function order($order){
		$this->_model->getSQLBuilder()->order($order);
	}

	public function limit($limit){
		$this->_model->getSQLBuilder()->limit($limit);
	}

	public function get(){
		$rs = $this->_model->getSQLBuilder()->execute();
		// @todo Make a DBException.
		if(!$rs) throw new Exception(DB::Error());

		$ret = array();
		foreach($rs as $row){
			$model = clone $this->_model;
			$model->_loadFromRecord($row);
			$ret[] = $model;
		}


		// Only return the model if "1" was requested as the limit.
		if($this->_model->getSQLBuilder()->getLimit() == 1){
			return (sizeof($ret))? $ret[0] : null;
		}
		else{
			return $ret;
		}
	}
}
