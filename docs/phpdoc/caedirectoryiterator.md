CAEDirectoryIterator
===============

Class that closely mimics PHP&#039;s DirectoryIterator object, only with CAE specifics,
ie: a File object is returned for each file instead of just the filename.




* Class name: CAEDirectoryIterator
* Namespace: 
* This class implements: Iterator




Properties
----------


### $_path

    private string $_path

The directory to iterate through.



* Visibility: **private**


### $_files

    private mixed $_files = array()

The internal listing of files assembled in the constructor.



* Visibility: **private**


### $_ignores

    private array $_ignores = array()

A list of files/directories to ignore in the sift procedure.



* Visibility: **private**


Methods
-------


### __construct

    mixed CAEDirectoryIterator::__construct($path)





* Visibility: **public**


#### Arguments
* $path **mixed**



### addIgnore

    mixed CAEDirectoryIterator::addIgnore(string $path)

Add a file or directory to ignore when retrieving results.

If this is a directory, everything in that directory will be ignored.

* Visibility: **public**


#### Arguments
* $path **string**



### addIgnores

    mixed CAEDirectoryIterator::addIgnores(array|string $list)

Add a list of files or directories to ignore when retrieving results.

The list can either be a singular array or a set of parameters.

* Visibility: **public**


#### Arguments
* $list **array|string**



### setPath

    mixed CAEDirectoryIterator::setPath($path)





* Visibility: **public**


#### Arguments
* $path **mixed**



### scan

    mixed CAEDirectoryIterator::scan(string $path)

Manually run a scan.  This is called automatically if a filename is given in the constructor.



* Visibility: **public**


#### Arguments
* $path **string**



### sift

    \unknown_type CAEDirectoryIterator::sift(\unknown_type $dir)

Sift through a directory and get the files in it.



* Visibility: **protected**


#### Arguments
* $dir **unknown_type**



### rewind

    mixed CAEDirectoryIterator::rewind()





* Visibility: **public**




### current

    mixed CAEDirectoryIterator::current()





* Visibility: **public**




### key

    mixed CAEDirectoryIterator::key()





* Visibility: **public**




### next

    mixed CAEDirectoryIterator::next()





* Visibility: **public**




### valid

    mixed CAEDirectoryIterator::valid()





* Visibility: **public**




### getATime

    integer CAEDirectoryIterator::getATime()





* Visibility: **public**




### getFilename

    string CAEDirectoryIterator::getFilename($prefix)





* Visibility: **public**


#### Arguments
* $prefix **mixed**


