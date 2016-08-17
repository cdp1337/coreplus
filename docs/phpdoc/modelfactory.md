ModelFactory
===============

Factory utility for models.

This class provides an interface for searching for and counting models.  Generally, there are shortcut functions
available on the Model class that utilize this class, namely Count, Find, and FindRaw.


* Class name: ModelFactory
* Namespace: 





Properties
----------


### $interface

    public \DMI_Backend $interface = null

Which DataModelInterface should this model execute its operations with.

99.9% of the time, it's fine to leave this as null, which will use the
system DMI.  If however you want to utilize a Model with Memcache,
(say for session information), it can be useful.

* Visibility: **public**


### $_model

    private string $_model

What model is this a factory of?



* Visibility: **private**


### $_dataset

    private \Core\Datamodel\Dataset $_dataset

Contains the dataset object for this search.



* Visibility: **private**


### $_stream

    private \Core\Datamodel\DatasetStream $_stream





* Visibility: **private**


Methods
-------


### __construct

    mixed ModelFactory::__construct(string $model)





* Visibility: **public**


#### Arguments
* $model **string**



### where

    mixed ModelFactory::where()

Where clause for the search, passed directly to the dataset object.



* Visibility: **public**




### whereGroup

    mixed ModelFactory::whereGroup()





* Visibility: **public**




### order

    mixed ModelFactory::order()





* Visibility: **public**




### limit

    mixed ModelFactory::limit()





* Visibility: **public**




### get

    array|null|\Model ModelFactory::get()

Get the result or results from this factory.

If limit is set to 1, either a Model or null is returned.
Else, an array of Models is returned, be it populated or empty.

* Visibility: **public**




### getRaw

    array ModelFactory::getRaw()

Get the results from this factory as a raw associative array.



* Visibility: **public**




### getNext

    \Model|null ModelFactory::getNext()

Similar to get(), but only one model at a time is rendered and returned.

This is ideal for large datasets and limited amounts of memory.

When the end of the stream is hit, null is returned.

* Visibility: **public**




### count

    integer ModelFactory::count()

Get a count of how many records are in this factory
(without counting the records one by one)



* Visibility: **public**




### getDataset

    \Core\Datamodel\Dataset ModelFactory::getDataset()

Get the raw dataset object for this factory.

This can sometimes be useful for advanced manipulations of the low-level object.

* Visibility: **public**




### _performMultisiteCheck

    mixed ModelFactory::_performMultisiteCheck()

Internal function to do the multisite check on the model.

If the model supports a site attribute and none requested, then set it to the current site.

* Visibility: **private**




### GetSchema

    \ModelSchema ModelFactory::GetSchema($model)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $model **mixed**


