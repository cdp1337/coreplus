ConfigHandler
===============

Core / ConfigHandler class

Core configuration handling class;
 handles getting and setting config values from the database and XML config files.

The class that handles all configuration getting and setting.
Can handle calls to XML config files and DB configuration `configs` table.


* Class name: ConfigHandler
* Namespace: 
* This class implements: [ISingleton](isingleton.md)




Properties
----------


### $Instance

    private null $Instance = null

The main instance of the config handler.  Used as the backend of all static calls.



* Visibility: **private**
* This property is **static**.


### $_directory

    private string $_directory

The directory of the configuration options, set from the constructor



* Visibility: **private**


### $_cacheFromDB

    private array $_cacheFromDB = array()

Cache of datamodels of the configuration options from the database.



* Visibility: **private**


### $_overrides

    private array $_overrides = array()

Cache of overrides set from other components.  These are available in memory ONLY.



* Visibility: **private**


Methods
-------


### __construct

    \ConfigHandler ConfigHandler::__construct()

Private constructor class to prevent outside instantiation.



* Visibility: **private**




### _loadConfigFile

    boolean ConfigHandler::_loadConfigFile($config)

Load the configuration variables from a requested config file, located inside of the config directory.



* Visibility: **private**


#### Arguments
* $config **mixed** - &lt;p&gt;string&lt;/p&gt;



### _clearCache

    mixed ConfigHandler::_clearCache()





* Visibility: **private**




### _get

    mixed ConfigHandler::_get(string $key)

Get the value for a given configuration key



* Visibility: **private**


#### Arguments
* $key **string**



### _loadDB

    mixed ConfigHandler::_loadDB()





* Visibility: **private**




### Singleton

    \ISingleton ISingleton::Singleton()





* Visibility: **public**
* This method is **static**.
* This method is defined by [ISingleton](isingleton.md)




### GetInstance

    \ConfigHandler ConfigHandler::GetInstance()





* Visibility: **public**
* This method is **static**.




### LoadConfigFile

    boolean ConfigHandler::LoadConfigFile($config)

Load the configuration variables from a requested config file, located inside of the config directory.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $config **mixed** - &lt;p&gt;string&lt;/p&gt;



### GetValue

    string|integer|boolean ConfigHandler::GetValue(string $key)

Retrieve a value for a requested key.

Alias of ConfigHandler::Get()

* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **string**



### GetConfig

    \ConfigModel|null ConfigHandler::GetConfig(string $key, boolean $autocreate)

Get the config model that is attached to the core configuration system.

This is the easiest way to create new config options.

If $autocreate is set to false and the key does not exist, the corresponding model will NOT be created.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **string** - &lt;p&gt;The configuration key to get&lt;/p&gt;
* $autocreate **boolean** - &lt;p&gt;Whether or not to create a model if not found&lt;/p&gt;



### Get

    string|integer|boolean ConfigHandler::Get(string $key)

Get a configuration value.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **string**



### Set

    boolean ConfigHandler::Set(string $key, string $value)

Set a configuration value.

This CANNOT create new configuration keys!
Please use GetConfig() for that.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **string** - &lt;p&gt;The key to set&lt;/p&gt;
* $value **string** - &lt;p&gt;The value to set&lt;/p&gt;



### FindConfigs

    array ConfigHandler::FindConfigs(string $keymatch)

Find config options based on a given string.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $keymatch **string**



### SetOverride

    mixed ConfigHandler::SetOverride($key, $value)

Set a configuration override value.  This is NOT saved in the database or anything, simply available in memory.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **mixed**
* $value **mixed**



### IsOverridden

    boolean ConfigHandler::IsOverridden($key)

See if a given key is overridden via non-config means, such as enterprise options or what not.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **mixed**



### CacheConfig

    mixed ConfigHandler::CacheConfig(\ConfigModel $config)

Add a config model to the system cache.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $config **[ConfigModel](configmodel.md)**



### _DBReadyHook

    mixed ConfigHandler::_DBReadyHook()





* Visibility: **public**
* This method is **static**.




### var_dump_cache

    mixed ConfigHandler::var_dump_cache()





* Visibility: **public**
* This method is **static**.



