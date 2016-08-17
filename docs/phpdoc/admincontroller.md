AdminController
===============

Admin controller, handles all /Admin requests




* Class name: AdminController
* Namespace: 
* Parent class: [Controller_2_1](controller_2_1.md)





Properties
----------


### $_request

    private \PageRequest $_request = null

The request object for the current page.



* Visibility: **private**


### $_model

    private \PageModel $_model = null

The page model for the current page.



* Visibility: **private**


### $_view

    private \View $_view = null

The view that gets returned when pages are executed.



* Visibility: **private**


### $accessstring

    public string $accessstring = null

Shared access string for this controller.

Optional, if set to non-null, it will be checked before any method is called.

* Visibility: **public**


Methods
-------


### __construct

    mixed AdminController::__construct()





* Visibility: **public**




### index

    integer AdminController::index()

Display the admin dashboard.

This page is primarily made up of widgets added by other systems.

* Visibility: **public**




### health

    mixed AdminController::health()

Full page to display the health checks of the site.



* Visibility: **public**




### serverid

    mixed AdminController::serverid()





* Visibility: **public**




### reinstallAll

    integer AdminController::reinstallAll()

Run through and reinstall all components and themes.



* Visibility: **public**




### config

    integer AdminController::config()

Display ALL the system configuration options.



* Visibility: **public**




### syncSearchIndex

    integer AdminController::syncSearchIndex()

Sync the search index fields of every model on the system.



* Visibility: **public**




### log

    integer AdminController::log()

Display a list of system logs that have been recorded.



* Visibility: **public**




### log_details

    integer AdminController::log_details()

Page to display full details of a system log, usually opened in an ajax dialog.



* Visibility: **public**




### pages

    mixed AdminController::pages()

Display a listing of all pages registered in the system.



* Visibility: **public**




### page_publish

    mixed AdminController::page_publish()

Shortcut for publishing a page.



* Visibility: **public**




### page_unpublish

    mixed AdminController::page_unpublish()

Shortcut for unpublishing a page.



* Visibility: **public**




### widgets

    mixed AdminController::widgets()

Display a listing of all pages registered in the system.



* Visibility: **public**




### widget_create

    mixed AdminController::widget_create()

Create a simple widget with the standard settings configurations.



* Visibility: **public**




### widget_update

    mixed AdminController::widget_update()

Create a simple widget with the standard settings configurations.



* Visibility: **public**




### widget_delete

    mixed AdminController::widget_delete()

Delete a simple widget.



* Visibility: **public**




### widgetinstances_save

    mixed AdminController::widgetinstances_save()





* Visibility: **public**




### testui

    mixed AdminController::testui()

Page to test the UI of various Core elements



* Visibility: **public**




### i18n

    integer AdminController::i18n()

Page to view and test the i18n settings and strings of this site.

Also useful for viewing what strings are currently installed and where they came from!

* Visibility: **public**




### seo_config

    mixed AdminController::seo_config()

Configure several of the SEO-based options on Core.



* Visibility: **public**




### performance_config

    mixed AdminController::performance_config()

Configure several of the performance-based options on Core.



* Visibility: **public**




### email_config

    mixed AdminController::email_config()





* Visibility: **public**




### email_test

    mixed AdminController::email_test()





* Visibility: **public**




### _WidgetCreateUpdateHandler

    mixed AdminController::_WidgetCreateUpdateHandler(\Form $form)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $form **[Form](form.md)**



### _ConfigSubmit

    mixed AdminController::_ConfigSubmit(\Form $form)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $form **[Form](form.md)**



### PagesSave

    boolean AdminController::PagesSave(\Form $form)

The save handler for /admin/pages quick edit.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $form **[Form](form.md)**



### _i18nSaveHandler

    mixed AdminController::_i18nSaveHandler(\Form $form)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $form **[Form](form.md)**



### _HealthCheckHook

    array AdminController::_HealthCheckHook()

Call to check some of the core requirements on Core, such as file permissions and the like.



* Visibility: **public**
* This method is **static**.




### _HealthCheckReport

    mixed AdminController::_HealthCheckReport()

The weekly health report to email to admins.

Will only send an email if there was an issue found with the site.
Otherwise, this will be used by the upstream maintainers to know what versions clients are connecting with.

* Visibility: **public**
* This method is **static**.




### getPageRequest

    \PageRequest Controller_2_1::getPageRequest()

Get the page request for the current page.



* Visibility: **protected**
* This method is defined by [Controller_2_1](controller_2_1.md)




### setPageRequest

    mixed Controller_2_1::setPageRequest(\PageRequest $request)

Set the page request for this page.  Only really useful in the PageRequest::Execute method.



* Visibility: **public**
* This method is defined by [Controller_2_1](controller_2_1.md)


#### Arguments
* $request **[PageRequest](pagerequest.md)**



### setView

    mixed Controller_2_1::setView(\View $view)

Internal function for setting the view object for this controller initially.

Really only useful internally and in the PageRequest object.  Everything else can probably safely ignore this.

* Visibility: **public**
* This method is defined by [Controller_2_1](controller_2_1.md)


#### Arguments
* $view **[View](view.md)**



### getView

    \View Controller_2_1::getView()

Get the view for this controller.

Up to the extending Controller to use this object is it wishes.

* Visibility: **public**
* This method is defined by [Controller_2_1](controller_2_1.md)




### getControls

    array|null Controller_2_1::getControls()

Function that is called to get the controls to the current view.

This function can either return an array of controls to be added, or just add them directly to the view.

* Visibility: **public**
* This method is defined by [Controller_2_1](controller_2_1.md)




### overwriteView

    mixed Controller_2_1::overwriteView(\View $newview)

Replace this controller's view with a different one.

This is useful for controllers that intercept a page request and replace their own content.

* Visibility: **protected**
* This method is defined by [Controller_2_1](controller_2_1.md)


#### Arguments
* $newview **[View](view.md)**



### getPageModel

    \PageModel Controller_2_1::getPageModel()

Get the page model for the current page.



* Visibility: **public**
* This method is defined by [Controller_2_1](controller_2_1.md)




### sendJSONError

    integer Controller_2_1::sendJSONError($code, $message, $redirect)

Set a JSON error message and optionally redirect if the page is not an ajax request.



* Visibility: **public**
* This method is defined by [Controller_2_1](controller_2_1.md)


#### Arguments
* $code **mixed**
* $message **mixed**
* $redirect **mixed**



### setAccess

    boolean Controller_2_1::setAccess(string $accessstring)

Set the access string for this view and do the access checks against the
currently logged in user.

Will also set the access string on the PageModel, since it needs to be reflected in the database.

* Visibility: **protected**
* This method is defined by [Controller_2_1](controller_2_1.md)


#### Arguments
* $accessstring **string**



### setContentType

    mixed Controller_2_1::setContentType(string $ctype)

Set the content of the view being returned.

Important for JSON, XML, and other types.

* Visibility: **protected**
* This method is defined by [Controller_2_1](controller_2_1.md)


#### Arguments
* $ctype **string**



### setTemplate

    mixed Controller_2_1::setTemplate($template)





* Visibility: **protected**
* This method is defined by [Controller_2_1](controller_2_1.md)


#### Arguments
* $template **mixed**



### Factory

    \Controller_2_1 Controller_2_1::Factory(string $name)

Return a valid Controller.

This is used because new $pagedat['controller'](); cannot provide typecasting :p

* Visibility: **public**
* This method is **static**.
* This method is defined by [Controller_2_1](controller_2_1.md)


#### Arguments
* $name **string**


