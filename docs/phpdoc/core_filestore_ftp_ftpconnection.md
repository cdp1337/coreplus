Core\Filestore\FTP\FTPConnection
===============

A short teaser of what FTPConnection does.

More lengthy description of what FTPConnection does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: FTPConnection
* Namespace: Core\Filestore\FTP





Properties
----------


### $conn

    private resource $conn





* Visibility: **private**


### $username

    public string $username





* Visibility: **public**


### $password

    public string $password





* Visibility: **public**


### $host

    public string $host





* Visibility: **public**


### $url

    public string $url





* Visibility: **public**


### $root

    public string $root





* Visibility: **public**


### $isLocal

    public boolean $isLocal = false





* Visibility: **public**


### $metaFiles

    protected array $metaFiles = array()





* Visibility: **protected**


### $_OpenConnections

    private array $_OpenConnections = array()





* Visibility: **private**
* This property is **static**.


### $lastSave

    private integer $lastSave





* Visibility: **private**


### $connected

    private boolean $connected = false





* Visibility: **private**


Methods
-------


### getConn

    resource Core\Filestore\FTP\FTPConnection::getConn()

Connect and return the FTP resource.

If already connected, then nothing is done.

* Visibility: **public**




### connect

    mixed Core\Filestore\FTP\FTPConnection::connect()

Connect to the set hostname using the appropriate credentials.



* Visibility: **public**




### reset

    mixed Core\Filestore\FTP\FTPConnection::reset()

Reset (or chdir), to the root directory.



* Visibility: **public**




### getFileHash

    string Core\Filestore\FTP\FTPConnection::getFileHash(string $filename)

Get the file contents hash of a given FTP file.



* Visibility: **public**


#### Arguments
* $filename **string**



### getFileModified

    integer Core\Filestore\FTP\FTPConnection::getFileModified(string $filename)

Get the file modified timestamp, (as UTC), of a given FTP file.



* Visibility: **public**


#### Arguments
* $filename **string**



### getFileSize

    integer Core\Filestore\FTP\FTPConnection::getFileSize(string $filename)

Get the file size of a given FTP file.



* Visibility: **public**


#### Arguments
* $filename **string**



### setFileHash

    mixed Core\Filestore\FTP\FTPConnection::setFileHash(string $filename, string $hash)

Set the hash of an FTP file



* Visibility: **public**


#### Arguments
* $filename **string**
* $hash **string**



### setFileModified

    mixed Core\Filestore\FTP\FTPConnection::setFileModified(string $filename, integer $timestamp)

Set the modified timestamp of an FTP file



* Visibility: **public**


#### Arguments
* $filename **string**
* $timestamp **integer**



### setFileSize

    mixed Core\Filestore\FTP\FTPConnection::setFileSize(string $filename, integer $size)

Set the size of an FTP file



* Visibility: **public**


#### Arguments
* $filename **string**
* $size **integer**



### getMetaFileObject

    \Core\Filestore\FTP\FTPMetaFile Core\Filestore\FTP\FTPConnection::getMetaFileObject(string $directory)





* Visibility: **public**


#### Arguments
* $directory **string**



### _syncMetas

    mixed Core\Filestore\FTP\FTPConnection::_syncMetas()





* Visibility: **private**




### ShutdownHook

    mixed Core\Filestore\FTP\FTPConnection::ShutdownHook()

Hook to save all metadata files that happen to be open on the open FTP connections.



* Visibility: **public**
* This method is **static**.



