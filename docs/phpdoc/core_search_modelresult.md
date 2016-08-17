Core\Search\ModelResult
===============

A search result from a model.

Primary difference is that it uses a template to render the Model instead of just the basic title.

<h3>Usage Examples</h3>


* Class name: ModelResult
* Namespace: Core\Search
* Parent class: [Core\Search\Result](core_search_result.md)





Properties
----------


### $_model

    public \Model $_model





* Visibility: **public**


### $title

    public string $title





* Visibility: **public**


### $link

    public string $link





* Visibility: **public**


### $query

    public string $query





* Visibility: **public**


### $relevancy

    public float $relevancy = 0.0





* Visibility: **public**


Methods
-------


### __construct

    mixed Core\Search\ModelResult::__construct(string $query, \Model $model)





* Visibility: **public**


#### Arguments
* $query **string** - &lt;p&gt;The original query, used to calculate the relevancy.&lt;/p&gt;
* $model **[Model](model.md)** - &lt;p&gt;The located model.&lt;/p&gt;



### fetch

    string Core\Search\Result::fetch()

Get this result entry as rendered HTML.



* Visibility: **public**
* This method is defined by [Core\Search\Result](core_search_result.md)




### _calculateRelevancy

    mixed Core\Search\ModelResult::_calculateRelevancy()





* Visibility: **private**




### render

    void Core\Search\Result::render()

Write this result to STDOUT.



* Visibility: **public**
* This method is defined by [Core\Search\Result](core_search_result.md)




### offsetExists

    boolean Core\Search\Result::offsetExists(mixed $offset)

Whether an offset exists



* Visibility: **public**
* This method is defined by [Core\Search\Result](core_search_result.md)


#### Arguments
* $offset **mixed** - &lt;p&gt;An offset to check for.&lt;/p&gt;



### offsetGet

    mixed Core\Search\Result::offsetGet(mixed $offset)

Offset to retrieve

Alias of Model::get()

* Visibility: **public**
* This method is defined by [Core\Search\Result](core_search_result.md)


#### Arguments
* $offset **mixed** - &lt;p&gt;The offset to retrieve.&lt;/p&gt;



### offsetSet

    void Core\Search\Result::offsetSet(mixed $offset, mixed $value)

Offset to set

Alias of Model::set()

* Visibility: **public**
* This method is defined by [Core\Search\Result](core_search_result.md)


#### Arguments
* $offset **mixed** - &lt;p&gt;The offset to assign the value to.&lt;/p&gt;
* $value **mixed** - &lt;p&gt;The value to set.&lt;/p&gt;



### offsetUnset

    void Core\Search\Result::offsetUnset(mixed $offset)

Offset to unset

This just sets the value to null.

* Visibility: **public**
* This method is defined by [Core\Search\Result](core_search_result.md)


#### Arguments
* $offset **mixed** - &lt;p&gt;The offset to unset.&lt;/p&gt;


