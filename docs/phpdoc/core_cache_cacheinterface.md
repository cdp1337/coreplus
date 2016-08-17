Core\Cache\CacheInterface
===============

Interface: CacheInterface
	Defines the methods that all implementing classes MUST have. Covers CRUD
(create, read, update, delete) methods, as well as others that are used in
the base CacheCore class.




* Interface name: CacheInterface
* Namespace: Core\Cache
* This is an **interface**






Methods
-------


### __construct

    mixed Core\Cache\CacheInterface::__construct(string $key, integer $expires)

Construct a new cache object with the given key and the given expiration.



* Visibility: **public**


#### Arguments
* $key **string**
* $expires **integer**



### create

    boolean Core\Cache\CacheInterface::create(mixed $data)

Method: create()
Creates a new cache. Placeholder method should be defined by the
implementing class.



* Visibility: **public**


#### Arguments
* $data **mixed** - &lt;p&gt;The data to cache.&lt;/p&gt;



### read

    mixed Core\Cache\CacheInterface::read()

Method: read()
Reads a cache. Placeholder method should be defined by the implementing class.



* Visibility: **public**




### update

    boolean Core\Cache\CacheInterface::update(mixed $data)

Method: update()
Updates an existing cache. Placeholder method should be defined by the implementing class.



* Visibility: **public**


#### Arguments
* $data **mixed** - &lt;p&gt;The data to cache.&lt;/p&gt;



### delete

    boolean Core\Cache\CacheInterface::delete()

Method: delete()
Deletes a cache. Placeholder method should be defined by the implementing class.



* Visibility: **public**




### flush

    boolean Core\Cache\CacheInterface::flush()

Method: flush()
 Invalidate all items in the cache



* Visibility: **public**



