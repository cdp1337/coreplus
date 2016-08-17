Core\Datamodel\Schema
===============

A short teaser of what DMISchema does.

More lengthy description of what DMISchema does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: Schema
* Namespace: Core\Datamodel





Properties
----------


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


### getColumn

    \Core\Datamodel\Columns\SchemaColumn|null Core\Datamodel\Schema::getColumn(string|integer $column)

Get a column by order (int) or name



* Visibility: **public**


#### Arguments
* $column **string|integer**



### getDiff

    array Core\Datamodel\Schema::getDiff(\Core\Datamodel\Schema $schema)

Get an array of differences between this schema and another schema.



* Visibility: **public**


#### Arguments
* $schema **[Core\Datamodel\Schema](core_datamodel_schema.md)**



### isDataIdentical

    boolean Core\Datamodel\Schema::isDataIdentical(\Core\Datamodel\Schema $schema)

Test if this schema is identical (from a datastore perspective) to another model schema.

Useful for reinstallations.

* Visibility: **public**


#### Arguments
* $schema **[Core\Datamodel\Schema](core_datamodel_schema.md)**


