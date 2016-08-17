HookHandler
===============

[PAGE DESCRIPTION HERE]




* Class name: HookHandler
* Namespace: 
* This class implements: [ISingleton](isingleton.md)




Properties
----------


### $RegisteredHooks

    private mixed $RegisteredHooks = array()





* Visibility: **private**
* This property is **static**.


### $Instance

    private mixed $Instance = null





* Visibility: **private**
* This property is **static**.


### $EarlyRegisteredHooks

    private mixed $EarlyRegisteredHooks = array()





* Visibility: **private**
* This property is **static**.


Methods
-------


### __construct

    mixed HookHandler::__construct()





* Visibility: **private**




### Singleton

    \ISingleton ISingleton::Singleton()





* Visibility: **public**
* This method is **static**.
* This method is defined by [ISingleton](isingleton.md)




### GetInstance

    mixed HookHandler::GetInstance()





* Visibility: **public**
* This method is **static**.




### AttachToHook

    void HookHandler::AttachToHook(string $hookName, string|array $callFunction)

Attach a call onto an existing hook.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $hookName **string** - &lt;p&gt;The name of the hook to bind to.&lt;/p&gt;
* $callFunction **string|array** - &lt;p&gt;The function to call.&lt;/p&gt;



### RegisterHook

    void HookHandler::RegisterHook(\Hook $hook)

Register a hook object with the global HookHandler object.

Allows for abstract calling of the hook.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $hook **[Hook](hook.md)**



### RegisterNewHook

    mixed HookHandler::RegisterNewHook($hookName, $description)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $hookName **mixed**
* $description **mixed**



### DispatchHook

    mixed HookHandler::DispatchHook(string $hookName, mixed $args)

Dispatch an event, optionally passing 1 or more parameters.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $hookName **string** - &lt;p&gt;The name of the hook to dispatch&lt;/p&gt;
* $args **mixed**



### GetAllHooks

    array HookHandler::GetAllHooks()

Simple function to return all hooks currently registered on the system.



* Visibility: **public**
* This method is **static**.




### GetHook

    null|\Hook HookHandler::GetHook($hookname)

Get the hook by name



* Visibility: **public**
* This method is **static**.


#### Arguments
* $hookname **mixed**



### PrintHooks

    mixed HookHandler::PrintHooks()

Just a simple debugging function to print out a list of the currently
registered hooks on the system.



* Visibility: **public**
* This method is **static**.



