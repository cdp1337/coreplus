ViewControl
===============

Just a tiny class for handling control links in the main page view.

These are usually tiny icons or snippets of text that provide a bit of inline administrative
functionality for pages.


* Class name: ViewControl
* Namespace: 
* This class implements: ArrayAccess




Properties
----------


### $link

    public string $link = '#'

Link for this control



* Visibility: **public**


### $title

    public string $title = ''

Title for this control



* Visibility: **public**


### $class

    public string $class = ''

CSS class name for this control



* Visibility: **public**


### $icon

    public string $icon = ''

Icon class name for this control

Set to blank to omit the icon

* Visibility: **public**


### $confirm

    public string $confirm = null

Confirm text for this link, useful for setting them as POST links.



* Visibility: **public**


### $otherattributes

    public array $otherattributes = array()

Any other attributes for the a tag.



* Visibility: **public**


Methods
-------


### fetch

    string ViewControl::fetch()

Get this control as HTML



* Visibility: **public**




### _fetchA

    string ViewControl::_fetchA()

Fetch the A tag for this element.

This is broken out into its own function since it has a decent amount of logic contained herein.

* Visibility: **private**




### set

    mixed ViewControl::set($key, $value)





* Visibility: **public**


#### Arguments
* $key **mixed**
* $value **mixed**



### offsetExists

    boolean ViewControl::offsetExists(mixed $offset)

Whether an offset exists



* Visibility: **public**


#### Arguments
* $offset **mixed** - &lt;p&gt;An offset to check for.&lt;/p&gt;



### offsetGet

    mixed ViewControl::offsetGet(mixed $offset)

Offset to retrieve



* Visibility: **public**


#### Arguments
* $offset **mixed** - &lt;p&gt;The offset to retrieve.&lt;/p&gt;



### offsetSet

    void ViewControl::offsetSet(mixed $offset, mixed $value)

Offset to set

Alias of Model::set()

* Visibility: **public**


#### Arguments
* $offset **mixed** - &lt;p&gt;The offset to assign the value to.&lt;/p&gt;
* $value **mixed** - &lt;p&gt;The value to set.&lt;/p&gt;



### offsetUnset

    void ViewControl::offsetUnset(mixed $offset)

Offset to unset

This actually doesn't do anything.

* Visibility: **public**


#### Arguments
* $offset **mixed** - &lt;p&gt;The offset to unset.&lt;/p&gt;


