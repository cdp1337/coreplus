Email
===============

Email wrapper around the system mail utility, (phpMailer in this case).




* Class name: Email
* Namespace: 





Properties
----------


### $_template

    private \Template $_template = null





* Visibility: **private**


### $_view

    private \View $_view





* Visibility: **private**


### $_mailer

    private \PHPMailer $_mailer





* Visibility: **private**


### $_encryption

    private mixed $_encryption





* Visibility: **private**


### $templatename

    public string $templatename

The template to render this view with.

Should be the partial path of the template, including emails/

* Visibility: **public**


Methods
-------


### __construct

    mixed Email::__construct()





* Visibility: **public**




### getTemplate

    \Core\Templates\TemplateInterface Email::getTemplate()

Get the template responsible for rendering this email.



* Visibility: **public**




### getMailer

    \PHPMailer Email::getMailer()

Get the mailer responsible for sending this email.



* Visibility: **public**




### addCustomHeader

    mixed Email::addCustomHeader($val)

Add a custom header to the email message



* Visibility: **public**


#### Arguments
* $val **mixed**



### assign

    mixed Email::assign(string $key, mixed $val)

Assign a value to this emails' template.

Just serves as a pass-through for the Template::assign() method.

* Visibility: **public**


#### Arguments
* $key **string**
* $val **mixed**



### renderBody

    string Email::renderBody()

Get the rendered body (taking the template into consideration)



* Visibility: **public**




### to

    mixed Email::to(string $address, string $name)

Set the "to" address for this email.

Will clear out any other to address.

* Visibility: **public**


#### Arguments
* $address **string**
* $name **string**



### from

    mixed Email::from($address, $name)





* Visibility: **public**


#### Arguments
* $address **mixed**
* $name **mixed**



### addBCC

    mixed Email::addBCC($address, $name)





* Visibility: **public**


#### Arguments
* $address **mixed**
* $name **mixed**



### addAddress

    mixed Email::addAddress(string $address, string $name)

Add a "to" address for this email.

Can be called multiple times for sending to multiple people, will
add addresses to the "to" recipient on each call.

* Visibility: **public**


#### Arguments
* $address **string**
* $name **string**



### addAttachment

    mixed Email::addAttachment(\Core\Filestore\File $file)

Add a file as an attachment!



* Visibility: **public**


#### Arguments
* $file **[Core\Filestore\File](core_filestore_file.md)**



### setReplyTo

    mixed Email::setReplyTo($address, string $name)

Set the Reply To address for this email.



* Visibility: **public**


#### Arguments
* $address **mixed**
* $name **string**



### setBody

    mixed Email::setBody(string $body, boolean $ishtml)

Set the body for this email.

This is typically not used, as the Template system should be used whenever possible,
but this is available for simple emails, ie: administrative "IT BROKE!" emails.

* Visibility: **public**


#### Arguments
* $body **string**
* $ishtml **boolean** - &lt;p&gt;Set to true if the $body is HTML.&lt;/p&gt;



### setSubject

    mixed Email::setSubject(string $subject)

Set the subject of this email.



* Visibility: **public**


#### Arguments
* $subject **string**



### setEncryption

    mixed Email::setEncryption(string $fingerprint)

Enable encryption on this email.



* Visibility: **public**


#### Arguments
* $fingerprint **string**



### send

    boolean Email::send()

Send the message



* Visibility: **public**



