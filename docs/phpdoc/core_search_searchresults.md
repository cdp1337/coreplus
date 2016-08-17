Core\Search\SearchResults
===============

A short teaser of what SearchResults does.

More lengthy description of what SearchResults does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: SearchResults
* Namespace: Core\Search





Properties
----------


### $haspagination

    public boolean $haspagination = false

Set to true to look for (and remember), the pagination values.



* Visibility: **public**


### $query

    public string $query = ''

The query for this search result query.

Optional, but can be used by tracking software.

* Visibility: **public**


### $_results

    private array $_results = array()





* Visibility: **private**


### $_currentpage

    private integer $_currentpage = 1

The current page, only takes effect if $haspagination is set to true.



* Visibility: **private**


### $_limit

    private integer $_limit = 50

The limit for this search results, only takes effect if $haspagination is set to true.



* Visibility: **private**


### $_sorted

    private boolean $_sorted = false





* Visibility: **private**


Methods
-------


### addResults

    mixed Core\Search\SearchResults::addResults(array $results)

Add a set of results onto this search



* Visibility: **public**


#### Arguments
* $results **array**



### addResult

    mixed Core\Search\SearchResults::addResult(\Core\Search\Result $result)

Add a single result onto this search.

Useful if a foreach operation is required prior to adding to the stack.

* Visibility: **public**


#### Arguments
* $result **[Core\Search\Result](core_search_result.md)**



### sortResults

    void Core\Search\SearchResults::sortResults()

Method to sort the results added by relevancy.



* Visibility: **public**




### get

    array Core\Search\SearchResults::get()

Get the array of results.



* Visibility: **public**




### getCount

    integer Core\Search\SearchResults::getCount()

Get the total size of the results matched.



* Visibility: **public**




### render

    mixed Core\Search\SearchResults::render()

Display the results as rendered HTML.



* Visibility: **public**



