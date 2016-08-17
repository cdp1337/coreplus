Core\Filestore\DirectoryIterator
===============

Advanced version of &quot;ls&quot; for directories

More lengthy description of what DirectorySearch does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: DirectoryIterator
* Namespace: Core\Filestore
* This class implements: Iterator




Properties
----------


### $findFiles

    public boolean $findFiles = true





* Visibility: **public**


### $findDirectories

    public boolean $findDirectories = true





* Visibility: **public**


### $recursive

    public boolean $recursive = false





* Visibility: **public**


### $findExtensions

    public array $findExtensions = array()





* Visibility: **public**


### $ignores

    public array $ignores = array()





* Visibility: **public**


### $pregMatch

    public string $pregMatch = ''





* Visibility: **public**


### $sort

    public boolean $sort = false





* Visibility: **public**


### $sortOn

    public string $sortOn = 'filename'





* Visibility: **public**


### $sortDir

    public string $sortDir = 'asc'





* Visibility: **public**


### $_results

    private mixed $_results = null





* Visibility: **private**


### $_baseDirectory

    private \Core\Filestore\Directory $_baseDirectory





* Visibility: **private**


Methods
-------


### __construct

    mixed Core\Filestore\DirectoryIterator::__construct(\Core\Filestore\Directory $directory)





* Visibility: **public**


#### Arguments
* $directory **[Core\Filestore\Directory](core_filestore_directory.md)**



### sortBy

    mixed Core\Filestore\DirectoryIterator::sortBy(string $on, string $dir)

Enable sorting on a specific key.



* Visibility: **public**


#### Arguments
* $on **string**
* $dir **string**



### scan

    array Core\Filestore\DirectoryIterator::scan()

Scan the directory for the given matches and return the results.



* Visibility: **public**




### rewind

    mixed Core\Filestore\DirectoryIterator::rewind()





* Visibility: **public**




### current

    mixed Core\Filestore\DirectoryIterator::current()





* Visibility: **public**




### key

    mixed Core\Filestore\DirectoryIterator::key()





* Visibility: **public**




### next

    mixed Core\Filestore\DirectoryIterator::next()





* Visibility: **public**




### valid

    mixed Core\Filestore\DirectoryIterator::valid()





* Visibility: **public**




### _sift

    array Core\Filestore\DirectoryIterator::_sift(null|string $base, null|\Core\Filestore\Directory $directory)

Sift through the specified directory for any matches and return them,

this can be called recursively

* Visibility: **private**


#### Arguments
* $base **null|string**
* $directory **null|[null](core_filestore_directory.md)**


