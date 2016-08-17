Core\Filestore\File
===============






* Interface name: File
* Namespace: Core\Filestore
* This is an **interface**


Constants
----------


### TYPE_ASSET

    const TYPE_ASSET = 'asset'





### TYPE_PUBLIC

    const TYPE_PUBLIC = 'public'





### TYPE_PRIVATE

    const TYPE_PRIVATE = 'private'





### TYPE_TMP

    const TYPE_TMP = 'tmp'





### TYPE_OTHER

    const TYPE_OTHER = 'other'







Methods
-------


### getFilesize

    string|integer Core\Filestore\File::getFilesize(boolean $formatted)

Get the filesize of this file object, as either raw bytes or a formatted string.



* Visibility: **public**


#### Arguments
* $formatted **boolean**



### getMimetype

    string Core\Filestore\File::getMimetype()

Get the mimetype of this file.



* Visibility: **public**




### getExtension

    string Core\Filestore\File::getExtension()

Get the extension of this file, (without the ".")



* Visibility: **public**




### getTitle

    string Core\Filestore\File::getTitle()

Get the title of this file, either generated from the filename or pulled from the meta data as appropriate.



* Visibility: **public**




### getURL

    string|boolean Core\Filestore\File::getURL()

Get a filename that can be retrieved from the web.

Resolves with the ROOT_DIR prefix already attached.

* Visibility: **public**




### getPreviewURL

    string Core\Filestore\File::getPreviewURL(string $dimensions)

Get a serverside-resized thumbnail url for this file.



* Visibility: **public**


#### Arguments
* $dimensions **string**



### getFilename

    string Core\Filestore\File::getFilename(boolean|null|string|mixed $prefix)

Get the filename of this file resolved to a specific directory, usually ROOT_PDIR or ROOT_WDIR.



* Visibility: **public**


#### Arguments
* $prefix **boolean|null|string|mixed** - &lt;p&gt;Determine the prefix requested
FALSE will return the Core-encoded string, (&quot;public/&quot;, &quot;asset/&quot;, etc)
NULL defaults to the ROOT_PDIR
&#039;&#039; returns the relative directory from the install base&lt;/p&gt;



### setFilename

    mixed Core\Filestore\File::setFilename($filename)

Set the filename of this file manually.

Useful for operating on a file after construction.

* Visibility: **public**


#### Arguments
* $filename **mixed** - &lt;p&gt;string&lt;/p&gt;



### getBasename

    string Core\Filestore\File::getBasename(boolean $withoutext)

Get the base filename of this file.



* Visibility: **public**


#### Arguments
* $withoutext **boolean** - &lt;p&gt;Set to true to drop the extension.&lt;/p&gt;



### getBaseFilename

    string Core\Filestore\File::getBaseFilename(boolean $withoutext)

Get the base filename of this file.

Alias of getBasename

* Visibility: **public**


#### Arguments
* $withoutext **boolean** - &lt;p&gt;Set to true to drop the extension.&lt;/p&gt;



### getDirectoryName

    string Core\Filestore\File::getDirectoryName()

Get the directory name of this file

Will return the parent directory name, ending with a trailing slash.

* Visibility: **public**




### getLocalFilename

    string Core\Filestore\File::getLocalFilename()

Get the filename for a local clone of this file.

For local files, it's the same thing, but remote files will be copied to a temporary local location first.

* Visibility: **public**




### getHash

    string Core\Filestore\File::getHash()

Get the hash for this file.

This is generally an MD5 sum of the file contents.

* Visibility: **public**




### getFilenameHash

    string Core\Filestore\File::getFilenameHash()

Get an ascii hash of the filename.

useful for transposing this file to another page call.

* Visibility: **public**




### delete

    boolean Core\Filestore\File::delete()

Delete this file from the filesystem.



* Visibility: **public**




### rename

    boolean Core\Filestore\File::rename($newname)

Rename this file to a new name



* Visibility: **public**


#### Arguments
* $newname **mixed**



### isImage

    boolean Core\Filestore\File::isImage()

Shortcut function to see if this file's mimetype is image/*



* Visibility: **public**




### isText

    boolean Core\Filestore\File::isText()

Shortcut function to see if this file's mimetype is text/*



* Visibility: **public**




### isPreviewable

    boolean Core\Filestore\File::isPreviewable()

Get if this file can be previewed in the web browser.



* Visibility: **public**




### displayPreview

    mixed Core\Filestore\File::displayPreview(string|integer $dimensions, boolean $includeHeader)

Display a preview of this file to the browser.  Must be an image.



* Visibility: **public**


#### Arguments
* $dimensions **string|integer** - &lt;p&gt;A string of the dimensions to create the image at, widthxheight.
Also supports the previous version of simply &quot;dimension&quot;, as an int.&lt;/p&gt;
* $includeHeader **boolean** - &lt;p&gt;Include the correct mimetype header or no.&lt;/p&gt;



### getMimetypeIconURL

    string Core\Filestore\File::getMimetypeIconURL(string $dimensions)

Get the mimetype icon for this file.



* Visibility: **public**


#### Arguments
* $dimensions **string**



### getQuickPreviewFile

    \Core\Filestore\File Core\Filestore\File::getQuickPreviewFile(string $dimensions)

Get the preview file object without actually populating the sources.

This is useful for checking to see if the file exists before resizing it over.

WARNING, this will NOT check if the file exists and/or copy data over!

* Visibility: **public**


#### Arguments
* $dimensions **string**



### getPreviewFile

    \Core\Filestore\File Core\Filestore\File::getPreviewFile(string $dimensions)

Get the preview file with the contents copied over resized/previewed.



* Visibility: **public**


#### Arguments
* $dimensions **string**



### inDirectory

    boolean Core\Filestore\File::inDirectory($path)

See if this file is in the requested directory.



* Visibility: **public**


#### Arguments
* $path **mixed** - &lt;p&gt;string&lt;/p&gt;



### identicalTo

    boolean Core\Filestore\File::identicalTo(\Core\Filestore\File $otherfile)

Check, (to the best of the interface's ability), if another file is identical to this one.



* Visibility: **public**


#### Arguments
* $otherfile **[Core\Filestore\File](core_filestore_file.md)**



### copyTo

    \Core\Filestore\File Core\Filestore\File::copyTo(string $dest, boolean $overwrite)

Copies the file to the requested destination.

If the destination is a directory (ends with a '/'), the same filename is used, (if possible).
If the destination is relative, ('.' or 'subdir/'), it is assumed relative to the current file.

* Visibility: **public**


#### Arguments
* $dest **string**
* $overwrite **boolean**



### copyFrom

    boolean Core\Filestore\File::copyFrom(\Core\Filestore\File $src, boolean $overwrite)

Make a copy of a source File into this File.

(Generally only useful internally)

* Visibility: **public**


#### Arguments
* $src **[Core\Filestore\File](core_filestore_file.md)** - &lt;p&gt;Source file backend&lt;/p&gt;
* $overwrite **boolean** - &lt;p&gt;true to overwrite existing file&lt;/p&gt;



### getContents

    mixed Core\Filestore\File::getContents()

Get the raw contents of this file

Essentially file_get_contents()

* Visibility: **public**




### putContents

    boolean Core\Filestore\File::putContents(mixed $data)

Write the raw contents of this file

Essentially file_put_contents()

* Visibility: **public**


#### Arguments
* $data **mixed**



### getContentsObject

    \Core\Filestore\Contents Core\Filestore\File::getContentsObject()

Get the contents object that can then be manipulated in more detail,
ie: an image can be displayed, compressed files can be uncompressed, etc.



* Visibility: **public**




### exists

    boolean Core\Filestore\File::exists()

Check if this file exists on the filesystem currently.



* Visibility: **public**




### isReadable

    boolean Core\Filestore\File::isReadable()

Check if this file is readable.



* Visibility: **public**




### isWritable

    boolean Core\Filestore\File::isWritable()

Check if this file is writable.



* Visibility: **public**




### isLocal

    boolean Core\Filestore\File::isLocal()

Simple function to indicate if this file is on the local filesystem.

For remote file types, just return false, otherwise return true.

* Visibility: **public**




### getMTime

    integer Core\Filestore\File::getMTime()

Get the modified time for this file as a unix timestamp.



* Visibility: **public**




### sendToUserAgent

    void Core\Filestore\File::sendToUserAgent(boolean $forcedownload)

Send a file to the user agent



* Visibility: **public**


#### Arguments
* $forcedownload **boolean** - &lt;p&gt;Set to true to force download instead of just sending the file.&lt;/p&gt;


