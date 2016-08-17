DMI_Query_Exception
===============






* Class name: DMI_Query_Exception
* Namespace: 
* Parent class: [DMI_Exception](dmi_exception.md)



Constants
----------


### ERRNO_NODATASET

    const ERRNO_NODATASET = '42S02'





### ERRNO_UNKNOWN

    const ERRNO_UNKNOWN = '07000'





Properties
----------


### $query

    public string $query = null

The query that caused the exception.



* Visibility: **public**


### $ansicode

    public mixed $ansicode





* Visibility: **public**


Methods
-------


### __construct

    mixed DMI_Exception::__construct($message, $code, $previous, $ansicode)





* Visibility: **public**
* This method is defined by [DMI_Exception](dmi_exception.md)


#### Arguments
* $message **mixed**
* $code **mixed**
* $previous **mixed**
* $ansicode **mixed**


