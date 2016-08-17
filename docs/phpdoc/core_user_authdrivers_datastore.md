Core\User\AuthDrivers\datastore
===============

A short teaser of what datastore does.

More lengthy description of what datastore does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: datastore
* Namespace: Core\User\AuthDrivers
* This class implements: [Core\User\AuthDriverInterface](core_user_authdriverinterface.md)


Constants
----------


### HASH_ITERATIONS

    const HASH_ITERATIONS = 11





Properties
----------


### $_usermodel

    protected \UserModel $_usermodel





* Visibility: **protected**


Methods
-------


### __construct

    mixed Core\User\AuthDriverInterface::__construct(\UserModel|null $usermodel)





* Visibility: **public**
* This method is defined by [Core\User\AuthDriverInterface](core_user_authdriverinterface.md)


#### Arguments
* $usermodel **[UserModel](usermodel.md)|null**



### checkPassword

    boolean Core\User\AuthDrivers\datastore::checkPassword(string $password)

Check that the supplied password or key is valid for this user.



* Visibility: **public**


#### Arguments
* $password **string** - &lt;p&gt;The password to verify&lt;/p&gt;



### setPassword

    boolean|string Core\User\AuthDrivers\datastore::setPassword($password)

Set the user's password using the necessary hashing



* Visibility: **public**


#### Arguments
* $password **mixed**



### isActive

    boolean|string Core\User\AuthDriverInterface::isActive()

Check if this user is active and can login.

If true is returned, the user is valid.
If false is returned, the user is invalid with no message.
If a string is returned, the user is invalid and a message is to be displayed to the user.

* Visibility: **public**
* This method is defined by [Core\User\AuthDriverInterface](core_user_authdriverinterface.md)




### renderLogin

    void Core\User\AuthDriverInterface::renderLogin(array $form_options)

Generate and print the rendered login markup to STDOUT.



* Visibility: **public**
* This method is defined by [Core\User\AuthDriverInterface](core_user_authdriverinterface.md)


#### Arguments
* $form_options **array**



### renderRegister

    void Core\User\AuthDriverInterface::renderRegister()

Generate and print the rendered registration markup to STDOUT.



* Visibility: **public**
* This method is defined by [Core\User\AuthDriverInterface](core_user_authdriverinterface.md)




### getAuthTitle

    string Core\User\AuthDriverInterface::getAuthTitle()

Get the title for this Auth driver.  Used in some automatic messages.



* Visibility: **public**
* This method is defined by [Core\User\AuthDriverInterface](core_user_authdriverinterface.md)




### getAuthIcon

    string Core\User\AuthDriverInterface::getAuthIcon()

Get the icon name for this Auth driver.



* Visibility: **public**
* This method is defined by [Core\User\AuthDriverInterface](core_user_authdriverinterface.md)




### getPasswordComplexityAsHTML

    string Core\User\AuthDrivers\datastore::getPasswordComplexityAsHTML()

Get the password complexity requirements as HTML.



* Visibility: **public**




### validatePassword

    boolean|string Core\User\AuthDrivers\datastore::validatePassword(string $password)

Validate the password based on configuration rules.



* Visibility: **public**


#### Arguments
* $password **string**



### pwgen

    string Core\User\AuthDrivers\datastore::pwgen()

Generate a password that meets the site complexity requirements.



* Visibility: **public**



