Core\Filestore\Backends\File_awss3_backend
===============






* Class name: File_awss3_backend
* Namespace: Core\Filestore\Backends
* This class implements: Core\Filestore\Backends\File




Properties
----------


### $_backend

    private \Core\Filestore\Backends\AmazonS3 $_backend





* Visibility: **private**


### $filename

    public mixed $filename





* Visibility: **public**


### $bucket

    public mixed $bucket





* Visibility: **public**


### $acl

    public mixed $acl = \Core\Filestore\Backends\AmazonS3::ACL_PUBLIC





* Visibility: **public**


### $storage

    public mixed $storage = \Core\Filestore\Backends\AmazonS3::STORAGE_STANDARD





* Visibility: **public**


### $_metadata

    private mixed $_metadata = null





* Visibility: **private**


Methods
-------


### __construct

    mixed Core\Filestore\Backends\File_awss3_backend::__construct($filename, $bucket)





* Visibility: **public**


#### Arguments
* $filename **mixed**
* $bucket **mixed**



### _getMetadata

    mixed Core\Filestore\Backends\File_awss3_backend::_getMetadata()





* Visibility: **private**




### getFilesize

    mixed Core\Filestore\Backends\File_awss3_backend::getFilesize($formatted)





* Visibility: **public**


#### Arguments
* $formatted **mixed**



### getMimetype

    mixed Core\Filestore\Backends\File_awss3_backend::getMimetype()





* Visibility: **public**




### getExtension

    mixed Core\Filestore\Backends\File_awss3_backend::getExtension()





* Visibility: **public**




### getURL

    string Core\Filestore\Backends\File_awss3_backend::getURL()

Get a filename that can be retrieved from the web.

Resolves with the ROOT_DIR prefix already attached.

* Visibility: **public**




### getPreviewURL

    mixed Core\Filestore\Backends\File_awss3_backend::getPreviewURL(string $dimensions)

Get a serverside-resized thumbnail url for this file.



* Visibility: **public**


#### Arguments
* $dimensions **string**



### getFilename

    mixed Core\Filestore\Backends\File_awss3_backend::getFilename($prefix)

Get the filename of this file resolved to a specific directory, usually ROOT_PDIR or ROOT_WDIR.



* Visibility: **public**


#### Arguments
* $prefix **mixed**



### getBaseFilename

    mixed Core\Filestore\Backends\File_awss3_backend::getBaseFilename($withoutext)

Get the base filename of this file.



* Visibility: **public**


#### Arguments
* $withoutext **mixed**



### getLocalFilename

    string Core\Filestore\Backends\File_awss3_backend::getLocalFilename()

Get the filename for a local clone of this file.

For local files, it's the same thing, but remote files will be copied to a temporary local location first.

* Visibility: **public**




### getHash

    mixed Core\Filestore\Backends\File_awss3_backend::getHash()

Get the hash for this file.



* Visibility: **public**




### delete

    mixed Core\Filestore\Backends\File_awss3_backend::delete()





* Visibility: **public**




### copyTo

    \Core\Filestore\Backends\File Core\Filestore\Backends\File_awss3_backend::copyTo(string|\Core\Filestore\Backends\File $dest, boolean $overwrite)

Copies the file to the requested destination.

If the destination is a directory (ends with a '/'), the same filename is used, (if possible).
If the destination is relative, ('.' or 'subdir/'), it is assumed relative to the current file.

* Visibility: **public**


#### Arguments
* $dest **string|Core\Filestore\Backends\File**
* $overwrite **boolean**



### copyFrom

    mixed Core\Filestore\Backends\File_awss3_backend::copyFrom($src, $overwrite)





* Visibility: **public**


#### Arguments
* $src **mixed**
* $overwrite **mixed**



### getContents

    mixed Core\Filestore\Backends\File_awss3_backend::getContents()





* Visibility: **public**




### putContents

    mixed Core\Filestore\Backends\File_awss3_backend::putContents($data, $mimetype)





* Visibility: **public**


#### Arguments
* $data **mixed**
* $mimetype **mixed**



### getContentsObject

    mixed Core\Filestore\Backends\File_awss3_backend::getContentsObject()





* Visibility: **public**




### isImage

    mixed Core\Filestore\Backends\File_awss3_backend::isImage()





* Visibility: **public**




### isText

    mixed Core\Filestore\Backends\File_awss3_backend::isText()





* Visibility: **public**




### isPreviewable

    boolean Core\Filestore\Backends\File_awss3_backend::isPreviewable()

Get if this file can be previewed in the web browser.



* Visibility: **public**




### inDirectory

    boolean Core\Filestore\Backends\File_awss3_backend::inDirectory($path)

See if this file is in the requested directory.



* Visibility: **public**


#### Arguments
* $path **mixed** - &lt;p&gt;string&lt;/p&gt;



### identicalTo

    mixed Core\Filestore\Backends\File_awss3_backend::identicalTo($otherfile)





* Visibility: **public**


#### Arguments
* $otherfile **mixed**



### exists

    mixed Core\Filestore\Backends\File_awss3_backend::exists()





* Visibility: **public**



