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

class Model implements ArrayAccess{

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
	
	const VALIDATION_EMAIL = 'Core::CheckEmailValidity';
	// Regex to match email addresses.
	// @see http://www.regular-expressions.info/email.html
	//const VALIDATION_EMAIL = "/[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/";

	const LINK_HASONE = 'one';
	const LINK_HASMANY = 'many';
	
	/**
	 * Which DataModelInterface should this model execute its operations with.
	 * 99.9% of the time, it's fine to leave this as null, which will use the
	 * system DMI.  If however you want to utilize a Model with Memcache, 
	 * (say for session information), it can be useful.
	 * 
	 * @var DMI_Backend
	 */
	public $interface = null;
	
	/**
	 * @var array Associative array of the data in this corresponding record.
	 */
	protected $_data = array();
	
	/**
	 * The data according to the database.
	 * Useful for something.... :/
	 * @var array
	 */
	protected $_datainit = array();
	
	/**
	 * Allow data to get overloaded onto models.
	 * This is common with Controllers tacking on extra data for templates to better handle the model.
	 * This data is not saved and does not effect the dirty flags.
	 * 
	 * @var array
	 */
	protected $_dataother = array();

	/**
	 * This is quicker than checking if $data and $datainit are the same every time.
	 * @var boolean Dirty flag, if true the data in the model has been changed.
	 */
	protected $_dirty = false;

	/**
	 * @var boolean Flag to signify if this record exists in the database.
	 */
	protected $_exists = false;

	protected $_linked = array();
	
	protected $_cacheable = true;
	
	/**
	 * A cache of the schema for this object.
	 * This is useful because the public Schema definition can have columns that are omitted.
	 * 
	 * @var array
	 */
	protected $_schemacache = null;
	
	/**
	 * The schema as per defined in the extending model.
	 * @var array 
	 */
	public static $Schema = array();
	
	public static $Indexes = array();
	
	
	public function __construct($key = null){
		
		// Update the _data array based on the schema.
		$s = self::GetSchema();
		foreach($s as $k => $v){
			$this->_data[$k] = (isset($v['default']))? $v['default'] : null;
		}
		
		// Check the index (primary), and the incoming data.  If it matches, load it up!
		$i = self::GetIndexes();
		
		if(isset($i['primary']) && func_num_args() == sizeof($i['primary'])){
			foreach($i['primary'] as $k => $v){
				$this->_data[$v] = func_get_arg($k);
			}
		}

		$this->load();
	}

	public function load(){
		
		// If there is no associated table, do not load anything.
		if(!self::GetTableName()){
			return;
		}

		// I need to check the pks first.
		// If they're not set I can't load anything from the database.
		$i = self::GetIndexes();
		
		$keys = array();
		if(isset($i['primary']) && sizeof($i['primary'])){
			foreach($i['primary'] as $k){
				if(($v = $this->get($k)) === null) return;
				
				// Remember the PK's for the query lookup later on.
				$keys[$k] = $v;
			}
		}
		
		if($this->_cacheable){
			$cachekey = $this->_getCacheKey();
			$cache = Core::Cache()->get($cachekey);
			
			// do something if cache succeeds....
		}
		
		$data = Dataset::Init()
			->select('*')
			->table(self::GetTableName())
			->where($keys)
			->execute($this->interface);
		
		if($data->num_rows){
			$this->_data = $data->current();
			$this->_datainit = $data->current();
			
			$this->_dirty = false;
			$this->_exists = true;
		}
		else{
			$this->_dirty = true;
			$this->_exists = false;
		}
		
		return;
	}

	public function save(){
		
		// Only do the same operation if it's been changed.
		if(!$this->_dirty) return false;
		
		// Do the key validation first of all.
		if(get_class($this) == 'PageModel'){
			$s = self::GetSchema();
			foreach($this->_data as $k => $v){
				// Date created and updated have their own validations.
				if(
					$s[$k]['type'] == Model::ATT_TYPE_CREATED ||
					$s[$k]['type'] == Model::ATT_TYPE_UPDATED
				){
					if(!(is_numeric($v) || !$v)) throw new DMI_Exception('Unable to save ' . self::GetTableName() . '.' . $k . ' has an invalid value.');
					continue;
				}
				// This key null?
				if($v === null && !(isset($s[$k]['null']) && $s[$k]['null'])){
					if(!isset($s[$k]['default'])) throw new DMI_Exception('Unable to save ' . self::GetTableName() . '.' . $k . ', null is not allowed and there is no default value set.');
				}
			}
		}
		
		if($this->_exists) $this->_saveExisting();
		else $this->_saveNew();
		
		$this->_exists = true;
		$this->_dirty = false;
		$this->_dataatinit = $this->_data;
		
		// Indicate that something happened.
		return true;
		
		/*
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
		*/
	}
	
	//// A few array access functions \\\\
	
	/**
	 * Whether a offset exists
	 * 
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 * @param mixed $offset An offset to check for.
	 * @return boolean Returns true on success or false on failure.
	 */
	public function offsetExists($offset) {
		return (array_key_exists($offset, $this->_data));
	}

	/**
	 * Offset to retrieve
	 * 
	 * Alias of Model::get()
	 * 
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 * @param mixed $offset The offset to retrieve.
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset) {
		return $this->get($offset);
	}

	/**
	 * Offset to set
	 * 
	 * Alias of Model::set()
	 * 
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset The offset to assign the value to.
	 * @param mixed $value The value to set.
	 * @return void 
	 */
	public function offsetSet($offset, $value) {
		$this->set($offset, $value);
	}

	/**
	 * Offset to unset
	 * 
	 * This just sets the value to null.
	 * 
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset The offset to unset.
	 * @return void 
	 */
	public function offsetUnset($offset) {
		$this->set($offset, null);
	}
	
	/**
	 * Get a valid schema of all keys of this model.
	 * 
	 * @return array 
	 */
	public function getKeySchemas(){
		if($this->_schemacache === null){
			$this->_schemacache = self::GetSchema();
			
			foreach($this->_schemacache as $k => $v){
				// These are all defaults for schemas.
				// Setting them to the default if they're not set will ensure that 
				// 'undefined index' notices are not incurred.
				if(!isset($v['type'])) $this->_schemacache[$k]['type'] = Model::ATT_TYPE_TEXT; // Default if not present.
				if(!isset($v['maxlength'])) $this->_schemacache[$k]['maxlength'] = false;
				if(!isset($v['null'])) $this->_schemacache[$k]['null'] = false;
				if(!isset($v['comment'])) $this->_schemacache[$k]['comment'] = false;
				if(!isset($v['default'])) $this->_schemacache[$k]['default'] = false;
			}
		}
		
		return $this->_schemacache;
	}
	
	/**
	 * Get a valid schema of the requested key of this model.
	 * @param type $key
	 * @return boolean 
	 */
	public function getKeySchema($key){
		$s = $this->getKeySchemas();
		
		if(!isset($s[$key])) return null;
		
		return $s[$key];
	}
	
	/*
	public boolean offsetExists ( mixed $offset )
	public mixed offsetGet ( mixed $offset )
	public void offsetSet ( mixed $offset , mixed $value )
	public void offsetUnset ( mixed $offset )
	*/
	private function _saveNew(){
		$i = self::GetIndexes();
		$s = self::GetSchema();
		
		if(!isset($i['primary'])) $i['primary'] = array(); // No primary schema defined... just don't make the in_array bail out.
		
		$dat = new Dataset();
		$dat->table(self::GetTableName());
		
		$idcol = false;
		foreach($this->_data as $k => $v){
			$keyschema = $s[$k];
			
			switch($keyschema['type']){
				case Model::ATT_TYPE_CREATED:
				case Model::ATT_TYPE_UPDATED:
					$nv = Time::GetCurrentGMT();
					$dat->insert($k, $nv);
					$this->_data[$k] = $nv;
					break;
				
				case Model::ATT_TYPE_ID:
					$dat->setID($k, $this->_data[$k]);
					$idcol = $k; // Remember this for after the save.
					break;
				default:
					$dat->insert($k, $v);
					break;
			}
		}
		
		$dat->execute($this->interface);
		
		if($idcol) $this->_data[$idcol] = $dat->getID();
	}

	/**
	 * Save an existing Model object into the database.
	 * Will create, set and execute a dataset object as appropriately internally.
	 */
	private function _saveExisting(){
		$i = self::GetIndexes();
		$s = self::GetSchema();
		
		// No primary schema defined... just don't make the in_array bail out.
		if(!isset($i['primary'])) $i['primary'] = array(); 
		
		// This is the dataset object that will be integral in this function.
		$dat = new Dataset();
		$dat->table(self::GetTableName());
		
		$idcol = false;
		foreach($this->_data as $k => $v){
			$keyschema = $s[$k];
			// Certain key types have certain functions.
			switch($keyschema['type']){
				case Model::ATT_TYPE_CREATED:
					// Already created... don't update the flag.
					continue 2;
				case Model::ATT_TYPE_UPDATED:
					// Update the updated timestamp with now.
					$nv = Time::GetCurrentGMT();
					$dat->update($k, $nv);
					$this->_data[$k] = $nv;
					continue 2;
				case Model::ATT_TYPE_ID:
					$dat->setID($k, $this->_data[$k]);
					$idcol = $k; // Remember this for after the save.
					continue 2;
			}
			//var_dump($k, $i['primary']);
			// Everything else
			if(in_array($k, $i['primary'])){
				// Just in case the new data changed....
				if($this->_datainit[$k] != $v) $dat->update($k, $v);
				
				$dat->where($k, $this->_datainit[$k]);
				
				$this->_data[$k] = $v;
			}
			else{
				if($this->_datainit[$k] == $v) continue; // Skip non-changed columns
				//echo "Setting [$k] = [$v]<br/>"; // DEBUG
				$dat->update($k, $v);
			}
		}
		//var_dump($dat); die(); // DEBUG
		$dat->execute($this->interface);
		// IDs don't change in updates, else they wouldn't be the id.
	}

	public function _loadFromRecord($record){
		$this->setFromArray($record);

		// And since this is supposed to be the initial load method... toggle the appropriate flags.
		$this->_datainit = $this->_data;
		$this->_dirty = false;
		$this->_exists = true;
	}
	
	public function delete(){
		if($this->exists()){
			// Delete the data from the database.
			$dat = new Dataset();
			
			$dat->table(self::GetTableName());
			
			$i = self::GetIndexes();
			
			if(!isset($i['primary'])){
				throw new Exception('Unable to delete model [ ' . get_class($this) . ' ] without any primary keys.');
			}
			
			foreach($i['primary'] as $k){
				$dat->where(array($k => $this->_data[$k]));
			}

			$dat->limit(1)->delete();
			
			if($dat->execute($this->interface)){
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
			$subbuilder->execute($this->interface);
			
			if(isset($this->_linked[$k]['records'])) unset($this->_linked[$k]['records']);
		}
	}
	
	public function validate($k, $v, $throwexception = false){
		// Is there validation available for this key?
		$s = self::GetSchema();
		
		// Default is true, since by default there is no validation.
		$valid = true;
		
		if(isset($s[$k]['validation'])){
			// Validation exists... check it.
			$check = $s[$k]['validation'];
			
			// Special "this" method.
			if(is_array($check) && sizeof($check) == 2 && $check[0] == 'this'){
				$valid = call_user_func(array($this, $check[1]), $v);
			}
			// Method-based validation.
			elseif( strpos($check, '::') !== false){
				// the method can either be true, false or a string.
				// Only if true is returned will that be triggered as success.
				$valid = call_user_func($check, $v);
			}
			// regex-based validation.  These don't have any return strings so they're easier.
			elseif(
				($check{0} == '/' && !preg_match($check, $v)) ||
				($check{0} == '#' && !preg_match($check, $v))
			){
				$valid = false;
			}
		}
		
		
		if($valid === true){
			// Validation's good, return true!
			return true;
		}
		
		
		// Failed?  Get a good message for the user.
		if($valid === false) $msg = isset($s[$k]['validationmessage']) ? $s[$k]['validationmessage'] : $k . ' fails validation';
		else $msg = $valid;
		
		
		if($throwexception){
			// Validation failed and an Exception was requested.
			throw new ModelValidationException($msg);
		}
		else{
			// Validation failed, but just return the message.
			return $msg;
		}
	}

	/**
	 * Set a value of a specific key.
	 * 
	 * The data is validated automatically as per the specific Model specifications.
	 * 
	 * This supports data overloading.
	 * 
	 * @param string $k The key to set
	 * @param mixed $v The value to set
	 * @return boolean True on success, false on no change needed
	 * @throws ModelValidationException
	 */
	public function set($k, $v){
		// $this->_data will always have the schema keys at least set to null.
		if(array_key_exists($k, $this->_data)){
			
			if($this->_data[$k] == $v) return false; // No change needed.
			
			// Is there validation for this key?
			// That function will handle all of this logic, (including the exception throwing)
			$this->validate($k, $v, true);
			
			// Set the propagation FIRST, that way I have the old key in memory to lookup.
			$this->_setLinkKeyPropagation($k, $v);

			$this->_data[$k] = $v;
			$this->_dirty = true;
			return true;
		}
		else{
			// Ok, let data to get overloaded for convenience sake.
			// This doesn't get any validation or anything however.
			$this->_dataother[$k] = $v;
			return true;
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

	/**
	 * Get the requested key for this object.
	 * 
	 * @param string $k
	 * @return mixed 
	 */
	public function get($k){
		if(array_key_exists($k, $this->_data)){
			return $this->_data[$k];
		}
		elseif(array_key_exists($k, $this->_dataother)){
			return $this->_dataother[$k];
		}
		else{
			return null;
		}
	}
	
	/**
	 * Just return this object as an array
	 * (essentially just the _data array... :p) 
	 */
	public function getAsArray(){
		return array_merge($this->_data, $this->_dataother);
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
	public static function Find($where = array(), $limit = null, $order = null){
		$fac = new ModelFactory(get_called_class());
		$fac->where($where);
		$fac->limit($limit);
		$fac->order($order);
		//var_dump($fac);
		return $fac->get();
	}
	
	public static function Count($where = array()){
		$fac = new ModelFactory(get_called_class());
		$fac->where($where);
		return $fac->count();
	}
	
	
	
	/*******************   Other Static Methods *************************/
	public static function GetTableName(){
		// Just a lookup table for rendered table names.
		// This is useful so the regex functions don't have to run more than once.
		static $_tablenames = array();
		$m = get_called_class();
		
		// Generic models cannot have tables.
		if($m == 'Model') return null;
		
		if(!isset($_tablenames[$m])){
			// Calculate the table class.

			// It's based on the main class's name.
			$tbl = $m;
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
			$_tablenames[$m] = DB_PREFIX . $tbl;
		}
		
		return $_tablenames[$m];
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
	 * Which DataModelInterface should this model execute its operations with.
	 * 99.9% of the time, it's fine to leave this as null, which will use the
	 * system DMI.  If however you want to utilize a Model with Memcache, 
	 * (say for session information), it can be useful.
	 * 
	 * @var DMI_Backend
	 */
	public $interface = null;
	
	/**
	 * What model is this a factory of?
	 * @var string 
	 */
	private $_model;
	
	/**
	 *
	 * @var Dataset
	 */
	private $_dataset;
	

	public function __construct($model){
		
		$this->_model = $model;
		
		$m = $this->_model;
		$this->_dataset = new Dataset();
		$this->_dataset->table($m::GetTablename());
		$this->_dataset->select('*');
	}

	public function where(){
		call_user_func_array(array($this->_dataset, 'where'), func_get_args());
	}
	
	public function order(){
		call_user_func_array(array($this->_dataset, 'order'), func_get_args());
	}

	public function limit(){
		call_user_func_array(array($this->_dataset, 'limit'), func_get_args());
	}
	
	public function get(){
		$rs = $this->_dataset->execute($this->interface);
		
		$ret = array();
		foreach($rs as $row){
			$model = new $this->_model();
			$model->_loadFromRecord($row);
			$ret[] = $model;
		}


		// Only return the model if "1" was requested as the limit.
		if($this->_dataset->_limit == 1){
			return (sizeof($ret))? $ret[0] : null;
		}
		else{
			return $ret;
		}
	}
	
	/**
	 * Get a count of how many records are in this factory 
	 * (without counting the records one by one)
	 * 
	 * @return int
	 */
	public function count(){
		$clone = clone $this->_dataset;
		$rs = $clone->count()->execute($this->interface);
		
		return $rs->num_rows;
	}
}



class ModelException extends Exception{  }

class ModelValidationException extends ModelException {  }