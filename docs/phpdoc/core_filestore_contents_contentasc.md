Core\Filestore\Contents\ContentASC
===============






* Class name: ContentASC
* Namespace: Core\Filestore\Contents
* This class implements: [Core\Filestore\Contents](core_filestore_contents.md)




Properties
----------


### $_file

    private \Core\Filestore\File $_file = null

The original file object



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

    mixed Core\Filestore\Contents\ContentASC::getContents()





* Visibility: **public**




### verify

    boolean Core\Filestore\Contents\ContentASC::verify()

Verify the GPG signature of this encrypted/signed file.

The public key MUST be installed already, otherwise this check will of
course return false because it's able to verify it.

* Visibility: **public**




### getKey

    string Core\Filestore\Contents\ContentASC::getKey()

Get the public key that was used to sign this file.



* Visibility: **public**




### decrypt

    mixed Core\Filestore\Contents\ContentASC::decrypt($dest)

Decrypt the encrypted/signed file and return a valid File object



* Visibility: **public**


#### Arguments
* $dest **mixed**


