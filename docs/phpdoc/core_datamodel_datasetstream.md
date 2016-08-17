Core\Datamodel\DatasetStream
===============

A wrapper around the dataset system to allow streaming large numbers of records for processing.

To use it, simply pass in a valid Dataset object into the constructor and proceed to use getRecord() at will.


* Class name: DatasetStream
* Namespace: Core\Datamodel





Properties
----------


### $_dataset

    private mixed $_dataset





* Visibility: **private**


### $_totalcount

    private mixed $_totalcount





* Visibility: **private**


### $_counter

    private mixed $_counter = -1





* Visibility: **private**


### $_startlimit

    private mixed $_startlimit





* Visibility: **private**


### $bufferlimit

    public integer $bufferlimit = 100

Total number of records to load into the buffer at a time.



* Visibility: **public**


Methods
-------


### __construct

    mixed Core\Datamodel\DatasetStream::__construct(\Core\Datamodel\Dataset $ds)





* Visibility: **public**


#### Arguments
* $ds **[Core\Datamodel\Dataset](core_datamodel_dataset.md)**



### getRecord

    array|null Core\Datamodel\DatasetStream::getRecord()

Get the next record from the dataset, or null if at the end of the list.



* Visibility: **public**



