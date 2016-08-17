FormDateTimeInput
===============

Class FormDateTimeInput provides a jQuery datepicker with time added on.

<h3>Parameters</h3>

<p>
All the options from the <a href="http://api.jqueryui.com/datepicker/" target="_BLANK">official jQuery date picker API</a>
are supported, simply pass them in as attributes.
</p>

<p>
Besides the jquery options, the datetime picker also supports several additional parameters, (see below).
</p>

<h4>displayformat</h4>
<p>
The "displayformat" option controls how a timestamp is formatted to display on the form.
This string should match the <a href="http://php.net/manual/en/function.date.php" target="_BLANK">format provided at php.net</a>.
</p>

<p>
This timestamp is converted from GMT to the user's default timezone automatically.
</p>

<h4>saveformat</h4>
<p>
Generally used alongside "displayformat".  Useful for taking a human-readable string and converting that back to a machine timestamp.
Set this to "U" to achieve this.
</p>

<p>
This string is converted from the user's default timezone to the value of <pre>savetimezone</pre> automatically.
</p>

<h4>savetimezone</h4>
<p>
The timezone to convert and save the time as.
Defaults to GMT since most times are saved as their GMT version.
However, if an actual date is saved to be used relatively to the user's local timezone, it may be more effective to save as a relative timezone.
</p>

<p>
Pass in <pre>Time::TIMEZONE_USER</pre> for the user's timezone, or any other valid timezone option.
</p>


* Class name: FormDateTimeInput
* Namespace: 
* Parent class: [FormTextInput](formtextinput.md)





Properties
----------


### $_javascriptconstructorstring

    public string $_javascriptconstructorstring = ''

The javascript construction string that's used in the javascript.



* Visibility: **public**


### $_attributes

    protected array $_attributes = array()

Array of attributes for this form element object.

Should be in key/value pair.

* Visibility: **protected**


### $_error

    protected mixed $_error





* Visibility: **protected**


### $_validattributes

    protected array $_validattributes = array()

Array of attributes to automatically return when getInputAttributes() is called.



* Visibility: **protected**


### $requiresupload

    public boolean $requiresupload = false

Boolean if this form element requires a file upload.

Only "file" type elements should require this.

* Visibility: **public**


### $validation

    public string $validation = null

An optional validation check for this element.

This can be multiple things, such as:

"/blah/" - Evaluated with preg_match.
"#blah#" - Also evaluated with preg_match.
"MyFoo::Blah" - Evaluated with call_user_func.

* Visibility: **public**


### $validationmessage

    public string $validationmessage = null

An optional message to post if the validation check fails.



* Visibility: **public**


### $persistent

    public boolean $persistent = true





* Visibility: **public**


### $classnames

    public mixed $classnames = array()





* Visibility: **public**


### $parent

    public null $parent = null





* Visibility: **public**


Methods
-------


### render

    string FormElement::render()

Render this form element and return the resulting HTML as a string



* Visibility: **public**
* This method is defined by [FormElement](formelement.md)




### setValue

    boolean FormElement::setValue(mixed $value)

This set explicitly handles the value, and has the extended logic required
 for error checking and validation.



* Visibility: **public**
* This method is defined by [FormElement](formelement.md)


#### Arguments
* $value **mixed** - &lt;p&gt;The value to set&lt;/p&gt;



### __construct

    mixed FormElement::__construct($atts)





* Visibility: **public**
* This method is defined by [FormElement](formelement.md)


#### Arguments
* $atts **mixed**



### getInputAttributes

    string FormElement::getInputAttributes()

Template helper function
gets the input attributes as a string



* Visibility: **public**
* This method is defined by [FormElement](formelement.md)




### set

    mixed FormElement::set($key, $value)





* Visibility: **public**
* This method is defined by [FormElement](formelement.md)


#### Arguments
* $key **mixed**
* $value **mixed**



### get

    mixed FormElement::get(string $key)

Get the requested attribute from this form element.



* Visibility: **public**
* This method is defined by [FormElement](formelement.md)


#### Arguments
* $key **string**



### getAsArray

    array FormElement::getAsArray()

Get all attributes of this form element as a flat array.



* Visibility: **public**
* This method is defined by [FormElement](formelement.md)




### setFromArray

    mixed FormElement::setFromArray($array)





* Visibility: **public**
* This method is defined by [FormElement](formelement.md)


#### Arguments
* $array **mixed**



### validate

    string|boolean FormElement::validate(mixed $value)

Validate a given value for this form element.

Will use the extendable validation logic if provided.

* Visibility: **public**
* This method is defined by [FormElement](formelement.md)


#### Arguments
* $value **mixed**



### getValueTitle

    string FormElement::getValueTitle()

Get the value of this element as a string
In select options, this will be the label of the option.



* Visibility: **public**
* This method is defined by [FormElement](formelement.md)




### hasError

    boolean FormElement::hasError()

Simple check to see if there is an error set on this form element.

True: there is an error.
False: no error present.

* Visibility: **public**
* This method is defined by [FormElement](formelement.md)




### getError

    string|false FormElement::getError()

Get the error string, or null if there is no error.



* Visibility: **public**
* This method is defined by [FormElement](formelement.md)




### setError

    mixed FormElement::setError(string $err, boolean $displayMessage)

Set the error message for this form element, optionally displaying it to the browser.



* Visibility: **public**
* This method is defined by [FormElement](formelement.md)


#### Arguments
* $err **string**
* $displayMessage **boolean**



### clearError

    mixed FormElement::clearError()





* Visibility: **public**
* This method is defined by [FormElement](formelement.md)




### getTemplateName

    mixed FormElement::getTemplateName()





* Visibility: **public**
* This method is defined by [FormElement](formelement.md)




### getClass

    string FormElement::getClass()

Template helper function
gets the css class of the element.



* Visibility: **public**
* This method is defined by [FormElement](formelement.md)




### getID

    string FormElement::getID()

Get the ID for this element, will either return the user-set ID, or an automatically generated one.



* Visibility: **public**
* This method is defined by [FormElement](formelement.md)




### lookupValueFrom

    mixed FormElement::lookupValueFrom(array $src)

Lookup the value from $src array for this given element.

Handles all name/array resolution automatically.

Note, this does NOT set the value, only looks up the value from the array.

* Visibility: **public**
* This method is defined by [FormElement](formelement.md)


#### Arguments
* $src **array**



### Factory

    \FormElement FormElement::Factory(string $type, array $attributes)

Get the appropriate form element based on the incoming type.



* Visibility: **public**
* This method is **static**.
* This method is defined by [FormElement](formelement.md)


#### Arguments
* $type **string**
* $attributes **array**


