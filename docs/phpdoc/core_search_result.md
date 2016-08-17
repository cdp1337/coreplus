Core\Search\Result
===============

A generic search result object.

Not extremely useful by itself, but acts as a useful base for other Result types!

<h3>Usage Examples</h3>


* Class name: Result
* Namespace: Core\Search
* This class implements: ArrayAccess




Properties
----------


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


### fetch

    string Core\Search\Result::fetch()

Get this result entry as rendered HTML.



* Visibility: **public**




### render

    void Core\Search\Result::render()

Write this result to STDOUT.



* Visibility: **public**




### offsetExists

    boolean Core\Search\Result::offsetExists(mixed $offset)

Whether an offset exists



* Visibility: **public**


#### Arguments
* $offset **mixed** - &lt;p&gt;An offset to check for.&lt;/p&gt;



### offsetGet

    mixed Core\Search\Result::offsetGet(mixed $offset)

Offset to retrieve

Alias of Model::get()

* Visibility: **public**


#### Arguments
* $offset **mixed** - &lt;p&gt;The offset to retrieve.&lt;/p&gt;



### offsetSet

    void Core\Search\Result::offsetSet(mixed $offset, mixed $value)

Offset to set

Alias of Model::set()

* Visibility: **public**


#### Arguments
* $offset **mixed** - &lt;p&gt;The offset to assign the value to.&lt;/p&gt;
* $value **mixed** - &lt;p&gt;The value to set.&lt;/p&gt;



### offsetUnset

    void Core\Search\Result::offsetUnset(mixed $offset)

Offset to unset

This just sets the value to null.

* Visibility: **public**


#### Arguments
* $offset **mixed** - &lt;p&gt;The offset to unset.&lt;/p&gt;


