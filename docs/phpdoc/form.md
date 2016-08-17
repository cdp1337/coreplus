Form
===============

The main Form object.




* Class name: Form
* Namespace: 
* Parent class: [FormGroup](formgroup.md)





Properties
----------


### $originalurl

    public string $originalurl = ''





* Visibility: **public**


### $referrer

    public string $referrer = ''





* Visibility: **public**


### $Mappings

    public array $Mappings = array('access' => 'FormAccessStringInput', 'button' => 'FormButtonInput', 'checkbox' => 'FormCheckboxInput', 'checkboxes' => 'FormCheckboxesInput', 'date' => 'FormDateInput', 'datetime' => 'FormDateTimeInput', 'file' => 'FormFileInput', 'hidden' => 'FormHiddenInput', 'license' => 'FormLicenseInput', 'markdown' => 'FormMarkdownInput', 'pageinsertables' => 'FormPageInsertables', 'pagemeta' => 'FormPageMeta', 'pagemetas' => 'FormPageMetasInput', 'pagemetaauthor' => 'FormPageMetaAuthorInput', 'pagemetakeywords' => 'FormPageMetaKeywordsInput', 'pageparentselect' => 'FormPageParentSelectInput', 'pagerewriteurl' => 'FormPageRewriteURLInput', 'pagethemeselect' => 'FormPageThemeSelectInput', 'pagepageselect' => 'FormPagePageSelectInput', 'password' => 'FormPasswordInput', 'radio' => 'FormRadioInput', 'reset' => 'FormResetInput', 'select' => 'FormSelectInput', 'state' => 'FormStateInput', 'submit' => 'FormSubmitInput', 'system' => 'FormSystemInput', 'text' => 'FormTextInput', 'textarea' => 'FormTextareaInput', 'time' => 'FormTimeInput', 'user' => 'FormUserInput', 'wysiwyg' => 'FormTextareaInput')

Standard mappings for 'text' to class of the FormElement.

This can be extended, ie: wysiwyg or captcha.

* Visibility: **public**
* This property is **static**.


### $GroupMappings

    public mixed $GroupMappings = array('tabs' => 'FormTabsGroup')





* Visibility: **public**
* This property is **static**.


### $_models

    private array $_models = array()

A cache of the actual models attached via addModel().



* Visibility: **private**


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
* This method is defined by [FormGroup](formgroup.md)


#### Arguments
* $atts **mixed**



### getTemplateName

    mixed FormGroup::getTemplateName()





* Visibility: **public**
* This method is defined by [FormGroup](formgroup.md)




### generateUniqueHash

    string Form::generateUniqueHash()

Generate a unique hash for this form and return it as a flattened string.



* Visibility: **public**




### render

    mixed FormGroup::render()





* Visibility: **public**
* This method is defined by [FormGroup](formgroup.md)




### getGroup

    \FormGroup Form::getGroup(string $name, string $type)

Get a group by its name/title.

Will create the group if it does not exist.

* Visibility: **public**


#### Arguments
* $name **string** - &lt;p&gt;Name of the group to find/create&lt;/p&gt;
* $type **string** - &lt;p&gt;Type of group, used in conjunction with the GroupMappings array&lt;/p&gt;



### getModel

    \Model Form::getModel(string $prefix)

Get the associated model for this form, if there is one.

This model will also be populated automatically with all the data submitted.

* Visibility: **public**


#### Arguments
* $prefix **string** - &lt;p&gt;The prefix name to lookup the model with.&lt;/p&gt;



### getModels

    array Form::getModels()

Get the unmodified models that are attached to this form.



* Visibility: **public**




### loadFrom

    mixed Form::loadFrom(array $src, boolean $quiet)

Load this form's values from the provided array, usually GET or POST.

This is really an internal function that should not be called externally.

* Visibility: **public**


#### Arguments
* $src **array**
* $quiet **boolean** - &lt;p&gt;Set to true to squelch errors.&lt;/p&gt;



### addModel

    mixed Form::addModel(\Model $model, string $prefix)

Add a model's rendered elements to this form.

All models must have a common prefix, generally this is "model", but if multiple models are on one form,
 then different prefixes can be used.

* Visibility: **public**


#### Arguments
* $model **[Model](model.md)** - &lt;p&gt;The model to populate elements from&lt;/p&gt;
* $prefix **string** - &lt;p&gt;The prefix to create elements as&lt;/p&gt;



### addElement

    mixed FormGroup::addElement($element, null|array $atts)

Add a given element, (or element type with attributes), onto this form or form group.



* Visibility: **public**
* This method is defined by [FormGroup](formgroup.md)


#### Arguments
* $element **mixed**
* $atts **null|array**



### switchElementType

    boolean Form::switchElementType(string $elementname, string $newtype)

Switch an element type from one to another.

This is useful for doing some fine tuning on a pre-generated form, ie
 a "string" field in the Model should be interperuted as an image upload.

* Visibility: **public**


#### Arguments
* $elementname **string** - &lt;p&gt;The name of the element to switch&lt;/p&gt;
* $newtype **string** - &lt;p&gt;The standard name of the new element type&lt;/p&gt;



### saveToSession

    void Form::saveToSession()

Internal method to save a serialized version of this object
    into the database so it can be loaded upon submitting.

This is now public as of 2.4.1, but don't call it, seriously, leave it alone.  It doesn't want to talk to you.  EVAR!

* Visibility: **public**




### clearFromSession

    mixed Form::clearFromSession()





* Visibility: **public**




### CheckSavedSessionData

    null Form::CheckSavedSessionData()

Function that is fired off on page load.

This checks if a form was submitted and that form was present in the SESSION.

* Visibility: **public**
* This method is **static**.




### BuildFromModel

    \Form Form::BuildFromModel(\Model $model)

Scan through a standard Model object and populate elements with the correct fields and information.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $model **[Model](model.md)**



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


