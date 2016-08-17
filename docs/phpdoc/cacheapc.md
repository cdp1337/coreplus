CacheAPC
===============

Class: CacheAPC
	Container for all APC-based cache methods. Inherits additional methods from CacheCore. Adheres to the ICacheCore interface.




* Class name: CacheAPC
* Namespace: 
* Parent class: CacheCore
* This class implements: ICacheCore






Methods
-------


### __construct

    mixed CacheAPC::__construct($name, $location, $expires, $gzip)

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

    mixed CacheAPC::create($data)

Method: create()
	Creates a new cache.



* Visibility: **public**


#### Arguments
* $data **mixed** - &lt;p&gt;mixed (Required) The data to cache.&lt;/p&gt;



### read

    mixed CacheAPC::read()

Method: read()
	Reads a cache.



* Visibility: **public**




### update

    mixed CacheAPC::update($data)

Method: update()
	Updates an existing cache.



* Visibility: **public**


#### Arguments
* $data **mixed** - &lt;p&gt;mixed (Required) The data to cache.&lt;/p&gt;



### delete

    mixed CacheAPC::delete()

Method: delete()
	Deletes a cache.



* Visibility: **public**




### is_expired

    mixed CacheAPC::is_expired()

Method: is_expired()
	Implemented here, but always returns false. APC manages it's own expirations.



* Visibility: **public**




### timestamp

    mixed CacheAPC::timestamp()

Method: timestamp()
	Implemented here, but always returns false. APC manages it's own expirations.



* Visibility: **public**




### reset

    mixed CacheAPC::reset()

Method: reset()
	Implemented here, but always returns false. APC manages it's own expirations.



* Visibility: **public**




### flush

    boolean CacheAPC::flush()

Method: flush()
 Invalidate all items in the cache



* Visibility: **public**



