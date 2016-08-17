ComponentFactory
===============

[PAGE DESCRIPTION HERE]




* Class name: ComponentFactory
* Namespace: 
* This is an **abstract** class





Properties
----------


### $_DBCache

    private array $_DBCache = null

Cache of versions in the database already.  Useful for reducing the number of lookups.



* Visibility: **private**
* This property is **static**.


Methods
-------


### _LookupComponentData

    array ComponentFactory::_LookupComponentData(string $componentname)

Internal function to lookup the saved data for a given component based on its name.

Will return null if it doesn't exist or an array.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $componentname **string** - &lt;p&gt;The name of the component to lookup&lt;/p&gt;



### Load

    \Component_2_1 ComponentFactory::Load(string $filename)

Load a Component of the appropriate version based on the XML file.

Will return either a Component if API 0.1, or a Component_2_1 if API 2.1

* Visibility: **public**
* This method is **static**.


#### Arguments
* $filename **string**



### ResolveNameToFile

    string ComponentFactory::ResolveNameToFile(string $name)

Resolve a component's name to its XML file, NOT fully resolved.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $name **string**


