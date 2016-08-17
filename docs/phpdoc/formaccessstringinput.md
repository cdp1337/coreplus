FormAccessStringInput
===============

Handles the form element for access strings.




* Class name: FormAccessStringInput
* Namespace: 
* Parent class: [FormElement](formelement.md)





Properties
----------


### $_targetname

    private string $_targetname = null

Holds the base name of this form group



* Visibility: **private**


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


### __construct

    mixed FormElement::__construct($atts)





* Visibility: **public**
* This method is defined by [FormElement](formelement.md)


#### Arguments
* $atts **mixed**



### render

    string FormElement::render()

Render this form element and return the resulting HTML as a string



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



### setValue

    boolean FormElement::setValue(mixed $value)

This set explicitly handles the value, and has the extended logic required
 for error checking and validation.



* Visibility: **public**
* This method is defined by [FormElement](formelement.md)


#### Arguments
* $value **mixed** - &lt;p&gt;The value to set&lt;/p&gt;



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




### getInputAttributes

    string FormElement::getInputAttributes()

Template helper function
gets the input attributes as a string



* Visibility: **public**
* This method is defined by [FormElement](formelement.md)




### Factory

    \FormElement FormElement::Factory(string $type, array $attributes)

Get the appropriate form element based on the incoming type.



* Visibility: **public**
* This method is **static**.
* This method is defined by [FormElement](formelement.md)


#### Arguments
* $type **string**
* $attributes **array**


