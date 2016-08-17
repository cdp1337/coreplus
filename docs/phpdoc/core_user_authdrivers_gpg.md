Core\User\AuthDrivers\gpg
===============

A short teaser of what datastore does.

More lengthy description of what datastore does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: gpg
* Namespace: Core\User\AuthDrivers
* This class implements: [Core\User\AuthDriverInterface](core_user_authdriverinterface.md)




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

    boolean Core\User\AuthDrivers\gpg::checkPassword(string $password)

Check that the supplied password or key is valid for this user.



* Visibility: **public**


#### Arguments
* $password **string** - &lt;p&gt;The password to verify&lt;/p&gt;



### isActive

    boolean|string Core\User\AuthDriverInterface::isActive()

Check if this user is active and can login.

If true is returned, the user is valid.
If false is returned, the user is invalid with no message.
If a string is returned, the user is invalid and a message is to be displayed to the user.

* Visibility: **public**
* This method is defined by [Core\User\AuthDriverInterface](core_user_authdriverinterface.md)




### canSetPassword

    boolean Core\User\AuthDrivers\gpg::canSetPassword()

Get if this user can set their password via the site.



* Visibility: **public**




### canLoginWithPassword

    boolean Core\User\AuthDrivers\gpg::canLoginWithPassword()

Get if this user can login via a password on the traditional login interface.



* Visibility: **public**




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




### SendVerificationEmail

    false|string Core\User\AuthDrivers\gpg::SendVerificationEmail(\UserModel $user, string $fingerprint, boolean $cli)

Send the commands to a user to verify they have access to the provided GPG key.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $user **[UserModel](usermodel.md)**
* $fingerprint **string**
* $cli **boolean** - &lt;p&gt;Set to false to send non-CLI instructions.&lt;/p&gt;



### ValidateVerificationResponse

    boolean|string Core\User\AuthDrivers\gpg::ValidateVerificationResponse(string $nonce, string $signature)

Validate the verification email, part 2 of confirmation.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $nonce **string**
* $signature **string**


