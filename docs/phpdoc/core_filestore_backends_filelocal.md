Core\Filestore\Backends\FileLocal
===============






* Class name: FileLocal
* Namespace: Core\Filestore\Backends
* This class implements: [Core\Filestore\File](core_filestore_file.md)




Properties
----------


### $_type

    public mixed $_type = \Core\Filestore\File::TYPE_OTHER





* Visibility: **public**


### $_filename

    protected string $_filename = null

The fully resolved filename of this file.



* Visibility: **protected**


### $_filenamecache

    private array $_filenamecache = array()





* Visibility: **private**


Methods
-------


### __construct

    mixed Core\Filestore\Backends\FileLocal::__construct($filename)





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



### setFilename

    mixed Core\Filestore\File::setFilename($filename)

Set the filename of this file manually.

Useful for operating on a file after construction.

* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)


#### Arguments
* $filename **mixed** - &lt;p&gt;string&lt;/p&gt;



### getBasename

    string Core\Filestore\File::getBasename(boolean $withoutext)

Get the base filename of this file.



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)


#### Arguments
* $withoutext **boolean** - &lt;p&gt;Set to true to drop the extension.&lt;/p&gt;



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




### rename

    boolean Core\Filestore\File::rename($newname)

Rename this file to a new name



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)


#### Arguments
* $newname **mixed**



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




### isWritable

    boolean Core\Filestore\File::isWritable()

Check if this file is writable.



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)




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




### sendToUserAgent

    void Core\Filestore\File::sendToUserAgent(boolean $forcedownload)

Send a file to the user agent



* Visibility: **public**
* This method is defined by [Core\Filestore\File](core_filestore_file.md)


#### Arguments
* $forcedownload **boolean** - &lt;p&gt;Set to true to force download instead of just sending the file.&lt;/p&gt;



### _Mkdir

    boolean Core\Filestore\Backends\FileLocal::_Mkdir($pathname, integer $mode, boolean $recursive)

Makes directory

Advanced version of mkdir().  Will try to use ftp functions if provided by the configuration.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $pathname **mixed**
* $mode **integer** - &lt;p&gt;[optional] &lt;p&gt;
The mode is 0777 by default, which means the widest possible
access. For more information on modes, read the details
on the chmod page.
&lt;/p&gt;&lt;/p&gt;
&lt;p&gt;
mode is ignored on Windows.
&lt;/p&gt;
&lt;p&gt;
Note that you probably want to specify the mode as an octal number,
which means it should have a leading zero. The mode is also modified
by the current umask, which you can change using
umask.
&lt;/p&gt;
* $recursive **boolean** - &lt;p&gt;[optional] Default to false.&lt;/p&gt;



### _Rename

    mixed Core\Filestore\Backends\FileLocal::_Rename($oldpath, $newpath)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $oldpath **mixed**
* $newpath **mixed**



### _PutContents

    boolean Core\Filestore\Backends\FileLocal::_PutContents(string $filename, mixed $data)

Write a string to a file



* Visibility: **public**
* This method is **static**.


#### Arguments
* $filename **string** - &lt;p&gt;
Path to the file where to write the data.
&lt;/p&gt;
* $data **mixed** - &lt;p&gt;
The data to write. Can be either a string, an
array or a stream resource.
&lt;/p&gt;
&lt;p&gt;
If data is a stream resource, the
remaining buffer of that stream will be copied to the specified file.
This is similar with using stream_copy_to_stream.
&lt;/p&gt;
&lt;p&gt;
You can also specify the data parameter as a single
dimension array. This is equivalent to
file_put_contents($filename, implode(&#039;&#039;, $array)).
&lt;/p&gt;



### _resizeTo

    mixed Core\Filestore\Backends\FileLocal::_resizeTo(\Core\Filestore\File $file, integer $width, integer $height, string $mode)

Resize this image and save the output as another File object.

This is used on conjunction with getPreview* and getQuickPreview.
QuickPreview creates the destination file in the correct directory
and getPreview* methods request the actual resizing.

* Visibility: **private**


#### Arguments
* $file **[Core\Filestore\File](core_filestore_file.md)** - &lt;p&gt;The destination file&lt;/p&gt;
* $width **integer** - &lt;p&gt;Width of the final image (in px)&lt;/p&gt;
* $height **integer** - &lt;p&gt;Height of the final image (in px)&lt;/p&gt;
* $mode **string** - &lt;p&gt;Mode (part of the geometry)&lt;/p&gt;


