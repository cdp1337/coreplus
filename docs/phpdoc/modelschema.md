ModelSchema
===============

A short teaser of what ModelSchema does.

More lengthy description of what ModelSchema does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: ModelSchema
* Namespace: 
* Parent class: [Core\Datamodel\Schema](core_datamodel_schema.md)





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


### __construct

    mixed ModelSchema::__construct($model)





* Visibility: **public**


#### Arguments
* $model **mixed**



### readModel

    mixed ModelSchema::readModel($model)





* Visibility: **public**


#### Arguments
* $model **mixed**



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


