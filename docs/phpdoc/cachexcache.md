CacheXCache
===============

Class: CacheXCache
	Container for all XCache-based cache methods. Inherits additional methods from CacheCore. Adheres to the ICacheCore interface.




* Class name: CacheXCache
* Namespace: 
* Parent class: CacheCore
* This class implements: ICacheCore






Methods
-------


### __construct

    mixed CacheXCache::__construct($name, $location, $expires, $gzip)

Method: __construct()
	The constructor



* Visibility: **public**


#### Arguments
* $name **mixed** - &lt;ul&gt;
&lt;li&gt;&lt;em&gt;string&lt;/em&gt; (Required) A name to uniquely identify the cache object.&lt;/li&gt;
&lt;/ul&gt;
* $location **mixed** - &lt;ul&gt;
&lt;li&gt;&lt;em&gt;string&lt;/em&gt; (Required) The location to store the cache object in. This may vary by cache method.&lt;/li&gt;
&lt;/ul&gt;
* $expires **mixed** - &lt;ul&gt;
&lt;li&gt;&lt;em&gt;integer&lt;/em&gt; (Required) The number of seconds until a cache object is considered stale.&lt;/li&gt;
&lt;/ul&gt;
* $gzip **mixed** - &lt;ul&gt;
&lt;li&gt;&lt;em&gt;boolean&lt;/em&gt; (Optional) Whether data should be gzipped before being stored. Defaults to true.&lt;/li&gt;
&lt;/ul&gt;



### create

    mixed CacheXCache::create($data)

Method: create()
	Creates a new cache.



* Visibility: **public**


#### Arguments
* $data **mixed** - &lt;p&gt;mixed (Required) The data to cache.&lt;/p&gt;



### read

    mixed CacheXCache::read()

Method: read()
	Reads a cache.



* Visibility: **public**




### update

    mixed CacheXCache::update($data)

Method: update()
	Updates an existing cache.



* Visibility: **public**


#### Arguments
* $data **mixed** - &lt;p&gt;mixed (Required) The data to cache.&lt;/p&gt;



### delete

    mixed CacheXCache::delete()

Method: delete()
	Deletes a cache.



* Visibility: **public**




### is_expired

    mixed CacheXCache::is_expired()

Method: is_expired()
	Defined here, but always returns false. XCache manages it's own expirations. It's worth
 mentioning that if the server is configured for a long xcache.var_gc_interval then it IS
 possible for expired data to remain in the var cache, though it is not possible to access
 it.



* Visibility: **public**




### timestamp

    mixed CacheXCache::timestamp()

Method: timestamp()
	Implemented here, but always returns false. XCache manages it's own expirations.



* Visibility: **public**




### reset

    mixed CacheXCache::reset()

Method: reset()
	Implemented here, but always returns false. XCache manages it's own expirations.



* Visibility: **public**




### flush

    boolean CacheXCache::flush()

Method: flush()
 Invalidate all items in the cache



* Visibility: **public**



