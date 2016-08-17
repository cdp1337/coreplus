Core\Cache\File
===============

Class: CacheFile




* Class name: File
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


### $_dir

    private mixed $_dir





* Visibility: **private**


### $_file

    private mixed $_file





* Visibility: **private**


### $_gzip

    private mixed $_gzip





* Visibility: **private**


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

    mixed Core\Cache\File::is_expired()

Method: is_expired()
	Checks whether the cache object is expired or not.



* Visibility: **private**



