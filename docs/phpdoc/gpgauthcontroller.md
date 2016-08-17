GPGAuthController
===============

A short teaser of what GPGAuthController does.

More lengthy description of what GPGAuthController does and why it's fantastic.

<h3>Usage Examples</h3>

<h4>Example 1</h4>
<p>Description 1</p>
<code>
// Some code for example 1
$a = $b;
</code>


<h4>Example 2</h4>
<p>Description 2</p>
<code>
// Some code for example 2
$b = $a;
</code>


* Class name: GPGAuthController
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


### reset

    mixed GPGAuthController::reset()

Public view to set or reset the GPG key.

This can work for users that are not currently set to use GPG, as it will perform an email confirmation.

* Visibility: **public**




### login2

    mixed GPGAuthController::login2()

View to login a user, this is actually a supplemental view for /user/login, as it requires an additional step.



* Visibility: **public**




### register2

    mixed GPGAuthController::register2()

Second page for GPG registration; should be called automatically.

This happens after the user uploads the GPG public key and is meant to allow the user to select the email to use based on the key's data.

* Visibility: **public**




### rawUpload

    mixed GPGAuthController::rawUpload()

Method to be expected to be called from the command line to upload a key.

This is used from the reset+configure page.

* Visibility: **public**




### rawVerify

    mixed GPGAuthController::rawVerify()





* Visibility: **public**




### rawLogin

    mixed GPGAuthController::rawLogin()





* Visibility: **public**




### configure

    mixed GPGAuthController::configure()

The public configure method for each user.

This helps the user set his/her public key that the system will use to authenticate with.

* Visibility: **public**




### configure2

    mixed GPGAuthController::configure2()

The second page for setting or updating GPG keys for a given user.

This is required because the command is sent to the user's email,
and this page will display the text area to submit the signed content.

* Visibility: **public**




### jsoncheck

    mixed GPGAuthController::jsoncheck()

View for checking if there was a successful action from the command line,
and redirect to the next page when successful.



* Visibility: **public**




### ResetHandler

    boolean|string GPGAuthController::ResetHandler(\Form $form)

Form submission page for anonymous-based set or reset attempts.

This requires an additional step because I don't want to expose the user ID based on their email.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $form **[Form](form.md)**



### ConfigureHandler

    boolean|string GPGAuthController::ConfigureHandler(\Form $form)

Form handler for the initial key set or reset request.

This sends out the email with the signing command.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $form **[Form](form.md)**



### Configure2Handler

    boolean|string GPGAuthController::Configure2Handler(\Form $form)

Form handler for the final submit to set or reset a GPG key.

This performs the actual key change in the database.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $form **[Form](form.md)**



### LoginHandler

    boolean|string GPGAuthController::LoginHandler(\Form $form)

Form handler for login requests.

This looks up the user's email and ensures that it's linked to a key.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $form **[Form](form.md)**



### Login2Handler

    boolean|mixed|string GPGAuthController::Login2Handler(\Form $form)

Form handler for the login page.

This will read the signed content and ensure that it was signed with
1) The user's exact key that they have previously registered
2) That the key has not been revoked
3) That the key has not expired
4) That the signed content matches the original content submitted for the challange/response.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $form **[Form](form.md)**



### RegisterHandler

    boolean|string GPGAuthController::RegisterHandler(\Form $form)

Handle the registration request for GPG-based accounts and do some preliminary checking.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $form **[Form](form.md)**



### GetUserControlLinks

    array GPGAuthController::GetUserControlLinks(integer $userid)

Hook receiver for /core/controllinks/user/view



* Visibility: **public**
* This method is **static**.


#### Arguments
* $userid **integer**



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


