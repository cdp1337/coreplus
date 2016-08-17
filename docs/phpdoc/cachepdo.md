CachePDO
===============

Class: CachePDO
Container for all PDO-based cache methods. Inherits additional methods from
CacheCore. Adheres to the ICacheCore interface.




* Class name: CachePDO
* Namespace: 
* Parent class: CacheCore
* This class implements: ICacheCore




Properties
----------


### $pdo

    public mixed $pdo = null

Property: pdo
	Reference to the PDO connection object.



* Visibility: **public**


### $dsn

    public mixed $dsn = null

Property: dsn
	Holds the parsed URL components.



* Visibility: **public**


### $dsn_string

    public mixed $dsn_string = null

Property: dsn_string
	Holds the PDO-friendly version of the connection string.



* Visibility: **public**


### $create

    public mixed $create = null

Property: create
	Holds the prepared statement for creating an entry.



* Visibility: **public**


### $read

    public mixed $read = null

Property: read
	Holds the prepared statement for reading an entry.



* Visibility: **public**


### $update

    public mixed $update = null

Property: update
	Holds the prepared statement for updating an entry.



* Visibility: **public**


### $reset

    public mixed $reset = null

Property: reset
	Holds the prepared statement for resetting the expiry of an entry.



* Visibility: **public**


### $delete

    public mixed $delete = null

Property: delete
	Holds the prepared statement for deleting an entry.



* Visibility: **public**


### $store_read

    public mixed $store_read = null

Property: store_read
	Holds the response of the read so we only need to fetch it once instead of doing multiple queries.



* Visibility: **public**


Methods
-------


### __construct

    mixed CachePDO::__construct($name, $location, $expires, $gzip)

Method: __construct()
	The constructor.

Tested with MySQL 5.0.x (http://mysql.com), PostgreSQL (http://postgresql.com), and SQLite 3.x (http://sqlite.org).
	SQLite 2.x is assumed to work. No other PDO-supported databases have been tested (e.g. Oracle, Microsoft SQL Server,
	IBM DB2, ODBC, Sybase, Firebird). Feel free to send patches for additional database support.

	See <http://php.net/pdo> for more information.

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

    mixed CachePDO::create($data)

Method: create()
	Creates a new cache.



* Visibility: **public**


#### Arguments
* $data **mixed** - &lt;p&gt;mixed (Required) The data to cache.&lt;/p&gt;



### read

    mixed CachePDO::read()

Method: read()
	Reads a cache.



* Visibility: **public**




### update

    mixed CachePDO::update($data)

Method: update()
	Updates an existing cache.



* Visibility: **public**


#### Arguments
* $data **mixed** - &lt;p&gt;mixed (Required) The data to cache.&lt;/p&gt;



### delete

    mixed CachePDO::delete()

Method: delete()
	Deletes a cache.



* Visibility: **public**




### timestamp

    mixed CachePDO::timestamp()

Method: timestamp()
	Retrieves the timestamp of the cache.



* Visibility: **public**




### reset

    mixed CachePDO::reset()

Method: reset()
	Resets the freshness of the cache.



* Visibility: **public**




### is_expired

    mixed CachePDO::is_expired()

Method: is_expired()
	Checks whether the cache object is expired or not.



* Visibility: **public**




### get_drivers

    mixed CachePDO::get_drivers()

Method: get_drivers()
	Returns a list of supported PDO database drivers. Identical to PDO::getAvailableDrivers().



* Visibility: **public**




### flush

    boolean CachePDO::flush()

Method: flush()
 Invalidate all items in the cache



* Visibility: **public**




### generate_timestamp

    mixed CachePDO::generate_timestamp()

Method: generate_timestamp()
	Returns a timestamp value apropriate to the current database type.



* Visibility: **private**



