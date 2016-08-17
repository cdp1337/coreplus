Core\Datamodel\Drivers\mysqli\mysqli_Schema
===============

A short teaser of what DMISchema does.

More lengthy description of what DMISchema does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: mysqli_Schema
* Namespace: Core\Datamodel\Drivers\mysqli
* Parent class: [Core\Datamodel\Schema](core_datamodel_schema.md)





Properties
----------


### $_backend

    private mixed $_backend





* Visibility: **private**


### $definitions

    public array $definitions = array()





* Visibility: **public**


### $order

    public array $order = array()





* Visibility: **public**


### $indexes

    public array $indexes = array()





* Visibility: **public**


### $aliases

    public array $aliases = array()





* Visibility: **public**


Methods
-------


### __construct

    mixed Core\Datamodel\Drivers\mysqli\mysqli_Schema::__construct(\Core\Datamodel\Drivers\mysqli\mysqli_backend $backend, $table)





* Visibility: **public**


#### Arguments
* $backend **[Core\Datamodel\Drivers\mysqli\mysqli_backend](core_datamodel_drivers_mysqli_mysqli_backend.md)**
* $table **mixed**



### readTable

    mixed Core\Datamodel\Drivers\mysqli\mysqli_Schema::readTable($table)

Read a table and populate this schema's column definitions.

Generally called automatically from the constructor.

* Visibility: **public**


#### Arguments
* $table **mixed**



### _getColumnDefinition

    \Core\Datamodel\Columns\SchemaColumn Core\Datamodel\Drivers\mysqli\mysqli_Schema::_getColumnDefinition(array $def)

Get the resolved column schema from a row returned by the mysql command SHOW FULL COLUMNS.



* Visibility: **private**


#### Arguments
* $def **array**



### getColumn

    \Core\Datamodel\Columns\SchemaColumn|null Core\Datamodel\Schema::getColumn(string|integer $column)

Get a column by order (int) or name



* Visibility: **public**
* This method is defined by [Core\Datamodel\Schema](core_datamodel_schema.md)


#### Arguments
* $column **string|integer**



### getDiff

    array Core\Datamodel\Schema::getDiff(\Core\Datamodel\Schema $schema)

Get an array of differences between this schema and another schema.



* Visibility: **public**
* This method is defined by [Core\Datamodel\Schema](core_datamodel_schema.md)


#### Arguments
* $schema **[Core\Datamodel\Schema](core_datamodel_schema.md)**



### isDataIdentical

    boolean Core\Datamodel\Schema::isDataIdentical(\Core\Datamodel\Schema $schema)

Test if this schema is identical (from a datastore perspective) to another model schema.

Useful for reinstallations.

* Visibility: **public**
* This method is defined by [Core\Datamodel\Schema](core_datamodel_schema.md)


#### Arguments
* $schema **[Core\Datamodel\Schema](core_datamodel_schema.md)**


