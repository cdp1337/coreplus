UpdaterWidget
===============

Class description here




* Class name: UpdaterWidget
* Namespace: 
* Parent class: [Widget_2_1](widget_2_1.md)





Properties
----------


### $_view

    private \View $_view = null

The view that gets returned when pages are executed.



* Visibility: **private**


### $_request

    private \WidgetRequest $_request = null

The widget request object for this call.



* Visibility: **private**


### $is_simple

    public boolean $is_simple = false

Set this to true if this widget is "simple".

Simple widgets to not require additional controllers and use the settings for the admin configuration.

* Visibility: **public**


### $settings

    public array $settings = array()

Array of the settings as the key and their default value as the value.

This is primarily useful on simple widgets, where Core's built-in admin manages the configuration and creation.

* Visibility: **public**


### $_instance

    public \WidgetInstanceModel $_instance = null

The WidgetInstance for this request.  Every widget MUST be instanced on some widgetarea.



* Visibility: **public**


### $_params

    public null $_params = null

Widgets that are manually called do not have instances attached to them,
so parameters are not retrievable via that.

This instead houses parameters for manually-called widgets. (ie: {widget ...} in the template)

* Visibility: **public**


### $_installable

    public null $_installable = null

If this widget was called from a widgetarea with an "installable" property,  that value
will be transposed here.  It may be useful for determining a userid or something.

.. maybe.

* Visibility: **public**


### $controls

    public \ViewControls $controls

The controls for ths widget



* Visibility: **public**


Methods
-------


### check

    mixed UpdaterWidget::check()





* Visibility: **public**




### getView

    \View Widget_2_1::getView()

Get the view for this controller.

Up to the extending Controller to use this object is it wishes.

* Visibility: **public**
* This method is defined by [Widget_2_1](widget_2_1.md)




### addControl

    mixed Widget_2_1::addControl(string|array|\Model $title, string $link, string|array $class)

Add a control into this widget

Useful for embedding functions and administrative utilities inline without having to adjust the
application template.

* Visibility: **public**
* This method is defined by [Widget_2_1](widget_2_1.md)


#### Arguments
* $title **string|array|[string](model.md)** - &lt;p&gt;The title to set for this control&lt;/p&gt;
* $link **string** - &lt;p&gt;The link to set for this control&lt;/p&gt;
* $class **string|array** - &lt;p&gt;The class name or array of attributes to set on this control
                           If this is an array, it should be an associative array for the advanced parameters&lt;/p&gt;



### addControls

    mixed Widget_2_1::addControls(array|\Model $controls)

Add an array of controls at once, useful in conjunction with the model->getControlLinks method.

If a Model is provided as the subject, that is used as the subject and all system hooks apply thereof.

* Visibility: **public**
* This method is defined by [Widget_2_1](widget_2_1.md)


#### Arguments
* $controls **array|[array](model.md)**



### getRequest

    mixed Widget_2_1::getRequest()





* Visibility: **public**
* This method is defined by [Widget_2_1](widget_2_1.md)




### getWidgetInstanceModel

    \WidgetInstanceModel Widget_2_1::getWidgetInstanceModel()

Get the widget instance model for this widget



* Visibility: **public**
* This method is defined by [Widget_2_1](widget_2_1.md)




### getWidgetModel

    \WidgetModel|null Widget_2_1::getWidgetModel()

Get the actual widget model for this instance.



* Visibility: **public**
* This method is defined by [Widget_2_1](widget_2_1.md)




### getFormSettings

    array Widget_2_1::getFormSettings()

Get the form data for the settings on this widget.

Has no effect for non-simple widgets.

* Visibility: **public**
* This method is defined by [Widget_2_1](widget_2_1.md)




### getPreviewImage

    string Widget_2_1::getPreviewImage()

Get the path for the preview image for this widget.

Should be an image of size 210x70, 210x140, or 210x210.

* Visibility: **public**
* This method is defined by [Widget_2_1](widget_2_1.md)




### setAccess

    boolean Widget_2_1::setAccess(string $accessstring)

Set the access string for this view and do the access checks against the
currently logged in user.

Will also set the access string on the PageModel, since it needs to be reflected in the database.

* Visibility: **protected**
* This method is defined by [Widget_2_1](widget_2_1.md)


#### Arguments
* $accessstring **string**



### setTemplate

    mixed Widget_2_1::setTemplate($template)





* Visibility: **protected**
* This method is defined by [Widget_2_1](widget_2_1.md)


#### Arguments
* $template **mixed**



### getParameter

    mixed Widget_2_1::getParameter($param)





* Visibility: **protected**
* This method is defined by [Widget_2_1](widget_2_1.md)


#### Arguments
* $param **mixed**



### getSetting

    mixed Widget_2_1::getSetting($key)





* Visibility: **protected**
* This method is defined by [Widget_2_1](widget_2_1.md)


#### Arguments
* $key **mixed**



### Factory

    \Widget_2_1|null Widget_2_1::Factory(string $name)

Return a valid Widget.

This is used because new $pagedat['controller'](); cannot provide typecasting :p

* Visibility: **public**
* This method is **static**.
* This method is defined by [Widget_2_1](widget_2_1.md)


#### Arguments
* $name **string**



### HookPageRender

    mixed Widget_2_1::HookPageRender()

Hook into /core/page/rendering to add the control link for this page if necessary and the user has the appropriate permissions.



* Visibility: **public**
* This method is **static**.
* This method is defined by [Widget_2_1](widget_2_1.md)



