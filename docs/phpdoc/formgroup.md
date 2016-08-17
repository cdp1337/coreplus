FormGroup
===============

Class FormGroup is the standard parent of any form or group of form elements that have children.




* Class name: FormGroup
* Namespace: 





Properties
----------


### $_elements

    protected mixed $_elements





* Visibility: **protected**


### $_attributes

    protected mixed $_attributes





* Visibility: **protected**


### $_validattributes

    protected mixed $_validattributes = array()





* Visibility: **protected**


### $requiresupload

    public boolean $requiresupload = false

Boolean if this form element requires a file upload.

Only "file" type elements should require this.

* Visibility: **public**


### $persistent

    public boolean $persistent = true





* Visibility: **public**


Methods
-------


### __construct

    mixed FormGroup::__construct($atts)





* Visibility: **public**


#### Arguments
* $atts **mixed**



### set

    mixed FormGroup::set($key, $value)





* Visibility: **public**


#### Arguments
* $key **mixed**
* $value **mixed**



### get

    mixed FormGroup::get($key)





* Visibility: **public**


#### Arguments
* $key **mixed**



### setFromArray

    mixed FormGroup::setFromArray($array)





* Visibility: **public**


#### Arguments
* $array **mixed**



### hasError

    mixed FormGroup::hasError()





* Visibility: **public**




### getErrors

    mixed FormGroup::getErrors()





* Visibility: **public**




### addElement

    mixed FormGroup::addElement($element, null|array $atts)

Add a given element, (or element type with attributes), onto this form or form group.



* Visibility: **public**


#### Arguments
* $element **mixed**
* $atts **null|array**



### addElementAfter

    mixed FormGroup::addElementAfter($newelement, $currentelement)





* Visibility: **public**


#### Arguments
* $newelement **mixed**
* $currentelement **mixed**



### switchElement

    mixed FormGroup::switchElement(\FormElement $oldelement, \FormElement $newelement)





* Visibility: **public**


#### Arguments
* $oldelement **[FormElement](formelement.md)**
* $newelement **[FormElement](formelement.md)**



### removeElement

    boolean FormGroup::removeElement(string $name)

Remove an element from the form by name.

Useful for automatically generated forms and working backwards instead of forward, (sometimes you only
want to remove one or two fields instead of creating twenty).

* Visibility: **public**


#### Arguments
* $name **string** - &lt;p&gt;The name of the element to remove.&lt;/p&gt;



### getTemplateName

    mixed FormGroup::getTemplateName()





* Visibility: **public**




### render

    mixed FormGroup::render()





* Visibility: **public**




### getClass

    string FormGroup::getClass()

Template helper function
gets the css class of the element.



* Visibility: **public**




### getID

    string FormGroup::getID()

Get the ID for this element, will either return the user-set ID, or an automatically generated one.



* Visibility: **public**




### getGroupAttributes

    string FormGroup::getGroupAttributes()

Template helper function
gets the input attributes as a string



* Visibility: **public**




### getElements

    array FormGroup::getElements(boolean $recursively, boolean $includegroups)

Get all elements in this group.



* Visibility: **public**


#### Arguments
* $recursively **boolean** - &lt;p&gt;Recurse into subgroups.&lt;/p&gt;
* $includegroups **boolean** - &lt;p&gt;Include those subgroups (if recursive is enabled)&lt;/p&gt;



### getElementsByName

    array FormGroup::getElementsByName($nameRegex)

Get all elements by *regex* name.

Useful for checkboxes, multi inputs, and other groups of input elements.

<h3>Example Usage</h3>
<code class="php"><pre>
The HTML form:
&lt;input name="values[123]"/&gt;
&lt;input name="values[124]"/&gt;
&lt;input name="values[125]"/&gt;

The PHP code:
$form->getElementsByName('values\[.*\]');
</pre></code>

* Visibility: **public**


#### Arguments
* $nameRegex **mixed** - &lt;p&gt;string The regex-friendly name of the elements to return.&lt;/p&gt;



### getElement

    \FormElement FormGroup::getElement(string $name)

Lookup and return an element based on its name.

Shortcut of getElementByName()

* Visibility: **public**


#### Arguments
* $name **string** - &lt;p&gt;The name of the element to lookup.&lt;/p&gt;



### getElementByName

    \FormElement FormGroup::getElementByName(string $name)

Lookup and return an element based on its name.



* Visibility: **public**


#### Arguments
* $name **string** - &lt;p&gt;The name of the element to lookup.&lt;/p&gt;



### getElementValue

    mixed FormGroup::getElementValue(string $name)

Shortcut to get the child element's value



* Visibility: **public**


#### Arguments
* $name **string**


