Core\Filestore\Contents\ContentCSV
===============

A short teaser of what \Core\Filestore\Contents\ContentCSV does.

More lengthy description of what \Core\Filestore\Contents\ContentCSV does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: ContentCSV
* Namespace: Core\Filestore\Contents
* This class implements: [Core\Filestore\Contents](core_filestore_contents.md)




Properties
----------


### $_hasheader

    public null $_hasheader = null

Has Header flag.

Set to null to try to guess if there is a header (default).
Set to true to force the first record to be picked up as a header.
Set to false to force no header.

* Visibility: **public**


### $_header

    private array $_header = null





* Visibility: **private**


### $_readerHandle

    private mixed $_readerHandle = null





* Visibility: **private**


### $_totalLines

    private null $_totalLines = null





* Visibility: **private**


Methods
-------


### __construct

    mixed Core\Filestore\Contents::__construct(\Core\Filestore\File $file)





* Visibility: **public**
* This method is defined by [Core\Filestore\Contents](core_filestore_contents.md)


#### Arguments
* $file **[Core\Filestore\File](core_filestore_file.md)**



### parse

    array Core\Filestore\Contents\ContentCSV::parse(string $delimiter, integer|null $lines)

Read an entire file into memory as an associative or indexes array.



* Visibility: **public**


#### Arguments
* $delimiter **string**
* $lines **integer|null** - &lt;p&gt;Set to an int to limit that number of lines to be returned.&lt;/p&gt;



### parseChunked

    array|false Core\Filestore\Contents\ContentCSV::parseChunked(string $delimiter, integer $lines)

Read up to N lines of a file into memory as an associative or indexes array.

Useful for large files!

* Visibility: **public**


#### Arguments
* $delimiter **string**
* $lines **integer**



### hasHeader

    boolean Core\Filestore\Contents\ContentCSV::hasHeader()

Actively do a simple check with heuristics on this file and see if it has a header.

(of just return the explicitly set value)

* Visibility: **public**




### getHeader

    array Core\Filestore\Contents\ContentCSV::getHeader()

Get the header columns



* Visibility: **public**




### getTotalLines

    integer Core\Filestore\Contents\ContentCSV::getTotalLines()

Get the total number of data lines in this CSV.

If there is a header, omit that, so 1000 lines with a header returns 999.

* Visibility: **public**



