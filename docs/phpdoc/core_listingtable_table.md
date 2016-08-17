Core\ListingTable\Table
===============

A short teaser of what Table does.

More lengthy description of what Table does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: Table
* Namespace: Core\ListingTable
* This class implements: Iterator




Properties
----------


### $_name

    private string $_name





* Visibility: **private**


### $_modelFactory

    private \ModelFactory $_modelFactory





* Visibility: **private**


### $_filters

    private \FilterForm $_filters





* Visibility: **private**


### $_editform

    private \Form $_editform





* Visibility: **private**


### $_columns

    private array $_columns = array()





* Visibility: **private**


### $_applied

    private boolean $_applied = false





* Visibility: **private**


### $_hassort

    private boolean $_hassort = false





* Visibility: **private**


### $_results

    private array $_results





* Visibility: **private**


### $_controls

    private null $_controls = null





* Visibility: **private**


Methods
-------


### __construct

    mixed Core\ListingTable\Table::__construct()





* Visibility: **public**




### getModelFactory

    \ModelFactory|null Core\ListingTable\Table::getModelFactory()

Get the underlying ModelFactory for this listing table, or null if none exists.



* Visibility: **public**




### getFilters

    \FilterForm Core\ListingTable\Table::getFilters()

Get the underlying FilterForm object to control this listing table's pagination and filters.

Will auto-create one if it doesn't exist.

* Visibility: **public**




### getFilterValue

    mixed Core\ListingTable\Table::getFilterValue($filter)

Get the value of the corresponding Filter element.



* Visibility: **public**


#### Arguments
* $filter **mixed** - &lt;p&gt;string&lt;/p&gt;



### getListings

    array|\Model|null Core\ListingTable\Table::getListings()

Get any/all the listings on this table.



* Visibility: **public**




### getTotalCount

    integer|null Core\ListingTable\Table::getTotalCount()

Get the total count for the number of records filtered.



* Visibility: **public**




### getEditForm

    \Form Core\ListingTable\Table::getEditForm()

Get the edit form for this listing table.



* Visibility: **public**




### getControls

    \ViewControls Core\ListingTable\Table::getControls()

Get the controls for this table, if any.



* Visibility: **public**




### addFilter

    mixed Core\ListingTable\Table::addFilter(string $type, array $atts)

Add a new filter for this listing table.



* Visibility: **public**


#### Arguments
* $type **string**
* $atts **array**



### addColumn

    mixed Core\ListingTable\Table::addColumn(string $title, string|null $sortkey, boolean $visible)

Add a new Column onto this Listing Table.

Will handle the filter sortable work automatically.

* Visibility: **public**


#### Arguments
* $title **string**
* $sortkey **string|null**
* $visible **boolean**



### setModelName

    mixed Core\ListingTable\Table::setModelName(string $name)

Set the model name, (and the underlying Factory object).



* Visibility: **public**


#### Arguments
* $name **string**



### setModelFactory

    mixed Core\ListingTable\Table::setModelFactory(\ModelFactory $factory)

Set the model factory itself.

This is useful if it's a child model from another Model.

* Visibility: **public**


#### Arguments
* $factory **[ModelFactory](modelfactory.md)**



### setName

    mixed Core\ListingTable\Table::setName(string $name)

Set the name for this table (and the corresponding filters), required for any session saving/lookup.



* Visibility: **public**


#### Arguments
* $name **string**



### setEditFormCaller

    mixed Core\ListingTable\Table::setEditFormCaller(string $method)

Set the callsmethod attribute on the edit form.



* Visibility: **public**


#### Arguments
* $method **string**



### setLimit

    mixed Core\ListingTable\Table::setLimit($limit)

Set the limit of results to show before pagination kicks in.



* Visibility: **public**


#### Arguments
* $limit **mixed**



### setDefaultSort

    mixed Core\ListingTable\Table::setDefaultSort(string $key, string $direction)

Set the default sort key and direction.

This should be done prior to loading the results!

* Visibility: **public**


#### Arguments
* $key **string** - &lt;p&gt;The key of the column to sort by&lt;/p&gt;
* $direction **string** - &lt;p&gt;&quot;DESC&quot; or &quot;ASC&quot; for descending or ascending sort&lt;/p&gt;



### addControl

    mixed Core\ListingTable\Table::addControl(string|array $title, string $link, string|array $class)

Add a control into the page template.

Useful for embedding functions and administrative utilities inline without having to adjust the
application template.

* Visibility: **public**


#### Arguments
* $title **string|array** - &lt;p&gt;The title to set for this control&lt;/p&gt;
* $link **string** - &lt;p&gt;The link to set for this control&lt;/p&gt;
* $class **string|array** - &lt;p&gt;The class name or array of attributes to set on this control
                           If this is an array, it should be an associative array for the advanced parameters&lt;/p&gt;



### loadFiltersFromRequest

    mixed Core\ListingTable\Table::loadFiltersFromRequest(\PageRequest|null $request)

Load the underlying Filters from a given request, (optionally).



* Visibility: **public**


#### Arguments
* $request **[PageRequest](pagerequest.md)|null**



### render

    mixed Core\ListingTable\Table::render($section)





* Visibility: **public**


#### Arguments
* $section **mixed**



### sendCSVHeader

    mixed Core\ListingTable\Table::sendCSVHeader(\View $view, string $title)

Send a CSV header and setup all necessary options to the View object to provide a download.

All the data headers will be rendered automatically, (with the exception of the final 'controls' column).

* Visibility: **public**


#### Arguments
* $view **[View](view.md)** - &lt;p&gt;Page view to manipulate&lt;/p&gt;
* $title **string** - &lt;p&gt;Title of the file, (will get converted to a valid URL)&lt;/p&gt;



### sendCSVRecord

    mixed Core\ListingTable\Table::sendCSVRecord(array $data)

Send an indexed array to the browser as a valid CSV record.

To be used in conjunction with sendCSVHeader().

All scalar data in the array will be sanitized automatically.

* Visibility: **public**


#### Arguments
* $data **array**



### rewind

    mixed Core\ListingTable\Table::rewind()





* Visibility: **public**




### current

    mixed Core\ListingTable\Table::current()





* Visibility: **public**




### key

    mixed Core\ListingTable\Table::key()





* Visibility: **public**




### next

    mixed Core\ListingTable\Table::next()





* Visibility: **public**




### valid

    mixed Core\ListingTable\Table::valid()





* Visibility: **public**




### _renderHead

    string Core\ListingTable\Table::_renderHead()

Render this table's head content, (everything above the records).



* Visibility: **private**




### _renderFilters

    string Core\ListingTable\Table::_renderFilters()

Render the filters



* Visibility: **private**




### _renderPagination

    string Core\ListingTable\Table::_renderPagination()

Render the pagination options.



* Visibility: **private**




### _renderFoot

    string Core\ListingTable\Table::_renderFoot()

Render this table's foot content, (everything below the records).



* Visibility: **private**



