UpdaterController
===============

Main Controller parent for the 2.1 API version.




* Class name: UpdaterController
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

    mixed UpdaterController::__construct()





* Visibility: **public**




### getControls

    array|null Controller_2_1::getControls()

Function that is called to get the controls to the current view.

This function can either return an array of controls to be added, or just add them directly to the view.

* Visibility: **public**
* This method is defined by [Controller_2_1](controller_2_1.md)




### index

    mixed UpdaterController::index()

Listing controller of the updater.



* Visibility: **public**




### check

    mixed UpdaterController::check()

Check for updates controller

This is just a very simple json function to return true or false on if there are updates for currently installed components.

This is so simple because its sole purpose is to just notify the user if there is an update available.
For more full-featured update scripts, look at the getupdates page; that actually returns the updates.

* Visibility: **public**




### getupdates

    mixed UpdaterController::getupdates()

Get the list of updates from remote repositories, (or session cache).



* Visibility: **public**




### repos

    mixed UpdaterController::repos()

Sites listing controller, displays all update sites and links to manage them.



* Visibility: **public**




### repos_add

    mixed UpdaterController::repos_add()

Add a repository to the site.

This will also handle the embedded keys, (as of 2.4.5).

This contains the first step and second steps.

* Visibility: **public**




### repos_edit

    mixed UpdaterController::repos_edit()





* Visibility: **public**




### repos_delete

    mixed UpdaterController::repos_delete()

Page to remove a repository.



* Visibility: **public**




### browse

    mixed UpdaterController::browse()

Browse the repositories for a component, be it new or update.

This is designed to give a syndicated list of ALL components in all available repos.

* Visibility: **public**




### upload

    mixed UpdaterController::upload()

View to manually upload a package to the system.

This shouldn't be used too often, but can be used for one-off packages that may not reside in a public repository.

* Visibility: **public**




### keys

    mixed UpdaterController::keys()





* Visibility: **public**




### keys_import

    mixed UpdaterController::keys_import()





* Visibility: **public**




### keys_delete

    mixed UpdaterController::keys_delete()





* Visibility: **public**




### component_disable

    mixed UpdaterController::component_disable()

Page that is called to disable a given component.

Performs all the necessary checks before disable, ie: dependencies from other components.

* Visibility: **public**




### component_enable

    mixed UpdaterController::component_enable()

Page that is called to enable a given component.

Performs all the necessary checks before enable, ie: dependencies from other components.

* Visibility: **public**




### component_install

    mixed UpdaterController::component_install()

Admin page to kick off the installation or upgrade of components.



* Visibility: **public**




### theme_install

    mixed UpdaterController::theme_install()

Admin page to kick off the installation or upgrade of themes.



* Visibility: **public**




### core_install

    mixed UpdaterController::core_install()

Admin page to kick off the installation or upgrade of the core.



* Visibility: **public**




### update_everything

    mixed UpdaterController::update_everything()

Admin page to do exactly as it states; update everything possible.



* Visibility: **public**




### _performInstall

    mixed UpdaterController::_performInstall($type, $name, $version)

Helper function called by the *_install views.



* Visibility: **private**


#### Arguments
* $type **mixed**
* $name **mixed**
* $version **mixed**



### _SaveRepo

    mixed UpdaterController::_SaveRepo(\Form $form)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $form **[Form](form.md)**



### _HealthCheckHook

    array UpdaterController::_HealthCheckHook()

Call to check for updates as part of the health checking system in Core.



* Visibility: **public**
* This method is **static**.




### _UploadHandler

    mixed UpdaterController::_UploadHandler(\Form $form)





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


