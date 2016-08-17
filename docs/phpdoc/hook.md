Hook
===============

The actual hook object that will have the events attached to it.

Also allows for extra information


* Class name: Hook
* Namespace: 



Constants
----------


### RETURN_TYPE_BOOL

    const RETURN_TYPE_BOOL = 'bool'





### RETURN_TYPE_VOID

    const RETURN_TYPE_VOID = 'void'





### RETURN_TYPE_ARRAY

    const RETURN_TYPE_ARRAY = 'array'





### RETURN_TYPE_STRING

    const RETURN_TYPE_STRING = 'string'





Properties
----------


### $name

    public string $name

The name of this hook.  MUST be system unique.



* Visibility: **public**


### $description

    public mixed $description





* Visibility: **public**


### $returnType

    public string $returnType = self::RETURN_TYPE_BOOL

The return type of this hook, MUST be one of the RETURN_TYPE_* strings.



* Visibility: **public**


### $_bindings

    private array $_bindings = array()

An array of bound function/methods to call when this event is dispatched.



* Visibility: **private**


Methods
-------


### __construct

    mixed Hook::__construct(string $name)

Instantiate a new generic hook object and register it with the global HookHandler.



* Visibility: **public**


#### Arguments
* $name **string**



### attach

    mixed Hook::attach($function)





* Visibility: **public**


#### Arguments
* $function **mixed**



### dispatch

    boolean Hook::dispatch(mixed $args)

Dispatch the event, calling any bound functions.



* Visibility: **public**


#### Arguments
* $args **mixed**



### callBinding

    null|boolean|array Hook::callBinding($call, $args)

Actually execute a binding call and get back the result.



* Visibility: **public**


#### Arguments
* $call **mixed**
* $args **mixed**



### __toString

    mixed Hook::__toString()





* Visibility: **public**




### getName

    mixed Hook::getName()





* Visibility: **public**




### getBindingCount

    mixed Hook::getBindingCount()





* Visibility: **public**




### getBindings

    array Hook::getBindings()

Get the array of bindings currently attached to this hook



* Visibility: **public**



