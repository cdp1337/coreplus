Core\Datamodel\DatasetWhereClause
===============

A short teaser of what DatasetWhereClause does.

More lengthy description of what DatasetWhereClause does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: DatasetWhereClause
* Namespace: Core\Datamodel





Properties
----------


### $_separator

    private string $_separator = 'AND'

If multiple statements are contained herein, this is the separator of all statements.



* Visibility: **private**


### $_statements

    private array $_statements = array()

The array of statements (of groups) contained herein



* Visibility: **private**


### $_name

    private string $_name

The name of this group/clause.  Completely meaningless other than external lookups.

(FUTURE FEATURE)

* Visibility: **private**


Methods
-------


### __construct

    mixed Core\Datamodel\DatasetWhereClause::__construct(string $name)





* Visibility: **public**


#### Arguments
* $name **string** - &lt;p&gt;The name of this group/clause.  Completely meaningless other than external lookups.&lt;/p&gt;



### addWhereParts

    mixed Core\Datamodel\DatasetWhereClause::addWhereParts($field, $operation, $value)

Add a where statement by the three components.

Only supports one where at a time, but useful for some of the tricky statements.

* Visibility: **public**


#### Arguments
* $field **mixed**
* $operation **mixed**
* $value **mixed**



### addWhere

    boolean Core\Datamodel\DatasetWhereClause::addWhere($arguments)

Add a where statement to this clause.

DOES NOT SUPPORT addWhere('key', 'value'); format!!!

* Visibility: **public**


#### Arguments
* $arguments **mixed**



### addWhereSub

    mixed Core\Datamodel\DatasetWhereClause::addWhereSub($sep, $arguments)

Shortcut function to add a subgroup to an existing group.



* Visibility: **public**


#### Arguments
* $sep **mixed**
* $arguments **mixed**



### getStatements

    mixed Core\Datamodel\DatasetWhereClause::getStatements()





* Visibility: **public**




### setSeparator

    mixed Core\Datamodel\DatasetWhereClause::setSeparator($sep)





* Visibility: **public**


#### Arguments
* $sep **mixed**



### getSeparator

    mixed Core\Datamodel\DatasetWhereClause::getSeparator()





* Visibility: **public**




### getAsArray

    mixed Core\Datamodel\DatasetWhereClause::getAsArray()

Sometimes you just want a good'ol "flat" representation.



* Visibility: **public**




### findByField

    array Core\Datamodel\DatasetWhereClause::findByField(string $fieldname)

Get any/all statements that have a field set to that which is requested.

Useful for looking up to see if a specific column has been set in a where statement.

* Visibility: **public**


#### Arguments
* $fieldname **string** - &lt;p&gt;The field to search for&lt;/p&gt;


