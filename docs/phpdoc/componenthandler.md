ComponentHandler
===============

Basically, the Component Handler is the previous library handler, only completed!




* Class name: ComponentHandler
* Namespace: 
* This class implements: [ISingleton](isingleton.md)




Properties
----------


### $instance

    private \ComponentHandler $instance = null

The instance of this object.



* Visibility: **private**
* This property is **static**.


### $_componentCache

    private array $_componentCache = array()

A list of every valid component on the system.



* Visibility: **private**


### $_classes

    private array $_classes = array()

List of every installed class and its location on the system.



* Visibility: **private**


### $_widgets

    private array $_widgets = array()

List of widgets available on the system.



* Visibility: **private**


### $_viewClasses

    private array $_viewClasses = array()

List of every installed view class and its location on the system.



* Visibility: **private**


### $_scriptlibraries

    private array $_scriptlibraries = array()

List of every available jslibrary and its call.

.

* Visibility: **private**


### $_loaded

    private boolean $_loaded = false

Internal check variable to know if this handler has been loaded.



* Visibility: **private**


### $_loadedComponents

    private array $_loadedComponents = array()

Every component that has been loaded into the system.



* Visibility: **private**


### $_viewSearchDirs

    private mixed $_viewSearchDirs = array()





* Visibility: **private**


### $_dbcache

    public array $_dbcache = array()

key/value array of records in the database.

Used as a lookup so the components only have to be queried once.

* Visibility: **public**


Methods
-------


### __construct

    void ComponentHandler::__construct()

Private constructor class to prevent outside instantiation.



* Visibility: **private**




### load

    mixed ComponentHandler::load()





* Visibility: **private**




### _registerComponent

    mixed ComponentHandler::_registerComponent($c)

Internally used method to notify the rest of the system that a given
   component has been loaded and is available.

Expects all checks to be done already.

* Visibility: **public**


#### Arguments
* $c **mixed**



### Singleton

    \ISingleton ISingleton::Singleton()





* Visibility: **public**
* This method is **static**.
* This method is defined by [ISingleton](isingleton.md)




### GetInstance

    \ComponentHandler ComponentHandler::GetInstance()

Alias of Singleton.



* Visibility: **public**
* This method is **static**.




### GetComponent

    \Component ComponentHandler::GetComponent(string $componentName)

Get the component object of a requested component.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $componentName **string**



### IsLibraryAvailable

    mixed ComponentHandler::IsLibraryAvailable($name, $version, $operation)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $name **mixed**
* $version **mixed**
* $operation **mixed**



### IsJSLibraryAvailable

    mixed ComponentHandler::IsJSLibraryAvailable($name, $version, $operation)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $name **mixed**
* $version **mixed**
* $operation **mixed**



### GetJSLibrary

    mixed ComponentHandler::GetJSLibrary($library)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $library **mixed**



### LoadScriptLibrary

    mixed ComponentHandler::LoadScriptLibrary($library)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $library **mixed**



### IsComponentAvailable

    mixed ComponentHandler::IsComponentAvailable($name, $version, $operation)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $name **mixed**
* $version **mixed**
* $operation **mixed**



### IsViewClassAvailable

    mixed ComponentHandler::IsViewClassAvailable($name, $casesensitive)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $name **mixed**
* $casesensitive **mixed**



### CheckClass

    void ComponentHandler::CheckClass(string $classname)

Simple autoload register function to lookup a classname and resolve it.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $classname **string**



### IsClassAvailable

    boolean ComponentHandler::IsClassAvailable($classname)

Just check if the class is available, do not load it.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $classname **mixed**



### GetAllComponents

    mixed ComponentHandler::GetAllComponents()





* Visibility: **public**
* This method is **static**.




### GetLoadedComponents

    mixed ComponentHandler::GetLoadedComponents()





* Visibility: **public**
* This method is **static**.




### GetLoadedClasses

    array ComponentHandler::GetLoadedClasses()

Get an array of every loaded class in the system.

Each key is the class name (lowercase), and the value is the fully resolved path

* Visibility: **public**
* This method is **static**.




### GetLoadedWidgets

    array ComponentHandler::GetLoadedWidgets()

Get every loaded widget in the system.



* Visibility: **public**
* This method is **static**.




### GetLoadedViewClasses

    mixed ComponentHandler::GetLoadedViewClasses()





* Visibility: **public**
* This method is **static**.




### GetLoadedLibraries

    array ComponentHandler::GetLoadedLibraries()

Get all the loaded libraries and their versions.



* Visibility: **public**
* This method is **static**.



