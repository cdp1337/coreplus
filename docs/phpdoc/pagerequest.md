PageRequest
===============

The main object responsible for setting up the page request and getting the data corresponding to it.




* Class name: PageRequest
* Namespace: 



Constants
----------


### METHOD_HEAD

    const METHOD_HEAD = 'HEAD'





### METHOD_GET

    const METHOD_GET = 'GET'





### METHOD_POST

    const METHOD_POST = 'POST'





### METHOD_PUT

    const METHOD_PUT = 'PUT'





### METHOD_PUSH

    const METHOD_PUSH = 'PUSH'





### METHOD_DELETE

    const METHOD_DELETE = 'DELETE'





Properties
----------


### $contentTypes

    public array $contentTypes = array()

Array of content types accepted by the browser.



* Visibility: **public**


### $acceptLanguages

    public array $acceptLanguages = array()

Array of languages accepted by the browser.



* Visibility: **public**


### $method

    public string $method = null

Request method, one of the PageRequest::METHOD_* strings.



* Visibility: **public**


### $useragent

    public string $useragent

Full string of the incoming user agent.



* Visibility: **public**


### $uri

    public string $uri





* Visibility: **public**


### $uriresolved

    public string $uriresolved





* Visibility: **public**


### $protocol

    public string $protocol





* Visibility: **public**


### $parameters

    public array $parameters = array()





* Visibility: **public**


### $ctype

    public string $ctype = \View::CTYPE_HTML

Content type requested



* Visibility: **public**


### $ext

    public string $ext = 'html'

The extension of the file requested, usually html, but may be pdf, gif, png, etc if necessary.



* Visibility: **public**


### $host

    public string $host





* Visibility: **public**


### $referrer

    public string $referrer





* Visibility: **public**


### $_pagemodel

    private \PageModel $_pagemodel = null





* Visibility: **private**


### $_rawPageData

    private array $_rawPageData = array()





* Visibility: **private**


### $_pageview

    private \View $_pageview = null

The view that will be used to render the page.

*IMPORTANT*, this may change throughout the page execution, should a component "hijack" the view.

* Visibility: **private**


### $_cached

    private boolean $_cached = false

Set to true if this is already a cached View, (so it doesn't re-cache it again).



* Visibility: **private**


Methods
-------


### __construct

    mixed PageRequest::__construct($uri)





* Visibility: **public**


#### Arguments
* $uri **mixed**



### prefersContentType

    boolean PageRequest::prefersContentType(string $type)

Check to see if the page request prefers a particular type of content type request.

This is useful for allowing JSON requests on a per-case basis in the controller.

* Visibility: **public**


#### Arguments
* $type **string**



### splitParts

    array PageRequest::splitParts()

Get an array of all the parts of this request, including:
'controller', 'method', 'parameters', 'baseurl', 'rewriteurl'



* Visibility: **public**




### getBaseURL

    string PageRequest::getBaseURL()

Shortcut function to return just the base url

Utilizes the SplitBaseURL method.

* Visibility: **public**




### getView

    \View PageRequest::getView()

Get the view component for this page request.



* Visibility: **public**




### execute

    mixed PageRequest::execute()

Execute the controller and method this page request points to.



* Visibility: **public**




### render

    mixed PageRequest::render()

Render the View to the browser.



* Visibility: **public**




### isNotCacheableReason

    null|string PageRequest::isNotCacheableReason()

Run some checks and return a reason that the page cannot be cached.

This is used in conjunction with the isCacheable method and used to write a header value as to why a page could not be cached.

* Visibility: **public**




### isCacheable

    boolean PageRequest::isCacheable()

Run the checks to see if this page request can be cached.



* Visibility: **public**




### setParameters

    mixed PageRequest::setParameters($params)

Set all parameters for this view



* Visibility: **public**


#### Arguments
* $params **mixed**



### setParameter

    mixed PageRequest::setParameter($key, $value)

Set a single parameter, useful for overriding.



* Visibility: **public**


#### Arguments
* $key **mixed**
* $value **mixed**



### getParameters

    array PageRequest::getParameters()

Get all parameters from the GET variables.

"Core" parameters are returned on a 0-based index, whereas named GET variables are returned with their respective name.

* Visibility: **public**




### getParameter

    null|string PageRequest::getParameter($key)

Get a single parameter from the GET variables.



* Visibility: **public**


#### Arguments
* $key **mixed** - &lt;p&gt;string|int The parameter to request&lt;/p&gt;



### getPost

    null|string|array PageRequest::getPost($key)

Just a shortcut function to make things consistent; returns a given POST variable.

If the parameter does not exist, null is simply returned.

It is still better to use the form system, as that has data sanitization and everything built in,
but this allows a lower-level of access to the variables without resorting to raw access.

* Visibility: **public**


#### Arguments
* $key **mixed** - &lt;p&gt;string|null The POST variable to get&lt;/p&gt;



### getCookie

    null|string|array PageRequest::getCookie(null|string $key)

Shortcut for getting cookie



* Visibility: **public**


#### Arguments
* $key **null|string**



### getPageModel

    \PageModel PageRequest::getPageModel()

Get the page model for the current page.



* Visibility: **public**




### isPost

    boolean PageRequest::isPost()

Simple check to see if the page request is a POST method.

Returns true if it is POST, false if anything else.

* Visibility: **public**




### isGet

    boolean PageRequest::isGet()

Simple check to see if the page request is a GET method.

Returns true if it is GET, false if anything else.

* Visibility: **public**




### isJSON

    boolean PageRequest::isJSON()

Simple check to see if the page request is a json content type.



* Visibility: **public**




### isAjax

    boolean PageRequest::isAjax()

Simple check to guess if the page request was an ajax-based request.



* Visibility: **public**




### getUserAgent

    \Core\UserAgent PageRequest::getUserAgent()

Get the user agent for this request.



* Visibility: **public**




### getReferrer

    string PageRequest::getReferrer()

Get the referrer of this request, based on $_SERVER information.



* Visibility: **public**




### getPreferredLanguage

    string PageRequest::getPreferredLanguage()

Get the user's preferred language set from either the browser of the LANG cookie.

This just returns the language portion, NOT the full string.

* Visibility: **public**




### getPreferredLocale

    string PageRequest::getPreferredLocale()

Get the user's preferred language+locale set from either the browser of the LANG cookie.



* Visibility: **public**




### _resolveMethod

    mixed PageRequest::_resolveMethod()





* Visibility: **private**




### _resolveAcceptHeader

    mixed PageRequest::_resolveAcceptHeader()





* Visibility: **private**




### _resolveLanguageHeader

    mixed PageRequest::_resolveLanguageHeader()





* Visibility: **private**




### _resolveUAHeader

    mixed PageRequest::_resolveUAHeader()





* Visibility: **private**




### GetSystemRequest

    \PageRequest PageRequest::GetSystemRequest()

The core page request instantiated from the browser.



* Visibility: **public**
* This method is **static**.




### PasswordProtectHandler

    boolean PageRequest::PasswordProtectHandler(\Form $form)

This is the form handler for a password protected page.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $form **[Form](form.md)**


