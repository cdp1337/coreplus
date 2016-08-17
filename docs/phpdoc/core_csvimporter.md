Core\CSVImporter
===============






* Class name: CSVImporter
* Namespace: Core





Properties
----------


### $counts

    public mixed $counts = null





* Visibility: **public**


### $filename

    public mixed $filename = null





* Visibility: **public**


### $key

    public mixed $key = null





* Visibility: **public**


### $sessionKey

    public mixed $sessionKey = null





* Visibility: **public**


### $columns

    public mixed $columns = array()





* Visibility: **public**


### $aliases

    public mixed $aliases = array()





* Visibility: **public**


### $maps

    public mixed $maps = null





* Visibility: **public**


### $hasHeader

    public mixed $hasHeader = null





* Visibility: **public**


### $_file

    private mixed $_file = null





* Visibility: **private**


### $_obj

    private \Core\Filestore\Contents\ContentCSV $_obj = null





* Visibility: **private**


Methods
-------


### __construct

    mixed Core\CSVImporter::__construct($key)

Construct a new object with a session key that will get used throughout the import process.



* Visibility: **public**


#### Arguments
* $key **mixed**



### addColumn

    mixed Core\CSVImporter::addColumn(string $key, null|string $title)

Add a named column, (and optionally a nice title), for the user to select from.

Only named columns are returned in the getChunk operation!

* Visibility: **public**


#### Arguments
* $key **string** - &lt;p&gt;The key name for this named column, (is used in the returning chunk array).&lt;/p&gt;
* $title **null|string** - &lt;p&gt;Optionally a human-friendly name for this named column to display in the selection dropdown.&lt;/p&gt;



### addAlias

    mixed Core\CSVImporter::addAlias(string $key, string $alias)

Add an alias for a named column that may be used by common spreadsheets.

Used if you have a field "email", whereas the CSV has a column "email_address".

* Visibility: **public**


#### Arguments
* $key **string** - &lt;p&gt;The key name of the column, (that must already be registered)&lt;/p&gt;
* $alias **string** - &lt;p&gt;The alias to match also for this named column, (gets remapped to the base key name).&lt;/p&gt;



### setFile

    mixed Core\CSVImporter::setFile(\Core\Filestore\File $file)

Set the file for this importer, usually done automatically.



* Visibility: **public**


#### Arguments
* $file **[Core\Filestore\File](core_filestore_file.md)**



### abortAndDestroy

    mixed Core\CSVImporter::abortAndDestroy()

Cleanup the process and remove all temporary files.



* Visibility: **public**




### render

    string Core\CSVImporter::render()

Get the rendered HTML of this process, based on the current stage where it's at.



* Visibility: **public**




### getStep

    integer Core\CSVImporter::getStep()

Get which step this import is on, 1-3.

1: no file uploaded, present the option to upload.
2: file uploaded and saved to temp; give the user options to select the column mapping.
3: Mapping available and import ready to start.

* Visibility: **public**




### getChunk

    array|false Core\CSVImporter::getChunk(integer $lines)

Get a chunk of N lines from the CSV.

This array will contain sub arrays that are keyed with the named columns and have their respective content for that record.

Will return false when at the end of the file.

* Visibility: **public**


#### Arguments
* $lines **integer**



### getTotalRecords

    integer Core\CSVImporter::getTotalRecords()

Get the total records that are to be imported.



* Visibility: **public**




### _renderImport1

    mixed Core\CSVImporter::_renderImport1()





* Visibility: **private**




### _renderImport2

    mixed Core\CSVImporter::_renderImport2()





* Visibility: **private**




### _renderImport3

    mixed Core\CSVImporter::_renderImport3()





* Visibility: **private**




### FormHandler1

    boolean Core\CSVImporter::FormHandler1(\Form $form)

Handler to save the CSV file locally.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $form **[Form](form.md)**



### FormHandler2

    mixed Core\CSVImporter::FormHandler2(\Form $form)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $form **[Form](form.md)**


