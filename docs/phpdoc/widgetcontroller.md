WidgetController
===============

Widget controller, management interface for widgets and the like.




* Class name: WidgetController
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

    mixed WidgetController::__construct()





* Visibility: **public**




### admin

    mixed WidgetController::admin()

Display a listing of all widgets registered in the system.



* Visibility: **public**




### create

    mixed WidgetController::create()

Create a simple widget with the standard settings configurations.



* Visibility: **public**




### update

    mixed WidgetController::update()

Create a simple widget with the standard settings configurations.



* Visibility: **public**




### delete

    mixed WidgetController::delete()

Delete a simple widget.



* Visibility: **public**




### instances_save

    mixed WidgetController::instances_save()





* Visibility: **public**




### instance_install

    integer WidgetController::instance_install()

Controller view to install 1 widget into one selected area, be that area a skin, or page template.



* Visibility: **public**




### instance_update

    mixed WidgetController::instance_update()

Controller view to update any instance-specific options for a given template.

Usually consists of just access permissions and display template, but more options could come in the future.

* Visibility: **public**




### instance_remove

    mixed WidgetController::instance_remove()

Controller view to update any instance-specific options for a given template.

Usually consists of just access permissions and display template, but more options could come in the future.

* Visibility: **public**




### instance_moveup

    mixed WidgetController::instance_moveup()

Controller view to update any instance-specific options for a given template.

Usually consists of just access permissions and display template, but more options could come in the future.

* Visibility: **public**




### instance_movedown

    mixed WidgetController::instance_movedown()

Controller view to update any instance-specific options for a given template.

Usually consists of just access permissions and display template, but more options could come in the future.

* Visibility: **public**




### _CreateUpdateHandler

    mixed WidgetController::_CreateUpdateHandler(\Form $form)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $form **[Form](form.md)**



### _InstanceHandler

    mixed WidgetController::_InstanceHandler(\Form $form)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $form **[Form](form.md)**



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


