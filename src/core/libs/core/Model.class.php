<?php
/**
 * Core model systems, including Model, ModelException, ModelFactory, and ModelValidationException.
 *
 * This file makes up a major component of the underlying datastore system of Core+.  It offers rather high
 * level of accessing and manipulating data in the datastore, essentially providing as the entirity of the
 * "M" part in MVC.
 *
 * In order to build custom models in this system, they must all extend the Model class.  By doing such,
 * they inherit this class's core abilities and functionality essential for operation, not to mention
 * they are picked up as valid Models in the core.
 *
 * @package Core
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

/**
 * Core Model object, main class responsible for the "M" component in MVC.
 *
 * Every Model in the system be it core models or user-created components, MUST extend this
 * class in order for proper functioning.
 */
class Model implements ArrayAccess {

	/**
	 * Short set of characters, usually < 256 in length.
	 */
	const ATT_TYPE_STRING = 'string';
	/**
	 * Any length of string, (up to approximately 64kb or so of text)
	 */
	const ATT_TYPE_TEXT = 'text';
	/**
	 * Any binary-safe data.
	 */
	const ATT_TYPE_DATA = 'data';
	/**
	 * Integer only, no precision
	 */
	const ATT_TYPE_INT = 'int';
	/**
	 * Number with decimal places.
	 */
	const ATT_TYPE_FLOAT = 'float';
	/**
	 * True or false value, approximate values are translated appropriately, (such as "yes" or "0").
	 */
	const ATT_TYPE_BOOL = 'boolean';
	/**
	 * Value from a set of options
	 */
	const ATT_TYPE_ENUM = 'enum';
	/**
	 * This is a unique id for a table, but instead of relying on auto-increment, it's based on Core's GenerateUUID method.
	 */
	const ATT_TYPE_UUID = '__uuid';
	/**
	 * A column that is a "foreign key" to a UUID column.
	 */
	const ATT_TYPE_UUID_FK = '__uuid_fk';
	/**
	 * The auto-incrementing integer of a table
	 */
	const ATT_TYPE_ID = '__id';
	/**
	 * A column that is a "foreign key" to an ID column.
	 */
	const ATT_TYPE_ID_FK = '__id_fk';
	/**
	 * Date this record was last updated, updated automatically
	 */
	const ATT_TYPE_UPDATED = '__updated';
	/**
	 * Date this record was originally created, set automatically
	 */
	const ATT_TYPE_CREATED = '__created';
	/**
	 * Date this record was deleted, (if deleted)
	 * Will be updated automatically on delete() instead of actually deleting the record.
	 *
	 * Calling delete() a second time on a "deleted" record will actually delete it from the system.
	 */
	const ATT_TYPE_DELETED = '__deleted';
	/**
	 * Site ID for the current site.
	 * Has no function outside of enterprise/multisite mode.
	 */
	const ATT_TYPE_SITE = '__site';

	/**
	 * Alias of another column in the record.
	 * Useful for column renames where the data needs to persist to the new column name.
	 */
	const ATT_TYPE_ALIAS = '__alias';

	/**
	 * Mysql datetime and ISO 8601 formatted datetime field.
	 * This format is discouraged in Core+, but allowed.
	 *
	 * This stores the data as 'YYYY-mm-dd HH:ii:ss'.
	 */
	const ATT_TYPE_ISO_8601_DATETIME = 'ISO_8601_datetime';

	/**
	 * Mysql-specific timestamp field.
	 * This stores the data as 'YYYY-mm-dd HH:ii:ss'.
	 */
	const ATT_TYPE_MYSQL_TIMESTAMP = 'mysql_timestamp';

	/**
	 * Mysql date and ISO 8601 formatted date field.
	 * This format is discouraged in Core+, but allowed.
	 *
	 * This stores the data as 'YYYY-mm-dd'.
	 */
	const ATT_TYPE_ISO_8601_DATE = 'ISO_8601_date';

	/**
	 * Validation for any non-blank value.
	 */
	const VALIDATION_NOTBLANK = "/^.+$/";

	/**
	 * Validation for a valid email address.
	 *
	 * Optionally can use DNS lookups to perform a more indepth validation of the domain.
	 */
	const VALIDATION_EMAIL = 'Core::CheckEmailValidity';
	// Regex to match email addresses.
	// @see http://www.regular-expressions.info/email.html
	//const VALIDATION_EMAIL = "/[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/";

	/**
	 * Validation for a valid protocol-based URL.
	 *
	 * This includes http, https, ftp, ssh, and any other protocol.
	 */
	const VALIDATION_URL = '#^[a-zA-Z]+://.+$#';

	/**
	 * Validation for a valid web URL.
	 *
	 * This will only match http:// or https://.
	 */
	const VALIDATION_URL_WEB = '#^[hH][tT][tT][pP][sS]{0,1}://.+$#';

	/**
	 * Definition for a model that has exactly one child table as a dependency.
	 *
	 * !WARNING! This gets deleted automatically if the parent is deleted!
	 */

	const LINK_HASONE  = 'one';
	/**
	 * Definition for a model that has several records from a child table for dependencies.
	 *
	 *  !WARNING! These get deleted automatically if the parent is deleted!
	 */
	const LINK_HASMANY = 'many';

	/**
	 * Definition for a model that *BELONGS* to one table.
	 *
	 * This is important because changes do not propagate up to the parent, as deleting the child
	 * should have no effect on the parent!
	 */
	const LINK_BELONGSTOONE = 'belongs_one';

	/**
	 * Definition for a model that *BELONGS* to many tables.
	 *
	 * A good example of this would be a linking table, that contains a Many-to-Many relationship
	 * between two models.
	 */
	const LINK_BELONGSTOMANY = 'belongs_many';

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
	 * Some models have encrypted fields.  This will store the decrypted data for the application to access.
	 * @var array
	 */
	protected $_datadecrypted = null;

	/**
	 * Allow data to get overloaded onto models.
	 * This is common with Controllers tacking on extra data for templates to better handle the model.
	 * This data is not saved and does not effect the dirty flags.
	 *
	 * @var array
	 */
	protected $_dataother = array();

	/**
	 * @var boolean Flag to signify if this record exists in the database.
	 */
	protected $_exists = false;

	/**
	 * @var array Set of linked children models on this model, populated by the Constructor and *link methods.
	 */
	protected $_linked = array();

	protected $_cacheable = true;

	/**
	 * @var array The schema as per defined in the extending model.
	 */
	public static $Schema = array();

	/**
	 * @var array Any indexes that are required on this data.  Must be defined in the extending model if used.
	 */
	public static $Indexes = array();

	/**
	 * @var bool Set to true if this model is searchable, (and auto-create the necessary search index fields).
	 */
	public static $HasSearch = false;

	/**
	 * @var bool Set to true if this model has a created timestamp, (and auto-create the necessary search index fields).
	 */
	public static $HasCreated = false;

	/**
	 * @var bool Set to true if this model has an updated timestamp, (and auto-create the necessary search index fields).
	 */
	public static $HasUpdated = false;

	/**
	 * @var bool Set to true if this model has a deleted timestamp, (and auto-create the necessary search index fields).
	 */
	public static $HasDeleted = false;

	public static $_ModelCache = array();

	/**
	 * @var array Cache for blank Find statements.
	 *
	 * ONLY used with a completely blank Find() query!!!
	 */
	public static $_ModelFindCache = array();

	/**
	 * @var array Array of rendered Schemas for each named model.
	 *
	 * Used to accelerate GetSchema on multiple calls for the same model type.
	 */
	protected static $_ModelSchemaCache = array();


	/*************************************************************************
	 ****                   STANDARD PUBLIC METHODS                       ****
	 *************************************************************************/

	/**
	 * Create a new instance of the requested model.
	 *
	 * @param null $key
	 *
	 * @throws Exception
	 */
	public function __construct($key = null) {

		// Update the _data array based on the schema.
		$s = self::GetSchema();
		foreach ($s as $k => $v) {
			// Aliases don't get special treatment :P
			if($v['type'] == Model::ATT_TYPE_ALIAS) continue;

			// Populate the default options based on the schema.
			$this->_data[$k] = (isset($v['default'])) ? $v['default'] : null;

			if(isset($v['link'])){
				// If the link is requested on this property, populate the linked array for the corresponding model!
				if(is_array($v['link'])){
					if(!isset($v['link']['model'])){
						throw new Exception('Required attribute [model] not provided on link [' . $k . '] of model [' . get_class($this) . ']');
					}
					$linkmodel = $v['link']['model'];
					$linktype  = isset($v['link']['type']) ? $v['link']['type'] : Model::LINK_HASONE;
					$linkon    = isset($v['link']['on']) ? $v['link']['on'] : 'id';
				}
				else{
					// Allow the short-hand version to be used too.
					// This will setup a 1-to-1 relationship.
					$linkmodel = $v['link'];
					$linktype  = Model::LINK_HASONE;
					$linkon    = 'id'; // ... erm yeah... hopefully this is it!
				}


				// And populate the linked array with this link data.
				$this->_linked[$linkmodel] = [
					'on'   => [$linkon => $k],
					'link' => $linktype,
				];
			}
		}

		// Check the index (primary), and the incoming data.  If it matches, load it up!
		$i = self::GetIndexes();
		$pri = (isset($i['primary'])) ? $i['primary'] : false;
		if($pri && !is_array($pri)) $pri = array($pri);

		if ($pri && func_num_args() == sizeof($i['primary'])) {
			foreach ($pri as $k => $v) {
				$this->_data[$v] = func_get_arg($k);
			}
		}

		if($key !== null){
			$this->load();
		}
	}

	/**
	 * Load this record from the datastore.
	 *
	 * Generally not needed to be called directly, but can be if required.
	 */
	public function load() {

		// If there is no associated table, do not load anything.
		if (!self::GetTableName()) {
			return;
		}

		// I need to check the pks first.
		// If they're not set I can't load anything from the database.
		$i = self::GetIndexes();
		$s = self::GetSchema();

		$pri = (isset($i['primary'])) ? $i['primary'] : false;
		if($pri && !is_array($pri)) $pri = array($pri);

		$keys = array();
		if ($pri && sizeof($i['primary'])) {
			foreach ($pri as $k) {
				$v = $this->get($k);

				// 2012.12.15 cp - I am changing this from return to continue to support enterprise data in non-enterprise mode.
				// specifically, the page model.  Pages can be -1 for global or a specific ID for that site.
				// in non-multisite mode, there are no other sites, so -1 and 0 are synonymous even though it's a primary key.
				if ($v === null) continue;

				// Remember the PK's for the query lookup later on.
				$keys[$k] = $v;
			}
		}

		if ($this->_cacheable) {
			$cachekey = $this->_getCacheKey();
			$cache    = Core::Cache()->get($cachekey);

			// do something if cache succeeds....
		}

		// If the enterprise/multimode is set and enabled and there's a site column here,
		// that should be enforced at a low level.
		/** @noinspection PhpUndefinedClassInspection */
		if(
			isset($s['site']) &&
			$s['site']['type'] == Model::ATT_TYPE_SITE &&
			Core::IsComponentAvailable('enterprise') &&
			MultiSiteHelper::IsEnabled() &&
			$this->get('site') === null
		){
			/** @noinspection PhpUndefinedClassInspection */
			$keys['site'] = MultiSiteHelper::GetCurrentSiteID();
		}


		$data = Core\Datamodel\Dataset::Init()
			->select('*')
			->table(self::GetTableName())
			->where($keys)
			->execute($this->interface);

		if ($data->num_rows) {
			$this->_data     = $data->current();
			$this->_datainit = $data->current();

			$this->_exists = true;
		}
		else {
			$this->_exists = false;
		}

		return;
	}

	/**
	 * Save this Model into the datastore.
	 *
	 * Return true if saved successfully, false if no change required,
	 * and will throw a DMI_Exception if there was an error.
	 *
	 * @return boolean
	 * @throws DMI_Exception
	 */
	public function save() {

		// Only do the same operation if it's been changed.
		// This is actually a little more in depth than simply seeing if it's been modified, as I also
		// have to look into the linked classes and see if it exists
		$save = false;
		// new models always get saved.
		if(!$this->_exists){
			$save = true;
		}
		// Models that have changed content always get saved.
		elseif($this->changed()){
			$save = true;
		}
		else{
			foreach($this->_linked as $k => $l){
				if(isset($l['records'])){
					// If there are any linked models in the records array, trigger the save.
					$save = true;
					break;
				}
				if(isset($l['purged'])){
					// If there are any linked models in the purged array, trigger the save, (to delete them).
					$save = true;
					break;
				}
			}
		}

		if(!$save){
			return false;
		}
		/*
		// Do the key validation first of all.
		if (get_class($this) == 'PageModel') {
			$s = self::GetSchema();
			foreach ($this->_data as $k => $v) {
				// Date created and updated have their own validations.
				if (
					$s[$k]['type'] == Model::ATT_TYPE_CREATED ||
					$s[$k]['type'] == Model::ATT_TYPE_UPDATED
				) {
					if (!(is_numeric($v) || !$v)) throw new DMI_Exception('Unable to save ' . self::GetTableName() . '.' . $k . ' has an invalid value.');
					continue;
				}
				// This key null?
				if ($v === null && !(isset($s[$k]['null']) && $s[$k]['null'])) {
					if (!isset($s[$k]['default'])) throw new DMI_Exception('Unable to save ' . self::GetTableName() . '.' . $k . ', null is not allowed and there is no default value set.');
				}
			}
		}
		*/

		// Dispatch the pre-save hook for models.
		// This allows utilities to hook in and modify the model or perform some other action.
		HookHandler::DispatchHook('/core/model/presave', $this);

		// NEW in 2.8, I need to run through the linked models and see if any local key is a foreign key of a linked model.
		// If it is, then I need to save that foreign model so I can get its updated primary key, (if it changed).
		foreach($this->_linked as $k => $l){
			// If this link has a 1-sized relationship and the local node is set as a FK, then read it as such.

			if(!is_array($l['on'])){
				// make sure it's an array.
				$l['on'] = array($l['on'] => $l['on'] );
			}

			if($l['link'] == Model::LINK_HASONE && sizeof($l['on']) == 1){
				reset($l['on']);
				$remotek = key($l['on']);
				$localk  = $l['on'][$remotek];
				$locals = $this->getKeySchema($localk);

				// No schema?  Ok, nothing to save to!
				if(!$locals) continue;

				// If this is not a FK, then I don't care!
				if($locals['type'] != Model::ATT_TYPE_UUID_FK) continue;

				// OTHERWISE..... ;)
				/** @var Model $model */
				$model = $l['records'];
				$model->save();
				$this->set($localk, $model->get($remotek));
			}
		}


		if ($this->_exists) $this->_saveExisting();
		else $this->_saveNew();


		// Go through any linked tables and ensure that they're saved as well.
		foreach($this->_linked as $k => $l){
			// No need to save if it was never loaded.
			//if(!(isset($l['records']))) continue;

			// No need to save if it was never loaded.
			//if(!(isset($l['records']) || $this->changed())) continue;

			switch($l['link']){
				case Model::LINK_HASONE:
					$models = isset($l['records']) ? array($l['records']) : null;
					$deletes = isset($l['purged']) ? $l['purged'] : null;
					break;
				case Model::LINK_HASMANY:
					$models = isset($l['records']) ? $l['records'] : null;
					$deletes = isset($l['purged']) ? $l['purged'] : null;
					break;
				default:
					// There is no default behaviour... other than to ignore it.
					$models = null;
					$deletes = null;
					break;
			}

			// Are there saves requested?
			if($models){
				foreach($models as $model){
					/** @var $model Model */
					// Ensure all linked fields still match up.  Something may have been changed in the parent.
					$model->setFromArray($this->_getLinkWhereArray($k));
					$model->save();
				}
			}

			// Are there deletes requested?
			if($deletes){
				foreach($deletes as $model){
					$model->delete();
				}

				unset($l['purged']);
			}
		}

		$this->_exists     = true;
		$this->_datainit = $this->_data;

		HookHandler::DispatchHook('/core/model/postsave', $this);

		// Indicate that something happened.
		return true;
	}

	/**
	 * Get the requested key for this object.
	 *
	 * @param string $k
	 *
	 * @return mixed
	 */
	public function get($k) {
		if($this->_datadecrypted !== null && array_key_exists($k, $this->_datadecrypted)){
			// Check if the data exists and was decrypted from the database.
			return $this->_datadecrypted[$k];
		}
		elseif (array_key_exists($k, $this->_data)) {
			// Check if this data was loaded from the original data array
			return $this->_data[$k];
		}
		elseif($this->getKeySchema($k) && $this->getKeySchema($k)['type'] == Model::ATT_TYPE_ALIAS){
			// This key is actually just an alias to another key.
			return $this->get( $this->getKeySchema($k)['alias'] );
		}
		elseif (array_key_exists($k, $this->_dataother)) {
			// Check if this data was set from the "set" command on a non-tracked column.
			return $this->_dataother[$k];
		}
		elseif($this->getLink($k)){
			// Check if this data is actually a linked model
			return $this->getLink($k);
		}
		else {
			return null;
		}
	}

	/**
	 * Get the human-readable label for this record.
	 *
	 * By default, it will sift through the schema looking for keys that appear to be human-readable terms,
	 * but for best results, please extend this method and have it return what's necessary for the given Model.
	 *
	 * @return string
	 */
	public function getLabel(){
		$s = $this->getKeySchemas();

		if(isset($s['name'])){
			return $this->get('name');
		}
		elseif(isset($s['title'])){
			return $this->get('title');
		}
		elseif(isset($s['key'])){
			return $this->get('key');
		}
		else{
			return 'Unnamed ' . $this->getPrimaryKeyString();
		}
	}

	/**
	 * Just return this object as an array
	 * (essentially just the _data array... :p)
	 *
	 * @return array
	 */
	public function getAsArray() {
		// Has there been data that has been decrypted?
		if($this->_datadecrypted !== null){
			return array_merge($this->_data, $this->_dataother, $this->_datadecrypted);
		}
		else{
			return array_merge($this->_data, $this->_dataother);
		}
	}

	/**
	 * Return this object as a flattened JSON array using json_encode.
	 *
	 * @return string
	 */
	public function getAsJSON(){
		return json_encode($this->getAsArray());
	}

	/**
	 * Get the data of this model.
	 * Don't use this, it's probably not what you need.
	 *
	 * @return array
	 */
	public function getData(){
		return $this->_data;
	}

	/**
	 * Get the initial data of this model as it was when it was loaded from teh database.
	 *
	 * @return array|null
	 */
	public function getInitialData(){
		return $this->_datainit;
	}

	/**
	 * Get a valid schema of all keys of this model.
	 *
	 * This will ensure all the core optional attributes are set at the
	 * default value and a few other dynamic attributes.
	 *
	 * Alias of Model::GetSchema()
	 *
	 * @return array
	 */
	public function getKeySchemas() {
		return self::GetSchema();
	}

	/**
	 * Get a valid schema of the requested key of this model.
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public function getKeySchema($key) {
		$s = self::GetSchema();

		if (!isset($s[$key])) return null;

		return $s[$key];
	}

	/**
	 * Get a textual representation of this Model as a flat string.
	 *
	 * Used by the search systems to index the model, (or multiple models into one).
	 *
	 * @return string
	 */
	public function getSearchIndexString(){
		// The default behaviour is to sift through the records on this model itself.
		$strs = [];

		foreach($this->getKeySchemas() as $k => $dat){

			// Skip file uploads.
			if(isset($dat['form']) && isset($dat['form']['type'])){
				// Skip files.
				if($dat['form']['type'] == 'file') continue;
			}

			// Skip the search indexes themselves
			if($k == 'search_index_str') continue;
			if($k == 'search_index_pri') continue;
			if($k == 'search_index_sec') continue;

			switch($dat['type']){
				case Model::ATT_TYPE_TEXT:
				case Model::ATT_TYPE_STRING:
					$val = $this->get($k);
					if($val) $strs[] = $val;
					break;
			}
		}

		return implode(' ', $strs);
	}

	/**
	 * Lookup and see if this model instance has a draft saved for it.
	 *
	 * @return bool
	 */
	public function hasDraft(){
		if(Core::IsComponentAvailable('model-audit')){
			/** @noinspection PhpUndefinedNamespaceInspection */
			/** @noinspection PhpUndefinedClassInspection */
			return ModelAudit\Helper::ModelHasDraft($this);
		}
		else{
			// If the underlying component is not available, drafts cannot be enabled!
			return false;
		}
	}

	/**
	 * Get the draft status of this model.
	 *
	 * @return string
	 */
	public function getDraftStatus(){
		if(!$this->exists()){
			// If it's here, it must be a draft creation :p
			return 'pending_creation';
		}
		elseif($this->hasDraft() && $this->get('___auditmodel')->get('data') == '[]'){
			// A blank data record on the audit model indicates that the request is to be deleted.
			return 'pending_deletion';
		}
		elseif($this->hasDraft()){
			// Otherwise, just changes were performed.
			return 'pending_update';
		}
		else{
			// And if it exists and no draft object attached... then this doesn't have one.
			return '';
		}
	}

	/**
	 * Load this model from an associative array, or record.
	 * This is meant to be called from the Factory system, and the data passed in
	 * MUST be sanitized and valid!
	 *
	 * @param array $record
	 */
	public function _loadFromRecord($record) {

		$this->_data = $record;

		// And since this is supposed to be the initial load method... toggle the appropriate flags.
		$this->_datainit = $this->_data;
		$this->_exists   = true;
	}

	/**
	 * Delete this record from the datastore.
	 *
	 * Will IMMEDIATELY remove the record!
	 *
	 * If this model has a "deleted" column that is set as a zero value, that record is set to the current timestamp instead.
	 * This functionality is meant for advanced record tracking such as those in use in sync systems.
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function delete() {

		$s = self::GetSchema();

		foreach ($this->_data as $k => $v) {
			if(!isset($s[$k])){
				// This key was not in the schema.  Probable reasons for this would be a column that was
				// removed from the schema in an upgrade, but was never removed from the database.
				// This is typical because the installer tries to be non-destructive when it comes to data.
				continue;
			}
			$keyschema = $s[$k];
			// Certain key types have certain functions.
			if($keyschema['type'] == Model::ATT_TYPE_DELETED) {
				// Is this record already set as deleted?
				// If it is, then proceed with the delete as usual.
				// Otherwise, update the record instead of deleting it.
				if(!$v){
					$nv = Time::GetCurrentGMT();
					$this->set($k, $nv);
					return $this->save();
				}
				else{
					// I can safely break out of the foreach statement at this stage.
					break;
				}
			}
		}

		// Blank out any dependent records based on links.
		// Since relationships may have various levels, I need to execute delete on each of them instead of just
		// issuing a broad delete request.
		foreach ($this->_linked as $k => $l) {

			switch($l['link']){
				case Model::LINK_HASONE:
					// Delete this child, (and any of its subordinates).
					$child = $this->getLink($k);
					$child->delete();
					break;
				case Model::LINK_HASMANY:
					// Request all the children and issue a delete on them.
					$children = $this->getLink($k);
					foreach($children as $child){
						$child->delete();
					}
					break;
				// There is no default behaviour... other than to ignore it.
			}

			if (isset($this->_linked[$k]['records'])) unset($this->_linked[$k]['records']);
		}


		if ($this->exists()) {
			$n = $this->_getTableName();
			$i = self::GetIndexes();
			// Delete the data from the database.
			$dat = new Core\Datamodel\Dataset();

			$dat->table($n);

			if (!isset($i['primary'])) {
				throw new Exception('Unable to delete model [ ' . get_class($this) . ' ] without any primary keys.');
			}

			$pri = $i['primary'];
			if(!is_array($pri)) $pri = array($pri);

			foreach ($pri as $k) {
				$dat->where(array($k => $this->_data[$k]));
			}

			$dat->limit(1)->delete();

			if ($dat->execute($this->interface)) {
				$this->_exists = false;
			}
		}


	}

	/**
	 * Handle data validation for keys.
	 *
	 * This will lookup if any "validation" is set on the schema, and check it if it exists.
	 * This will not actually do any setting, simply return true or throw an exception, (if requested).
	 *
	 * @param string $k The key to validate
	 * @param mixed  $v The value to validate with
	 * @param bool   $throwexception Set to true if you would like this function to throw errors.
	 *
	 * @return bool|mixed|string
	 * @throws ModelValidationException
	 */
	public function validate($k, $v, $throwexception = false) {
		// Is there validation available for this key?
		$s = self::GetSchema();

		// Default is true, since by default there is no validation.
		$valid = true;

		// If this key is not required and not filled in, skip validation.... it's valid.
		if($v == '' || $v === null){
			if(!isset($s['required']) || !$s['required']){
				return true;
			}
		}

		if (isset($s[$k]['validation'])) {
			// Validation exists... check it.
			$check = $s[$k]['validation'];

			// Special "this" method.
			if (is_array($check) && sizeof($check) == 2 && $check[0] == 'this') {
				$valid = call_user_func(array($this, $check[1]), $v);
			}
			// Method-based validation.
			elseif (strpos($check, '::') !== false) {
				// the method can either be true, false or a string.
				// Only if true is returned will that be triggered as success.
				$valid = call_user_func($check, $v);
			}
			// regex-based validation.  These don't have any return strings so they're easier.
			elseif (
				($check{0} == '/' && !preg_match($check, $v)) ||
				($check{0} == '#' && !preg_match($check, $v))
			) {
				$valid = false;
			}
		}


		if ($valid === true) {
			// Validation's good, return true!
			return true;
		}


		// Failed?  Get a good message for the user.
		if ($valid === false) $msg = isset($s[$k]['validationmessage']) ? $s[$k]['validationmessage'] : $k . ' fails validation';
		else $msg = $valid;


		if ($throwexception) {
			// Validation failed and an Exception was requested.
			throw new ModelValidationException($msg);
		}
		else {
			// Validation failed, but just return the message.
			return $msg;
		}
	}

	/**
	 * Translate a key to the strict version of it.
	 * ie: if a given key is a "Boolean" and the string "true" is given, that should be resolved to 1.
	 *
	 * This will also handle null and default values more gracefully than trying to pass them directly to the
	 * underlying datamodel.
	 *
	 * @param string $k The key to lookup
	 * @param mixed $v  The value to check
	 *
	 * @return mixed The translated value
	 */
	public function translateKey($k, $v){
		$s = self::GetSchema();

		// Not in the schema.... just return the value unmodified.
		if(!isset($s[$k])) return $v;

		// Shortcut
		$t = &$s[$k];

		$type = $t['type']; // Type is one of the required properties.

		// Try to determine the generic default value if not set.
		if(!isset($t['default'])){
			if(isset($t['null']) && $t['null']){
				// Easy enough!
				$default = null;
			}
			else{
				// Not so easy, I need to guess the default based on the type.
				switch($type){
					case Model::ATT_TYPE_BOOL:
					case Model::ATT_TYPE_CREATED:
					case Model::ATT_TYPE_FLOAT:
					case Model::ATT_TYPE_INT:
					case Model::ATT_TYPE_UPDATED:
					case Model::ATT_TYPE_DELETED:
						$default = '0';
						break;
					case Model::ATT_TYPE_DATA:
					case Model::ATT_TYPE_STRING:
					case Model::ATT_TYPE_TEXT:
						$default = '';
						break;
					case Model::ATT_TYPE_ISO_8601_DATE:
						$default = '0000-00-00';
						break;
					case Model::ATT_TYPE_ISO_8601_DATETIME:
						$default = '0000-00-00 00:00:00';
						break;
					case Model::ATT_TYPE_ID:
					case Model::ATT_TYPE_UUID:
						$default = null;
						break;
					// Umm
					default:
						$default = '';
						break;
				}
			}
		}
		else{
			// Simple enough :)
			$default = $t['default'];
		}


		// This part of the logic will detect if the default value should be used!
		// Usually this is pretty simple, but some values require a little extra care.
		switch($type){
			// Damn datetime strings :/
			case Model::ATT_TYPE_ISO_8601_DATE:
				if($v == '' || $v == '0000-00-00' || $v === null){
					// This gets remapped to "default", which may or may not be the same.
					$v = $default;
				}
				break;

			case Model::ATT_TYPE_ISO_8601_DATETIME:
				if($v == '' || $v == '0000-00-00 00:00:00' || $v === null){
					// This gets remapped to "default", which may or may not be the same.
					$v = $default;
				}
				break;

			default:
				// These may or may not remapped... all depends on what the "default" value is.
				if($v === null){
					$v = $default;
				}
				break;
		}



		// Now for the validation.
		// This will translate invalid values to valid ones!
		// Most values are suitable as-is.
		switch($type){
			case Model::ATT_TYPE_BOOL:
				if($v === true){
					$v = '1';
				}
				elseif($v === false){
					$v = '0';
				}
				else{
					switch(strtolower($v)){
						// This is used by checkboxes
						case 'yes':
							// A single checkbox will have the value of "on" if checked
						case 'on':
							// Hidden inputs will have the value of "1"
						case 1:
							// sometimes the string "true" is sent.
						case 'true':
							$v = '1';
							break;
						default:
							$v = '0';
					}
				}
				break;
		}

		return $v;
	}

	/**
	 * Set a value of a specific key.
	 *
	 * The data is validated automatically as per the specific Model specifications.
	 *
	 * This supports data overloading.
	 *
	 * @param string $k The key to set
	 * @param mixed  $v The value to set
	 *
	 * @return boolean True on success, false on no change needed
	 * @throws ModelValidationException
	 */
	public function set($k, $v) {
		// $this->_data will always have the schema keys at least set to null.
		if (array_key_exists($k, $this->_data)) {

			$keydat = $this->getKeySchema($k);

			if($this->_data[$k] === null && $v === null){
				// Both values are NULL, No change needed.
				return false;
			}
			elseif(
				$this->_data[$k] !== null &&
				$keydat['type'] == Model::ATT_TYPE_STRING
			){
				if(\Core\compare_strings($this->_data[$k], $v)){
					// The attribute type is a string and they seem to be identical...
					return false;
				}
			}
			elseif ($this->_data[$k] !== null){
				if(\Core\compare_values($this->_data[$k], $v)){
					// The data is something or other, but they still seem to be identical.
					return false;
				}
			}

			// Is there validation for this key?
			// That function will handle all of this logic, (including the exception throwing)
			$this->validate($k, $v, true);

			// Some model types get special treatment with the translation function... ie: booleans.
			// everything else will simply return the unmodified value.
			$v = $this->translateKey($k, $v);

			// Set the propagation FIRST, that way I have the old key in memory to lookup.
			$this->_setLinkKeyPropagation($k, $v);

			// See if this is an encrypted field first.  If it is... set the decrypted version and encrypt it.
			if($keydat['encrypted']){
				$this->decryptData();
				$this->_datadecrypted[$k] = $v;
				$this->_data[$k] = $this->encryptValue($v);
			}
			else{
				$this->_data[$k] = $v;
			}
			return true;
		}
		elseif($this->getKeySchema($k) && $this->getKeySchema($k)['type'] == Model::ATT_TYPE_ALIAS){
			// It's an alias, set the aliased column instead.
			return $this->set( $this->getKeySchema($k)['alias'], $v);
		}
		else {
			// Ok, let data to get overloaded for convenience sake.
			// This doesn't get any validation or anything however.
			$this->_dataother[$k] = $v;
			return true;
		}
	}

	/**
	 * Get the model factory for a given link.
	 *
	 * Useful for manipulating the factory of the data.
	 *
	 * @param $linkname
	 *
	 * @return ModelFactory
	 */
	public function getLinkFactory($linkname){
		if (!isset($this->_linked[$linkname])) return null; // @todo Error Handling

		$c = $this->_getLinkClassName($linkname);

		$f = new ModelFactory($c);
		switch($this->_linked[$linkname]['link']){
			case Model::LINK_HASONE:
			case Model::LINK_BELONGSTOONE:
				$f->limit(1);
				break;
		}

		$wheres = $this->_getLinkWhereArray($linkname);
		$f->where($wheres);

		// Yup, no get or anything, just build the factory.
		return $f;
	}

	/**
	 * Get linked models to this model based on a link name
	 *
	 * If the link type is a one-to-one or many-to-one, (HASONE), a single Model is returned.
	 * else this behaves as the Find function, where an array of models is returned.
	 *
	 * @param string      $linkname The linked model name (minus the Model part)
	 * @param null|string $order    Specify the order clause
	 *
	 * @return Model|array
	 */
	public function getLink($linkname, $order = null) {
		if (!isset($this->_linked[$linkname])) return null; // @todo Error Handling

		// Allow order to be set from the model itself.
		if($order === null && isset($this->_linked[$linkname]['order'])){
			$order = $this->_linked[$linkname]['order'];
		}

		// Try to keep these in cache, so when they change I'll be able to save them on the parent's save function.
		if (!isset($this->_linked[$linkname]['records'])) {

			$f = $this->getLinkFactory($linkname);
			$c = $this->_getLinkClassName($linkname);
			$wheres = $this->_getLinkWhereArray($linkname);

			if ($order) $f->order($order);
			$this->_linked[$linkname]['records'] = $f->get();

			// Ensure that it's a valid record and not null.  If it's a LINK_ONE, the factory will return null if it doesn't exist.
			if ($this->_linked[$linkname]['records'] === null) {
				$this->_linked[$linkname]['records'] = new $c();
				foreach ($wheres as $k => $v) {
					$this->_linked[$linkname]['records']->set($k, $v);
				}
			}
		}

		return $this->_linked[$linkname]['records'];
	}

	/**
	 * In 1-to-1 mode, this returns either the single record matched or nothing at all.
	 * In 1-to-M mode, this returns an attached object with the requested search keys, either bound or new.
	 *
	 * @param string $linkname
	 * @param array  $searchkeys
	 * @return bool|\Model|null
	 */
	public function findLink($linkname, $searchkeys = array()) {
		$l = $this->getLink($linkname);
		if ($l === null) return null;

		// aka 1-to-1 mode.
		if (!is_array($l)) {
			$f = true;
			foreach ($searchkeys as $k => $v) {
				if ($l->get($k) != $v) {
					$f = false;
					break;
				}
			}
			return ($f) ? $l : false;
		}
		else {
			foreach ($l as $model) {
				$f = true;
				foreach ($searchkeys as $k => $v) {
					if ($model->get($k) != $v) {
						$f = false;
						break;
					}
				}
				if ($f) return $model;
			}

			// Still here?  Guess it didn't find it.
			// Create the element, attach it and return that!
			$c = $this->_getLinkClassName($linkname);
			/** @var $model Model */
			$model = new $c();
			$model->setFromArray($this->_getLinkWhereArray($linkname));
			$model->setFromArray($searchkeys);
			var_dump($model); die();
			$model->load();
			$this->_linked[$linkname]['records'][] = $model;
			return $model;
		}
		// Search through each of these and find a matching
	}

	/**
	 * Add a model to the set of linked records, (or replace it in the case or HASONE).
	 *
	 * Administrative method used internally by some systems.  This allows a link to be overwritten externally.
	 *
	 * Particularly useful for BELONGSTOONE models being updated by their parent.
	 *
	 * @param       $linkname
	 * @param Model $model
	 *
	 * @return void
	 */
	public function setLink($linkname, Model $model) {
		if (!isset($this->_linked[$linkname])) return; // @todo Error Handling

		// Update the cached model.
		switch($this->_linked[$linkname]['link']){
			case Model::LINK_HASONE:
			case Model::LINK_BELONGSTOONE:
				$this->_linked[$linkname]['records'] = $model;
				break;
			case Model::LINK_HASMANY:
			case Model::LINK_BELONGSTOMANY:
				if(!isset($this->_linked[$linkname]['records'])) $this->_linked[$linkname]['records'] = array();
				$this->_linked[$linkname]['records'][] = $model;
				break;
		}
	}

	/**
	 * Reset the linked models in this model.  Useful for deleting a child and not wanting them to come back as linked.
	 *
	 * @param $linkname
	 */
	public function resetLink($linkname){
		if (!isset($this->_linked[$linkname])) return; // @todo Error Handling

		$this->_linked[$linkname]['records'] = null;
	}

	/**
	 * Mark a linked model for deletion.
	 * Doesn't actually delete the linked model until this element is saved.
	 *
	 * @param Model $link
	 * @return bool
	 */
	public function deleteLink(Model $link){
		// Since I don't get the linkname like usual ones...
		foreach($this->_linked as $linkname => $linkset){
			if(!isset($linkset['records'])) continue;

			if(is_array($linkset['records'])){
				foreach($linkset['records'] as $k => $rec){
					if($rec == $link){
						if(!isset($this->_linked[$linkname]['purged'])){
							$this->_linked[$linkname]['purged'] = array();
						}
						$this->_linked[$linkname]['purged'][] = $link;
						unset($this->_linked[$linkname]['records'][$k]);
						return true;
					}
				}
			}
			elseif($linkset['records'] == $link){
				if(!isset($this->_linked[$linkname]['purged'])){
					$this->_linked[$linkname]['purged'] = array();
				}
				$this->_linked[$linkname]['purged'][] = $link;
				$this->_linked[$linkname]['records'] = null;
				return true;
			}
		}

		return false;
	}

	/**
	 * Set properties on this model from an associative array of key/value pairs.
	 *
	 * @param $array
	 */
	public function setFromArray($array) {
		foreach ($array as $k => $v) {
			$this->set($k, $v);
		}
	}

	/**
	 * Set properties on this model from a form object, optionally with a specific prefix.
	 *
	 * @param Form        $form   Form object to pull data from
	 * @param string|null $prefix Prefix that all keys should be matched to, (optional)
	 */
	public function setFromForm(Form $form, $prefix = null){

		// Get every "prefix[...]" element, as they key up 1-to-1.
		$els = $form->getElements(true, false);
		foreach ($els as $e) {
			// If a specific prefix was requested and this element does not match up, skip it!
			if ($prefix){
				if(!preg_match('/^' . $prefix . '\[(.*?)\].*/', $e->get('name'), $matches)) continue;
				$key = $matches[1];
			}
			else{
				$key = $e->get('name');
			}

			$val    = $e->get('value');
			$schema = $this->getKeySchema($key);

			// If there is no schema entry for this key, no reason to try to set it as it doesn't exist in the Model.
			if(!$schema) continue;

			$this->set($key, $val);
		}
	}

	/**
	 * Converse to setFromForm, this method is called on each form element created when calling addModel or BuildFromModel.
	 *
	 * Any special instructions for your model's elements can go here, simply extend this method and add logic as necessary.
	 *
	 * @param             $key
	 * @param FormElement $element
	 */
	public function setToFormElement($key, FormElement $element){
		// This method left intentionally blank.
		// If custom logic is required here, extend this method in your model and do so there.
	}

	/**
	 * Method that is called on the model after "addModel" is called on a form.
	 *
	 * Any special logic such as adding custom elements from the model can be done here, simply extend this method and add logic as necessary.
	 *
	 * @param Form   $form
	 * @param string $prefix
	 */
	public function addToFormPost(Form $form, $prefix){
		// This method left intentionally blank.
		// If custom logic is required here, extend this method in your model and do so there.
	}

	/**
	 * Get if this model exists in the datastore already.
	 *
	 * @return bool
	 */
	public function exists() {
		return $this->_exists;
	}

	/**
	 * Get if this model is marked as deleted and/or deleted already.
	 *
	 * @return bool
	 */
	public function isdeleted(){
		$s = self::GetSchema();

		foreach ($this->_data as $k => $v) {
			if(!isset($s[$k])){
				// This key was not in the schema.  Probable reasons for this would be a column that was
				// removed from the schema in an upgrade, but was never removed from the database.
				// This is typical because the installer tries to be non-destructive when it comes to data.
				continue;
			}
			$keyschema = $s[$k];
			// Certain key types have certain functions.
			if($keyschema['type'] == Model::ATT_TYPE_DELETED) {
				if($v){
					// Is this record already set as deleted?
					// If value is > 0|NULL, it's marked as deleted!
					return true;
				}
			}
		}

		if( sizeof($this->_datainit) > 0 && !$this->_exists ){
			// The data at time of initialization existed, but
			// the exists flag is set to false, which gets set on initialization.
			// This means that delete() was called at some point.
			return true;
		}

		// Still no?  OK!
		return false;
	}

	/**
	 * Get if this model is a new entity that doesn't exist in the datastore.
	 *
	 * @return bool
	 */
	public function isnew() {
		return !$this->_exists;
	}

	/**
	 * Get if this model has changes that are pending to be applied back to the datastore.
	 *
	 * @return bool
	 */
	public function changed(){
		$s = self::GetSchema();

		foreach ($this->_data as $k => $v) {
			if(!isset($s[$k])){
				// This key was not in the schema.  Probable reasons for this would be a column that was
				// removed from the schema in an upgrade, but was never removed from the database.
				// This is typical because the installer tries to be non-destructive when it comes to data.
				continue;
			}
			$keyschema = $s[$k];

			// Certain key types are to be ignored in the "changed" logic check.  Namely automatic timestamps.
			switch ($keyschema['type']) {
				case Model::ATT_TYPE_CREATED:
				case Model::ATT_TYPE_UPDATED:
					continue 2;
			}

			// It's a standard column, check and see if it matches the datainit value.
			// If the datainit key doesn't exist, that also constitutes as a changed flag!
			// ** I'm beginning to really dislike PHP....
			if(!array_key_exists($k, $this->_datainit)){
				//echo "$k changed!<br/>\n"; // DEBUG
				return true;
			}

			if($this->_datainit[$k] != $this->_data[$k]){
				// This will match if "blah" is different than "foo", but fails at "" is different than "0".

				//echo "$k changed!<br/>\n"; // DEBUG
				//var_dump($this->_datainit[$k], $this->_data[$k]); // DEBUG
				return true;
			}


			if(isset($keyschema['type']) && $keyschema['type'] == Model::ATT_TYPE_STRING){
				// If this attribute is a string, use Core's string comparison.
				if(!\Core\compare_strings($this->_datainit[$k], $this->_data[$k])) return true;
			}
			else{
				// Default, more precise comparison.
				// This one knows the difference between "", "0", and false!
				if(!\Core\compare_values($this->_datainit[$k], $this->_data[$k])) return true;
			}

			// The data seems to have matched up, nothing to see here, move on!
		}

		// Oh, if it's gotten past all the data keys, then the data must have been identical!
		return false;
	}

	/**
	 * Function to call to decrypt data from this model.
	 *
	 * NOTE, to increase performance, data is NOT automatically decrypted upon loading data from the datastore!
	 */
	public function decryptData(){
		if($this->_datadecrypted === null){
			$this->_datadecrypted = array();

			foreach($this->getKeySchemas() as $k => $v){
				// Since certain keys in a model may be encrypted.
				if($v['encrypted']){
					$payload = $this->_data[$k];
					if($payload === null || $payload === '' || $payload === false){
						$this->_datadecrypted[$k] = null;
						continue;
					}

					preg_match('/^\$([^$]*)\$([0-9]*)\$(.*)$/m', $payload, $matches);

					$cipher = $matches[1];
					$passes = $matches[2];
					$size = openssl_cipher_iv_length($cipher);
					// Now I can trim off the beginning crap from the encrypted string.
					$dec = substr($payload, strlen($cipher) + 5, 0-$size);
					$iv = substr($payload, 0-$size);

					for($i=0; $i<$passes; $i++){
						$dec = openssl_decrypt($dec, $cipher, SECRET_ENCRYPTION_PASSPHRASE, true, $iv);
					}

					$this->_datadecrypted[$k] = $dec;
				}
			}
		}
	}

	/**
	 * Get the table name for this class
	 *
	 * @return null|string
	 */
	public function _getTableName(){
		return self::GetTableName();
	}

	/**
	 * Get the primary key value(s) of this model as a string
	 *
	 * @return string
	 */
	public function getPrimaryKeyString(){
		$bits = array();
		$i = self::GetIndexes();

		if(isset($i['primary'])){
			// It should be an array, but doesn't have to be.
			$pri = $i['primary'];
			if(!is_array($pri)) $pri = array($pri);

			foreach ($pri as $k) {
				$val = $this->get($k);
				if ($val === null) $val = 'null';
				elseif ($val === false) $val = 'false';
				$bits[] = $val;
			}
		}

		return implode('-', $bits);
	}


	/*************************************************************************
	 ****                    ARRAY ACCESS METHODS                         ****
	 *************************************************************************/

	/**
	 * Whether an offset exists
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param mixed $offset An offset to check for.
	 *
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
	 *
	 * @param mixed $offset The offset to retrieve.
	 *
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
	 *
	 * @param mixed $offset The offset to assign the value to.
	 * @param mixed $value The value to set.
	 *
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
	 *
	 * @param mixed $offset The offset to unset.
	 *
	 * @return void
	 */
	public function offsetUnset($offset) {
		$this->set($offset, null);
	}

	/**
	 * Go through any linked tables and update them if the linking key has been changed.
	 *
	 * This needs to actually go through the database and update the saved keys if necessary.
	 *
	 * @param string $key
	 * @param mixed $newval
	 */
	protected function _setLinkKeyPropagation($key, $newval) {
		// If this model does not exist yet, there will be no information linked in the database.
		$exists = $this->exists();

		foreach ($this->_linked as $lk => $l) {

			// I can't change my parent data.
			// NO CHANGING YOUR PARENTS!
			if($l['link'] == Model::LINK_BELONGSTOONE) continue;
			if($l['link'] == Model::LINK_BELONGSTOMANY) continue;

			$dolink = false;
			// I can't use the getLinkWhereArray function, because that will resolve the key.
			// I need the key itself.
			if (!isset($l['on'])) {
				// @todo automatic linking
			}
			elseif (is_array($l['on'])) {
				foreach ($l['on'] as $k => $v) {
					if (is_numeric($k) && $v == $key) $dolink = true;
					elseif (!is_numeric($k) && $k == $key) $dolink = true;
				}
			}
			else {
				if ($l['on'] == $key) $dolink = true;
			}

			// $dolink should now be true/false.  If true I need to load that linked table and set the new key.
			if (!$dolink) continue;

			if($exists){
				// Get the data and update it!
				$links = $this->getLink($lk);
				if (!is_array($links)) $links = array($links);

				foreach ($links as $model) {
					$model->set($key, $newval);
				}
			}
			else{
				// Only update the cached data, as nothing has been saved, so the database doesn't have anything.
				if(!isset($this->_linked[$lk]['records'])) continue;

				if(is_array($this->_linked[$lk]['records'])){
					// Standard 1-to-M structure
					foreach($this->_linked[$lk]['records'] as $model){
						$model->set($key, $newval);
					}
				}
				else{
					// Only a 1-to-1 structure
					$this->_linked[$lk]['records']->set($key, $newval);
				}
			}
		}
	}

	/**
	 * Get the fully resolved ClassName of the requested link name.
	 *
	 * @param string $linkname Name of one of the linked models.
	 *
	 * @return null|string
	 */
	protected function _getLinkClassName($linkname) {
		// Determine the class.
		$c = (isset($this->_linked[$linkname]['class'])) ? $this->_linked[$linkname]['class'] : $linkname . 'Model';

		if (!is_subclass_of($c, 'Model')) return null; // @todo Error Handling

		return $c;
	}

	/**
	 * Called internally by the save() method for new records.
	 */
	protected function _saveNew() {
		$i = self::GetIndexes();
		$s = self::GetSchema();
		$n = $this->_getTableName();

		if (!isset($i['primary'])) $i['primary'] = array(); // No primary schema defined... just don't make the in_array bail out.

		$dat = new Core\Datamodel\Dataset();
		$dat->table($n);

		$idcol = false;
		foreach ($this->_data as $k => $v) {
			$keyschema = $s[$k];

			switch ($keyschema['type']) {
				case Model::ATT_TYPE_CREATED:
				case Model::ATT_TYPE_UPDATED:
					// If this value has already been set, (some advanced utilities may want to specify a different updated or created time),
					// then allow that value to stick.
					if($v){
						$dat->insert($k, $v);
					}
					else{
						$nv = Time::GetCurrentGMT();
						$dat->insert($k, $nv);
						$this->_data[$k] = $nv;
					}
					break;

				case Model::ATT_TYPE_ID:
					$dat->setID($k, $this->_data[$k]);
					$idcol = $k; // Remember this for after the save.
					break;

				case Model::ATT_TYPE_UUID:
					if($this->_data[$k] && isset($this->_datainit[$k]) && $this->_datainit[$k]){
						// Yay, a UUID is already set, no need to really do much.
						$nv = $this->_data[$k];
						// It's already set and this will most likely be ignored, but may not be for UPDATE statements...
						// although there shouldn't be any update statements here.... but ya never know
						$dat->setID($k, $nv);
					}
					elseif($this->_data[$k]){
						// a UUID is already set, but it doesn't exist yet still.
						// This means that the UUID was set externally even though the record is new.
						// THIS IS ALLOWED!
						// Insert it as typical key.
						$nv = $this->_data[$k];
						$dat->insert($k, $nv);
						$dat->setID($k, $nv);
					}
					else{
						// I need to generate a new key and set that.
						$nv = Core::GenerateUUID();
						// In this case, the database isn't going to care what the column is, other than the fact it's unique.
						// It will be :)
						$dat->insert($k, $nv);
						// And I need to set this on the data so it's available next time.
						$this->_data[$k] = $nv;
						$dat->setID($k, $nv);
					}
					// Remember this for after the save.
					$idcol = $k;
					break;

				case Model::ATT_TYPE_SITE:
					if(
						Core::IsComponentAvailable('enterprise') &&
						MultiSiteHelper::IsEnabled() &&
						($v === null || $v === false)
					){
						$site = MultiSiteHelper::GetCurrentSiteID();
						$dat->insert('site', $site);
						$this->_data[$k] = $site;
					}
					elseif($v === null || $v === false){
						$dat->insert('site', 0);
						$this->_data[$k] = 0;
					}
					else{
						$dat->insert($k, $v);
					}
					break;

				default:
					// Make sure this value is resolved to its strict version!
					// This is because the underlying data layer will throw kinipshits if (for example),
					// NULL is passed in on a non-null column.
					$v = $this->translateKey($k, $v);

					$dat->insert($k, $v);
					break;
			}
		}

		$dat->execute($this->interface);

		if ($idcol) $this->_data[$idcol] = $dat->getID();
	}

	/**
	 * Save an existing Model object into the database.
	 * Will create, set and execute a dataset object as appropriately internally.
	 *
	 * @param boolean $useset Set to true to have this model use an INSERT_UPDATE statement instead of just UPDATE.
	 * @return bool
	 */
	protected function _saveExisting($useset = false) {

		// If this model doesn't have any changes, don't save it!
		if(!$this->changed()) return false;

		$i = self::GetIndexes();
		$s = self::GetSchema();
		$n = $this->_getTableName();

		// No primary schema defined or it's a string, (single value)... just don't make the in_array bail out.
		$pri = isset($i['primary']) ? $i['primary'] : array();

		if($pri && !is_array($pri)) $pri = array($pri);

		if($pri && !is_array($pri)) $pri = array($pri);

		// This is the dataset object that will be integral in this function.
		$dat = new Core\Datamodel\Dataset();
		$dat->table($n);

		$idcol = false;
		foreach ($this->_data as $k => $v) {
			if(!isset($s[$k])){
				// This key was not in the schema.  Probable reasons for this would be a column that was
				// removed from the schema in an upgrade, but was never removed from the database.
				// This is typical because the installer tries to be non-destructive when it comes to data.
				continue;
			}
			$keyschema = $s[$k];
			// Certain key types have certain functions.
			switch ($keyschema['type']) {
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
				case Model::ATT_TYPE_UUID:
					$dat->setID($k, $this->_data[$k]);
					$idcol = $k; // Remember this for after the save.
					continue 2;
			}

			// Make sure this value is resolved to its strict version!
			// This is because the underlying data layer will throw kinipshits if (for example),
			// NULL is passed in on a non-null column.
			$v = $this->translateKey($k, $v);

			//var_dump($k, $i['primary']);
			// Everything else
			if (in_array($k, $pri)) {
				// Just in case the new data changed....
				if ($this->_datainit[$k] != $v){
					if($useset){
						$dat->set($k, $v);
					}
					else{
						$dat->update($k, $v);
					}
				}

				$dat->where($k, $this->_datainit[$k]);

				$this->_data[$k] = $v;
			}
			else {
				// Do some logic to see if I can skip updating non-changed columns.
				if(isset($this->_datainit[$k])){
					if($keyschema['type'] == Model::ATT_TYPE_STRING){
						if(\Core\compare_strings($this->_datainit[$k], $v)) continue;
					}
					else{
						if(\Core\compare_values($this->_datainit[$k], $v)) continue;
					}
				}

				//echo "Setting [$k] = [$v]<br/>"; // DEBUG
				if($useset){
					$dat->set($k, $v);
				}
				else{
					$dat->update($k, $v);
				}
			}
		}

		// No data.. nothing to change I guess!
		// This is a failsafe should (for some reason), the changed() method doesn't return the correct value.
		if(!sizeof($dat->_sets)){
			return false;
		}
		//var_dump($dat); die('mep'); // DEBUG
		$dat->execute($this->interface);
		// IDs don't change in updates, else they wouldn't be the id.
	}

	/**
	 * Get the where array of criteria for a given link.
	 * Useful for manually tweaking the clause.
	 *
	 * @param $linkname
	 *
	 * @return array|null
	 */
	protected function _getLinkWhereArray($linkname) {
		if (!isset($this->_linked[$linkname])) return null; // @todo Error Handling

		// Build a standard where criteria that can be used throughout this function.
		$wheres = array();

		if (!isset($this->_linked[$linkname]['on'])) {
			return null; // @todo automatic linking.
		}
		elseif (is_array($this->_linked[$linkname]['on'])) {
			foreach ($this->_linked[$linkname]['on'] as $k => $v) {
				if (is_numeric($k)) $wheres[$v] = $this->get($v);
				else $wheres[$k] = $this->get($v);
			}
		}
		else {
			$k          = $this->_linked[$linkname]['on'];
			$wheres[$k] = $this->get($k);
		}


		// Pages have a special extra here.  If it's enterprise/multisite mode, enforce that relationship.
		if($linkname == 'Page' && Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
			// See if there's a site column on this schema.  If there is, enforce that binding too!
			$schema = self::GetSchema();
			if(isset($schema['site']) && $schema['site']['type'] == Model::ATT_TYPE_SITE){
				$wheres['site'] = $this->get('site');
			}
		}


		return $wheres;
	}

	/**
	 * Method to encrypt a specific key for storage.
	 *
	 * Called internally by the set function.
	 * Will return the encrypted data.
	 *
	 * @param mixed $value The plain-text value to encrypt
	 * @return string Encrypted data
	 */
	protected function encryptValue($value){
		$cipher = 'AES-256-CBC';
		$passes = 10;
		$size = openssl_cipher_iv_length($cipher);
		$iv = mcrypt_create_iv($size, MCRYPT_RAND);

		$enc = $value;
		for($i=0; $i<$passes; $i++){
			$enc = openssl_encrypt($enc, $cipher, SECRET_ENCRYPTION_PASSPHRASE, true, $iv);
		}

		$payload = '$' . $cipher . '$' . str_pad($passes, 2, '0', STR_PAD_LEFT) . '$' . $enc . $iv;
		return $payload;
	}

	protected function _getCacheKey() {
		if (!$this->_cacheable) return false;
		$i = self::GetIndexes();

		if (!(isset($i['primary']) && sizeof($i['primary']))) return false;

		$keys = $this->getPrimaryKeyString();

		return 'DATA:' . self::GetTableName() . ':' . $keys;
	}


	/*************************************************************************
	 ****                    FACTORY-RELATED STATIC METHODS               ****
	 *************************************************************************/

	/**
	 * Constructor alternative that utilizes caching to save on database lookups.
	 *
	 * @static
	 *
	 * @param $keys
	 * @return Model
	 */
	public static function Construct($keys = null){
		$class = get_called_class();

		// New ones do not get cached.
		if($keys === null){
			return new $class();
		}

		$cache = '';
		foreach(func_get_args() as $a){
			$cache .= $a . '-';
		}
		$cache = substr($cache, 0, -1);

		if(!isset(self::$_ModelCache[$class])){
			self::$_ModelCache[$class] = array();
		}
		if(!isset(self::$_ModelCache[$class][$cache])){
			$reflection = new ReflectionClass($class);
			/** @var $obj Model */
			$obj = $reflection->newInstanceArgs(func_get_args());

			self::$_ModelCache[$class][$cache] = $obj;
		}

		//var_dump($cache);
		return self::$_ModelCache[$class][$cache];
	}

	/**
	 * Factory shortcut function to do a search for the specific records.
	 *
	 * @static
	 * @param array|string    $where Where clause
	 * @param int|string|null $limit Limit clause
	 * @param string|null     $order Order clause
	 *
	 * @return array|null|Model
	 */
	public static function Find($where = array(), $limit = null, $order = null) {

		$classname = get_called_class();

		if(!sizeof($where) && $limit === null && $order === null){
			// Try to cache them :p

			if(!isset(self::$_ModelFindCache[$classname])){
				$fac = new ModelFactory($classname);
				self::$_ModelFindCache[$classname] = $fac->get();
			}

			return self::$_ModelFindCache[$classname];
		}

		$fac = new ModelFactory($classname);
		$fac->where($where);
		$fac->limit($limit);
		$fac->order($order);
		//var_dump($fac);
		return $fac->get();
	}

	/**
	 * Factory shortcut function to do a search for the specific records and return them as a raw array.
	 *
	 * @static
	 *
	 * @param array $where
	 * @param null  $limit
	 * @param null  $order
	 *
	 * @return array
	 */
	public static function FindRaw($where = array(), $limit = null, $order = null) {
		$fac = new ModelFactory(get_called_class());
		$fac->where($where);
		$fac->limit($limit);
		$fac->order($order);
		//var_dump($fac);
		return $fac->getRaw();
	}

	/**
	 * Get a count of records that match a given where criteria
	 *
	 * @static
	 *
	 * @param array $where
	 *
	 * @return int
	 */
	public static function Count($where = array()) {
		$fac = new ModelFactory(get_called_class());
		$fac->where($where);
		return $fac->count();
	}

	/**
	 * Perform a model search on the records of this Model.
	 *
	 * @param string $query The base query to search
	 * @param array $where  Any additional where parameters to add onto the factory
	 *
	 * @return array An array of ModelResult objects.
	 */
	public static function Search($query, $where = array()){
		$ret = [];

		// If this object does not support searching, simply return an empty array.
		$ref = new ReflectionClass(get_called_class());

		if(!$ref->getProperty('HasSearch')->getValue()){
			return $ret;
		}

		$fac = new ModelFactory(get_called_class());

		if(sizeof($where)){
			$fac->where($where);
		}

		if($ref->getProperty('HasDeleted')->getValue()){
			$fac->where('deleted = 0');
		}

		$fac->where(\Core\Search\Helper::GetWhereClause($query));
		foreach($fac->get() as $m){
			/** @var Model $m */
			$sr = new \Core\Search\ModelResult($query, $m);

			// This may happen since the where clause can be a little open-ended.
			if($sr->relevancy < 1) continue;
			$sr->title = $m->getLabel();
			$sr->link  = $m->get('baseurl');

			$ret[] = $sr;
		}
		return $ret;
	}

	/*************************************************************************
	 ****                    OTHER STATIC METHODS                         ****
	 *************************************************************************/

	/**
	 * Get the table name for a given Model object
	 *
	 * @static
	 * @return string
	 */
	public static function GetTableName() {
		// Just a lookup table for rendered table names.
		// This is useful so the regex functions don't have to run more than once.
		static $_tablenames = array();
		$m = get_called_class();

		// Generic models cannot have tables.
		if ($m == 'Model') return null;

		if (!isset($_tablenames[$m])) {
			// Calculate the table class.

			// It's based on the main class's name.
			$tbl = $m;
			//$tbl = get_class($this);

			// If it ends in Model... trim that bit off.  It's assumed that the prefix is what we want.
			if (preg_match('/Model$/', $tbl)) $tbl = substr($tbl, 0, -5);

			// Replace any capitalized letters with a _[letter].
			$tbl = preg_replace('/([A-Z])/', '_$1', $tbl);

			// Of course this would produce something similar to _Foo_Mep_Blah.. don't need the beginning _.
			if ($tbl{0} == '_') $tbl = substr($tbl, 1);

			// And lowercase.
			$tbl = strtolower($tbl);

			// Prepend the DB_PREFIX and save!
			$_tablenames[$m] = DB_PREFIX . $tbl;
		}

		return $_tablenames[$m];
	}

	/**
	 * Get the resolved schema for this Model type.
	 *
	 * This is called by several other methods, including getKeySchemas and getKeySchema.
	 *
	 * @throws Exception
	 *
	 * @return mixed
	 */
	public static function GetSchema() {
		/** @var string $classname The class name of the extending class. */
		$classname = get_called_class();

		if(!isset(self::$_ModelSchemaCache[$classname])){

			// First I need the parent's class, if it's not Model.
			$parent = get_parent_class($classname);
			if($parent != 'Model'){
				// Because the "Model" class doesn't have a schema... that's up to classes that extend it.
				$parentref = new ReflectionClass($parent);
				$ref = new ReflectionClass($classname);
				self::$_ModelSchemaCache[$classname] = array_merge(
					$parentref->getProperty('Schema')->getValue(),
					$ref->getProperty('Schema')->getValue()
				);
			}
			else{
				// Because the "Model" class doesn't have a schema... that's up to classes that extend it.
				$ref = new ReflectionClass($classname);
				self::$_ModelSchemaCache[$classname] = $ref->getProperty('Schema')->getValue();
			}

			// Link it so I don't have to type out the full path.
			$schema =& self::$_ModelSchemaCache[$classname];

			// There are a variety of dynamic columns that are defined in Core.

			if($ref->getProperty('HasCreated')->getValue()){
				// Only add this column if it doesn't already exist.
				if(!isset($schema['created'])){
					$schema['created'] = [
						'type' => Model::ATT_TYPE_CREATED,
						'null' => false,
						'default' => 0,
						'comment' => 'The created timestamp of this record, populated automatically',
					];
				}
			}

			if($ref->getProperty('HasUpdated')->getValue()){
				// Only add this column if it doesn't already exist.
				if(!isset($schema['updated'])){
					$schema['updated'] = [
						'type' => Model::ATT_TYPE_UPDATED,
						'null' => false,
						'default' => 0,
						'comment' => 'The updated timestamp of this record, populated automatically',
					];
				}
			}

			if($ref->getProperty('HasDeleted')->getValue()){
				// Only add this column if it doesn't already exist.
				if(!isset($schema['deleted'])){
					$schema['deleted'] = [
						'type' => Model::ATT_TYPE_DELETED,
						'null' => false,
						'default' => 0,
						'comment' => 'The deleted timestamp of this record, populated automatically',
					];
				}
			}

			if($ref->getProperty('HasSearch')->getValue()){
				// Tack on the search fields automatically.
				$schema['search_index_str'] = [
					'type' => Model::ATT_TYPE_TEXT,
					'required' => false,
					'null' => true,
					'default' => null,
					'formtype' => 'disabled',
					'comment' => 'The search index of this record as a string'
				];
				$schema['search_index_pri'] = [
					'type' => Model::ATT_TYPE_TEXT,
					'required' => false,
					'null' => true,
					'default' => null,
					'formtype' => 'disabled',
					'comment' => 'The search index of this record as the DMP primary version'
				];
				$schema['search_index_sec'] = [
					'type' => Model::ATT_TYPE_TEXT,
					'required' => false,
					'null' => true,
					'default' => null,
					'formtype' => 'disabled',
					'comment' => 'The search index of this record as the DMP secondary version'
				];
			}

			// Now handle the optional fields that are expected by the logic regardless.
			foreach ($schema as $k => $v) {
				// These are all defaults for schemas.
				// Setting them to the default if they're not set will ensure that
				// 'undefined index' notices are not incurred.
				if (!isset($v['type']))               $schema[$k]['type']      = Model::ATT_TYPE_TEXT; // Default if not present.
				if (!isset($v['maxlength']))          $schema[$k]['maxlength'] = false;
				if (!isset($v['null']))               $schema[$k]['null']      = false;
				if (!isset($v['comment']))            $schema[$k]['comment']   = '';
				if (!array_key_exists('default', $v)) $schema[$k]['default']   = false;
				if (!isset($v['encrypted']))          $schema[$k]['encrypted'] = false;
				if (!isset($v['required']))           $schema[$k]['required']  = false;

				// Aliases reference other columns.  The other column must exist.
				if($v['type'] == Model::ATT_TYPE_ALIAS){
					if(!isset($v['alias'])){
						throw new Exception('Model [' . $classname . '] has alias key [' . $k . '] that does not have an "alias" attribute.  Every ATT_TYPE_ALIAS key MUST have exactly one "alias"');
					}

					if(!isset($schema[ $v['alias'] ])){
						throw new Exception('Model [' . $classname . '] has alias key [' . $k . '] that points to a key that does not exist, [' . $v['alias'] . '].  All aliases MUST exist in the same model!');
					}

					if($schema[ $v['alias'] ]['type'] == Model::ATT_TYPE_ALIAS){
						throw new Exception('Model [' . $classname . '] has alias key [' . $k . '] that points to another alias.  Aliases MUST NOT point to another alias... bad things could happen.');
					}
				}


				if($v['type'] == Model::ATT_TYPE_ENUM){
					// Enums have an options array!
					$schema[$k]['options'] = isset($schema[$k]['options']) ? $schema[$k]['options'] : array();
				}
				else{
					// Other fields don't.
					$schema[$k]['options'] = null;
				}

				// Generate a title for this key from the form data.
				// This can be useful for Model utilities that want to display the
				// human-friendly name instead of the machine name.
				if(isset($v['title'])){
					// For the schema data, the "title" attribute is the highest priority.
					$schema[$k]['title'] = $v['title'];
				}
				elseif(isset($v['form']) && is_array($v['form']) && isset($v['form']['title'])){
					// Next, form.title is the preferred default.
					$schema[$k]['title'] = $v['form']['title'];
				}
				elseif(isset($v['formtitle'])){
					// This is an older shorthand format that's supported too.
					$schema[$k]['title'] = $v['formtitle'];
				}
				else{
					// Guess I need to calculate this manually then....
					$schema[$k]['title'] = ucwords(str_replace('_', ' ', $k));
				}
			}
		}

		return self::$_ModelSchemaCache[$classname];
	}

	public static function GetIndexes() {
		/** @var string $classname The class name of the extending class. */
		$classname = get_called_class();

		// First I need the parent's class, if it's not Model.
		$parent = get_parent_class($classname);
		if($parent != 'Model'){
			// Because the "Model" class doesn't have a schema... that's up to classes that extend it.
			$parentref = new ReflectionClass($parent);
			$ref = new ReflectionClass($classname);
			return array_merge(
				$parentref->getProperty('Indexes')->getValue(),
				$ref->getProperty('Indexes')->getValue()
			);
		}
		else{
			// Because the "Model" class doesn't have a schema... that's up to classes that extend it.
			$ref = new ReflectionClass($classname);
			return $ref->getProperty('Indexes')->getValue();
		}
	}
}

/**
 * Factory utility for models.
 *
 * This class provides an interface for searching for and counting models.  Generally, there are shortcut functions
 * available on the Model class that utilize this class, namely Count, Find, and FindRaw.
 */
class ModelFactory {

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
	 * Contains the dataset object for this search.
	 *
	 * @var Core\Datamodel\Dataset
	 */
	private $_dataset;

	/**
	 * @var \Core\Datamodel\DatasetStream The stream object used in getNext.
	 */
	private $_stream;


	/**
	 * @param string $model
	 */
	public function __construct($model) {

		$this->_model = $model;

		$m              = $this->_model;
		$this->_dataset = new Core\Datamodel\Dataset();
		$this->_dataset->table($m::GetTablename());
		$this->_dataset->select('*');
	}

	/**
	 * Where clause for the search, passed directly to the dataset object.
	 */
	public function where() {
		call_user_func_array(array($this->_dataset, 'where'), func_get_args());
	}

	public function whereGroup() {
		call_user_func_array(array($this->_dataset, 'whereGroup'), func_get_args());
	}

	public function order() {
		call_user_func_array(array($this->_dataset, 'order'), func_get_args());
	}

	public function limit() {
		call_user_func_array(array($this->_dataset, 'limit'), func_get_args());
	}

	/**
	 * Get the result or results from this factory.
	 *
	 * If limit is set to 1, either a Model or null is returned.
	 * Else, an array of Models is returned, be it populated or empty.
	 *
	 * @return array|null|Model
	 */
	public function get() {
		$this->_performMultisiteCheck();
		$rs = $this->_dataset->execute($this->interface);

		$ret = array();
		foreach ($rs as $row) {
			$model = new $this->_model();
			$model->_loadFromRecord($row);
			$ret[] = $model;
		}


		// Only return the model if "1" was requested as the limit.
		if ($this->_dataset->_limit == 1) {
			return (sizeof($ret)) ? $ret[0] : null;
		}
		else {
			return $ret;
		}
	}

	/**
	 * Get the results from this factory as a raw associative array.
	 *
	 * @return array
	 */
	public function getRaw(){
		$this->_performMultisiteCheck();
		$rs = $this->_dataset->execute($this->interface);

		return $rs->_data;
	}

	/**
	 * Similar to get(), but only one model at a time is rendered and returned.
	 *
	 * This is ideal for large datasets and limited amounts of memory.
	 *
	 * When the end of the stream is hit, null is returned.
	 *
	 * @return Model|null
	 */
	public function getNext(){
		if($this->_stream === null){
			// Setup this stream object!
			$this->_performMultisiteCheck();
			$this->_stream = new \Core\Datamodel\DatasetStream($this->_dataset);
		}

		$next = $this->_stream->getRecord();

		if($next === null){
			// End of the stream has been reached!
			return null;
		}

		/** @var Model $model */
		$model = new $this->_model();
		$model->_loadFromRecord($next);

		return $model;
	}

	/**
	 * Get a count of how many records are in this factory
	 * (without counting the records one by one)
	 *
	 * @return int
	 */
	public function count() {
		$this->_performMultisiteCheck();
		$clone = clone $this->_dataset;
		$rs    = $clone->count()->execute($this->interface);

		return $rs->num_rows;
	}

	/**
	 * Get the raw dataset object for this factory.
	 * This can sometimes be useful for advanced manipulations of the low-level object.
	 *
	 * @since 2.1
	 * @return Core\Datamodel\Dataset
	 */
	public function getDataset(){
		return $this->_dataset;
	}

	/**
	 * Internal function to do the multisite check on the model.
	 * If the model supports a site attribute and none requested, then set it to the current site.
	 */
	private function _performMultisiteCheck(){

		$m = $this->_model;
		$ref = new ReflectionClass($m);

		$schema = $ref->getMethod('GetSchema')->invoke(null);
		$index = $ref->getMethod('GetIndexes')->invoke(null);
		//$schema = $m::GetSchema();
		//$index = $m::

		// Is there a site property?  If not I don't even care.
		if(
			isset($schema['site']) &&
			$schema['site']['type'] == Model::ATT_TYPE_SITE &&
			Core::IsComponentAvailable('enterprise') &&
			MultiSiteHelper::IsEnabled()
		){
			// I want to look it up because if the script actually set the site, then
			// it evidently wants it for a reason.
			$siteexact = (sizeof($this->_dataset->getWhereClause()->findByField('site')) > 0);
			$idexact = false;

			// The primary check will allow a model to be instantiated with the exact primary key string.
			if(isset($index['primary'])){
				$allids = true;
				foreach($index['primary'] as $k){
					if(sizeof($this->_dataset->getWhereClause()->findByField($k)) == 0){
						$allids = false;
						break;
					}
				}
				if($allids) $idexact = true;
			}

			if(!($siteexact || $idexact)){
				$w = new DatasetWhereClause();
				$w->setSeparator('or');
				$w->addWhere('site = ' . MultiSiteHelper::GetCurrentSiteID());
				$w->addWhere('site = -1');
				$this->_dataset->where($w);

				//$this->_dataset->where('site = ' . MultiSiteHelper::GetCurrentSiteID());
			}
		}
	}


	/**
	 * @since 2.4.0
	 * @param $model
	 *
	 * @return ModelSchema
	 */
	public static function GetSchema($model){
		$s = new ModelSchema($model);
		return $s;
	}
}


class ModelException extends Exception {
}

class ModelValidationException extends ModelException {
}




