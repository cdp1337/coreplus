Core\Cache\Memcache
===============

Class: Memcache




* Class name: Memcache
* Namespace: Core\Cache
* This class implements: [Core\Cache\CacheInterface](core_cache_cacheinterface.md)




Properties
----------


### $_key

    private mixed $_key





* Visibility: **private**


### $_expires

    private mixed $_expires





* Visibility: **private**


### $_gzip

    private mixed $_gzip





* Visibility: **private**


### $_Connection

    private mixed $_Connection





* Visibility: **private**
* This property is **static**.


### $_IsMemcached

    private mixed $_IsMemcached





* Visibility: **private**
* This property is **static**.


Methods
-------


### __construct

    mixed Core\Cache\CacheInterface::__construct(string $key, integer $expires)

Construct a new cache object with the given key and the given expiration.



* Visibility: **public**
* This method is defined by [Core\Cache\CacheInterface](core_cache_cacheinterface.md)


#### Arguments
* $key **string**
* $expires **integer**



### create

    boolean Core\Cache\CacheInterface::create(mixed $data)

Method: create()
Creates a new cache. Placeholder method should be defined by the
implementing class.



* Visibility: **public**
* This method is defined by [Core\Cache\CacheInterface](core_cache_cacheinterface.md)


#### Arguments
* $data **mixed** - &lt;p&gt;The data to cache.&lt;/p&gt;



### read

    mixed Core\Cache\CacheInterface::read()

Method: read()
Reads a cache. Placeholder method should be defined by the implementing class.



* Visibility: **public**
* This method is defined by [Core\Cache\CacheInterface](core_cache_cacheinterface.md)




### update

    boolean Core\Cache\CacheInterface::update(mixed $data)

Method: update()
Updates an existing cache. Placeholder method should be defined by the implementing class.



* Visibility: **public**
* This method is defined by [Core\Cache\CacheInterface](core_cache_cacheinterface.md)


#### Arguments
* $data **mixed** - &lt;p&gt;The data to cache.&lt;/p&gt;



### delete

    boolean Core\Cache\CacheInterface::delete()

Method: delete()
Deletes a cache. Placeholder method should be defined by the implementing class.



* Visibility: **public**
* This method is defined by [Core\Cache\CacheInterface](core_cache_cacheinterface.md)




### flush

    boolean Core\Cache\CacheInterface::flush()

Method: flush()
 Invalidate all items in the cache



* Visibility: **public**
* This method is defined by [Core\Cache\CacheInterface](core_cache_cacheinterface.md)




### is_expired

    mixed Core\Cache\Memcache::is_expired()

Method: is_expired()

Memcache manages it's own expirations.

* Visibility: **private**



