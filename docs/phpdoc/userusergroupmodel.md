UserUserGroupModel
===============

Model for UserUserGroupModel

Every Model in the system be it core models or user-created components, MUST extend this
class in order for proper functioning.


* Class name: UserUserGroupModel
* Namespace: 
* Parent class: [Model](model.md)



Constants
----------


### ATT_TYPE_STRING

    const ATT_TYPE_STRING = 'string'





### ATT_TYPE_TEXT

    const ATT_TYPE_TEXT = 'text'





### ATT_TYPE_DATA

    const ATT_TYPE_DATA = 'data'





### ATT_TYPE_INT

    const ATT_TYPE_INT = 'int'





### ATT_TYPE_FLOAT

    const ATT_TYPE_FLOAT = 'float'





### ATT_TYPE_BOOL

    const ATT_TYPE_BOOL = 'boolean'





### ATT_TYPE_ENUM

    const ATT_TYPE_ENUM = 'enum'





### ATT_TYPE_UUID

    const ATT_TYPE_UUID = '__uuid'





### ATT_TYPE_UUID_FK

    const ATT_TYPE_UUID_FK = '__uuid_fk'





### ATT_TYPE_ID

    const ATT_TYPE_ID = '__id'





### ATT_TYPE_ID_FK

    const ATT_TYPE_ID_FK = '__id_fk'





### ATT_TYPE_UPDATED

    const ATT_TYPE_UPDATED = '__updated'





### ATT_TYPE_CREATED

    const ATT_TYPE_CREATED = '__created'





### ATT_TYPE_DELETED

    const ATT_TYPE_DELETED = '__deleted'





### ATT_TYPE_SITE

    const ATT_TYPE_SITE = '__site'





### ATT_TYPE_ALIAS

    const ATT_TYPE_ALIAS = '__alias'





### ATT_TYPE_ISO_8601_DATETIME

    const ATT_TYPE_ISO_8601_DATETIME = 'ISO_8601_datetime'





### ATT_TYPE_MYSQL_TIMESTAMP

    const ATT_TYPE_MYSQL_TIMESTAMP = 'mysql_timestamp'





### ATT_TYPE_ISO_8601_DATE

    const ATT_TYPE_ISO_8601_DATE = 'ISO_8601_date'





### VALIDATION_NOTBLANK

    const VALIDATION_NOTBLANK = "/^.+$/"





### VALIDATION_EMAIL

    const VALIDATION_EMAIL = 'Core::CheckEmailValidity'





### VALIDATION_URL

    const VALIDATION_URL = '#^[a-zA-Z]+://.+$#'





### VALIDATION_URL_WEB

    const VALIDATION_URL_WEB = '#^[hH][tT][tT][pP][sS]{0,1}://.+$#'





### VALIDATION_INT_GT0

    const VALIDATION_INT_GT0 = 'Core::CheckIntGT0Validity'





### VALIDATION_NUMBER_WHOLE

    const VALIDATION_NUMBER_WHOLE = "/^[0-9]*$/"





### VALIDATION_CURRENCY_USD

    const VALIDATION_CURRENCY_USD = '#^(\$)?[,0-9]*(?:\.[0-9]{2})?$#'





### LINK_HASONE

    const LINK_HASONE = 'one'





### LINK_HASMANY

    const LINK_HASMANY = 'many'





### LINK_BELONGSTOONE

    const LINK_BELONGSTOONE = 'belongs_one'





### LINK_BELONGSTOMANY

    const LINK_BELONGSTOMANY = 'belongs_many'





### ATT_ENCODING_BASE64

    const ATT_ENCODING_BASE64 = 'base64'





### ATT_ENCODING_JSON

    const ATT_ENCODING_JSON = 'json'





### ATT_ENCODING_SERIALIZE

    const ATT_ENCODING_SERIALIZE = 'serialize'





### ATT_ENCODING_GZIP

    const ATT_ENCODING_GZIP = 'gzip'





### ATT_ENCODING_UTF8

    const ATT_ENCODING_UTF8 = 'utf8'





Properties
----------


### $Schema

    public array $Schema = array()





* Visibility: **public**
* This property is **static**.


### $Indexes

    public array $Indexes = array()





* Visibility: **public**
* This property is **static**.


### $interface

    public \Core\Datamodel\BackendInterface $interface = null

Which DataModelInterface should this model execute its operations with.

99.9% of the time, it's fine to leave this as null, which will use the
system DMI.  If however you want to utilize a Model with Memcache,
(say for session information), it can be useful.

* Visibility: **public**


### $_dataother

    protected array $_dataother = array()

Allow data to get overloaded onto models.

This is common with Controllers tacking on extra data for templates to better handle the model.
This data is not saved and does not effect the dirty flags.

* Visibility: **protected**


### $_columns

    protected null $_columns = null





* Visibility: **protected**


### $_aliases

    protected null $_aliases = null





* Visibility: **protected**


### $_exists

    protected boolean $_exists = false





* Visibility: **protected**


### $_linked

    protected array $_linked = array()





* Visibility: **protected**


### $_linkIndexCache

    protected array $_linkIndexCache = array()





* Visibility: **protected**


### $_cacheable

    protected mixed $_cacheable = true





* Visibility: **protected**


### $HasSearch

    public boolean $HasSearch = false





* Visibility: **public**
* This property is **static**.


### $HasCreated

    public boolean $HasCreated = false





* Visibility: **public**
* This property is **static**.


### $HasUpdated

    public boolean $HasUpdated = false





* Visibility: **public**
* This property is **static**.


### $HasDeleted

    public boolean $HasDeleted = false





* Visibility: **public**
* This property is **static**.


### $_ModelCache

    public mixed $_ModelCache = array()





* Visibility: **public**
* This property is **static**.


### $_ModelFindCache

    public array $_ModelFindCache = array()





* Visibility: **public**
* This property is **static**.


### $_ModelSchemaCache

    protected array $_ModelSchemaCache = array()





* Visibility: **protected**
* This property is **static**.


### $_DeferInserts

    protected array $_DeferInserts = array()

Used with the defer save option to bulk-insert commands when possible.

This is used to speed up bulk INSERT statements.

* Visibility: **protected**
* This property is **static**.


### $_ModelSupplementals

    protected array $_ModelSupplementals = array()

List of models that provide supplemental functionality on the base model.

Used for GetSchema, GetIndexes, and the various Model-based hooks.

* Visibility: **protected**
* This property is **static**.


Methods
-------


### __construct

    mixed Model::__construct(null $key)

Create a new instance of the requested model.



* Visibility: **public**
* This method is defined by [Model](model.md)


#### Arguments
* $key **null**



### load

    mixed Model::load()

Load this record from the datastore.

Generally not needed to be called directly, but can be if required.

* Visibility: **public**
* This method is defined by [Model](model.md)




### save

    boolean Model::save(boolean $defer)

Save this Model into the datastore.

Return true if saved successfully, false if no change required,
and will throw a DMI_Exception if there was an error.

As of 5.0.0, bulk inserts can be performed by passing TRUE as the one argument.
If this is done, you MUST call CommitSaves() after all data has been stored!

* Visibility: **public**
* This method is defined by [Model](model.md)


#### Arguments
* $defer **boolean** - &lt;p&gt;Set to true to batch-save this data as a BULK INSERT.&lt;/p&gt;



### get

    mixed Model::get(string $k)

Get the requested key for this object.



* Visibility: **public**
* This method is defined by [Model](model.md)


#### Arguments
* $k **string**



### getColumn

    \Core\Datamodel\Columns\SchemaColumn|null Model::getColumn(string $key)

Get the column schema for a given key, or null if it doesn't exist.



* Visibility: **public**
* This method is defined by [Model](model.md)


#### Arguments
* $key **string**



### __toString

    string Model::__toString()

Get this model as a string



* Visibility: **public**
* This method is defined by [Model](model.md)




### getLabel

    string Model::getLabel()

Get the human-readable label for this record.

By default, it will sift through the schema looking for keys that appear to be human-readable terms,
but for best results, please extend this method and have it return what's necessary for the given Model.

* Visibility: **public**
* This method is defined by [Model](model.md)




### getAsArray

    array Model::getAsArray()

Just return this object as an array
(essentially just the _data array.

.. :p)

* Visibility: **public**
* This method is defined by [Model](model.md)




### getAsJSON

    string Model::getAsJSON()

Return this object as a flattened JSON array using json_encode.



* Visibility: **public**
* This method is defined by [Model](model.md)




### getData

    array Model::getData()

Get the data of this model.

Don't use this, it's probably not what you need.

* Visibility: **public**
* This method is defined by [Model](model.md)




### getInitialData

    array|null Model::getInitialData()

Get the initial data of this model as it was when it was loaded from teh database.



* Visibility: **public**
* This method is defined by [Model](model.md)




### getKeySchemas

    array Model::getKeySchemas()

Get a valid schema of all keys of this model.

This will ensure all the core optional attributes are set at the
default value and a few other dynamic attributes.

Alias of Model::GetSchema()

* Visibility: **public**
* This method is defined by [Model](model.md)




### getKeySchema

    null|array Model::getKeySchema(string $key)

Get a valid schema of the requested key of this model or null if it doesn't exist.



* Visibility: **public**
* This method is defined by [Model](model.md)


#### Arguments
* $key **string**



### getSearchIndexString

    string Model::getSearchIndexString()

Get a textual representation of this Model as a flat string.

Used by the search systems to index the model, (or multiple models into one).

* Visibility: **public**
* This method is defined by [Model](model.md)




### hasDraft

    boolean Model::hasDraft()

Lookup and see if this model instance has a draft saved for it.



* Visibility: **public**
* This method is defined by [Model](model.md)




### getDraftStatus

    string Model::getDraftStatus()

Get the draft status of this model.



* Visibility: **public**
* This method is defined by [Model](model.md)




### getControlLinks

    array Model::getControlLinks()

Get an array of control links for this model.

Please call array_merge($results, parent::getControlLinks())
in any extending method to retain the supplemental model functionality.

The returned data MUST be either an empty array or an index array of arrays.
Each internal array should have link, title, icon, and any other parameter supported by the ViewControl

* Visibility: **public**
* This method is defined by [Model](model.md)




### _loadFromRecord

    mixed Model::_loadFromRecord(array $record)

Load this model from an associative array, or record.

This is meant to be called from the Factory system, and the data passed in
MUST be sanitized and valid!

* Visibility: **public**
* This method is defined by [Model](model.md)


#### Arguments
* $record **array**



### delete

    boolean Model::delete()

Delete this record from the datastore.

Will IMMEDIATELY remove the record!

If this model has a "deleted" column that is set as a zero value, that record is set to the current timestamp instead.
This functionality is meant for advanced record tracking such as those in use in sync systems.

* Visibility: **public**
* This method is defined by [Model](model.md)




### validate

    boolean|mixed|string Model::validate(string $k, mixed $v, boolean $throwexception)

Handle data validation for keys.

This will lookup if any "validation" is set on the schema, and check it if it exists.
This will not actually do any setting, simply return true or throw an exception, (if requested).

* Visibility: **public**
* This method is defined by [Model](model.md)


#### Arguments
* $k **string** - &lt;p&gt;The key to validate&lt;/p&gt;
* $v **mixed** - &lt;p&gt;The value to validate with&lt;/p&gt;
* $throwexception **boolean** - &lt;p&gt;Set to true if you would like this function to throw errors.&lt;/p&gt;



### set

    mixed Model::set(string $k, mixed $v)

Set a value of a specific key.

The data is validated automatically as per the specific Model specifications.

This supports data overloading.

* Visibility: **public**
* This method is defined by [Model](model.md)


#### Arguments
* $k **string** - &lt;p&gt;The key to set&lt;/p&gt;
* $v **mixed** - &lt;p&gt;The value to set&lt;/p&gt;



### getLinkFactory

    \ModelFactory Model::getLinkFactory(string $linkname)

Get the model factory for a given link.

Useful for manipulating the factory of the data.

* Visibility: **public**
* This method is defined by [Model](model.md)


#### Arguments
* $linkname **string**



### getLink

    \Model|array Model::getLink(string $linkname, null|string $order)

Get linked models to this model based on a link name

If the link type is a one-to-one or many-to-one, (HASONE), a single Model is returned.
else this behaves as the Find function, where an array of models is returned.

* Visibility: **public**
* This method is defined by [Model](model.md)


#### Arguments
* $linkname **string** - &lt;p&gt;The linked model name (minus the Model part)&lt;/p&gt;
* $order **null|string** - &lt;p&gt;Specify the order clause&lt;/p&gt;



### findLink

    boolean|\Model|null Model::findLink(string $linkname, array $searchkeys)

In 1-to-1 mode, this returns either the single record matched or nothing at all.

In 1-to-M mode, this returns an attached object with the requested search keys, either bound or new.

* Visibility: **public**
* This method is defined by [Model](model.md)


#### Arguments
* $linkname **string**
* $searchkeys **array**



### setLink

    void Model::setLink($linkname, \Model $model)

Add a model to the set of linked records, (or replace it in the case or HASONE).

Administrative method used internally by some systems.  This allows a link to be overwritten externally.

Particularly useful for BELONGSTOONE models being updated by their parent.

* Visibility: **public**
* This method is defined by [Model](model.md)


#### Arguments
* $linkname **mixed**
* $model **[Model](model.md)**



### resetLink

    mixed Model::resetLink($linkname)

Reset the linked models in this model.  Useful for deleting a child and not wanting them to come back as linked.



* Visibility: **public**
* This method is defined by [Model](model.md)


#### Arguments
* $linkname **mixed**



### deleteLink

    boolean Model::deleteLink(\Model $link)

Mark a linked model for deletion.

Doesn't actually delete the linked model until this element is saved.

* Visibility: **public**
* This method is defined by [Model](model.md)


#### Arguments
* $link **[Model](model.md)**



### changedLink

    boolean Model::changedLink(string $linkname)

Get if the given link by name has changed.

This accounts for a newly created one, deleted one, or simply modified link.

Also handles 1-M and 1-1 links

* Visibility: **public**
* This method is defined by [Model](model.md)


#### Arguments
* $linkname **string** - &lt;p&gt;The link, (by name), to get if changed.&lt;/p&gt;



### setFromArray

    mixed Model::setFromArray($array)

Set properties on this model from an associative array of key/value pairs.



* Visibility: **public**
* This method is defined by [Model](model.md)


#### Arguments
* $array **mixed**



### setFromForm

    mixed Model::setFromForm(\Form $form, string|null $prefix)

Set properties on this model from a form object, optionally with a specific prefix.



* Visibility: **public**
* This method is defined by [Model](model.md)


#### Arguments
* $form **[Form](form.md)** - &lt;p&gt;Form object to pull data from&lt;/p&gt;
* $prefix **string|null** - &lt;p&gt;Prefix that all keys should be matched to, (optional)&lt;/p&gt;



### setToFormElement

    mixed Model::setToFormElement($key, \FormElement $element)

Converse to setFromForm, this method is called on each form element created when calling addModel or BuildFromModel.

Any special instructions for your model's elements can go here, simply extend this method and add logic as necessary.

* Visibility: **public**
* This method is defined by [Model](model.md)


#### Arguments
* $key **mixed**
* $element **[FormElement](formelement.md)**



### addToFormPost

    mixed Model::addToFormPost(\Form $form, string $prefix)

Method that is called on the model after "addModel" is called on a form.

Any special logic such as adding custom elements from the model can be done here, simply extend this method and add logic as necessary.

* Visibility: **public**
* This method is defined by [Model](model.md)


#### Arguments
* $form **[Form](form.md)**
* $prefix **string**



### exists

    boolean Model::exists()

Get if this model exists in the datastore already.



* Visibility: **public**
* This method is defined by [Model](model.md)




### isdeleted

    boolean Model::isdeleted()

Get if this model is marked as deleted and/or deleted already.



* Visibility: **public**
* This method is defined by [Model](model.md)




### isnew

    boolean Model::isnew()

Get if this model is a new entity that doesn't exist in the datastore.



* Visibility: **public**
* This method is defined by [Model](model.md)




### changed

    boolean Model::changed(string|null $key)

Get if this model has changes that are pending to be applied back to the datastore.



* Visibility: **public**
* This method is defined by [Model](model.md)


#### Arguments
* $key **string|null** - &lt;p&gt;Optionally set a key name here to check only that one key.&lt;/p&gt;



### decryptData

    mixed Model::decryptData()

Function to call to decrypt data from this model.

As of 5.1.0, this is called automatically and therefores this does nothing.

* Visibility: **public**
* This method is defined by [Model](model.md)




### _getTableName

    null|string Model::_getTableName()

Get the table name for this class



* Visibility: **public**
* This method is defined by [Model](model.md)




### getPrimaryKeyString

    string Model::getPrimaryKeyString()

Get the primary key value(s) of this model as a string



* Visibility: **public**
* This method is defined by [Model](model.md)




### offsetExists

    boolean Model::offsetExists(mixed $offset)

Whether an offset exists



* Visibility: **public**
* This method is defined by [Model](model.md)


#### Arguments
* $offset **mixed** - &lt;p&gt;An offset to check for.&lt;/p&gt;



### offsetGet

    mixed Model::offsetGet(mixed $offset)

Offset to retrieve

Alias of Model::get()

* Visibility: **public**
* This method is defined by [Model](model.md)


#### Arguments
* $offset **mixed** - &lt;p&gt;The offset to retrieve.&lt;/p&gt;



### offsetSet

    void Model::offsetSet(mixed $offset, mixed $value)

Offset to set

Alias of Model::set()

* Visibility: **public**
* This method is defined by [Model](model.md)


#### Arguments
* $offset **mixed** - &lt;p&gt;The offset to assign the value to.&lt;/p&gt;
* $value **mixed** - &lt;p&gt;The value to set.&lt;/p&gt;



### offsetUnset

    void Model::offsetUnset(mixed $offset)

Offset to unset

This just sets the value to null.

* Visibility: **public**
* This method is defined by [Model](model.md)


#### Arguments
* $offset **mixed** - &lt;p&gt;The offset to unset.&lt;/p&gt;



### _setLinkKeyPropagation

    mixed Model::_setLinkKeyPropagation(string $key, mixed $newval)

Go through any linked tables and update them if the linking key has been changed.

This needs to actually go through the database and update the saved keys if necessary.

* Visibility: **protected**
* This method is defined by [Model](model.md)


#### Arguments
* $key **string**
* $newval **mixed**



### _getLinkClassName

    null|string Model::_getLinkClassName(string $linkname)

Get the fully resolved ClassName of the requested link name.



* Visibility: **protected**
* This method is defined by [Model](model.md)


#### Arguments
* $linkname **string** - &lt;p&gt;Name of one of the linked models.&lt;/p&gt;



### _saveNew

    mixed Model::_saveNew($defer)

Called internally by the save() method for new records.



* Visibility: **protected**
* This method is defined by [Model](model.md)


#### Arguments
* $defer **mixed**



### _saveExisting

    boolean Model::_saveExisting(boolean $useset)

Save an existing Model object into the database.

Will create, set and execute a dataset object as appropriately internally.

* Visibility: **protected**
* This method is defined by [Model](model.md)


#### Arguments
* $useset **boolean** - &lt;p&gt;Set to true to have this model use an INSERT_UPDATE statement instead of just UPDATE.&lt;/p&gt;



### _getLinkWhereArray

    array|null Model::_getLinkWhereArray(string $linkname)

Get the where array of criteria for a given link.

Useful for manually tweaking the clause.

* Visibility: **protected**
* This method is defined by [Model](model.md)


#### Arguments
* $linkname **string**



### _getLinkIndex

    integer|null Model::_getLinkIndex(string $name)

Translate a link name, (be it full Model name, partial model name, or linked key name), to the index in _linked.



* Visibility: **protected**
* This method is defined by [Model](model.md)


#### Arguments
* $name **string**



### _getCacheKey

    mixed Model::_getCacheKey()





* Visibility: **protected**
* This method is defined by [Model](model.md)




### Construct

    \Model Model::Construct($keys)

Constructor alternative that utilizes caching to save on database lookups.

Since this caches the model in memory, it is ill-advised to use this for very large numbers of records.
Around 50k records stored in memory, it'll consume about 256MB of RAM.

* Visibility: **public**
* This method is **static**.
* This method is defined by [Model](model.md)


#### Arguments
* $keys **mixed**



### Find

    array|null|\Model Model::Find(array|string $where, integer|string|null $limit, string|null $order)

Shortcut method to find instances of this Model that match a given where clause.



* Visibility: **public**
* This method is **static**.
* This method is defined by [Model](model.md)


#### Arguments
* $where **array|string** - &lt;p&gt;Where clause&lt;/p&gt;
* $limit **integer|string|null** - &lt;p&gt;Limit clause&lt;/p&gt;
* $order **string|null** - &lt;p&gt;Order clause&lt;/p&gt;



### GetAllAsOptions

    array Model::GetAllAsOptions()

Get all records of this Model type as a set of options that can be used with a select box.

This can be extended in the specific Model if additional functionality is required;
this is simply a default scaffolding that may not work on all instances.

* Visibility: **public**
* This method is **static**.
* This method is defined by [Model](model.md)




### FindRaw

    array Model::FindRaw(array $where, null $limit, null $order)

Factory shortcut function to do a search for the specific records and return them as a raw array.



* Visibility: **public**
* This method is **static**.
* This method is defined by [Model](model.md)


#### Arguments
* $where **array**
* $limit **null**
* $order **null**



### Count

    integer Model::Count(array $where)

Get a count of records that match a given where criteria



* Visibility: **public**
* This method is **static**.
* This method is defined by [Model](model.md)


#### Arguments
* $where **array**



### Search

    array Model::Search(string $query, array $where)

Perform a model search on the records of this Model.



* Visibility: **public**
* This method is **static**.
* This method is defined by [Model](model.md)


#### Arguments
* $query **string** - &lt;p&gt;The base query to search&lt;/p&gt;
* $where **array** - &lt;p&gt;Any additional where parameters to add onto the factory&lt;/p&gt;



### EncryptValue

    string Model::EncryptValue(mixed $value)

Method to encrypt a specific key for storage.

Called internally by the set function.
Will return the encrypted data.

* Visibility: **public**
* This method is **static**.
* This method is defined by [Model](model.md)


#### Arguments
* $value **mixed** - &lt;p&gt;The plain-text value to encrypt&lt;/p&gt;



### DecryptValue

    null|string Model::DecryptValue($payload)

Decrypt a given value, can be called internally or externally.



* Visibility: **public**
* This method is **static**.
* This method is defined by [Model](model.md)


#### Arguments
* $payload **mixed**



### GetTableName

    string Model::GetTableName()

Get the table name for a given Model object



* Visibility: **public**
* This method is **static**.
* This method is defined by [Model](model.md)




### GetSchema

    mixed Model::GetSchema()

Get the resolved schema for this Model type.

This is called by several other methods, including getKeySchemas and getKeySchema.

* Visibility: **public**
* This method is **static**.
* This method is defined by [Model](model.md)




### AddSupplemental

    mixed Model::AddSupplemental(string $original, string $supplemental)

Internally used method to add a supplemental model to the base model.

Used to allow components to append the database of another component!

* Visibility: **public**
* This method is **static**.
* This method is defined by [Model](model.md)


#### Arguments
* $original **string**
* $supplemental **string**



### GetIndexes

    mixed Model::GetIndexes()





* Visibility: **public**
* This method is **static**.
* This method is defined by [Model](model.md)




### CommitSaves

    mixed Model::CommitSaves()





* Visibility: **public**
* This method is **static**.
* This method is defined by [Model](model.md)




### _StandardizeSchemaDefinition

    array Model::_StandardizeSchemaDefinition(array $schema)





* Visibility: **private**
* This method is **static**.
* This method is defined by [Model](model.md)


#### Arguments
* $schema **array**


