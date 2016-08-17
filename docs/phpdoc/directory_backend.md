Directory_Backend
===============

DESCRIPTION




* Interface name: Directory_Backend
* Namespace: 
* This is an **interface**






Methods
-------


### __construct

    mixed Directory_Backend::__construct($directory)





* Visibility: **public**


#### Arguments
* $directory **mixed**



### ls

    array Directory_Backend::ls()

List the files and directories in this directory and return the
respective file identifier for each file/directory



* Visibility: **public**




### isReadable

    boolean Directory_Backend::isReadable()

Tells whether a directory exists and is readable



* Visibility: **public**




### isWritable

    mixed Directory_Backend::isWritable()





* Visibility: **public**




### mkdir

    boolean Directory_Backend::mkdir()

Create this directory, (has no effect if already exists)
Returns true if successful, null if exists, and false if failure



* Visibility: **public**




### rename

    boolean Directory_Backend::rename($newname)

Rename this directory



* Visibility: **public**


#### Arguments
* $newname **mixed**



### getPath

    string Directory_Backend::getPath()

Get this directory's fully resolved path



* Visibility: **public**




### getBasename

    string Directory_Backend::getBasename()

Get just the basename of this directory



* Visibility: **public**




### remove

    mixed Directory_Backend::remove()

Remove a directory and recursively any file inside it.



* Visibility: **public**




### get

    mixed Directory_Backend::get(string $name)

Find and get a directory or file that matches the name provided.

Will search run down subdirectories if a tree'd path is provided.

* Visibility: **public**


#### Arguments
* $name **string**


