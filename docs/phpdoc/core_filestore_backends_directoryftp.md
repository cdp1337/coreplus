Core\Filestore\Backends\DirectoryFTP
===============






* Class name: DirectoryFTP
* Namespace: Core\Filestore\Backends
* This class implements: [Core\Filestore\Directory](core_filestore_directory.md)




Properties
----------


### $_prefix

    protected mixed $_prefix





* Visibility: **protected**


### $_path

    protected mixed $_path





* Visibility: **protected**


### $_type

    private mixed $_type





* Visibility: **private**


### $_files

    private array $_files = null

The internal listing of files



* Visibility: **private**


### $_ignores

    private array $_ignores = array()

A list of files/directories to ignore when listing files



* Visibility: **private**


### $_ftp

    protected  $_ftp

The backend FTP resource.

This is a native PHP object.

* Visibility: **protected**


### $_islocal

    protected boolean $_islocal = false

Set to true if this FTP connection is the proxy for local files.



* Visibility: **protected**


Methods
-------


### __construct

    mixed Core\Filestore\Backends\DirectoryFTP::__construct($directory, $ftpobject)





* Visibility: **public**


#### Arguments
* $directory **mixed**
* $ftpobject **mixed**



### ls

    array Core\Filestore\Directory::ls(null|string $extension, boolean $recursive)

List the files and directories in this directory and return the
respective file identifier for each file/directory



* Visibility: **public**
* This method is defined by [Core\Filestore\Directory](core_filestore_directory.md)


#### Arguments
* $extension **null|string** - &lt;p&gt;The extension to look for, (optional)&lt;/p&gt;
* $recursive **boolean** - &lt;p&gt;Set to true to recurse into sub directories and perform the same search.&lt;/p&gt;



### isReadable

    boolean Core\Filestore\Directory::isReadable()

Tells whether a directory exists and is readable



* Visibility: **public**
* This method is defined by [Core\Filestore\Directory](core_filestore_directory.md)




### isWritable

    mixed Core\Filestore\Directory::isWritable()





* Visibility: **public**
* This method is defined by [Core\Filestore\Directory](core_filestore_directory.md)




### exists

    boolean Core\Filestore\Directory::exists()

Check and see if this exists and is in-fact a directory.



* Visibility: **public**
* This method is defined by [Core\Filestore\Directory](core_filestore_directory.md)




### mkdir

    boolean Core\Filestore\Directory::mkdir()

Create this directory, (has no effect if already exists)
Returns true if successful, null if exists, and false if failure



* Visibility: **public**
* This method is defined by [Core\Filestore\Directory](core_filestore_directory.md)




### rename

    mixed Core\Filestore\Directory::rename($newname)





* Visibility: **public**
* This method is defined by [Core\Filestore\Directory](core_filestore_directory.md)


#### Arguments
* $newname **mixed**



### getPath

    string Core\Filestore\Directory::getPath()

Get this directory's fully resolved path



* Visibility: **public**
* This method is defined by [Core\Filestore\Directory](core_filestore_directory.md)




### setPath

    void Core\Filestore\Directory::setPath($path)

Set the path for this directory.



* Visibility: **public**
* This method is defined by [Core\Filestore\Directory](core_filestore_directory.md)


#### Arguments
* $path **mixed**



### getBasename

    string Core\Filestore\Directory::getBasename()

Get just the basename of this directory



* Visibility: **public**
* This method is defined by [Core\Filestore\Directory](core_filestore_directory.md)




### delete

    mixed Core\Filestore\Directory::delete()

Delete a directory and recursively any file inside it.



* Visibility: **public**
* This method is defined by [Core\Filestore\Directory](core_filestore_directory.md)




### remove

    mixed Core\Filestore\Directory::remove()

Delete a directory and recursively any file inside it.

Alias of delete

* Visibility: **public**
* This method is defined by [Core\Filestore\Directory](core_filestore_directory.md)




### get

    null|\Core\Filestore\File|\Core\Filestore\Directory Core\Filestore\Directory::get(string $name)

Find and get a directory or file that matches the name provided.

Will search run down subdirectories if a tree'd path is provided.

* Visibility: **public**
* This method is defined by [Core\Filestore\Directory](core_filestore_directory.md)


#### Arguments
* $name **string**



### getExtension

    null Core\Filestore\Backends\DirectoryFTP::getExtension()

To ensure compatibility with the File system.



* Visibility: **public**




### _sift

    mixed Core\Filestore\Backends\DirectoryFTP::_sift()

Sift through a directory and get the files in it.

This is an internal function to populate the contents of $this->_files.

* Visibility: **private**



