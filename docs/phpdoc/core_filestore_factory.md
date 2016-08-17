Core\Filestore\Factory
===============

A short teaser of what Factory does.

More lengthy description of what Factory does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: Factory
* Namespace: Core\Filestore
* This is an **abstract** class





Properties
----------


### $_Files

    protected array $_Files = array()

Array of file objects that have been instantiated to act as a cache.



* Visibility: **protected**
* This property is **static**.


### $_Directories

    protected array $_Directories = array()

Array of file objects that have been instantiated to act as a cache.



* Visibility: **protected**
* This property is **static**.


### $_ResolveCache

    protected array $_ResolveCache = array()

Cache of incoming URIs to the fully resolved version.



* Visibility: **protected**
* This property is **static**.


Methods
-------


### File

    \Core\Filestore\File Core\Filestore\Factory::File($uri)

Static function to act as Factory for the underlying Filestore system.

This will parse the incoming URI and return the appropriate type based on Core settings and filetype.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $uri **mixed**



### Directory

    \Core\Filestore\Directory Core\Filestore\Factory::Directory($uri)

Static function to act as Factory for the underlying Filestore system.

This will parse the incoming URI and return the appropriate type based on Core settings and filetype.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $uri **mixed**



### ResolveAssetFile

    \Core\Filestore\File Core\Filestore\Factory::ResolveAssetFile($filename)

Resolve a name for an asset to an actual file.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $filename **mixed**



### RemoveFromCache

    mixed Core\Filestore\Factory::RemoveFromCache($file)

If a file needs to be removed from cache, (ie it was renamed, deleted, etc)
this method should be called to ensure that a future call doesn't use a corrupt/incorrect file!



* Visibility: **public**
* This method is **static**.


#### Arguments
* $file **mixed** - &lt;p&gt;string|File&lt;/p&gt;


