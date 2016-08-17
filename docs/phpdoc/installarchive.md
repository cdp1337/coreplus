InstallArchive
===============

[PAGE DESCRIPTION HERE]




* Class name: InstallArchive
* Namespace: 



Constants
----------


### SIGNATURE_NONE

    const SIGNATURE_NONE = 0





### SIGNATURE_VALID

    const SIGNATURE_VALID = 1





### SIGNATURE_INVALID

    const SIGNATURE_INVALID = 2





Properties
----------


### $_file

    private mixed $_file





* Visibility: **private**


### $_manifestdata

    private mixed $_manifestdata





* Visibility: **private**


### $_signature

    private mixed $_signature





* Visibility: **private**


### $_fileconflicts

    private mixed $_fileconflicts





* Visibility: **private**


### $_filelist

    private mixed $_filelist





* Visibility: **private**


Methods
-------


### __construct

    mixed InstallArchive::__construct($file)

The constructor takes a File object, filename string, or URL string, and provides easy access for
handling operations on that file.



* Visibility: **public**


#### Arguments
* $file **mixed**



### hasValidSignature

    mixed InstallArchive::hasValidSignature()





* Visibility: **public**




### checkSignature

    mixed InstallArchive::checkSignature()





* Visibility: **public**




### _checkGPGSignature

    mixed InstallArchive::_checkGPGSignature()





* Visibility: **private**




### getManifest

    mixed InstallArchive::getManifest()

Every package tarball should have a manifest file in it.

.. extract that out to get its metadata.

* Visibility: **public**




### _getManifest

    mixed InstallArchive::_getManifest($filename)





* Visibility: **private**


#### Arguments
* $filename **mixed**



### _decryptTo

    mixed InstallArchive::_decryptTo($filename)





* Visibility: **private**


#### Arguments
* $filename **mixed**



### getFilelist

    mixed InstallArchive::getFilelist()

Get all the filenames in this archive.



* Visibility: **public**




### _getFilelist

    mixed InstallArchive::_getFilelist($filename)





* Visibility: **private**


#### Arguments
* $filename **mixed**



### extractFile

    mixed InstallArchive::extractFile($filename, $to)

Extract a specific file to a specified directory.



* Visibility: **public**


#### Arguments
* $filename **mixed**
* $to **mixed**



### getFileConflicts

    mixed InstallArchive::getFileConflicts()

Check the filesystem for an installed version and get any 'conflicts'
there may be.  Think conflict as in SVN version and not windows 'this file already exists' version.



* Visibility: **public**




### getBaseDir

    mixed InstallArchive::getBaseDir()





* Visibility: **public**




### _getFileConflictsComponent

    mixed InstallArchive::_getFileConflictsComponent($arrayoffiles)





* Visibility: **private**


#### Arguments
* $arrayoffiles **mixed**


