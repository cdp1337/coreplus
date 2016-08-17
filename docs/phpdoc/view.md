View
===============

Provides all elements required to connect the Controller&#039;s data and logic back to the browser in the necessary format.




* Class name: View
* Namespace: 



Constants
----------


### ERROR_OTHER

    const ERROR_OTHER = 1





### ERROR_NOERROR

    const ERROR_NOERROR = 200





### ERROR_BADREQUEST

    const ERROR_BADREQUEST = 400





### ERROR_UNAUTHORIZED

    const ERROR_UNAUTHORIZED = 401





### ERROR_PAYMENTREQUIRED

    const ERROR_PAYMENTREQUIRED = 402





### ERROR_ACCESSDENIED

    const ERROR_ACCESSDENIED = 403





### ERROR_NOTFOUND

    const ERROR_NOTFOUND = 404





### ERROR_METHODNOTALLOWED

    const ERROR_METHODNOTALLOWED = 405





### ERROR_NOTACCEPTABLE

    const ERROR_NOTACCEPTABLE = 406





### ERROR_PROXYAUTHENTICATIONREQUIRED

    const ERROR_PROXYAUTHENTICATIONREQUIRED = 407





### ERROR_REQUESTTIMEOUT

    const ERROR_REQUESTTIMEOUT = 408





### ERROR_CONFLICT

    const ERROR_CONFLICT = 409





### ERROR_GONE

    const ERROR_GONE = 410





### ERROR_LENGTHREQUIRED

    const ERROR_LENGTHREQUIRED = 411





### ERROR_PRECONDITIONFAILED

    const ERROR_PRECONDITIONFAILED = 412





### ERROR_ENTITYTOOLARGE

    const ERROR_ENTITYTOOLARGE = 413





### ERROR_URITOOLARGE

    const ERROR_URITOOLARGE = 414





### ERROR_UNSUPPORTEDMEDIATYPE

    const ERROR_UNSUPPORTEDMEDIATYPE = 415





### ERROR_RANGENOTSATISFIABLE

    const ERROR_RANGENOTSATISFIABLE = 416





### ERROR_EXPECTATIONFAILED

    const ERROR_EXPECTATIONFAILED = 417





### ERROR_SERVERERROR

    const ERROR_SERVERERROR = 500





### MODE_PAGE

    const MODE_PAGE = 'page'





### MODE_WIDGET

    const MODE_WIDGET = 'widget'





### MODE_NOOUTPUT

    const MODE_NOOUTPUT = 'nooutput'





### MODE_AJAX

    const MODE_AJAX = 'ajax'





### MODE_PAGEORAJAX

    const MODE_PAGEORAJAX = 'pageorajax'





### MODE_EMAILORPRINT

    const MODE_EMAILORPRINT = 'print'





### METHOD_GET

    const METHOD_GET = 'GET'





### METHOD_POST

    const METHOD_POST = 'POST'





### METHOD_PUT

    const METHOD_PUT = 'PUT'





### METHOD_HEAD

    const METHOD_HEAD = 'HEAD'





### METHOD_DELETE

    const METHOD_DELETE = 'DELETE'





### CTYPE_HTML

    const CTYPE_HTML = 'text/html'





### CTYPE_PLAIN

    const CTYPE_PLAIN = 'text/plain'





### CTYPE_JSON

    const CTYPE_JSON = 'application/json'





### CTYPE_XML

    const CTYPE_XML = 'application/xml'





### CTYPE_ICS

    const CTYPE_ICS = 'text/calendar'





### CTYPE_RSS

    const CTYPE_RSS = 'application/rss+xml'





Properties
----------


### $error

    public mixed $error





* Visibility: **public**


### $_template

    private mixed $_template





* Visibility: **private**


### $_params

    private mixed $_params





* Visibility: **private**


### $baseurl

    public string $baseurl

The base URL of this view.  Used to resolve the template filename.



* Visibility: **public**


### $title

    public mixed $title





* Visibility: **public**


### $access

    public string $access

The access string for this page.



* Visibility: **public**


### $templatename

    public string $templatename

The template to render this view with.

Should be the partial path of the template, including pages/

* Visibility: **public**


### $contenttype

    public string $contenttype = \View::CTYPE_HTML

The content type of this view.

Generally set from the controller.

This is sent to the browser if it's a page-type view as the Header: Content-Type field.

* Visibility: **public**


### $mastertemplate

    public string $mastertemplate

The master template to render this view with.

Should be just the filename, as it will be located automatically.

* Visibility: **public**


### $breadcrumbs

    public mixed $breadcrumbs = array()





* Visibility: **public**


### $controls

    public \ViewControls $controls

The controls for ths view



* Visibility: **public**


### $mode

    public string $mode

The mode of this View.

Greatly affects the rendering result, since this can be a full page or a single widget.

MUST be one of the valid View::MODE_* strings!

* Visibility: **public**


### $jsondata

    public mixed $jsondata = null

An array, object, string, or other data that is sent to the browser via json_encode if content type is set to JSON.



* Visibility: **public**


### $updated

    public null $updated = null

Set this to a non-null value to set the http-equiv="last-modified" metatag.

Also handles the Header: Last-Modified field.

* Visibility: **public**


### $head

    public array $head = array()

Any "other" string to put into the head.  This can include <link> tags, or any other tag not defined otherwise.



* Visibility: **public**


### $meta

    public array $meta = array()

Associative array of meta data for this view.



* Visibility: **public**


### $scripts

    public array $scripts = array('head' => array(), 'foot' => array())

Array of scripts to load in the head and foot of the document, respectively.



* Visibility: **public**


### $stylesheets

    public array $stylesheets = array()

Array of stylesheets to load in the head of the document.



* Visibility: **public**


### $canonicalurl

    public null $canonicalurl = null

If you wish to override the canonical URL for this page, it can be done so with this variable.

If left null, it will be populated automatically with the URL resolution system.

By setting this variable to false, the canonical link is ignored and not rendered to the browser.

This variable is used to set the appropriate meta data, ie: link type="canonical" and meta key="og:url".

* Visibility: **public**


### $allowerrors

    public boolean $allowerrors = false

Set to true to allow the page template to run with errors.

By default, all errors are caught and the system template overrides the page template. This is
a security precaution to prevent the template from being rendered when access is denied.

If HOWEVER that is the preferred behaviour, ie: user logins, set this to true to allow the page template
to be used in rendering.

* Visibility: **public**


### $ssl

    public boolean $ssl = false

Set to true to require this page to be viewed as SSL.

Obviously if SSL is not enabled on this site, this has no effect.

* Visibility: **public**


### $record

    public boolean $record = true

Set to false to skip this View from being recorded in analytical tools and navigation.  Useful for JSON or POST pages.



* Visibility: **public**


### $_bodyCache

    private null $_bodyCache = null

When fetching the body numerous times, the contents may be cached here to speed up fetchBody().



* Visibility: **private**


### $_fetchCache

    private null $_fetchCache = null

When fetching this entire skin+body numerous times, (ie: cache uses), the entire HTML may be cached here
to be used by cache.



* Visibility: **private**


### $bodyclasses

    public array $bodyclasses = array()

An array of the body classes and their values.

These automatically get appended in the <body> tag of the skin, providing the skin supports it.

* Visibility: **public**


### $htmlAttributes

    public array $htmlAttributes = array()





* Visibility: **public**


### $headers

    public array $headers = array()





* Visibility: **public**


### $cacheable

    protected boolean $cacheable = true





* Visibility: **protected**


### $parent

    public null $parent = null

For widget and sub-page based views, they need to have a parent to render specific elements to,
namely classes, styles, scripts, and the like.



* Visibility: **public**


Methods
-------


### __construct

    mixed View::__construct()





* Visibility: **public**




### setParameters

    mixed View::setParameters($params)





* Visibility: **public**


#### Arguments
* $params **mixed**



### getParameters

    mixed View::getParameters()





* Visibility: **public**




### getParameter

    mixed View::getParameter($key)





* Visibility: **public**


#### Arguments
* $key **mixed**



### getTemplate

    \Core\Templates\TemplateInterface View::getTemplate()

Get the template responsible for rendering this View's body content.

Based on templatename.

* Visibility: **public**




### getTitle

    string View::getTitle()

Get the title of this page, (with automatic i18n translation)



* Visibility: **public**




### overrideTemplate

    boolean View::overrideTemplate($template)

Override a template, useful for forcing a different template type for this view.



* Visibility: **public**


#### Arguments
* $template **mixed** - &lt;p&gt;Core\Templates\TemplateInterface&lt;/p&gt;



### assign

    mixed View::assign($key, $val)

Assign a variable to this view



* Visibility: **public**


#### Arguments
* $key **mixed** - &lt;p&gt;string&lt;/p&gt;
* $val **mixed** - &lt;p&gt;mixed&lt;/p&gt;



### assignVariable

    mixed View::assignVariable($key, $val)

Alias of assign



* Visibility: **public**


#### Arguments
* $key **mixed** - &lt;p&gt;string&lt;/p&gt;
* $val **mixed** - &lt;p&gt;mixed&lt;/p&gt;



### getVariable

    mixed View::getVariable(string $key)

Get a variable that was set with "assign()"



* Visibility: **public**


#### Arguments
* $key **string**



### fetchBody

    mixed View::fetchBody()





* Visibility: **public**




### fetch

    mixed|null|string View::fetch()

Fetch this view as an HTML string.



* Visibility: **public**




### render

    void View::render()

Render this view and send all appropriate headers to the browser, (if applicable)



* Visibility: **public**




### addBreadcrumb

    mixed View::addBreadcrumb($title, null $link)

Add a breadcrumb to the end of the breadcrumb stack.



* Visibility: **public**


#### Arguments
* $title **mixed**
* $link **null**



### setBreadcrumbs

    mixed View::setBreadcrumbs($array)

Override and replace the breadcrumbs with an array.



* Visibility: **public**


#### Arguments
* $array **mixed**



### getBreadcrumbs

    array View::getBreadcrumbs()

Get this view's breadcrumbs as an array



* Visibility: **public**




### addControl

    mixed View::addControl(string|array|\Model $title, string $link, string|array $class)

Add a control into the page template.

Useful for embedding functions and administrative utilities inline without having to adjust the
application template.

* Visibility: **public**


#### Arguments
* $title **string|array|[string](model.md)** - &lt;p&gt;The title to set for this control&lt;/p&gt;
* $link **string** - &lt;p&gt;The link to set for this control&lt;/p&gt;
* $class **string|array** - &lt;p&gt;The class name or array of attributes to set on this control
                           If this is an array, it should be an associative array for the advanced parameters&lt;/p&gt;



### addControls

    mixed View::addControls(array|\Model $controls)

Add an array of controls at once, useful in conjunction with the model->getControlLinks method.

If a Model is provided as the subject, that is used as the subject and all system hooks apply thereof.

* Visibility: **public**


#### Arguments
* $controls **array|[array](model.md)**



### setAccess

    boolean View::setAccess(string $accessstring)

Set the access string for this view and do the access checks against the
currently logged in user.

If the user does not have access to the resource, $this->error is set to 403.

(if you only want to set the access string, please just use $view->access = 'your_string';)

* Visibility: **public**


#### Arguments
* $accessstring **string**



### checkAccess

    boolean View::checkAccess()

Check the access currently set on the view against the currently logged in user.

If the user does not have access to the resource, $this->error is set to 403.

* Visibility: **public**




### isCacheable

    boolean View::isCacheable()

Get if this View is cacheable



* Visibility: **public**




### getHeadContent

    string View::getHeadContent()

Get the content to be inserted into the <head> tag for this view.



* Visibility: **public**




### getFootContent

    string View::getFootContent()

Get the content to be inserted just before the </body> tag for this view.



* Visibility: **public**




### addScript

    mixed View::addScript(string $script, string $location)

Add a script to the global View object.

This will be rendered when a page-level view is rendered.

* Visibility: **public**


#### Arguments
* $script **string**
* $location **string**



### appendBodyContent

    mixed View::appendBodyContent($content)





* Visibility: **public**


#### Arguments
* $content **mixed**



### addStylesheet

    mixed View::addStylesheet(string $link, string $media)

Add a linked stylesheet file to the global View object.



* Visibility: **public**


#### Arguments
* $link **string** - &lt;p&gt;The link of the stylesheet&lt;/p&gt;
* $media **string** - &lt;p&gt;Media to display the stylesheet with.&lt;/p&gt;



### addStyle

    mixed View::addStyle(string $style)

Add an inline style to the global View object.



* Visibility: **public**


#### Arguments
* $style **string** - &lt;p&gt;The contents of the &lt;style&gt; tag.&lt;/p&gt;



### setHTMLAttribute

    mixed View::setHTMLAttribute(string $attribute, string $value)

Set an HTML attribute



* Visibility: **public**


#### Arguments
* $attribute **string** - &lt;p&gt;key&lt;/p&gt;
* $value **string** - &lt;p&gt;value&lt;/p&gt;



### getHTMLAttributes

    array|string View::getHTMLAttributes(boolean $asarray)

Get the HTML attributes as either a string or an array.

These attributes are from the <html> tag.

* Visibility: **public**


#### Arguments
* $asarray **boolean** - &lt;p&gt;Set to false for a string, true for an array.&lt;/p&gt;



### addMetaName

    mixed View::addMetaName(string $key, string $value)

Add a meta name, (and value), to this view.



* Visibility: **public**


#### Arguments
* $key **string**
* $value **string**



### addMeta

    mixed View::addMeta($string)

Add a full meta string to the head of this view.

This should be formatted as &lt;meta name="blah" content="foo"/&gt;, (or however as necessary).

* Visibility: **public**


#### Arguments
* $string **mixed**



### addHead

    mixed View::addHead($string)

Add a full string to the head of this view.



* Visibility: **public**


#### Arguments
* $string **mixed**



### addHeader

    mixed View::addHeader(string $key, string $value)

Method to append a header onto the array of headers to be sent to the browser when render is called.



* Visibility: **public**


#### Arguments
* $key **string**
* $value **string**



### disableCache

    mixed View::disableCache()

Disable the View cache on this object and any/all parents



* Visibility: **public**




### _syncFromView

    mixed View::_syncFromView(\View $view)

Internal only method to sync another view's metadata into this one.

This is to allow Views to have certain attributes bubble up to the top-most view to be rendered out correctly.

* Visibility: **protected**


#### Arguments
* $view **[View](view.md)**



### GetHead

    string View::GetHead()

Get the head data for the system view.



* Visibility: **public**
* This method is **static**.




### GetFoot

    string View::GetFoot()





* Visibility: **public**
* This method is **static**.



