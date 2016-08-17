Core\Datamodel\BackendInterface
===============






* Interface name: BackendInterface
* Namespace: Core\Datamodel
* This is an **interface**






Methods
-------


### execute

    mixed Core\Datamodel\BackendInterface::execute(\Core\Datamodel\Dataset $dataset)

Execute a given Dataset object on this backend



* Visibility: **public**


#### Arguments
* $dataset **[Core\Datamodel\Dataset](core_datamodel_dataset.md)**



### tableExists

    boolean Core\Datamodel\BackendInterface::tableExists(string $tablename)

Check to see if a given table exists without causing an error.



* Visibility: **public**


#### Arguments
* $tablename **string**



### createTable

    boolean Core\Datamodel\BackendInterface::createTable(string $table, \Core\Datamodel\Schema $schema)

Create a table on this backend with the provided Schema.



* Visibility: **public**


#### Arguments
* $table **string** - &lt;p&gt;Table name to be created&lt;/p&gt;
* $schema **[Core\Datamodel\Schema](core_datamodel_schema.md)** - &lt;p&gt;Schema to create table with&lt;/p&gt;



### modifyTable

    boolean|array Core\Datamodel\BackendInterface::modifyTable(string $table, \Core\Datamodel\Schema $schema)

Modify a table to match a new schema.

This is used to keep the database in sync with the code upon upgrades, installations and reinstalls.

* Visibility: **public**


#### Arguments
* $table **string** - &lt;p&gt;Table name to be created&lt;/p&gt;
* $schema **[Core\Datamodel\Schema](core_datamodel_schema.md)** - &lt;p&gt;Schema to match&lt;/p&gt;



### dropTable

    boolean Core\Datamodel\BackendInterface::dropTable($table)

Drop a table from the system.



* Visibility: **public**


#### Arguments
* $table **mixed**



### describeTable

    \Core\Datamodel\Schema Core\Datamodel\BackendInterface::describeTable(string $table)

Describe the schema of a given table



* Visibility: **public**


#### Arguments
* $table **string** - &lt;p&gt;Table name to query&lt;/p&gt;



### showTables

    array Core\Datamodel\BackendInterface::showTables()

Get a flat array of table names currently available on this backend.



* Visibility: **public**



