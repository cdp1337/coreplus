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
 * @author Charlie Powell <charlie@evalagency.com>
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
	 * Validation for GT 0 ints.
	 */
	const VALIDATION_INT_GT0 = 'Core::CheckIntGT0Validity';

	/**
	 * Simple validation for any whole numeric digits.
	 *
	 * 0, 1, 1337, 100000 are all valid whole numbers
	 */
	const VALIDATION_NUMBER_WHOLE = "/^[0-9]*$/";

	/**
	 * Simple validation for USD currencies
	 */
	const VALIDATION_CURRENCY_USD = '#^(\$)?[,0-9]*(?:\.[0-9]{2})?$#';

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
	
	/** Data encoding for base64 data, only available on ATT_TYPE_DATA columns! */
	const ATT_ENCODING_BASE64 = 'base64';
	/** Data encoding for JSON data, only available on ATT_TYPE_DATA columns! */
	const ATT_ENCODING_JSON = 'json';
	/** Data encoding for serialized data, only available on ATT_TYPE_DATA columns! */
	const ATT_ENCODING_SERIALIZE = 'serialize';
	/** Data encoding for compressed data, only available on ATT_TYPE_DATA columns! */
	const ATT_ENCODING_GZIP = 'gzip';
	/** Default encoding for strings in the database, available for any STRING-based columns. */
	const ATT_ENCODING_UTF8 = 'utf8';

	/**
	 * Which DataModelInterface should this model execute its operations with.
	 * 99.9% of the time, it's fine to leave this as null, which will use the
	 * system DMI.  If however you want to utilize a Model with Memcache,
	 * (say for session information), it can be useful.
	 *
	 * @var \Core\Datamodel\BackendInterface
	 */
	public $interface = null;

	/**
	 * Allow data to get overloaded onto models.
	 * This is common with Controllers tacking on extra data for templates to better handle the model.
	 * This data is not saved and does not effect the dirty flags.
	 *
	 * @var array
	 */
	protected $_dataother = [];

	/**
	 * @var null|array Set of columns along with the data represented therein.
	 */
	protected $_columns = null;

	/**
	 * @var null|array Array of aliases defined on this model, used because they can work from getters and setters.
	 */
	protected $_aliases = null;

	/**
	 * @var boolean Flag to signify if this record exists in the database.
	 */
	protected $_exists = false;

	/** @var array Set of linked children models on this model, populated by the Constructor and *link methods. */
	protected $_linked = [];

	/** @var array Cache of link names to their index, used to speed up repeated queries. */
	protected $_linkIndexCache = [];

	protected $_cacheable = true;

	/**
	 * @var array The schema as per defined in the extending model.
	 */
	public static $Schema = [];

	/**
	 * @var array Any indexes that are required on this data.  Must be defined in the extending model if used.
	 */
	public static $Indexes = [];

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

	public static $_ModelCache = [];

	/**
	 * @var array Cache for blank Find statements.
	 *
	 * ONLY used with a completely blank Find() query!!!
	 */
	public static $_ModelFindCache = [];

	/**
	 * @var array Array of rendered Schemas for each named model.
	 *
	 * Used to accelerate GetSchema on multiple calls for the same model type.
	 */
	protected static $_ModelSchemaCache = [];

	/**
	 * Used with the defer save option to bulk-insert commands when possible.
	 * This is used to speed up bulk INSERT statements.
	 *
	 * @var array
	 */
	protected static $_DeferInserts = [];

	/**
	 * List of models that provide supplemental functionality on the base model.
	 *
	 * Used for GetSchema, GetIndexes, and the various Model-based hooks.
	 *
	 * @var array
	 */
	protected static $_ModelSupplementals = [];


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

		// If an extending model set links in that constructor, reformat them to ensure they're set correctly.
		if(sizeof($this->_linked)){
			$clone = $this->_linked;
			$this->_linked = [];
			foreach($clone as $model => $dat){
				if(strrpos($model, 'Model') !== strlen($model) - 5){
					// All models need to end with Model, (allow shorthand definitions to ignore that though)
					$model .= 'Model';
				}
				$dat['model'] = $model;
				$this->_linked[] = $dat;
			}
		}

		// Update the _data array based on the schema.
		$s = self::GetSchema();
		$this->_columns = [];
		$this->_aliases = [];
		foreach ($s as $k => $sdat) {
			
			if($sdat['type'] == Model::ATT_TYPE_ALIAS){
				$this->_aliases[$k] = $sdat['alias'];
			}
			else{
				$this->_columns[$k] = \Core\Datamodel\Columns\SchemaColumn::FactoryFromSchema($sdat);
				$this->_columns[$k]->field = $k;
				$this->_columns[$k]->parent = $this;
			}
			
			if(isset($sdat['link'])){
				// If the link is requested on this property, populate the linked array for the corresponding model!
				if(is_array($sdat['link'])){
					if(!isset($sdat['link']['model'])){
						throw new Exception('Required attribute [model] not provided on link [' . $k . '] of model [' . get_class($this) . ']');
					}
					if(!isset($sdat['link']['type'])){
						throw new Exception('Required attribute [type] not provided on link [' . $k . '] of model [' . get_class($this) . ']');
					}
					$linkmodel = $sdat['link']['model'];
					$linktype  = isset($sdat['link']['type']) ? $sdat['link']['type'] : Model::LINK_HASONE;
					$linkon    = isset($sdat['link']['on']) ? $sdat['link']['on'] : 'id';
				}
				else{
					// Allow the short-hand version to be used too.
					// This will setup a 1-to-1 relationship.
					$linkmodel = $sdat['link'];
					$linktype  = Model::LINK_HASONE;
					$linkon    = 'id'; // ... erm yeah... hopefully this is it!
				}

				if(strrpos($linkmodel, 'Model') !== strlen($linkmodel) - 5){
					// All models need to end with Model, (allow shorthand definitions to ignore that though)
					$linkmodel .= 'Model';
				}


				// And populate the linked array with this link data.
				$this->_linked[] = [
					'key'   => $k,
					'model' => $linkmodel,
					'on'    => is_array($linkon) ? $linkon : [$linkon => $k],
					'link'  => $linktype,
				];
			}
		}

		// Check the index (primary), and the incoming data.  If it matches, load it up!
		$i = self::GetIndexes();
		$pri = (isset($i['primary'])) ? $i['primary'] : false;
		if($pri && !is_array($pri)) $pri = [$pri];

		if ($pri && func_num_args() == sizeof($i['primary'])) {
			foreach ($pri as $idx => $k) {
				/** @var \Core\Datamodel\Columns\SchemaColumn $c */
				$c = $this->_columns[$k];
				$c->setValueFromApp(func_get_arg($idx));
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
		if($pri && !is_array($pri)) $pri = [$pri];

		$keys = [];
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

		//if ($this->_cacheable) {
			//$cachekey = $this->_getCacheKey();
			//$cache    = Core::Cache()->get($cachekey);

			// do something if cache succeeds....
		//}

		// If the enterprise/multimode is set and enabled and there's a site column here,
		// that should be enforced at a low level.
		/** @noinspection PhpUndefinedClassInspection */
		if(
			isset($s['site']) &&
			$s['site']['type'] == Model::ATT_TYPE_SITE &&
			Core::IsComponentAvailable('multisite') &&
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
			$this->_loadFromRecord($data->current());
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
	 * As of 5.0.0, bulk inserts can be performed by passing TRUE as the one argument.
	 * If this is done, you MUST call CommitSaves() after all data has been stored!
	 * 
	 * @param bool $defer Set to true to batch-save this data as a BULK INSERT.
	 *
	 * @return boolean
	 * @throws DMI_Exception
	 */
	public function save($defer = false) {

		$classname = strtolower(get_called_class());

		\Core\Utilities\Profiler\Profiler::GetDefaultProfiler()->record(
			'Issuing save() on ' . $classname . ' ' . $this->getLabel()
		);

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
			foreach($this->_linked as $l){
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
			\Core\Utilities\Profiler\Profiler::GetDefaultProfiler()->record(
				'No column detected as changed, skipping save.'
			);
			return false;
		}

		// Dispatch the pre-save hook for models.
		// This allows utilities to hook in and modify the model or perform some other action.
		// Allow all supplemental models to tap into this too!
		if(isset(self::$_ModelSupplementals[$classname])){
			foreach(self::$_ModelSupplementals[$classname] as $supplemental){
				if(class_exists($supplemental)){
					$ref = new ReflectionClass($supplemental);
					if($ref->hasMethod('PreSaveHook')){
						$ref->getMethod('PreSaveHook')->invoke(null, $this);
					}
				}
			}
		}
		HookHandler::DispatchHook('/core/model/presave', $this);

		// NEW in 2.8, I need to run through the linked models and see if any local key is a foreign key of a linked model.
		// If it is, then I need to save that foreign model so I can get its updated primary key, (if it changed).
		foreach($this->_linked as $l){
			// If this link has a 1-sized relationship and the local node is set as a FK, then read it as such.

			if(!is_array($l['on'])){
				// make sure it's an array.
				$l['on'] = [$l['on'] => $l['on'] ];
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
				if(isset($l['records'])){
					/** @var Model $model */
					$model = $l['records'];
				}
				elseif(isset($this->_columns[$localk]) && $this->_columns[$localk]->value instanceof Model){
					// The alternative location for this linked model to be.
					// This can happen when the link is established in the Schema data and the parent record doesn't exist yet.
					/** @var Model $model */
					$model = $this->_columns[$localk];
				}
				else{
					// No valid model found... :/
					continue;
				}

				$model->save();
				$this->set($localk, $model->get($remotek));
			}
		}


		if ($this->_exists){
			$changed = $this->_saveExisting();
			\Core\Utilities\Profiler\Profiler::GetDefaultProfiler()->record(
				'Saved existing record to database'
			);
		}
		else{
			// Inserts can be deferred!
			$this->_saveNew($defer);
			$changed = true;
			\Core\Utilities\Profiler\Profiler::GetDefaultProfiler()->record(
				'Saved new record to database'
			);
		}


		// Go through any linked tables and ensure that they're saved as well.
		foreach($this->_linked as $k => $l){
			// No need to save if it was never loaded.
			//if(!(isset($l['records']))) continue;

			// No need to save if it was never loaded.
			//if(!(isset($l['records']) || $this->changed())) continue;

			switch($l['link']){
				case Model::LINK_HASONE:
					$models = isset($l['records']) ? [$l['records']] : null;
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

			// Are there deletes requested?
			// Deletes MUST happen before creates because if there is a record that overrides an existing record,
			// but that existing record is set to be deleted, the delete operation must be before the create operation.
			//
			// This happens on the page updates with meta fields.
			// The meta fields are completely re-created, and saved at one time only.
			// Since the incoming data has the same PK as the existing data, (that's already marked as deteled),
			// the operation fails if these orders are reversed.
			if($deletes){
				foreach($deletes as $model){
					$model->delete();
					$changed = true;
				}

				unset($l['purged']);
			}

			// Are there saves requested?
			if($models){
				foreach($models as $model){
					/** @var $model Model */
					// Ensure all linked fields still match up.  Something may have been changed in the parent.
					$model->setFromArray($this->_getLinkWhereArray($k));
					if($model->save()){
						$changed = true;
					}
				}
			}
		}

		// Commit all the columns
		$this->_exists   = true;
		foreach($this->_columns as $c){
			/** @var \Core\Datamodel\Columns\SchemaColumn $c */
			$c->commit();
		}

		// Check and see if this model extends another model.
		// If it does, then create/update that parent object to keep it in sync!
		if(($class = get_parent_class($this)) != 'Model'){
			$idx = self::GetIndexes();
			if(isset($idx['primary']) && sizeof($idx['primary']) == 1){
				$schema = $this->getKeySchema($idx['primary'][0]);
				if($schema['type'] == Model::ATT_TYPE_UUID){
					$refp = new ReflectionClass($class);
					$refm = $refp->getMethod('Construct');
					/** @var Model $parent */
					$parent = $refm->invoke(null, $this->get($idx['primary'][0]));

					// Populate the parent with this child's data.
					// Any non-existent field will simply be ignored.
					$parent->setFromArray($this->getAsArray());
					if($parent->save()){
						$changed = true;
					}
				}
			}
		}

		if($changed){
			// Allow all supplemental models to tap into this too!
			if(isset(self::$_ModelSupplementals[$classname])){
				foreach(self::$_ModelSupplementals[$classname] as $supplemental){
					if(class_exists($supplemental)){
						$ref = new ReflectionClass($supplemental);
						if($ref->hasMethod('PostSaveHook')){
							$ref->getMethod('PostSaveHook')->invoke(null, $this);
						}
					}
				}
			}
			HookHandler::DispatchHook('/core/model/postsave', $this);
			// Indicate that something happened.
			return true;
		}
		else{
			// Nothing happened!
			return false;
		}
	}

	/**
	 * Get the requested key for this object.
	 *
	 * @param string $k
	 *
	 * @return mixed
	 */
	public function get($k) {
		if($k === '__CLASS__'){
			// Magic key
			return get_called_class();
		}
		elseif($k === '__PRIMARYKEY__'){
			// Magic key
			return $this->getPrimaryKeyString();
		}
		elseif(isset($this->_columns[$k])){
			// It's a standard column object!
			return $this->_columns[$k]->valueTranslated;
		}
		elseif(isset($this->_aliases[$k])){
			// It's an alias of another key, re-call this method on that key instead.
			return $this->get($this->_aliases[$k]);
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
	 * Get the column schema for a given key, or null if it doesn't exist.
	 * 
	 * @param string $key
	 * 
	 * @return \Core\Datamodel\Columns\SchemaColumn|null
	 */
	public function getColumn($key){
		return isset($this->_columns[$key]) ? $this->_columns[$key] : null;
	}

	/**
	 * Get this model as a string
	 *
	 * @return string
	 */
	public function __toString(){
		return $this->getLabel();
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
		$ret = [];
		foreach($this->_columns as $c){
			/** @var \Core\Datamodel\Columns\SchemaColumn $c */
			$ret[$c->field] = $c->valueTranslated;
		}
		return $ret;
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
		$ret = [];
		foreach($this->_columns as $c){
			/** @var \Core\Datamodel\Columns\SchemaColumn $c */
			$ret[$c->field] = $c->value;
		}
		return $ret;
	}

	/**
	 * Get the initial data of this model as it was when it was loaded from teh database.
	 *
	 * @return array|null
	 */
	public function getInitialData(){
		$ret = [];
		foreach($this->_columns as $c){
			/** @var \Core\Datamodel\Columns\SchemaColumn $c */
			$ret[$c->field] = $c->valueDB;
		}
		return $ret;
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
	 * Get a valid schema of the requested key of this model or null if it doesn't exist.
	 *
	 * @param string $key
	 *
	 * @return null|array
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

					if(preg_match('/^[0-9\- \.\(\)]*$/', $val) && trim($val) != ''){
						// If this is a numeric-based value, compress all the numbers without formatting.
						// This is to support phone numbers that may have arbitrary formatting applied.
						$val = preg_replace('/[ \-\.\(\)]/', '', $val);
					}

					if($val){
						$strs[] = $val;
					}
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
	 * Get an array of control links for this model.
	 * 
	 * Please call array_merge($results, parent::getControlLinks())
	 * in any extending method to retain the supplemental model functionality.
	 *
	 * The returned data MUST be either an empty array or an index array of arrays.
	 * Each internal array should have link, title, icon, and any other parameter supported by the ViewControl
	 *
	 * @see ViewControl.class.php
	 *
	 * @return array
	 */
	public function getControlLinks(){
		$ret = [];

		$classname = strtolower(get_class($this));

		// Allow all supplemental models to tap into the schema too!
		if(isset(self::$_ModelSupplementals[$classname])){
			foreach(self::$_ModelSupplementals[$classname] as $supplemental){
				if(class_exists($supplemental)){
					$ref = new ReflectionClass($supplemental);
					if($ref->hasMethod('GetControlLinks')){
						$supplementalRet = $ref->getMethod('GetControlLinks')->invoke(null, $this);
						
						// Include some error handling for supplemental models that are not setup fully.
						if(!is_array($supplementalRet)){
							trigger_error($supplemental . '::GetControlLinks must return an array!', E_USER_NOTICE);
						}
						else{
							$ret = array_merge($ret, $supplementalRet);	
						}
					}
				}
			}
		}

		return $ret;
	}

	/**
	 * Load this model from an associative array, or record.
	 * This is meant to be called from the Factory system, and the data passed in
	 * MUST be sanitized and valid!
	 *
	 * @param array $record
	 */
	public function _loadFromRecord($record) {
		
		foreach($record as $k => $v){
			if(isset($this->_columns[$k])){
				/** @var \Core\Datamodel\Columns\SchemaColumn $c */
				$c = $this->_columns[$k];
				$c->setValueFromDB($v);
			}
		}
		$this->_exists = true;
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

		$classname = strtolower(get_called_class());
		// Allow all supplemental models to tap into this too!
		if(isset(self::$_ModelSupplementals[$classname])){
			foreach(self::$_ModelSupplementals[$classname] as $supplemental){
				if(class_exists($supplemental)){
					$ref = new ReflectionClass($supplemental);
					if($ref->hasMethod('PreDeleteHook')){
						$ref->getMethod('PreDeleteHook')->invoke(null, $this);
					}
				}
			}
		}

		foreach ($this->_columns as $c) {
			/** @var \Core\Datamodel\Columns\SchemaColumn $c */
			
			// Certain key types have certain functions.
			if($c->type == Model::ATT_TYPE_DELETED) {
				// Is this record already set as deleted?
				// If it is, then proceed with the delete as usual.
				// Otherwise, update the record instead of deleting it.
				if(!$c->value){
					$nv = Time::GetCurrentGMT();
					$this->set($c->field, $nv);
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
						/** @var Model $child */
						$child->delete();
					}
					break;
				// There is no default behaviour... other than to ignore it.
			}

			if (isset($this->_linked[$k]['records'])) unset($this->_linked[$k]['records']);
		}


		if ($this->exists()) {

			// Check and see if this model extends another model.
			// If it does, then create/update that parent object to keep it in sync!
			if(($class = get_parent_class($this)) != 'Model'){
				$idx = self::GetIndexes();
				if(isset($idx['primary']) && sizeof($idx['primary']) == 1){
					$schema = $this->getKeySchema($idx['primary'][0]);
					if($schema['type'] == Model::ATT_TYPE_UUID){
						$refp = new ReflectionClass($class);
						$refm = $refp->getMethod('Construct');
						/** @var Model $parent */
						$parent = $refm->invoke(null, $this->get($idx['primary'][0]));
						$parent->delete();
					}
				}
			}

			$n = $this->_getTableName();
			$i = self::GetIndexes();
			// Delete the data from the database.
			$dat = new Core\Datamodel\Dataset();

			$dat->table($n);

			if (!isset($i['primary'])) {
				throw new Exception('Unable to delete model [ ' . get_class($this) . ' ] without any primary keys.');
			}

			$pri = $i['primary'];
			if(!is_array($pri)) $pri = [$pri];

			foreach ($pri as $k) {
				$dat->where([$k => $this->get($k)]);
			}

			$dat->limit(1)->delete();

			if ($dat->execute($this->interface)) {
				$this->_exists = false;
			}
		}

		return true;
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
				$valid = call_user_func([$this, $check[1]], $v);
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
		elseif($s[$k]['type'] == Model::ATT_TYPE_INT){
			// Default validation for INTs.
			if(!isset($s[$k]['validationmessage'])){
				$s[$k]['validationmessage'] = $k . ' must be a valid number.';
			}

			if(!(
				is_int($v) ||
				ctype_digit($v) ||
				(is_float($v) && strpos($v, '.') === false)
			)){
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
	 * Set a value of a specific key.
	 *
	 * The data is validated automatically as per the specific Model specifications.
	 *
	 * This supports data overloading.
	 *
	 * @param string $k The key to set
	 * @param mixed  $v The value to set
	 *
	 * @throws ModelValidationException
	 */
	public function set($k, $v) {
		if($v instanceof Model && $this->_getLinkIndex($k) !== null){
			// Allow setting a linked Model via set().
			// This allows a full Model to be passed in from let's say a form system.
			$this->setLink($k, $v);
			return;
		}
		
		// Remap this to an alias if one is set.
		if(isset($this->_aliases[$k])){
			$k = $this->_aliases[$k];
		}

		$keydat = $this->getKeySchema($k);
		
		if($keydat === null){
			// If the key schema isn't available, then simply set the dataother and exit out.
			// This generally means that the key isn't a registered column on this model
			// and that the developer is overloading the model with extra data.
			$this->_dataother[$k] = $v;
			return;
		}

		// Is there validation for this key?
		// That function will handle all of this logic, (including the exception throwing)
		$this->validate($k, $v, true);

		// Set the propagation FIRST, that way I have the old key in memory to lookup.
		$this->_setLinkKeyPropagation($k, $v);
		
		/** @var \Core\Datamodel\Columns\SchemaColumn $c */
		$c = $this->_columns[$k];
		
		$c->setValueFromApp($v);
	}

	///////////////////////////////////////////////////////////////////////////
	////                      LINK SPECIFIC METHODS                        //// 

	/**
	 * Get the model factory for a given link.
	 *
	 * Useful for manipulating the factory of the data.
	 *
	 * @param string $linkname
	 *
	 * @return ModelFactory
	 */
	public function getLinkFactory($linkname){
		$idx = $this->_getLinkIndex($linkname);
		if($idx === null){
			return null; // @todo Error Handling
		}

		$c = $this->_getLinkClassName($linkname);

		$f = new ModelFactory($c);
		switch($this->_linked[$idx]['link']){
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
		$idx = $this->_getLinkIndex($linkname);
		if($idx === null){
			return null; // @todo Error Handling
		}

		// Allow order to be set from the model itself.
		if($order === null && isset($this->_linked[$idx]['order'])){
			$order = $this->_linked[$idx]['order'];
		}

		// Try to keep these in cache, so when they change I'll be able to save them on the parent's save function.
		if (!isset($this->_linked[$idx]['records'])) {

			$f       = $this->getLinkFactory($linkname);
			$c       = $this->_getLinkClassName($linkname);
			$wheres  = $this->_getLinkWhereArray($linkname);
			$isBlank = true;

			// Check and see if there is even a local key to attach anything onto!
			foreach($wheres as $val){
				if(trim($val) != ''){
					$isBlank = false;
					break;
				}
			}

			if($isBlank){
				// Skip the lookup, it'll be blank anyway!
				// This saves a few ms on bulk pages that check a lot of potentially empty records.
				$this->_linked[$idx]['records'] = ($f->getDataset()->_limit == 1) ? null : [];
			}
			else{
				if ($order){
					$f->order($order);
				}
				$this->_linked[$idx]['records'] = $f->get();
			}

			// Ensure that it's a valid record and not null.  If it's a LINK_ONE, the factory will return null if it doesn't exist.
			if ($this->_linked[$idx]['records'] === null) {
				$this->_linked[$idx]['records'] = new $c();
				foreach ($wheres as $k => $v) {
					$this->_linked[$idx]['records']->set($k, $v);
				}
			}
		}

		return $this->_linked[$idx]['records'];
	}

	/**
	 * In 1-to-1 mode, this returns either the single record matched or nothing at all.
	 * In 1-to-M mode, this returns an attached object with the requested search keys, either bound or new.
	 *
	 * @param string $linkname
	 * @param array  $searchkeys
	 * @return bool|\Model|null
	 */
	public function findLink($linkname, $searchkeys = []) {
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
			//var_dump($model); die();
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
		$idx = $this->_getLinkIndex($linkname);
		if($idx === null){
			return null; // @todo Error Handling
		}

		// Update the cached model.
		switch($this->_linked[$idx]['link']){
			case Model::LINK_HASONE:
			case Model::LINK_BELONGSTOONE:
				$this->_linked[$idx]['records'] = $model;
				break;
			case Model::LINK_HASMANY:
			case Model::LINK_BELONGSTOMANY:
				if(!isset($this->_linked[$idx]['records'])) $this->_linked[$idx]['records'] = [];
				$this->_linked[$idx]['records'][] = $model;
				break;
		}
	}

	/**
	 * Reset the linked models in this model.  Useful for deleting a child and not wanting them to come back as linked.
	 *
	 * @param $linkname
	 */
	public function resetLink($linkname){
		$idx = $this->_getLinkIndex($linkname);
		if($idx === null){
			return null; // @todo Error Handling
		}

		$this->_linked[$idx]['records'] = null;
		if(isset($this->_linked[$idx]['purged'])){
			unset($this->_linked[$idx]['purged']);
		}
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
		foreach($this->_linked as $idx => $linkset){
			if(!isset($linkset['records'])) continue;

			if(is_array($linkset['records'])){
				foreach($linkset['records'] as $k => $rec){
					if($rec === $link){
						if(!isset($this->_linked[$idx]['purged'])){
							$this->_linked[$idx]['purged'] = [];
						}
						$this->_linked[$idx]['purged'][] = $link;
						unset($this->_linked[$idx]['records'][$k]);
						return true;
					}
				}
			}
			elseif($linkset['records'] === $link){
				if(!isset($this->_linked[$idx]['purged'])){
					$this->_linked[$idx]['purged'] = [];
				}
				$this->_linked[$idx]['purged'][] = $link;
				$this->_linked[$idx]['records'] = null;
				return true;
			}
		}

		return false;
	}

	/**
	 * Get if the given link by name has changed.
	 * 
	 * This accounts for a newly created one, deleted one, or simply modified link.
	 * 
	 * Also handles 1-M and 1-1 links
	 * 
	 * @param string $linkname The link, (by name), to get if changed. 
	 *
	 * @return bool
	 */
	public function changedLink($linkname){
		$idx = $this->_getLinkIndex($linkname);
		if($idx === null){
			return false; // @todo Error Handling
		}

		if($this->_linked[$idx]['records'] === null){
			// If this link is not even loaded, then it can't have been changed.
			return false;
		}
		if(isset($this->_linked[$idx]['deleted']) && $this->_linked[$idx]['deleted'] !== null){
			// If there is a deleted IDX and it's set to an array, then at least one link was deleted, CHANGE!
			return true;
		}

		if(is_array($this->_linked[$idx]['records'])){
			foreach($this->_linked[$idx]['records'] as $subm){
				if($subm->changed()){
					// There is at least one changed sub model!  CHANGE!
					return true;
				}
			}
		}
		elseif($this->_linked[$idx]['records'] instanceof Model){
			if($this->_linked[$idx]['records']->changed()){
				// I think this can happen.... :/
				return true;
			}
		}
		
		// Shrugs!
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

		foreach ($this->_columns as $c) {
			/** @var \Core\Datamodel\Columns\SchemaColumn $c */
			
			// Certain key types have certain functions.
			if($c->type == Model::ATT_TYPE_DELETED && $c->value) {
				// Is this record already set as deleted?
				// If value is > 0|NULL, it's marked as deleted!
				return true;
			}
		}
		
		// Otherwise, it's safe to check the value of exists.
		return !$this->_exists;
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
	 * @param string|null $key Optionally set a key name here to check only that one key.
	 *
	 * @return bool
	 */
	public function changed($key = null){
		
		if($key === null){
			// Check all columns!
			foreach($this->_columns as $c){
				/** @var \Core\Datamodel\Columns\SchemaColumn $c */
				if($c->changed()){
					return true;
				}
			}
			return false;
		}
		elseif(isset($this->_columns[$key])){
			// Individual key was requested.
			if($this->_columns[$key]->changed()){
				return true;
			}
			else{
				return false;
			}
		}
		elseif(isset($this->_dataother[$key])){
			// I have no ability to know if this field was changed, but it's set!
			return true;
		}
		else{
			return false;
		}
	}

	/**
	 * Function to call to decrypt data from this model.
	 *
	 * As of 5.1.0, this is called automatically and therefores this does nothing.
	 */
	public function decryptData(){
		return;
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
		$bits = [];
		$i = self::GetIndexes();

		if(isset($i['primary'])){
			// It should be an array, but doesn't have to be.
			$pri = $i['primary'];
			if(!is_array($pri)) $pri = [$pri];

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
		return (array_key_exists($offset, $this->_columns));
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
				if (!is_array($links)) $links = [$links];

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
		$idx = $this->_getLinkIndex($linkname);
		if($idx === null){
			return null; // @todo Error Handling
		}

		// Determine the class.
		$c = $this->_linked[$idx]['model'];

		if (!is_subclass_of($c, 'Model')){
			return null; // @todo Error Handling
		}

		return $c;
	}

	/**
	 * Called internally by the save() method for new records.
	 */
	protected function _saveNew($defer = false) {
		$i = self::GetIndexes();
		$s = self::GetSchema();
		$n = $this->_getTableName();

		if (!isset($i['primary'])) $i['primary'] = []; // No primary schema defined... just don't make the in_array bail out.

		if($defer){
			$inserts = []; // key => value map for this model
			if(!isset(self::$_DeferInserts[$n])){
				$dat = new Core\Datamodel\Dataset();
				$dat->table($n);
				$dat->_mode = \Core\Datamodel\Dataset::MODE_BULK_INSERT;
				self::$_DeferInserts[$n] = [
					'dataset' => $dat,
					'interface' => $this->interface,
				];
			}
			else{
				$dat = self::$_DeferInserts[$n]['dataset'];
			}
		}
		else{
			$dat = new Core\Datamodel\Dataset();
			$dat->table($n);
		}

		$idcol = false;
		
		foreach($this->_columns as $c){
			/** @var \Core\Datamodel\Columns\SchemaColumn $c */
			if($c->type == Model::ATT_TYPE_UUID){
				if($c->value && $c->valueDB){
					// Yay, a UUID is already set, no need to really do much.
					// It's already set and this will most likely be ignored, but may not be for UPDATE statements...
					// although there shouldn't be any update statements here.... but ya never know
					if($defer){
						$inserts[$c->field] = $c->getInsertValue();
					}
					else{
						$dat->setID($c->field, $c->getInsertValue());
					}
				}
				else{
					// a UUID is already set, but it doesn't exist yet still.
					// This means that the UUID was set externally even though the record is new.
					// THIS IS ALLOWED!
					// Insert it as typical key.
					// Addtionally if this is a new key, the column will automatically generate a UUID as necessary.
					if($defer){
						$inserts[$c->field] = $c->getInsertValue();
					}
					else{
						$dat->insert($c->field, $c->getInsertValue());
						$dat->setID($c->field, $c->getInsertValue());
					}
				}
				// Remember this for after the save.
				$idcol = $c->field;
			}
			elseif($c->type == Model::ATT_TYPE_ID){
				if($c->value){
					// An ID is already set on this key, even though it's an auto-increment ID.
					// Allow this as you may be syncing a model from another system and want their IDs to match up.
					// NOTE, this can be a dangerous operation.
					if($defer){
						$inserts[$c->field] = $c->getInsertValue();
					}
					else{
						$dat->insert($c->field, $c->getInsertValue());
					}
				}
				if(!$defer){
					$dat->setID($c->field, $c->getInsertValue());
					// Remember this for after the save.
					$idcol = $c->field;
				}
			}
			else{
				if($defer){
					$inserts[$c->field] = $c->getInsertValue();
				}
				else{
					$dat->insert($c->field, $c->getInsertValue());
				}
			}
		}
//var_dump($dat); die();
		if($defer) {
			$dat->_sets[] = $inserts;
		}
		else{
			$dat->execute($this->interface);
			if ($idcol){
				$this->_columns[$idcol]->setValueFromDB($dat->getID());
			}
		}
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
		$pri = isset($i['primary']) ? $i['primary'] : [];

		if($pri && !is_array($pri)) $pri = [$pri];

		if($pri && !is_array($pri)) $pri = [$pri];

		// This is the dataset object that will be integral in this function.
		$dat = new Core\Datamodel\Dataset();
		$dat->table($n);

		foreach($this->_columns as $c){
			/** @var \Core\Datamodel\Columns\SchemaColumn $c */
			
			if($c->type == Model::ATT_TYPE_ID || $c->type == Model::ATT_TYPE_UUID){
				$dat->setID($c->field, $c->value);
			}
			elseif($c->changed()){
				$dat->update($c->field, $c->getUpdateValue());
			}

			if (in_array($c->field, $pri)) {
				// Any field keyed as a primary field sets the WHERE clause.
				$dat->where($c->field, $c->getUpdateValue());
			}
		}
		
		//var_dump($dat); die();

		// No data.. nothing to change I guess!
		// This is a failsafe should (for some reason), the changed() method doesn't return the correct value.
		if(!sizeof($dat->_sets)){
			return false;
		}
		//var_dump($dat); die('mep'); // DEBUG
		$dat->execute($this->interface);
		// IDs don't change in updates, else they wouldn't be the id.
		return true;
	}

	/**
	 * Get the where array of criteria for a given link.
	 * Useful for manually tweaking the clause.
	 *
	 * @param string $linkname
	 *
	 * @return array|null
	 */
	protected function _getLinkWhereArray($linkname) {
		$idx = $this->_getLinkIndex($linkname);
		if($idx === null){
			return null; // @todo Error Handling
		}

		// Build a standard where criteria that can be used throughout this function.
		$wheres = [];

		if (!isset($this->_linked[$idx]['on'])) {
			return null; // @todo automatic linking.
		}
		elseif (is_array($this->_linked[$idx]['on'])) {
			foreach ($this->_linked[$idx]['on'] as $k => $v) {
				if (is_numeric($k)) $wheres[$v] = $this->get($v);
				else $wheres[$k] = $this->get($v);
			}
		}
		else {
			$k          = $this->_linked[$idx]['on'];
			$wheres[$k] = $this->get($k);
		}


		// Pages have a special extra here.  If it's enterprise/multisite mode, enforce that relationship.
		if($linkname === 'Page' && Core::IsComponentAvailable('multisite') && MultiSiteHelper::IsEnabled()){
			// See if there's a site column on this schema.  If there is, enforce that binding too!
			$schema = self::GetSchema();
			if(isset($schema['site']) && $schema['site']['type'] == Model::ATT_TYPE_SITE){
				$wheres['site'] = $this->get('site');
			}
		}


		return $wheres;
	}

	/**
	 * Translate a link name, (be it full Model name, partial model name, or linked key name), to the index in _linked.
	 *
	 * @param string $name
	 *
	 * @return int|null
	 */
	protected function _getLinkIndex($name){
		if(isset($this->_linkIndexCache[$name])){
			return $this->_linkIndexCache[ $name ];
		}

		foreach($this->_linked as $idx => $dat){
			if($idx === $name){
				// May happen if the index is passed in, (could happen internally).
				return $idx;
			}
			if(isset($dat['key']) && $dat['key'] == $name){
				$this->_linkIndexCache[$name] = $idx;
				return $idx;
			}
			if(isset($dat['model']) && $dat['model'] == $name){
				$this->_linkIndexCache[$name] = $idx;
				return $idx;
			}
			if(isset($dat['model']) && $dat['model'] == $name . 'Model'){
				$this->_linkIndexCache[$name] = $idx;
				return $idx;
			}
		}

		// No matches?
		$this->_linkIndexCache[$name] = null;
		return null;
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
	 * Since this caches the model in memory, it is ill-advised to use this for very large numbers of records.
	 * Around 50k records stored in memory, it'll consume about 256MB of RAM.
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
			self::$_ModelCache[$class] = [];
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
	 * Shortcut method to find instances of this Model that match a given where clause.
	 *
	 * @static
	 * @param array|string    $where Where clause
	 * @param int|string|null $limit Limit clause
	 * @param string|null     $order Order clause
	 *
	 * @return array|null|Model
	 */
	public static function Find($where = [], $limit = null, $order = null) {

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
	 * Get all records of this Model type as a set of options that can be used with a select box.
	 *
	 * This can be extended in the specific Model if additional functionality is required;
	 * this is simply a default scaffolding that may not work on all instances.
	 *
	 * @static
	 *
	 * @return array
	 */
	public static function GetAllAsOptions() {

		$classname = get_called_class();
		$ref = new ReflectionClass($classname);

		// Execute Find using default parameters.
		// (That method will cache results by default).
		$results = $ref->getMethod('Find')->invoke(null);
		$idx = $ref->getMethod('GetIndexes')->invoke(null);

		if(!isset($idx['primary'])){
			return ['' => 'Unable to automatically get ' . $classname . ' as options because no primary key defined!'];
		}

		if(sizeof($idx['primary']) > 1){
			return ['' => 'Unable to automatically get ' . $classname . ' as options because primary key defined as multiple columns!'];
		}

		$id = $idx['primary'][0];

		$options = [];

		foreach($results as $res){
			/** @var Model $res */
			$options[ $res->get($id) ] = $res->getLabel();
		}

		return $options;
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
	public static function FindRaw($where = [], $limit = null, $order = null) {
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
	public static function Count($where = []) {
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
	public static function Search($query, $where = []){
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

		// Sort the results before returning them.
		// Because otherwise, what's the point of a search algorithm?!?
		usort($ret, function($a, $b) {
			/** @var $a Core\Search\ModelResult */
			/** @var $b Core\Search\ModelResult */
			return $a->relevancy < $b->relevancy;
		});

		return $ret;
	}

	/*************************************************************************
	 ****                    OTHER STATIC METHODS                         ****
	 *************************************************************************/

	/**
	 * Method to encrypt a specific key for storage.
	 *
	 * Called internally by the set function.
	 * Will return the encrypted data.
	 *
	 * @param mixed $value The plain-text value to encrypt
	 * @return string Encrypted data
	 */
	public static function EncryptValue($value){
		$cipher = 'AES-256-CBC';
		$passes = 10;
		$size = openssl_cipher_iv_length($cipher);
		$iv = mcrypt_create_iv($size, MCRYPT_RAND);

		if($value === '') return '';
		elseif($value === null) return null;

		$enc = $value;
		for($i=0; $i<$passes; $i++){
			$enc = openssl_encrypt($enc, $cipher, SECRET_ENCRYPTION_PASSPHRASE, true, $iv);
		}

		$payload = '$' . $cipher . '$' . str_pad($passes, 2, '0', STR_PAD_LEFT) . '$' . $enc . $iv;
		return $payload;
	}

	/**
	 * Decrypt a given value, can be called internally or externally.
	 *
	 * @param $payload
	 *
	 * @return null|string
	 */
	public static function DecryptValue($payload) {
		if($payload === null || $payload === '' || $payload === false){
			return null;
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

		return $dec;
	}

	/**
	 * Get the table name for a given Model object
	 *
	 * @static
	 * @return string
	 */
	public static function GetTableName() {
		// Just a lookup table for rendered table names.
		// This is useful so the regex functions don't have to run more than once.
		static $_tablenames = [];
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
		$classname = strtolower(get_called_class());

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
						'formtype' => 'disabled',
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
						'formtype' => 'disabled',
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
						'formtype' => 'disabled',
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

				$schema[$k] = self::_StandardizeSchemaDefinition($schema[$k]);
				
				// Attach where this schema index came from, useful for some supplemental scripts like i18n scanner.
				$schema[$k]['_defining_model'] = $classname;
				$schema[$k]['_is_supplemental'] = false;
			}

			// Allow all supplemental models to tap into the schema too!
			if(isset(self::$_ModelSupplementals[$classname])){
				foreach(self::$_ModelSupplementals[$classname] as $supplemental){
					if(class_exists($supplemental)){
						$ref = new ReflectionClass($supplemental);
						if($ref->hasProperty('Schema')) {
							// Retrieve the supplemental Schema from this new component
							$s = $ref->getProperty('Schema')->getValue();

							foreach($s as $k => $dat){
								$schema[$k] = self::_StandardizeSchemaDefinition($dat);

								// Attach where this schema index came from, useful for some supplemental scripts like i18n scanner.
								$schema[$k]['_defining_model'] = $supplemental;
								$schema[$k]['_is_supplemental'] = true;
							}
						}
					}
				}
			}
		}

		return self::$_ModelSchemaCache[$classname];
	}

	/**
	 * Internally used method to add a supplemental model to the base model.
	 *
	 * Used to allow components to append the database of another component!
	 *
	 * @param string $original
	 * @param string $supplemental
	 */
	public static function AddSupplemental($original, $supplemental){
		if(!isset(self::$_ModelSupplementals[$original])){
			self::$_ModelSupplementals[$original] = [];
		}

		self::$_ModelSupplementals[$original][] = $supplemental;
		
		// Clear the cache so that the next check for this model contains the supplemental data.
		if(isset(self::$_ModelSchemaCache[$original])){
			self::$_ModelSchemaCache[$original] = null;
		}
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

	public static function CommitSaves(){
		/** @var string $classname The class name of the extending class. */
		$classname = get_called_class();

		$tableName = self::GetTableName();

		if(!isset(self::$_DeferInserts[$tableName])){
			return;
		}

		/** @var \Core\Datamodel\Dataset $dat */
		$dat = self::$_DeferInserts[$tableName]['dataset'];
		$interface = self::$_DeferInserts[$tableName]['interface'];
		$dat->execute($interface);

		unset(self::$_DeferInserts[$tableName]);
	}

	/**
	 * @param array $schema
	 *
	 * @return array
	 */
	private static function _StandardizeSchemaDefinition($schema){
		// These are all defaults for schemas.
		// Setting them to the default if they're not set will ensure that
		// 'undefined index' notices are not incurred.
		if (!isset($schema['type']))               $schema['type']      = Model::ATT_TYPE_TEXT; // Default if not present.
		if (!isset($schema['maxlength']))          $schema['maxlength'] = false;
		if (!isset($schema['null']))               $schema['null']      = false;
		if (!isset($schema['comment']))            $schema['comment']   = false;
		if (!array_key_exists('default', $schema)) $schema['default']   = false;
		if (!isset($schema['encrypted']))          $schema['encrypted'] = false;
		if (!isset($schema['required']))           $schema['required']  = false;
		if (!isset($schema['encoding']))           $schema['encoding']  = false;

		if($schema['default'] === false && $schema['null'] === true){
			// Easiest case!  NULL is allowed on this column.
			$schema['default'] = null;
		}
		
		if($schema['type'] == Model::ATT_TYPE_ENUM){
			// Enums have an options array!
			$schema['options'] = isset($schema['options']) ? $schema['options'] : [];
		}
		else{
			// Other fields don't.
			$schema['options'] = false;
		}
		
		return $schema;
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
		call_user_func_array([$this->_dataset, 'where'], func_get_args());
	}

	public function whereGroup() {
		call_user_func_array([$this->_dataset, 'whereGroup'], func_get_args());
	}

	public function order() {
		call_user_func_array([$this->_dataset, 'order'], func_get_args());
	}

	public function limit() {
		call_user_func_array([$this->_dataset, 'limit'], func_get_args());
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

		$ret = [];
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
			Core::IsComponentAvailable('multisite') &&
			MultiSiteHelper::IsEnabled()
		){
			// I want to look it up because if the script actually set the site, then
			// it evidently wants it for a reason.
			$siteexact = (sizeof($this->_dataset->getWhereClause()->findByField('site')) > 0);
			$idexact = false;

			// The primary check will allow a model to be instantiated with the exact primary key string.
			$pri = isset($index['primary']) ? $index['primary'] : null;
			
			if($pri && !is_array($pri)) $pri = [$pri];
			
			if($pri){
				$allids = true;
				foreach($pri as $k){
					if(sizeof($this->_dataset->getWhereClause()->findByField($k)) == 0){
						$allids = false;
						break;
					}
				}
				if($allids) $idexact = true;
			}

			if(!($siteexact || $idexact)){
				$w = new \Core\Datamodel\DatasetWhereClause();
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
