DMI
===============

A top level interface class for the Data Model Interface.

Provides abstraction for different backends.


* Class name: DMI
* Namespace: 





Properties
----------


### $_backend

    protected \Core\Datamodel\DMI_Backend $_backend = null





* Visibility: **protected**


### $_Interface

    protected \DMI $_Interface = null

This points to the system/global DMI object.



* Visibility: **protected**
* This property is **static**.


Methods
-------


### __construct

    mixed DMI::__construct($backend, $host, $user, $pass, $database)





* Visibility: **public**


#### Arguments
* $backend **mixed**
* $host **mixed**
* $user **mixed**
* $pass **mixed**
* $database **mixed**



### setBackend

    mixed DMI::setBackend($backend)





* Visibility: **public**


#### Arguments
* $backend **mixed**



### connect

    \Core\Datamodel\BackendInterface|null DMI::connect($host, $user, $pass, $database)





* Visibility: **public**


#### Arguments
* $host **mixed**
* $user **mixed**
* $pass **mixed**
* $database **mixed**



### connection

    \Core\Datamodel\BackendInterface DMI::connection()





* Visibility: **public**




### GetSystemDMI

    \DMI DMI::GetSystemDMI()

Get the current system DMI based on configuration values.



* Visibility: **public**
* This method is **static**.



