Controller_2_1
===============

Main Controller parent for the 2.1 API version.




* Class name: Controller_2_1
* Namespace: 





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


### getPageRequest

    \PageRequest Controller_2_1::getPageRequest()

Get the page request for the current page.



* Visibility: **protected**




### setPageRequest

    mixed Controller_2_1::setPageRequest(\PageRequest $request)

Set the page request for this page.  Only really useful in the PageRequest::Execute method.



* Visibility: **public**


#### Arguments
* $request **[PageRequest](pagerequest.md)**



### setView

    mixed Controller_2_1::setView(\View $view)

Internal function for setting the view object for this controller initially.

Really only useful internally and in the PageRequest object.  Everything else can probably safely ignore this.

* Visibility: **public**


#### Arguments
* $view **[View](view.md)**



### getView

    \View Controller_2_1::getView()

Get the view for this controller.

Up to the extending Controller to use this object is it wishes.

* Visibility: **public**




### getControls

    array|null Controller_2_1::getControls()

Function that is called to get the controls to the current view.

This function can either return an array of controls to be added, or just add them directly to the view.

* Visibility: **public**




### overwriteView

    mixed Controller_2_1::overwriteView(\View $newview)

Replace this controller's view with a different one.

This is useful for controllers that intercept a page request and replace their own content.

* Visibility: **protected**


#### Arguments
* $newview **[View](view.md)**



### getPageModel

    \PageModel Controller_2_1::getPageModel()

Get the page model for the current page.



* Visibility: **public**




### sendJSONError

    integer Controller_2_1::sendJSONError($code, $message, $redirect)

Set a JSON error message and optionally redirect if the page is not an ajax request.



* Visibility: **public**


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


#### Arguments
* $accessstring **string**



### setContentType

    mixed Controller_2_1::setContentType(string $ctype)

Set the content of the view being returned.

Important for JSON, XML, and other types.

* Visibility: **protected**


#### Arguments
* $ctype **string**



### setTemplate

    mixed Controller_2_1::setTemplate($template)





* Visibility: **protected**


#### Arguments
* $template **mixed**



### Factory

    \Controller_2_1 Controller_2_1::Factory(string $name)

Return a valid Controller.

This is used because new $pagedat['controller'](); cannot provide typecasting :p

* Visibility: **public**
* This method is **static**.


#### Arguments
* $name **string**


