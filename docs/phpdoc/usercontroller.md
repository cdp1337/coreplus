UserController
===============

Main controller for the user system

This controller is only responsible for Core user functions.
Authentication-specific functions must be contained on the specific auth driver or its respective controller.


* Class name: UserController
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


### admin

    null|integer UserController::admin()

Admin listing of all the users



* Visibility: **public**




### me

    null|integer UserController::me()

Show the current user's profile.



* Visibility: **public**




### view

    null|integer UserController::view()

Show a user's profile, (Admin view only).



* Visibility: **public**




### edit

    mixed UserController::edit()

View to edit the user account, both administratively and from within the user's profile.



* Visibility: **public**




### connectedprofiles

    mixed UserController::connectedprofiles()

Function to edit the user's connected profiles.



* Visibility: **public**




### login

    \View UserController::login()

Display the login page for whatever drivers may happen to be installed.



* Visibility: **public**




### linkfacebook

    mixed UserController::linkfacebook()

Ajax page to allow for quickly linking the current user to a facebook account from a strictly javascript interface.



* Visibility: **public**




### register

    integer UserController::register()

Display the register page for new users.



* Visibility: **public**




### register2

    mixed UserController::register2()

The actual Core registration page.

This renders all the user's configurable options at registration.

* Visibility: **public**




### logout

    mixed UserController::logout()





* Visibility: **public**




### activate

    mixed UserController::activate()

Simple controller to activate a user account.

Meant to be called with json only.

* Visibility: **public**




### delete

    integer UserController::delete()

Permanently delete a user account and all configuration options attached.



* Visibility: **public**




### sudo

    mixed UserController::sudo()

View to sudo as another user.



* Visibility: **public**




### import

    mixed UserController::import()

Import a set of users from a CSV file.



* Visibility: **public**




### import_cancel

    mixed UserController::import_cancel()

Link to abort the import process.



* Visibility: **public**




### _import1

    mixed UserController::_import1()

Display the initial upload option that will kick off the rest of the import options.



* Visibility: **private**




### _import2

    mixed UserController::_import2()

There has been a file selected; check that file for headers and what not to display something useful to the user.



* Visibility: **private**




### _import3

    mixed UserController::_import3()





* Visibility: **private**




### jshelper

    mixed UserController::jshelper()

This is a helper controller to expose server-side data to javascript.

It's useful for currently logged in user and what not.
Obviously nothing critical is exposed here, since it'll be sent to the useragent.

* Visibility: **public**




### _HookHandler403

    mixed UserController::_HookHandler403(\View $view)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $view **[View](view.md)**



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


