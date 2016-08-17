Core\Datamodel\Columns\SchemaColumn_text
===============






* Class name: SchemaColumn_text
* Namespace: Core\Datamodel\Columns
* Parent class: [Core\Datamodel\Columns\SchemaColumn](core_datamodel_columns_schemacolumn.md)





Properties
----------


### $field

    public string $field

The field name or key name of this column



* Visibility: **public**


### $type

    public string $type = '__UNDEFINED__'

Specifies the data type contained in this column.  Must be one of the \Model::ATT_TYPE_* fields.



* Visibility: **public**


### $required

    public boolean $required = false

Set to true to disallow blank values



* Visibility: **public**


### $maxlength

    public boolean $maxlength = false

Maximum length in characters (or bytes), of data stored.



* Visibility: **public**


### $options

    public null $options = null

ATT_TYPE_ENUM column types expect a set of values.  This is defined here as an array.



* Visibility: **public**


### $default

    public boolean $default = false

Default value to use for this column



* Visibility: **public**


### $null

    public boolean $null = false

Allow null values for this column.  If set to true, null is preserved as null.  False will change null values to blank.



* Visibility: **public**


### $comment

    public string $comment = ''

Comment to add onto the database column.  Useful for administrative comments for.



* Visibility: **public**


### $precision

    public null $precision = null

ATT_TYPE_FLOAT supports precision for its data.  Should be set as a string such as "6,2" for 6 digits left of decimal,
2 digits right of decimal.



* Visibility: **public**


### $encrypted

    public boolean $encrypted = false

Core+ allows data to be encrypted / decrypted on-the-fly.  This is useful for sensitive information such as
credit card data or authorization credentials for external sources.  Setting this to true will store all
information as encrypted, and allow it to be read decrypted.



* Visibility: **public**


### $autoinc

    public boolean $autoinc = false

Indicator if this column needs to be auto incremented from the datamodel.



* Visibility: **public**


### $encoding

    public string $encoding = null

The default encoding of this schema column.



* Visibility: **public**


### $aliasof

    public null $aliasof = null





* Visibility: **public**


### $valueDB

    public mixed $valueDB = null





* Visibility: **public**


### $valueTranslated

    public mixed $valueTranslated = null





* Visibility: **public**


### $value

    public mixed $value = null





* Visibility: **public**


### $parent

    public null $parent = null





* Visibility: **public**


### $formAttributes

    public array $formAttributes = array()





* Visibility: **public**


Methods
-------


### __construct

    mixed Core\Datamodel\Columns\SchemaColumn_text::__construct()





* Visibility: **public**




### setValueFromApp

    mixed Core\Datamodel\Columns\SchemaColumn::setValueFromApp(mixed $val)

Set the value from the application/userspace for this column

Handles all translations and conversions as necessary.

* Visibility: **public**
* This method is defined by [Core\Datamodel\Columns\SchemaColumn](core_datamodel_columns_schemacolumn.md)


#### Arguments
* $val **mixed**



### changed

    boolean Core\Datamodel\Columns\SchemaColumn::changed()

Check if this value has changed between the database and working copy.



* Visibility: **public**
* This method is defined by [Core\Datamodel\Columns\SchemaColumn](core_datamodel_columns_schemacolumn.md)




### isIdenticalTo

    boolean Core\Datamodel\Columns\SchemaColumn::isIdenticalTo(\Core\Datamodel\Columns\SchemaColumn $col)

Check to see if this column is datastore identical to another column.



* Visibility: **public**
* This method is defined by [Core\Datamodel\Columns\SchemaColumn](core_datamodel_columns_schemacolumn.md)


#### Arguments
* $col **[Core\Datamodel\Columns\SchemaColumn](core_datamodel_columns_schemacolumn.md)**



### getDiff

    null|string Core\Datamodel\Columns\SchemaColumn::getDiff(\Core\Datamodel\Columns\SchemaColumn $col)

Get the actual differences between this schema and another column.

Will return null if there are no differences.

* Visibility: **public**
* This method is defined by [Core\Datamodel\Columns\SchemaColumn](core_datamodel_columns_schemacolumn.md)


#### Arguments
* $col **[Core\Datamodel\Columns\SchemaColumn](core_datamodel_columns_schemacolumn.md)**



### getInsertValue

    string Core\Datamodel\Columns\SchemaColumn::getInsertValue()

Get the value appropriate for INSERT statements.



* Visibility: **public**
* This method is defined by [Core\Datamodel\Columns\SchemaColumn](core_datamodel_columns_schemacolumn.md)




### getUpdateValue

    string Core\Datamodel\Columns\SchemaColumn::getUpdateValue()

Get the value appropriate for UPDATE statements.



* Visibility: **public**
* This method is defined by [Core\Datamodel\Columns\SchemaColumn](core_datamodel_columns_schemacolumn.md)




### getFormElementType

    string Core\Datamodel\Columns\SchemaColumn::getFormElementType()

Get the form element type that is the default for this type of field type.



* Visibility: **public**
* This method is defined by [Core\Datamodel\Columns\SchemaColumn](core_datamodel_columns_schemacolumn.md)




### getFormElementAttributes

    array Core\Datamodel\Columns\SchemaColumn::getFormElementAttributes()

Get an array of the form element attributes for this column.



* Visibility: **public**
* This method is defined by [Core\Datamodel\Columns\SchemaColumn](core_datamodel_columns_schemacolumn.md)




### getAsFormElement

    \FormElement|null Core\Datamodel\Columns\SchemaColumn::getAsFormElement()

Get this column value as a valid form element.



* Visibility: **public**
* This method is defined by [Core\Datamodel\Columns\SchemaColumn](core_datamodel_columns_schemacolumn.md)




### commit

    mixed Core\Datamodel\Columns\SchemaColumn::commit()

Simple method to mark this data as committed to the database.

This is expected to be called from the Model's save procedure.

* Visibility: **public**
* This method is defined by [Core\Datamodel\Columns\SchemaColumn](core_datamodel_columns_schemacolumn.md)




### setSchema

    mixed Core\Datamodel\Columns\SchemaColumn::setSchema(array $schema)

Load rendered schema data, usually from a Model declaration, for this column.



* Visibility: **public**
* This method is defined by [Core\Datamodel\Columns\SchemaColumn](core_datamodel_columns_schemacolumn.md)


#### Arguments
* $schema **array**



### setValueFromDB

    mixed Core\Datamodel\Columns\SchemaColumn::setValueFromDB(mixed $val)

Set the value from the database for this column

Handles all translations and conversions as necessary.

* Visibility: **public**
* This method is defined by [Core\Datamodel\Columns\SchemaColumn](core_datamodel_columns_schemacolumn.md)


#### Arguments
* $val **mixed**



### Factory

    \Core\Datamodel\Columns\SchemaColumn Core\Datamodel\Columns\SchemaColumn::Factory(string $type)





* Visibility: **public**
* This method is **static**.
* This method is defined by [Core\Datamodel\Columns\SchemaColumn](core_datamodel_columns_schemacolumn.md)


#### Arguments
* $type **string**



### FactoryFromSchema

    mixed Core\Datamodel\Columns\SchemaColumn::FactoryFromSchema($schema)





* Visibility: **public**
* This method is **static**.
* This method is defined by [Core\Datamodel\Columns\SchemaColumn](core_datamodel_columns_schemacolumn.md)


#### Arguments
* $schema **mixed**


