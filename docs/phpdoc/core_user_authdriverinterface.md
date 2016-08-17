Core\User\AuthDriverInterface
===============

The interface that dictates how user authentication backends function.




* Interface name: AuthDriverInterface
* Namespace: Core\User
* This is an **interface**






Methods
-------


### __construct

    mixed Core\User\AuthDriverInterface::__construct(\UserModel|null $usermodel)





* Visibility: **public**


#### Arguments
* $usermodel **[UserModel](usermodel.md)|null**



### isActive

    boolean|string Core\User\AuthDriverInterface::isActive()

Check if this user is active and can login.

If true is returned, the user is valid.
If false is returned, the user is invalid with no message.
If a string is returned, the user is invalid and a message is to be displayed to the user.

* Visibility: **public**




### renderLogin

    void Core\User\AuthDriverInterface::renderLogin(array $form_options)

Generate and print the rendered login markup to STDOUT.



* Visibility: **public**


#### Arguments
* $form_options **array**



### renderRegister

    void Core\User\AuthDriverInterface::renderRegister()

Generate and print the rendered registration markup to STDOUT.



* Visibility: **public**




### getAuthTitle

    string Core\User\AuthDriverInterface::getAuthTitle()

Get the title for this Auth driver.  Used in some automatic messages.



* Visibility: **public**




### getAuthIcon

    string Core\User\AuthDriverInterface::getAuthIcon()

Get the icon name for this Auth driver.



* Visibility: **public**



