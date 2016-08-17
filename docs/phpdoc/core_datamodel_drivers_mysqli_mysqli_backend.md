Core\Datamodel\Drivers\mysqli\mysqli_backend
===============






* Class name: mysqli_backend
* Namespace: Core\Datamodel\Drivers\mysqli
* This class implements: [Core\Datamodel\BackendInterface](core_datamodel_backendinterface.md)




Properties
----------


### $_conn

    private \mysqli $_conn = null





* Visibility: **private**


Methods
-------


### connect

    mixed|void Core\Datamodel\Drivers\mysqli\mysqli_backend::connect(string $host, string $user, string $pass, string $database)

Create a new connection to a mysql server.



* Visibility: **public**


#### Arguments
* $host **string**
* $user **string**
* $pass **string**
* $database **string**



### execute

    mixed Core\Datamodel\BackendInterface::execute(\Core\Datamodel\Dataset $dataset)

Execute a given Dataset object on this backend



* Visibility: **public**
* This method is defined by [Core\Datamodel\BackendInterface](core_datamodel_backendinterface.md)


#### Arguments
* $dataset **[Core\Datamodel\Dataset](core_datamodel_dataset.md)**



### getConnection

    \mysqli|null Core\Datamodel\Drivers\mysqli\mysqli_backend::getConnection()





* Visibility: **public**




### tableExists

    boolean Core\Datamodel\BackendInterface::tableExists(string $tablename)

Check to see if a given table exists without causing an error.



* Visibility: **public**
* This method is defined by [Core\Datamodel\BackendInterface](core_datamodel_backendinterface.md)


#### Arguments
* $tablename **string**



### createTable

    boolean Core\Datamodel\BackendInterface::createTable(string $table, \Core\Datamodel\Schema $schema)

Create a table on this backend with the provided Schema.



* Visibility: **public**
* This method is defined by [Core\Datamodel\BackendInterface](core_datamodel_backendinterface.md)


#### Arguments
* $table **string** - &lt;p&gt;Table name to be created&lt;/p&gt;
* $schema **[Core\Datamodel\Schema](core_datamodel_schema.md)** - &lt;p&gt;Schema to create table with&lt;/p&gt;



### modifyTable

    boolean|array Core\Datamodel\BackendInterface::modifyTable(string $table, \Core\Datamodel\Schema $schema)

Modify a table to match a new schema.

This is used to keep the database in sync with the code upon upgrades, installations and reinstalls.

* Visibility: **public**
* This method is defined by [Core\Datamodel\BackendInterface](core_datamodel_backendinterface.md)


#### Arguments
* $table **string** - &lt;p&gt;Table name to be created&lt;/p&gt;
* $schema **[Core\Datamodel\Schema](core_datamodel_schema.md)** - &lt;p&gt;Schema to match&lt;/p&gt;



### dropTable

    boolean Core\Datamodel\BackendInterface::dropTable($table)

Drop a table from the system.



* Visibility: **public**
* This method is defined by [Core\Datamodel\BackendInterface](core_datamodel_backendinterface.md)


#### Arguments
* $table **mixed**



### showTables

    array Core\Datamodel\BackendInterface::showTables()

Get a flat array of table names currently available on this backend.



* Visibility: **public**
* This method is defined by [Core\Datamodel\BackendInterface](core_datamodel_backendinterface.md)




### _getTables

    array Core\Datamodel\Drivers\mysqli\mysqli_backend::_getTables()





* Visibility: **public**




### _describeTableSchema

    \Core\Datamodel\Drivers\mysqli\MySQLi_Schema Core\Datamodel\Drivers\mysqli\mysqli_backend::_describeTableSchema(string $table)

Alias of describeTable



* Visibility: **public**


#### Arguments
* $table **string**



### _describeTableIndexes

    \Core\Datamodel\Drivers\mysqli\MySQLi_Schema Core\Datamodel\Drivers\mysqli\mysqli_backend::_describeTableIndexes($table)

Alias of describeTable

Now that they're combined, there's no need to keep them separate.

* Visibility: **public**


#### Arguments
* $table **mixed**



### _rawExecute

    mixed Core\Datamodel\Drivers\mysqli\mysqli_backend::_rawExecute(string $type, string $string)

Execute a raw query

Returns FALSE on failure. For successful SELECT, SHOW, DESCRIBE or
EXPLAIN queries mysqli_query() will return a result object. For other
successful queries mysqli_query() will return TRUE.

* Visibility: **public**


#### Arguments
* $type **string** - &lt;p&gt;Either read or write.&lt;/p&gt;
* $string **string** - &lt;p&gt;The string to execute&lt;/p&gt;



### describeTable

    \Core\Datamodel\Schema Core\Datamodel\BackendInterface::describeTable(string $table)

Describe the schema of a given table



* Visibility: **public**
* This method is defined by [Core\Datamodel\BackendInterface](core_datamodel_backendinterface.md)


#### Arguments
* $table **string** - &lt;p&gt;Table name to query&lt;/p&gt;



### _executeGet

    mixed Core\Datamodel\Drivers\mysqli\mysqli_backend::_executeGet(\Core\Datamodel\Dataset $dataset)

Parse and execute a GET/SELECT statement



* Visibility: **private**


#### Arguments
* $dataset **[Core\Datamodel\Dataset](core_datamodel_dataset.md)**



### _executeInsert

    mixed Core\Datamodel\Drivers\mysqli\mysqli_backend::_executeInsert(\Core\Datamodel\Dataset $dataset)

Parse and execute an INSERT statement



* Visibility: **private**


#### Arguments
* $dataset **[Core\Datamodel\Dataset](core_datamodel_dataset.md)**



### _executeUpdate

    mixed Core\Datamodel\Drivers\mysqli\mysqli_backend::_executeUpdate(\Core\Datamodel\Dataset $dataset)

Parse and execute an UPDATE statement



* Visibility: **private**


#### Arguments
* $dataset **[Core\Datamodel\Dataset](core_datamodel_dataset.md)**



### _executeDelete

    mixed Core\Datamodel\Drivers\mysqli\mysqli_backend::_executeDelete(\Core\Datamodel\Dataset $dataset)

Parse and execute a DELETE statement



* Visibility: **private**


#### Arguments
* $dataset **[Core\Datamodel\Dataset](core_datamodel_dataset.md)**



### _executeCount

    mixed Core\Datamodel\Drivers\mysqli\mysqli_backend::_executeCount(\Core\Datamodel\Dataset $dataset)

Parse and execute a count on a given table with a given where clause



* Visibility: **private**


#### Arguments
* $dataset **[Core\Datamodel\Dataset](core_datamodel_dataset.md)**



### _executeAlter

    mixed Core\Datamodel\Drivers\mysqli\mysqli_backend::_executeAlter(\Core\Datamodel\Dataset $dataset)

Process and execute an ALTER statement



* Visibility: **private**


#### Arguments
* $dataset **[Core\Datamodel\Dataset](core_datamodel_dataset.md)**



### _parseWhere

    string Core\Datamodel\Drivers\mysqli\mysqli_backend::_parseWhere(\Core\Datamodel\Dataset $dataset)

Parse the where clause of a given dataset.

This is abstracted away because it's common functionality between SELECT, UPDATE and DELETE.

This method ONLY parses the WHERE clause and returns a valid SQL snippet.
If no where clauses are found, a blank string is returned.

* Visibility: **private**


#### Arguments
* $dataset **[Core\Datamodel\Dataset](core_datamodel_dataset.md)**



### _parseWhereClause

    string Core\Datamodel\Drivers\mysqli\mysqli_backend::_parseWhereClause(\Core\Datamodel\DatasetWhereClause $group)

The recursive function that will return the actual SQL string from a group.



* Visibility: **private**


#### Arguments
* $group **[Core\Datamodel\DatasetWhereClause](core_datamodel_datasetwhereclause.md)**



### _getColumnString

    string Core\Datamodel\Drivers\mysqli\mysqli_backend::_getColumnString(\Core\Datamodel\Columns\SchemaColumn $column)

Parse a column and get its mysql definition string.

Useful for create table and modify table routines.

* Visibility: **private**


#### Arguments
* $column **[Core\Datamodel\Columns\SchemaColumn](core_datamodel_columns_schemacolumn.md)**



### _schemasDiffer

    boolean Core\Datamodel\Drivers\mysqli\mysqli_backend::_schemasDiffer(\Core\Datamodel\Schema $oldSchema, \Core\Datamodel\Schema $newSchema)

Simple method to return if two schemas are different



* Visibility: **private**


#### Arguments
* $oldSchema **[Core\Datamodel\Schema](core_datamodel_schema.md)**
* $newSchema **[Core\Datamodel\Schema](core_datamodel_schema.md)**



### ProcessSQLFile

    array Core\Datamodel\Drivers\mysqli\mysqli_backend::ProcessSQLFile($file)

Process an SQL file and return an array of generic dataset objects.

<h3>Usage Examples</h3>


<h4>Example 1</h4>
<p>Standard Usage</p>
<code>
// Some code for example 1
$file = ROOT_PDIR . 'components/foo/upgrades/000-do-something-awesome.sql';
$records = mysqli_backend::ProcessSQLFile($file);
foreach($records as $rec){
    $rec->execute();
}
</code>

* Visibility: **public**
* This method is **static**.


#### Arguments
* $file **mixed**



### ProcessSQLStatement

    array Core\Datamodel\Drivers\mysqli\mysqli_backend::ProcessSQLStatement(string $rawstatement)

Convert a raw SQL statement to a generic dataset, (if possible).



* Visibility: **public**
* This method is **static**.


#### Arguments
* $rawstatement **string**


