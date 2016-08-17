FormPageInsertables
===============

Class FormPageInsertables




* Class name: FormPageInsertables
* Namespace: 
* Parent class: [FormGroup](formgroup.md)
* **Warning:** this class is **deprecated**. This means that this class will likely be removed in a future version.





Properties
----------


### $_selector

    private \FormPagePageSelectInput $_selector

A pointer to the page selector.

.. remember it here just to save on lookups later.

* Visibility: **private**
* **Warning:** this property is **deprecated**. This means that this property will likely be removed in a future version.


### $_elements

    protected mixed $_elements





* Visibility: **protected**
* **Warning:** this property is **deprecated**. This means that this property will likely be removed in a future version.


### $_attributes

    protected mixed $_attributes





* Visibility: **protected**
* **Warning:** this property is **deprecated**. This means that this property will likely be removed in a future version.


### $_validattributes

    protected mixed $_validattributes = array()





* Visibility: **protected**
* **Warning:** this property is **deprecated**. This means that this property will likely be removed in a future version.


### $requiresupload

    public boolean $requiresupload = false

Boolean if this form element requires a file upload.

Only "file" type elements should require this.

* Visibility: **public**
* **Warning:** this property is **deprecated**. This means that this property will likely be removed in a future version.


### $persistent

    public boolean $persistent = true





* Visibility: **public**
* **Warning:** this property is **deprecated**. This means that this property will likely be removed in a future version.


Methods
-------


### __construct

    mixed FormGroup::__construct($atts)





* Visibility: **public**
* This method is defined by [FormGroup](formgroup.md)


#### Arguments
* $atts **mixed**



### setTemplateName

    mixed FormPageInsertables::setTemplateName($templatename)





* Visibility: **public**
* **Warning:** this method is **deprecated**. This means that this method will likely be removed in a future version.


#### Arguments
* $templatename **mixed**



### save

    mixed FormPageInsertables::save()

Save the elements back to the database for the bound base_url.



* Visibility: **public**
* **Warning:** this method is **deprecated**. This means that this method will likely be removed in a future version.




### getTemplateName

    mixed FormGroup::getTemplateName()





* Visibility: **public**
* This method is defined by [FormGroup](formgroup.md)




### set

    mixed FormGroup::set($key, $value)





* Visibility: **public**
* This method is defined by [FormGroup](formgroup.md)


#### Arguments
* $key **mixed**
* $value **mixed**



### get

    mixed FormGroup::get($key)





* Visibility: **public**
* This method is defined by [FormGroup](formgroup.md)


#### Arguments
* $key **mixed**



### setFromArray

    mixed FormGroup::setFromArray($array)





* Visibility: **public**
* This method is defined by [FormGroup](formgroup.md)


#### Arguments
* $array **mixed**



### hasError

    mixed FormGroup::hasError()





* Visibility: **public**
* This method is defined by [FormGroup](formgroup.md)




### getErrors

    mixed FormGroup::getErrors()





* Visibility: **public**
* This method is defined by [FormGroup](formgroup.md)




### addElement

    mixed FormGroup::addElement($element, null|array $atts)

Add a given element, (or element type with attributes), onto this form or form group.



* Visibility: **public**
* This method is defined by [FormGroup](formgroup.md)


#### Arguments
* $element **mixed**
* $atts **null|array**



### addElementAfter

    mixed FormGroup::addElementAfter($newelement, $currentelement)





* Visibility: **public**
* This method is defined by [FormGroup](formgroup.md)


#### Arguments
* $newelement **mixed**
* $currentelement **mixed**



### switchElement

    mixed FormGroup::switchElement(\FormElement $oldelement, \FormElement $newelement)





* Visibility: **public**
* This method is defined by [FormGroup](formgroup.md)


#### Arguments
* $oldelement **[FormElement](formelement.md)**
* $newelement **[FormElement](formelement.md)**



### removeElement

    boolean FormGroup::removeElement(string $name)

Remove an element from the form by name.

Useful for automatically generated forms and working backwards instead of forward, (sometimes you only
want to remove one or two fields instead of creating twenty).

* Visibility: **public**
* This method is defined by [FormGroup](formgroup.md)


#### Arguments
* $name **string** - &lt;p&gt;The name of the element to remove.&lt;/p&gt;



### render

    mixed FormGroup::render()





* Visibility: **public**
* This method is defined by [FormGroup](formgroup.md)




### getClass

    string FormGroup::getClass()

Template helper function
gets the css class of the element.



* Visibility: **public**
* This method is defined by [FormGroup](formgroup.md)




### getID

    string FormGroup::getID()

Get the ID for this element, will either return the user-set ID, or an automatically generated one.



* Visibility: **public**
* This method is defined by [FormGroup](formgroup.md)




### getGroupAttributes

    string FormGroup::getGroupAttributes()

Template helper function
gets the input attributes as a string



* Visibility: **public**
* This method is defined by [FormGroup](formgroup.md)




### getElements

    array FormGroup::getElements(boolean $recursively, boolean $includegroups)

Get all elements in this group.



* Visibility: **public**
* This method is defined by [FormGroup](formgroup.md)


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
* This method is defined by [FormGroup](formgroup.md)


#### Arguments
* $nameRegex **mixed** - &lt;p&gt;string The regex-friendly name of the elements to return.&lt;/p&gt;



### getElement

    \FormElement FormGroup::getElement(string $name)

Lookup and return an element based on its name.

Shortcut of getElementByName()

* Visibility: **public**
* This method is defined by [FormGroup](formgroup.md)


#### Arguments
* $name **string** - &lt;p&gt;The name of the element to lookup.&lt;/p&gt;



### getElementByName

    \FormElement FormGroup::getElementByName(string $name)

Lookup and return an element based on its name.



* Visibility: **public**
* This method is defined by [FormGroup](formgroup.md)


#### Arguments
* $name **string** - &lt;p&gt;The name of the element to lookup.&lt;/p&gt;



### getElementValue

    mixed FormGroup::getElementValue(string $name)

Shortcut to get the child element's value



* Visibility: **public**
* This method is defined by [FormGroup](formgroup.md)


#### Arguments
* $name **string**


