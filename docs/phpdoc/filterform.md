FilterForm
===============

Filter system




* Class name: FilterForm
* Namespace: 



Constants
----------


### LINK_TYPE_STANDARD

    const LINK_TYPE_STANDARD = ' = '





### LINK_TYPE_GT

    const LINK_TYPE_GT = ' > '





### LINK_TYPE_GE

    const LINK_TYPE_GE = ' >= '





### LINK_TYPE_LT

    const LINK_TYPE_LT = ' < '





### LINK_TYPE_LE

    const LINK_TYPE_LE = ' <= '





### LINK_TYPE_STARTSWITH

    const LINK_TYPE_STARTSWITH = '_startswith_'





### LINK_TYPE_CONTAINS

    const LINK_TYPE_CONTAINS = '_contains_'





Properties
----------


### $hassort

    public boolean $hassort = false

Set to true to look for (and remember), sortkey and sortdir as well.



* Visibility: **public**


### $haspagination

    public boolean $haspagination = false

Set to true to look for (and remember), the pagination values.



* Visibility: **public**


### $_name

    private mixed $_name = null





* Visibility: **private**


### $_elements

    private mixed $_elements = array()





* Visibility: **private**


### $_elementindexes

    private mixed $_elementindexes = array()





* Visibility: **private**


### $_sortkeys

    private null $_sortkeys = null

null or an array of valid sort keys, the first being the default.



* Visibility: **private**


### $_sortkey

    private null $_sortkey = null

null or the sort key of the current view.



* Visibility: **private**


### $_sortdir

    private string $_sortdir = 'down'

Sorting direction, think ascending or descending.



* Visibility: **private**


### $_limitOptions

    private array $_limitOptions = array(25, 50, 100, 250, 500)





* Visibility: **private**


### $_limit

    private integer $_limit = 50





* Visibility: **private**


### $_total

    private null $_total = null

The total number of entries for this filterset.  Set automatically from applyToFactory and callable externally.



* Visibility: **private**


### $_currentpage

    private integer $_currentpage = 1

The current page, only takes effect if $haspagination is set to true.



* Visibility: **private**


Methods
-------


### __construct

    mixed FilterForm::__construct()

Create a new filter form object



* Visibility: **public**




### setName

    mixed FilterForm::setName(string $filtername)

Set the name for this filter, required for any session saving/lookup.



* Visibility: **public**


#### Arguments
* $filtername **string**



### addElement

    mixed FilterForm::addElement(string|\FormElement $element, null|array $atts)

Add an element to this filter set.

This is the exact same as the native Form system.

* Visibility: **public**


#### Arguments
* $element **string|[string](formelement.md)** - &lt;p&gt;Type of element, (or the form element itself)&lt;/p&gt;
* $atts **null|array** - &lt;p&gt;[optional] An associative array of parameters for this form element&lt;/p&gt;



### load

    mixed FilterForm::load(\PageRequest $request)

Load the values from either the page request or the session data.



* Visibility: **public**


#### Arguments
* $request **[PageRequest](pagerequest.md)**



### loadSession

    mixed FilterForm::loadSession()

Load the values from the session data.

This is automatically called by the load function.

* Visibility: **public**




### render

    string FilterForm::render()

Fetch this filter set as a string

(should probably be called fetch, but whatever)

* Visibility: **public**




### renderReadonly

    string FilterForm::renderReadonly()

Fetch this filter set as an HTML string

This result set will be readonly however!

* Visibility: **public**




### hasSet

    boolean FilterForm::hasSet()

Return true/false on if this filter has any filters set by the user.

Essentially will just check if all elements have their value set to "" or to null.

* Visibility: **public**




### hasFilters

    boolean FilterForm::hasFilters()

Return true or false if this filterset has any filters set.

Useful for detecting if the filters HTML should be rendered.

* Visibility: **public**




### pagination

    string FilterForm::pagination()

Fet this filter set's pagination options as a string.



* Visibility: **public**




### get

    mixed|null FilterForm::get(string $name)

Get one element's value based on its name



* Visibility: **public**


#### Arguments
* $name **string** - &lt;p&gt;The name of the element to retrieve&lt;/p&gt;



### setSortDirection

    boolean FilterForm::setSortDirection($dir)

Set the sort direction for this filterset.

The direction must be one of the following: "up", "down", "asc", "desc".
(asc/desc are remapped to up/down automatically)

* Visibility: **public**


#### Arguments
* $dir **mixed** - &lt;p&gt;&quot;up&quot; or &quot;down&quot;.  It must be up or down because fontawesome has those keys instead of &quot;asc&quot; and &quot;desc&quot; :p&lt;/p&gt;



### getSortDirection

    string|null FilterForm::getSortDirection()

Get the sort direction, either up or down.



* Visibility: **public**




### getSortKey

    null|string FilterForm::getSortKey()

Get the sort key.



* Visibility: **public**




### addSortKey

    boolean FilterForm::addSortKey(string $key)

Add a single sort key onto this Filter.



* Visibility: **public**


#### Arguments
* $key **string**



### setSortkeys

    boolean FilterForm::setSortkeys(array $arr)

Set the valid sort keys for this filterset.



* Visibility: **public**


#### Arguments
* $arr **array**



### setSortKey

    boolean FilterForm::setSortKey(string $key)

Set the active sort key currently.

If sotkeys is populated, it MUST be one of the keys in that array!

* Visibility: **public**


#### Arguments
* $key **string**



### getTotalCount

    integer|null FilterForm::getTotalCount()

Get the total count for the number of records filtered.



* Visibility: **public**




### setLimit

    mixed FilterForm::setLimit(integer $limit)

Set the limit for pagination, will default to 50.



* Visibility: **public**


#### Arguments
* $limit **integer**



### setPage

    mixed FilterForm::setPage($page)





* Visibility: **public**


#### Arguments
* $page **mixed**



### getOrder

    mixed FilterForm::getOrder()

Get the sort keys as an order clause, passable into the dataset system.



* Visibility: **public**




### setTotalCount

    mixed FilterForm::setTotalCount(integer $count)

Set the total count for the number of records.

This is externally available in case the factory is modified externally, which is perfectly allowed.

* Visibility: **public**


#### Arguments
* $count **integer**



### applyToFactory

    mixed FilterForm::applyToFactory(\ModelFactory $factory)

Given all the user defined filter, sort, and what not, apply those values to the ModelFactory if possible.



* Visibility: **public**


#### Arguments
* $factory **[ModelFactory](modelfactory.md)**



### _render

    mixed FilterForm::_render($readonly)





* Visibility: **private**


#### Arguments
* $readonly **mixed**


