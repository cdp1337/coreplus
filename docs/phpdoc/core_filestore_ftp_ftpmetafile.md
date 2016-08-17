Core\Filestore\FTP\FTPMetaFile
===============

A short teaser of what FTPMetaFile does.

More lengthy description of what FTPMetaFile does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: FTPMetaFile
* Namespace: Core\Filestore\FTP





Properties
----------


### $_ftp

    private \Core\Filestore\FTP\FTPConnection $_ftp





* Visibility: **private**


### $_dir

    private mixed $_dir





* Visibility: **private**


### $_contents

    private mixed $_contents





* Visibility: **private**


### $_local

    private \Core\Filestore\Backends\FileLocal $_local





* Visibility: **private**


### $_changed

    private mixed $_changed = false





* Visibility: **private**


Methods
-------


### __construct

    mixed Core\Filestore\FTP\FTPMetaFile::__construct(string $directory, \Core\Filestore\FTP\FTPConnection $ftp)





* Visibility: **public**


#### Arguments
* $directory **string**
* $ftp **[Core\Filestore\FTP\FTPConnection](core_filestore_ftp_ftpconnection.md)**



### getMetas

    array Core\Filestore\FTP\FTPMetaFile::getMetas(string $file)

Get an associative array of all metadata associated to the requested file.



* Visibility: **public**


#### Arguments
* $file **string**



### set

    mixed Core\Filestore\FTP\FTPMetaFile::set(string $file, string $key, string $value, boolean $commit)

Set an arbitrary key with a value on a given file.



* Visibility: **public**


#### Arguments
* $file **string**
* $key **string**
* $value **string**
* $commit **boolean**



### saveMetas

    mixed Core\Filestore\FTP\FTPMetaFile::saveMetas()

Save this meta file back up to the FTP server.



* Visibility: **public**



