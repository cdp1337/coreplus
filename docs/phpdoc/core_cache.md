Core\Cache
===============

Class Cache
This is the main public interface that will be used by external scripts.




* Class name: Cache
* Namespace: Core





Properties
----------


### $_KeyCache

    private array $_KeyCache = array()

Cache of keyed data used for get/set.

Instead of calling a new cache object every time and loading its data.... every time
this will contain a cache of the key's data.

* Visibility: **private**
* This property is **static**.


### $_Backend

    private null $_Backend = null





* Visibility: **private**
* This property is **static**.


Methods
-------


### Get

    mixed Core\Cache::Get(string $key, integer $expires)

Get a cached value based on its key



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **string**
* $expires **integer**



### Set

    boolean Core\Cache::Set(string $key, mixed $value, integer $expires)

Set a cached value on a given key



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **string**
* $value **mixed**
* $expires **integer**



### Delete

    boolean Core\Cache::Delete($key)

Delete a given key from the system cache



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **mixed**



### Flush

    boolean Core\Cache::Flush()

Flush the entire system cache



* Visibility: **public**
* This method is **static**.




### _Factory

    \Core\Cache\CacheInterface Core\Cache::_Factory($key, integer $expires)





* Visibility: **private**
* This method is **static**.


#### Arguments
* $key **mixed**
* $expires **integer**


