Core\Filestore\Directory
===============

Interface Directory




* Interface name: Directory
* Namespace: Core\Filestore
* This is an **interface**






Methods
-------


### ls

    array Core\Filestore\Directory::ls(null|string $extension, boolean $recursive)

List the files and directories in this directory and return the
respective file identifier for each file/directory



* Visibility: **public**


#### Arguments
* $extension **null|string** - &lt;p&gt;The extension to look for, (optional)&lt;/p&gt;
* $recursive **boolean** - &lt;p&gt;Set to true to recurse into sub directories and perform the same search.&lt;/p&gt;



### isReadable

    boolean Core\Filestore\Directory::isReadable()

Tells whether a directory exists and is readable



* Visibility: **public**




### isWritable

    mixed Core\Filestore\Directory::isWritable()





* Visibility: **public**




### exists

    boolean Core\Filestore\Directory::exists()

Check and see if this exists and is in-fact a directory.



* Visibility: **public**




### mkdir

    boolean Core\Filestore\Directory::mkdir()

Create this directory, (has no effect if already exists)
Returns true if successful, null if exists, and false if failure



* Visibility: **public**




### rename

    mixed Core\Filestore\Directory::rename($newname)





* Visibility: **public**


#### Arguments
* $newname **mixed**



### getPath

    string Core\Filestore\Directory::getPath()

Get this directory's fully resolved path



* Visibility: **public**




### setPath

    void Core\Filestore\Directory::setPath($path)

Set the path for this directory.



* Visibility: **public**


#### Arguments
* $path **mixed**



### getBasename

    string Core\Filestore\Directory::getBasename()

Get just the basename of this directory



* Visibility: **public**




### delete

    mixed Core\Filestore\Directory::delete()

Delete a directory and recursively any file inside it.



* Visibility: **public**




### remove

    mixed Core\Filestore\Directory::remove()

Delete a directory and recursively any file inside it.

Alias of delete

* Visibility: **public**




### get

    null|\Core\Filestore\File|\Core\Filestore\Directory Core\Filestore\Directory::get(string $name)

Find and get a directory or file that matches the name provided.

Will search run down subdirectories if a tree'd path is provided.

* Visibility: **public**


#### Arguments
* $name **string**


