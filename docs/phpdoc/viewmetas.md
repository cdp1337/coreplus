ViewMetas
===============

The main controller for a set of controls, can be instantiated with either page level or inline operations,
it doesn&#039;t care which.




* Class name: ViewMetas
* Namespace: 
* This class implements: Iterator, ArrayAccess




Properties
----------


### $_links

    private mixed $_links = array()





* Visibility: **private**


### $_pos

    private mixed $_pos





* Visibility: **private**


Methods
-------


### current

    mixed ViewMetas::current()

(PHP 5 &gt;= 5.0.0)<br/>
Return the current element



* Visibility: **public**




### next

    void ViewMetas::next()

(PHP 5 &gt;= 5.0.0)<br/>
Move forward to next element



* Visibility: **public**




### key

    mixed ViewMetas::key()

(PHP 5 &gt;= 5.0.0)<br/>
Return the key of the current element



* Visibility: **public**




### valid

    boolean ViewMetas::valid()

(PHP 5 &gt;= 5.0.0)<br/>
Checks if current position is valid



* Visibility: **public**




### rewind

    void ViewMetas::rewind()

(PHP 5 &gt;= 5.0.0)<br/>
Rewind the Iterator to the first element



* Visibility: **public**




### offsetExists

    boolean ViewMetas::offsetExists(mixed $offset)

(PHP 5 &gt;= 5.0.0)<br/>
Whether a offset exists



* Visibility: **public**


#### Arguments
* $offset **mixed** - &lt;p&gt;
An offset to check for.
&lt;/p&gt;



### offsetGet

    mixed ViewMetas::offsetGet(mixed $offset)

(PHP 5 &gt;= 5.0.0)<br/>
Offset to retrieve



* Visibility: **public**


#### Arguments
* $offset **mixed** - &lt;p&gt;
The offset to retrieve.
&lt;/p&gt;



### offsetSet

    void ViewMetas::offsetSet(mixed $offset, mixed $value)

(PHP 5 &gt;= 5.0.0)<br/>
Offset to set



* Visibility: **public**


#### Arguments
* $offset **mixed** - &lt;p&gt;
The offset to assign the value to.
&lt;/p&gt;
* $value **mixed** - &lt;p&gt;
The value to set.
&lt;/p&gt;



### offsetUnset

    void ViewMetas::offsetUnset(mixed $offset)

(PHP 5 &gt;= 5.0.0)<br/>
Offset to unset



* Visibility: **public**


#### Arguments
* $offset **mixed** - &lt;p&gt;
The offset to unset.
&lt;/p&gt;



### addLinks

    mixed ViewMetas::addLinks(array $links)

Add an array of links to this control set.



* Visibility: **public**


#### Arguments
* $links **array**



### fetch

    array ViewMetas::fetch()

Get this meta set as an array of key-indexed elements

Each array key is the keyname of the meta tag, (description, keywords, etc).

* Visibility: **public**



