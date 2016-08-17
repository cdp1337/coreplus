Core\Filestore\Backends\FileRemote
===============






* Class name: FileRemote
* Namespace: Core\Filestore\Backends
* This class implements: [Core\Filestore\File](core_filestore_file.md)




Properties
----------


### $username

    public string $username = null

The username to use if basic authentication is required.



* Visibility: **public**


### $password

    public string $password = null

The password to use if basic authentication is required.



* Visibility: **public**


### $cacheable

    public boolean $cacheable = true

Set to false to require the remote file to be downloaded every request.



* Visibility: **public**


### $_url

    private string $_url = null

The fully resolved filename of this file.



* Visibility: **private**


### $_headers

    private array $_headers = null

A key/value paired array of headers for this given URL.

Useful for determining if a file exists without downloading it.

* Visibility: **private**


### $_response

    private integer $_response = null

The response code for this file.

Generally 200, 302, or 404.

* Visibility: **private**


### $_tmplocal

    private \Core\Filestore\Backends\FileLocal $_tmplocal = null

Temporary local version of the file.

This is necessary for some operations such as "copyFrom" and "identicalTo"

* Visibility: **private**


### $_requestHeaders

    private mixed $_requestHeaders = null





* Visibility: **private**


### $_method

    private mixed $_method = 'GET'





* Visibility: **private**


### $_payload

    private mixed $_payload = null





* Visibility: **private**


### $_redirectFile

    protected null $_redirectFile = null

If the file was a 302, this is the temporary redirect placeholder.

This is a separate file because according to the RFC2616 spec,
requests that fall under a 302 are independent of each other and should be cached independently.

* Visibility: **protected**


### $_redirectCount

    protected integer $_redirectCount

Level of redirect counts this file request is under.

Used to prevent infinite redirect loops.

* Visibility: **protected**


Methods
-------


### __construct

    mixed Core\Filestore\Backends\FileRemote::__construct($filename)





* Visibility: **public**


#### Arguments
* $filename **mixed**



### getTitle

    string Core\Filestore\File::getTitle()

Get the title of this file, either generated from the filename or pulled from the meta data as appropriate.



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)




### getFilesize

    string|integer Core\Filestore\File::getFilesize(boolean $formatted)

Get the filesize of this file object, as either raw bytes or a formatted string.



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)


#### Arguments
* $formatted **boolean**



### getMimetype

    string Core\Filestore\File::getMimetype()

Get the mimetype of this file.



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)




### getExtension

    string Core\Filestore\File::getExtension()

Get the extension of this file, (without the ".")



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)




### getURL

    string|boolean Core\Filestore\File::getURL()

Get a filename that can be retrieved from the web.

Resolves with the ROOT_DIR prefix already attached.

* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)




### getFilename

    string Core\Filestore\File::getFilename(boolean|null|string|mixed $prefix)

Get the filename of this file resolved to a specific directory, usually ROOT_PDIR or ROOT_WDIR.



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)


#### Arguments
* $prefix **boolean|null|string|mixed** - &lt;p&gt;Determine the prefix requested
FALSE will return the Core-encoded string, (&quot;public/&quot;, &quot;asset/&quot;, etc)
NULL defaults to the ROOT_PDIR
&#039;&#039; returns the relative directory from the install base&lt;/p&gt;



### getBaseFilename

    string Core\Filestore\File::getBaseFilename(boolean $withoutext)

Get the base filename of this file.

Alias of getBasename

* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)


#### Arguments
* $withoutext **boolean** - &lt;p&gt;Set to true to drop the extension.&lt;/p&gt;



### getDirectoryName

    string Core\Filestore\File::getDirectoryName()

Get the directory name of this file

Will return the parent directory name, ending with a trailing slash.

* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)




### getLocalFilename

    string Core\Filestore\File::getLocalFilename()

Get the filename for a local clone of this file.

For local files, it's the same thing, but remote files will be copied to a temporary local location first.

* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)




### getFilenameHash

    string Core\Filestore\File::getFilenameHash()

Get an ascii hash of the filename.

useful for transposing this file to another page call.

* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)




### getHash

    string Core\Filestore\File::getHash()

Get the hash for this file.

This is generally an MD5 sum of the file contents.

* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)




### delete

    boolean Core\Filestore\File::delete()

Delete this file from the filesystem.



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)




### copyTo

    \Core\Filestore\File Core\Filestore\File::copyTo(string $dest, boolean $overwrite)

Copies the file to the requested destination.

If the destination is a directory (ends with a '/'), the same filename is used, (if possible).
If the destination is relative, ('.' or 'subdir/'), it is assumed relative to the current file.

* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)


#### Arguments
* $dest **string**
* $overwrite **boolean**



### copyFrom

    boolean Core\Filestore\File::copyFrom(\Core\Filestore\File $src, boolean $overwrite)

Make a copy of a source File into this File.

(Generally only useful internally)

* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)


#### Arguments
* $src **[Core\Filestore\File](core_filestore_file.md)** - &lt;p&gt;Source file backend&lt;/p&gt;
* $overwrite **boolean** - &lt;p&gt;true to overwrite existing file&lt;/p&gt;



### getContents

    mixed Core\Filestore\File::getContents()

Get the raw contents of this file

Essentially file_get_contents()

* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)




### putContents

    boolean Core\Filestore\File::putContents(mixed $data)

Write the raw contents of this file

Essentially file_put_contents()

* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)


#### Arguments
* $data **mixed**



### getContentsObject

    \Core\Filestore\Contents Core\Filestore\File::getContentsObject()

Get the contents object that can then be manipulated in more detail,
ie: an image can be displayed, compressed files can be uncompressed, etc.



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)




### isImage

    boolean Core\Filestore\File::isImage()

Shortcut function to see if this file's mimetype is image/*



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)




### isText

    boolean Core\Filestore\File::isText()

Shortcut function to see if this file's mimetype is text/*



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)




### isPreviewable

    boolean Core\Filestore\File::isPreviewable()

Get if this file can be previewed in the web browser.



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)




### displayPreview

    mixed Core\Filestore\File::displayPreview(string|integer $dimensions, boolean $includeHeader)

Display a preview of this file to the browser.  Must be an image.



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)


#### Arguments
* $dimensions **string|integer** - &lt;p&gt;A string of the dimensions to create the image at, widthxheight.
Also supports the previous version of simply &quot;dimension&quot;, as an int.&lt;/p&gt;
* $includeHeader **boolean** - &lt;p&gt;Include the correct mimetype header or no.&lt;/p&gt;



### getPreviewURL

    string Core\Filestore\File::getPreviewURL(string $dimensions)

Get a serverside-resized thumbnail url for this file.



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)


#### Arguments
* $dimensions **string**



### inDirectory

    boolean Core\Filestore\File::inDirectory($path)

See if this file is in the requested directory.



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)


#### Arguments
* $path **mixed** - &lt;p&gt;string&lt;/p&gt;



### identicalTo

    boolean Core\Filestore\File::identicalTo(\Core\Filestore\File $otherfile)

Check, (to the best of the interface's ability), if another file is identical to this one.



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)


#### Arguments
* $otherfile **[Core\Filestore\File](core_filestore_file.md)**



### exists

    boolean Core\Filestore\File::exists()

Check if this file exists on the filesystem currently.



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)




### isReadable

    boolean Core\Filestore\File::isReadable()

Check if this file is readable.



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)




### isOK

    mixed Core\Filestore\Backends\FileRemote::isOK()





* Visibility: **public**




### requiresAuthentication

    mixed Core\Filestore\Backends\FileRemote::requiresAuthentication()





* Visibility: **public**




### getStatus

    integer Core\Filestore\Backends\FileRemote::getStatus()

Get the HTTP status code for this file.



* Visibility: **public**




### isLocal

    boolean Core\Filestore\File::isLocal()

Simple function to indicate if this file is on the local filesystem.

For remote file types, just return false, otherwise return true.

* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)




### getMTime

    integer Core\Filestore\File::getMTime()

Get the modified time for this file as a unix timestamp.



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)




### getBasename

    string Core\Filestore\File::getBasename(boolean $withoutext)

Get the base filename of this file.



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)


#### Arguments
* $withoutext **boolean** - &lt;p&gt;Set to true to drop the extension.&lt;/p&gt;



### rename

    boolean Core\Filestore\File::rename($newname)

Rename this file to a new name



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)


#### Arguments
* $newname **mixed**



### getMimetypeIconURL

    string Core\Filestore\File::getMimetypeIconURL(string $dimensions)

Get the mimetype icon for this file.



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)


#### Arguments
* $dimensions **string**



### getQuickPreviewFile

    \Core\Filestore\File Core\Filestore\File::getQuickPreviewFile(string $dimensions)

Get the preview file object without actually populating the sources.

This is useful for checking to see if the file exists before resizing it over.

WARNING, this will NOT check if the file exists and/or copy data over!

* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)


#### Arguments
* $dimensions **string**



### getPreviewFile

    \Core\Filestore\File Core\Filestore\File::getPreviewFile(string $dimensions)

Get the preview file with the contents copied over resized/previewed.



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)


#### Arguments
* $dimensions **string**



### isWritable

    boolean Core\Filestore\File::isWritable()

Check if this file is writable.



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)




### setFilename

    mixed Core\Filestore\File::setFilename($filename)

Set the filename of this file manually.

Useful for operating on a file after construction.

* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)


#### Arguments
* $filename **mixed** - &lt;p&gt;string&lt;/p&gt;



### setMethod

    mixed Core\Filestore\Backends\FileRemote::setMethod(string $method)

Set the request method for this remote file



* Visibility: **public**


#### Arguments
* $method **string**



### setPayload

    mixed Core\Filestore\Backends\FileRemote::setPayload($data)





* Visibility: **public**


#### Arguments
* $data **mixed**



### setRequestHeader

    mixed Core\Filestore\Backends\FileRemote::setRequestHeader(string $value, string $key)

Set a particular REQUEST header to this file.



* Visibility: **public**


#### Arguments
* $value **string** - &lt;p&gt;The header key to set before the &#039;:&#039;)&lt;/p&gt;
* $key **string** - &lt;p&gt;The header value to set (after the &#039;:&#039;)&lt;/p&gt;



### _getHeaders

    mixed Core\Filestore\Backends\FileRemote::_getHeaders()

Get the headers for this given file.

This will go out and query the server with a HEAD request if no headers set otherwise.

ONLY applicable with GET based requests!

* Visibility: **protected**




### _getHeader

    mixed Core\Filestore\Backends\FileRemote::_getHeader($header)





* Visibility: **protected**


#### Arguments
* $header **mixed**



### _getTmpLocal

    \Core\Filestore\Backends\FileLocal Core\Filestore\Backends\FileRemote::_getTmpLocal()

Get the temporary local version of the file.

This is useful for doing operations such as hash and identicalto.

* Visibility: **protected**




### sendToUserAgent

    void Core\Filestore\File::sendToUserAgent(boolean $forcedownload)

Send a file to the user agent



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)


#### Arguments
* $forcedownload **boolean** - &lt;p&gt;Set to true to force download instead of just sending the file.&lt;/p&gt;


