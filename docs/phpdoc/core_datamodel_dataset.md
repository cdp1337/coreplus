Core\Datamodel\Dataset
===============






* Class name: Dataset
* Namespace: Core\Datamodel
* This class implements: Iterator


Constants
----------


### MODE_ALTER

    const MODE_ALTER = 'alter'





### MODE_GET

    const MODE_GET = 'get'





### MODE_INSERT

    const MODE_INSERT = 'insert'





### MODE_BULK_INSERT

    const MODE_BULK_INSERT = 'bulk_insert'





### MODE_UPDATE

    const MODE_UPDATE = 'update'





### MODE_INSERTUPDATE

    const MODE_INSERTUPDATE = 'insertupdate'





### MODE_DELETE

    const MODE_DELETE = 'delete'





### MODE_COUNT

    const MODE_COUNT = 'count'





Properties
----------


### $_table

    public mixed $_table





* Visibility: **public**


### $_selects

    public mixed $_selects = null





* Visibility: **public**


### $_where

    public null $_where = null

The root where clause for this dataset



* Visibility: **public**


### $_mode

    public string $_mode = \Core\Datamodel\Dataset::MODE_GET





* Visibility: **public**


### $_sets

    public array $_sets = array()





* Visibility: **public**


### $_idcol

    public mixed $_idcol = null





* Visibility: **public**


### $_idval

    public mixed $_idval = null





* Visibility: **public**


### $_limit

    public mixed $_limit = false





* Visibility: **public**


### $_order

    public mixed $_order = false





* Visibility: **public**


### $_data

    public mixed $_data = null





* Visibility: **public**


### $num_rows

    public mixed $num_rows = null





* Visibility: **public**


### $_inserts

    private mixed $_inserts = null





* Visibility: **private**


### $_updates

    private mixed $_updates = null





* Visibility: **private**


### $_deletes

    private mixed $_deletes = null





* Visibility: **private**


### $_isBulk

    private boolean $_isBulk = false





* Visibility: **private**


### $_renames

    public null $_renames = null

Column renames used in the alter mode



* Visibility: **public**


### $uniquerecords

    public boolean $uniquerecords = false

Set to true to return only unique records, ala SELECT DISTINCT



* Visibility: **public**


Methods
-------


### __construct

    mixed Core\Datamodel\Dataset::__construct()





* Visibility: **public**




### __clone

    mixed Core\Datamodel\Dataset::__clone()

On clone, make a deep copy of this object!

This is required so that the WHERE clause does not get copied by memory space.
Otherwise altering the clone dataset will modify the original dataset!

* Visibility: **public**




### select

    \Core\Datamodel\Dataset Core\Datamodel\Dataset::select()

Set the columns to select.

Argument can be the following:
null: reset the array to blank
single value: add the value to the columns
array of values: add each value to the columns
multiple arguments: add each value to the columns

* Visibility: **public**




### insert

    \Core\Datamodel\Dataset Core\Datamodel\Dataset::insert()





* Visibility: **public**




### bulkInsert

    \Core\Datamodel\Dataset Core\Datamodel\Dataset::bulkInsert(array $data)

Request a bulk insert.



* Visibility: **public**


#### Arguments
* $data **array** - &lt;p&gt;Key/Value array of the data to bulk insert as a new record&lt;/p&gt;



### update

    \Core\Datamodel\Dataset Core\Datamodel\Dataset::update()





* Visibility: **public**




### set

    \Core\Datamodel\Dataset Core\Datamodel\Dataset::set()





* Visibility: **public**




### renameColumn

    \Core\Datamodel\Dataset Core\Datamodel\Dataset::renameColumn()

Rename a column in this dataset, primarlly an administrative / installer function.



* Visibility: **public**




### delete

    \Core\Datamodel\Dataset Core\Datamodel\Dataset::delete()

Delete an entire record or specific key from the store, (on supported DMIs).

For noSQL type databases, (LDAP, Mongo), this operation can delete a specific key from an object.
Otherwise, simply calling it will request the entire record/object to be deleted.

* Visibility: **public**




### count

    \Core\Datamodel\Dataset Core\Datamodel\Dataset::count()

Set this dataset to only return the count of records.



* Visibility: **public**




### setID

    mixed Core\Datamodel\Dataset::setID($key, $val)





* Visibility: **public**


#### Arguments
* $key **mixed**
* $val **mixed**



### getID

    null|integer Core\Datamodel\Dataset::getID()

Get the ID of the inserted column; useful for auto-incs.

Will return null if there is no ID set.

* Visibility: **public**




### getMode

    string Core\Datamodel\Dataset::getMode()

Get the mode for this query

Pulled dynamically based on what parameters have been requested.

* Visibility: **public**




### getInserts

    null|array Core\Datamodel\Dataset::getInserts()

Get the columns to insert



* Visibility: **public**




### getUpdates

    null|array Core\Datamodel\Dataset::getUpdates()

Get the columns to update along with their new values.



* Visibility: **public**




### getDeletes

    null|array Core\Datamodel\Dataset::getDeletes()

Get the columns to delete and/or the values on the key to delete.



* Visibility: **public**




### table

    \Core\Datamodel\Dataset Core\Datamodel\Dataset::table(string $tablename)





* Visibility: **public**


#### Arguments
* $tablename **string**



### unique

    \Core\Datamodel\Dataset Core\Datamodel\Dataset::unique(boolean $unique)





* Visibility: **public**


#### Arguments
* $unique **boolean**



### getWhereClause

    \Core\Datamodel\DatasetWhereClause Core\Datamodel\Dataset::getWhereClause()





* Visibility: **public**




### where

    \Core\Datamodel\Dataset Core\Datamodel\Dataset::where()

Set or add to the where clause for this query.

Argument passed in can be a multitude of options:
key/value paired array:


Supported formats:

The most simple method, set the where clause to look where one specific key is a value.
<pre>
where("key", "value");
</pre>

Just a regular string for the where statement
<pre>
where('key = some value');
where('key > 123');
where('key LIKE something%foo');
</pre>

Associative array of simple equal wheres.  This method is limiting in that it only supports '=' checks.
<pre>
where(array('key' => 'value1', 'key2' => 'value2'));
</pre>

Indexed array of multiple where statements, allow any value check.
<pre>
where(array('key = value1', 'key2 > 123'));
</pre>

* Visibility: **public**




### whereGroup

    \Core\Datamodel\Dataset Core\Datamodel\Dataset::whereGroup(string $separator, array|string $wheres)

Allow for grouping of groups of where clauses.

This is useful for statements such as
WHERE (this = 1 OR that = 1) AND something = blah;

The where clause can either be a single array, a single string, or a list of arguments

* Visibility: **public**


#### Arguments
* $separator **string** - &lt;p&gt;&#039;AND&#039;, &#039;OR&#039;&lt;/p&gt;
* $wheres **array|string**



### limit

    \Core\Datamodel\Dataset Core\Datamodel\Dataset::limit()

Set the limit for this dataset.

Supports a single argument for a hard limit or two arguments for starting at and limit.

* Visibility: **public**




### order

    \Core\Datamodel\Dataset Core\Datamodel\Dataset::order()





* Visibility: **public**




### execute

    \Core\Datamodel\Dataset Core\Datamodel\Dataset::execute(\Core\Datamodel\BackendInterface $interface)





* Visibility: **public**


#### Arguments
* $interface **[Core\Datamodel\BackendInterface](core_datamodel_backendinterface.md)**



### executeAndGet

    array|null|mixed Core\Datamodel\Dataset::executeAndGet(null $interface)

Execute this query and return the records or record, based on requested criteria.

If limit == 1 and only one select was issued, that singular value or null is returned.
If limit == 1 and more than one select was issued, an associative array is returned.
If select contains 1 key and it's not "*", an indexed array is returned containing all results.
Otherwise, an array of associative arrays is returned.

* Visibility: **public**


#### Arguments
* $interface **null**



### rewind

    mixed Core\Datamodel\Dataset::rewind()





* Visibility: **public**




### current

    mixed Core\Datamodel\Dataset::current()





* Visibility: **public**




### key

    mixed Core\Datamodel\Dataset::key()





* Visibility: **public**




### next

    mixed Core\Datamodel\Dataset::next()





* Visibility: **public**




### valid

    mixed Core\Datamodel\Dataset::valid()





* Visibility: **public**




### Init

    \Core\Datamodel\Dataset Core\Datamodel\Dataset::Init()

Simple constructor that allows chaining.



* Visibility: **public**
* This method is **static**.



