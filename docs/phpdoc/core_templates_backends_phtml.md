Core\Templates\Backends\PHTML
===============






* Class name: PHTML
* Namespace: Core\Templates\Backends
* This class implements: [Core\Templates\TemplateInterface](core_templates_templateinterface.md)




Properties
----------


### $_scope

    private array $_scope = array()

An array of scoped variables for this template.



* Visibility: **private**


### $_filename

    protected String $_filename

The filename of this template



* Visibility: **protected**


### $_view

    private \View $_view = null





* Visibility: **private**


Methods
-------


### assign

    mixed Core\Templates\TemplateInterface::assign(array|string $tpl_var, mixed $value)

Assign a variable into the template

This is required because templates are sandboxed from the rest of the application.

* Visibility: **public**
* This method is defined by [Core\Templates\TemplateInterface](core_templates_templateinterface.md)


#### Arguments
* $tpl_var **array|string** - &lt;p&gt;the template variable name(s)&lt;/p&gt;
* $value **mixed** - &lt;p&gt;the value to assign&lt;/p&gt;



### getTemplateVars

    string|array|null Core\Templates\TemplateInterface::getTemplateVars(string|null $varname)

Returns a single or all template variables



* Visibility: **public**
* This method is defined by [Core\Templates\TemplateInterface](core_templates_templateinterface.md)


#### Arguments
* $varname **string|null** - &lt;p&gt;variable name or null&lt;/p&gt;



### fetch

    string Core\Templates\TemplateInterface::fetch($template)

Fetch fully rendered HTML from this template.



* Visibility: **public**
* This method is defined by [Core\Templates\TemplateInterface](core_templates_templateinterface.md)


#### Arguments
* $template **mixed** - &lt;p&gt;string Fully resolved filename of the template to render&lt;/p&gt;



### render

    void Core\Templates\TemplateInterface::render($template)

Display the fully rendered HTML from this template to the browser.



* Visibility: **public**
* This method is defined by [Core\Templates\TemplateInterface](core_templates_templateinterface.md)


#### Arguments
* $template **mixed** - &lt;p&gt;string Fully resolved filename of the template to render&lt;/p&gt;



### getVariable

    mixed Core\Templates\TemplateInterface::getVariable(string $varname)

Get a single variable from the template variables.



* Visibility: **public**
* This method is defined by [Core\Templates\TemplateInterface](core_templates_templateinterface.md)


#### Arguments
* $varname **string** - &lt;p&gt;The name of the variable&lt;/p&gt;



### setFilename

    void Core\Templates\TemplateInterface::setFilename(string $template)

Set a template filename to be remembered if fetch or render are called with null parameters.



* Visibility: **public**
* This method is defined by [Core\Templates\TemplateInterface](core_templates_templateinterface.md)


#### Arguments
* $template **string** - &lt;p&gt;Filename to remember for this template.&lt;/p&gt;



### getBasename

    string Core\Templates\TemplateInterface::getBasename()

Get the basename of this template



* Visibility: **public**
* This method is defined by [Core\Templates\TemplateInterface](core_templates_templateinterface.md)




### getFilename

    string|null Core\Templates\TemplateInterface::getFilename()

Get the full filename of this template



* Visibility: **public**
* This method is defined by [Core\Templates\TemplateInterface](core_templates_templateinterface.md)




### hasOptionalStylesheets

    boolean Core\Templates\TemplateInterface::hasOptionalStylesheets()

Scan through this template file and see if it has optional stylesheets that the admin can select to enable.



* Visibility: **public**
* This method is defined by [Core\Templates\TemplateInterface](core_templates_templateinterface.md)




### hasWidgetAreas

    boolean Core\Templates\TemplateInterface::hasWidgetAreas()

Scan through this template file and see if it has widgetareas contained within.



* Visibility: **public**
* This method is defined by [Core\Templates\TemplateInterface](core_templates_templateinterface.md)




### getWidgetAreas

    array Core\Templates\TemplateInterface::getWidgetAreas()

Get an array of widget areas defined on this template.

The returning array is associative with the widgetarea name as the key,
and each value is an array of name and installable.

* Visibility: **public**
* This method is defined by [Core\Templates\TemplateInterface](core_templates_templateinterface.md)




### getOptionalStylesheets

    array Core\Templates\TemplateInterface::getOptionalStylesheets()

Get the list of optional stylesheets in this template.

The returned array will be an array of the attributes on the declaration, with at minimum 'src' and 'title'.

* Visibility: **public**
* This method is defined by [Core\Templates\TemplateInterface](core_templates_templateinterface.md)




### getInsertables

    array Core\Templates\TemplateInterface::getInsertables()

Get an array of the insertables in this template.

Should have "name", "type", "title", "value", and "description" in each array.
Should also have any formelement-specific key necessary for operation, ie: "basedir", "accept", etc.

* Visibility: **public**
* This method is defined by [Core\Templates\TemplateInterface](core_templates_templateinterface.md)




### getView

    \View Core\Templates\TemplateInterface::getView()

Get the registered view for this template, useful for setting CSS and Scripts in correct locations in the markup.

If no view has been set on this template, then \Core\view() should be returned.

* Visibility: **public**
* This method is defined by [Core\Templates\TemplateInterface](core_templates_templateinterface.md)




### setView

    void Core\Templates\TemplateInterface::setView(\View $view)

Set the registered view for this template, usually set from the View.



* Visibility: **public**
* This method is defined by [Core\Templates\TemplateInterface](core_templates_templateinterface.md)


#### Arguments
* $view **[View](view.md)**


