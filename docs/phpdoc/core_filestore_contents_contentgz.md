Core\Filestore\Contents\ContentGZ
===============






* Class name: ContentGZ
* Namespace: Core\Filestore\Contents
* This class implements: [Core\Filestore\Contents](core_filestore_contents.md)




Properties
----------


### $_file

    private mixed $_file = null





* Visibility: **private**


Methods
-------


### __construct

    mixed Core\Filestore\Contents::__construct(\Core\Filestore\File $file)





* Visibility: **public**
* This method is defined by [Core\Filestore\Contents](core_filestore_contents.md)


#### Arguments
* $file **[Core\Filestore\File](core_filestore_file.md)**



### getContents

    mixed Core\Filestore\Contents\ContentGZ::getContents()





* Visibility: **public**




### uncompress

    mixed Core\Filestore\Contents\ContentGZ::uncompress(\Core\Filestore\File|boolean $dst)

Uncompress this file contents and return the result.

Obviously, if a multi-gigibyte file is read with no immediate destination,
you'll probably run out of memory.

* Visibility: **public**


#### Arguments
* $dst **[Core\Filestore\File](core_filestore_file.md)|boolean** - &lt;p&gt;The destination to write the uncompressed data to
       If not provided, just returns the data.&lt;/p&gt;


