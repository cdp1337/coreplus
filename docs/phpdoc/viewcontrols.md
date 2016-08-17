ViewControls
===============

The main controller for a set of controls, can be instantiated with either page level or inline operations,
it doesn&#039;t care which.




* Class name: ViewControls
* Namespace: 
* This class implements: Iterator, ArrayAccess




Properties
----------


### $hovercontext

    public boolean $hovercontext = true

Set to true to set this control to be a hover context menu, (if available in the theme).



* Visibility: **public**


### $_links

    private mixed $_links = array()





* Visibility: **private**


### $_pos

    private mixed $_pos





* Visibility: **private**


### $_data

    private mixed $_data = array()





* Visibility: **private**


Methods
-------


### current

    mixed ViewControls::current()

(PHP 5 &gt;= 5.0.0)<br/>
Return the current element



* Visibility: **public**




### next

    void ViewControls::next()

(PHP 5 &gt;= 5.0.0)<br/>
Move forward to next element



* Visibility: **public**




### key

    mixed ViewControls::key()

(PHP 5 &gt;= 5.0.0)<br/>
Return the key of the current element



* Visibility: **public**




### valid

    boolean ViewControls::valid()

(PHP 5 &gt;= 5.0.0)<br/>
Checks if current position is valid



* Visibility: **public**




### rewind

    void ViewControls::rewind()

(PHP 5 &gt;= 5.0.0)<br/>
Rewind the Iterator to the first element



* Visibility: **public**




### offsetExists

    boolean ViewControls::offsetExists(mixed $offset)

(PHP 5 &gt;= 5.0.0)<br/>
Whether a offset exists



* Visibility: **public**


#### Arguments
* $offset **mixed** - &lt;p&gt;
An offset to check for.
&lt;/p&gt;



### offsetGet

    mixed ViewControls::offsetGet(mixed $offset)

(PHP 5 &gt;= 5.0.0)<br/>
Offset to retrieve



* Visibility: **public**


#### Arguments
* $offset **mixed** - &lt;p&gt;
The offset to retrieve.
&lt;/p&gt;



### offsetSet

    void ViewControls::offsetSet(mixed $offset, mixed $value)

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

    void ViewControls::offsetUnset(mixed $offset)

(PHP 5 &gt;= 5.0.0)<br/>
Offset to unset



* Visibility: **public**


#### Arguments
* $offset **mixed** - &lt;p&gt;
The offset to unset.
&lt;/p&gt;



### addLinks

    mixed ViewControls::addLinks(array $links)

Add an array of links to this control set.



* Visibility: **public**


#### Arguments
* $links **array**



### addLink

    mixed ViewControls::addLink(\ViewControl $link)

Add a single link to this control set.

Useful for the times when you cannot access the ViewControls as an array.

* Visibility: **public**


#### Arguments
* $link **[ViewControl](viewcontrol.md)**



### fetch

    string ViewControls::fetch()

Get this control set as HTML



* Visibility: **public**




### hasLinks

    boolean ViewControls::hasLinks()

Check if this control set has any links in it.

Useful for templates where you don't want to render the container if there is nothing to render inside.

* Visibility: **public**




### setProxyText

    mixed ViewControls::setProxyText($text)





* Visibility: **public**


#### Arguments
* $text **mixed**



### setProxyForce

    mixed ViewControls::setProxyForce($force)





* Visibility: **public**


#### Arguments
* $force **mixed**



### Dispatch

    \ViewControls ViewControls::Dispatch(string $baseurl, mixed $subject)

Shortcut function to dispatch the /core/controllinks hook to request functions for a given subject.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $baseurl **string** - &lt;p&gt;The baseurl, (excluding /core/controllinks), of the request&lt;/p&gt;
* $subject **mixed** - &lt;p&gt;The subject matter of this hook, (if any)&lt;/p&gt;



### DispatchModel

    \ViewControls ViewControls::DispatchModel(\Model $model)

Shortcut function to dispatch the /core/controllinks hook to request functions for a given subject.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $model **[Model](model.md)** - &lt;p&gt;The subject matter of this hook, (if any)&lt;/p&gt;



### DispatchAndFetch

    string ViewControls::DispatchAndFetch(string $baseurl, mixed $subject)

Shortcut function to dispatch the /core/controllinks hook to request functions for a given subject.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $baseurl **string** - &lt;p&gt;The baseurl, (excluding /core/controllinks), of the request&lt;/p&gt;
* $subject **mixed** - &lt;p&gt;The subject matter of this hook, (if any)&lt;/p&gt;


