Core
===============

Core class of this entire system.




* Class name: Core
* Namespace: 
* This class implements: [ISingleton](isingleton.md)




Properties
----------


### $instance

    private \Core $instance

The singleton instance of the Core object.



* Visibility: **private**
* This property is **static**.


### $_LoadedComponents

    private mixed $_LoadedComponents = false

Is set to true when the components are loaded.



* Visibility: **private**
* This property is **static**.


### $_components

    private array $_components = null

An array of every enabled Component in the system.



* Visibility: **private**


### $_componentsDisabled

    private array $_componentsDisabled = array()

Array of the disabled components on the system.

This is useful for the updater where the admin can enable/disable packages.

* Visibility: **private**


### $_libraries

    private array $_libraries = array()

An array of every library in the system.

This is useful because some components register additional libraries.

* Visibility: **private**


### $_classes

    private array $_classes = array()

List of every installed class and its location on the system.



* Visibility: **private**


### $_tmpclasses

    private array $_tmpclasses = array()





* Visibility: **private**


### $_widgets

    private array $_widgets = array()

List of widgets available on the system.



* Visibility: **private**


### $_viewClasses

    private array $_viewClasses = array()

List of every installed view class and its location on the system.



* Visibility: **private**


### $_scriptlibraries

    private array $_scriptlibraries = array()

List of every available jslibrary and its call.

.

* Visibility: **private**


### $_loaded

    private mixed $_loaded = false





* Visibility: **private**


### $_componentobj

    private \Component_2_1 $_componentobj

The component object that contains the 'Core' definition.



* Visibility: **private**


### $_profiletimes

    private array $_profiletimes = array()

Events and the microtime it took to get there from initialization.

Useful for benchmarking and performance tuning.

* Visibility: **private**


### $_permissions

    private array $_permissions = array()

All permissions that are registered from components.



* Visibility: **private**


### $_features

    private array $_features = array()





* Visibility: **private**


Methods
-------


### load

    mixed Core::load()





* Visibility: **public**




### isLoadable

    boolean Core::isLoadable()

Just a simple function to make this object compatable with the Component objects.



* Visibility: **public**




### isValid

    mixed Core::isValid()





* Visibility: **public**




### loadFiles

    \unknown_type Core::loadFiles()

Another simple function to make this object compatible with the Component objects.



* Visibility: **public**




### hasLibrary

    mixed Core::hasLibrary()





* Visibility: **public**




### hasModule

    mixed Core::hasModule()





* Visibility: **public**




### hasJSLibrary

    mixed Core::hasJSLibrary()





* Visibility: **public**




### getClassList

    mixed Core::getClassList()





* Visibility: **public**




### getViewClassList

    mixed Core::getViewClassList()





* Visibility: **public**




### getLibraryList

    mixed Core::getLibraryList()





* Visibility: **public**




### getViewSearchDirs

    mixed Core::getViewSearchDirs()





* Visibility: **public**




### getIncludePaths

    mixed Core::getIncludePaths()





* Visibility: **public**




### install

    mixed Core::install()





* Visibility: **public**




### upgrade

    mixed Core::upgrade()





* Visibility: **public**




### __construct

    mixed Core::__construct()





* Visibility: **private**




### _isInstalled

    mixed Core::_isInstalled()





* Visibility: **private**




### _needsUpdated

    mixed Core::_needsUpdated()





* Visibility: **private**




### _loadComponents

    mixed Core::_loadComponents()

Load all the components in the system, replacement for the Core.



* Visibility: **private**




### _registerComponent

    mixed Core::_registerComponent(\Component_2_1 $c)

Internally used method to notify the rest of the system that a given
   component has been loaded and is available.

Expects all checks to be done already.

* Visibility: **public**


#### Arguments
* $c **[Component_2_1](component_2_1.md)**



### CheckClass

    void Core::CheckClass(string $classname)

Simple autoload register function to lookup a classname and resolve it.

This was a direct port from the Core.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $classname **string**



### LoadComponents

    mixed Core::LoadComponents()





* Visibility: **public**
* This method is **static**.




### DB

    \DMI_Backend Core::DB()

Shortcut function to get the current system database/datamodel interface.



* Visibility: **public**
* This method is **static**.




### FTP

    resource Core::FTP()

Get the global FTP connection.

Returns the FTP resource or false on failure.

* Visibility: **public**
* This method is **static**.




### User

    \UserModel Core::User()

Get the current user model that is logged in.



* Visibility: **public**
* This method is **static**.




### File

    \Core\Filestore\File Core::File(string $filename)

Instantiate a new File object, ready for manipulation or access.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $filename **string**



### Directory

    \Directory_Backend Core::Directory(string $directory)

Instantiate a new Directory object, ready for manipulation or access.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $directory **string**



### TranslateDimensionToPreviewSize

    string Core::TranslateDimensionToPreviewSize(string $dimensions)

Translate a dimension, (or dimensions), to a "preview size"
of sm, med, lg or xl.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $dimensions **string** - &lt;p&gt;Dimensions to translate&lt;/p&gt;



### GetPermissions

    array Core::GetPermissions()

Get all registered permissions for all loaded components.



* Visibility: **public**
* This method is **static**.




### GetComponent

    \Component_2_1 Core::GetComponent(string $name)

Get the component object by its name.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $name **string** - &lt;p&gt;Name of the requested component&lt;/p&gt;



### GetComponents

    array Core::GetComponents()

Get all components



* Visibility: **public**
* This method is **static**.




### GetDisabledComponents

    mixed Core::GetDisabledComponents()





* Visibility: **public**
* This method is **static**.




### GetComponentByController

    \Component_2_1|null Core::GetComponentByController(string $controller)

Lookup a component by a controller.

Useful for figuring out what API version a given controller needs to be handled as.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $controller **string**



### GetStandardHTTPHeaders

    array|string Core::GetStandardHTTPHeaders(boolean $forcurl, boolean $autoclose)

Get the standard HTTP request headers for retrieving remote files.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $forcurl **boolean** - &lt;p&gt;Set to true to return headers suitable for cURL.&lt;/p&gt;
* $autoclose **boolean** - &lt;p&gt;Set to true to include a &quot;Connection: close&quot; header.&lt;/p&gt;



### Singleton

    \ISingleton ISingleton::Singleton()





* Visibility: **public**
* This method is **static**.
* This method is defined by [ISingleton](isingleton.md)




### GetInstance

    \Core Core::GetInstance()

Get the core singleton object



* Visibility: **public**
* This method is **static**.




### _LoadFromDatabase

    mixed Core::_LoadFromDatabase()





* Visibility: **public**
* This method is **static**.




### IsClassAvailable

    boolean Core::IsClassAvailable(string $classname)

Check if a given class is available in the system as-is.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $classname **string**



### IsLibraryAvailable

    mixed Core::IsLibraryAvailable($name, $version, $operation)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $name **mixed**
* $version **mixed**
* $operation **mixed**



### IsJSLibraryAvailable

    mixed Core::IsJSLibraryAvailable($name, $version, $operation)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $name **mixed**
* $version **mixed**
* $operation **mixed**



### GetLibraryVersion

    string|null Core::GetLibraryVersion(string $library)

Get the version for a loaded library or NULL if not loaded.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $library **string**



### GetJSLibrary

    null|array Core::GetJSLibrary(string $library)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $library **string**



### GetJSLibraries

    array Core::GetJSLibraries()

Get a flat list of javascript libraries currently available.



* Visibility: **public**
* This method is **static**.




### GetClasses

    array Core::GetClasses()

Get all the classes that are currently available and loaded.



* Visibility: **public**
* This method is **static**.




### LoadScriptLibrary

    mixed Core::LoadScriptLibrary($library)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $library **mixed**



### IsComponentAvailable

    mixed Core::IsComponentAvailable($name, $version, $operation)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $name **mixed**
* $version **mixed**
* $operation **mixed**



### IsComponentReady

    string|true Core::IsComponentReady($name)

Utility check to see if a requested component is ready to use.

This is different from isAvailable, as it will query the component to see if it's configured and ready for use!

If it's not ready, either an error message or URL is to be returned.
Otherwise, TRUE is returned to indicate that it's ready.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $name **mixed**



### IsInstalled

    mixed Core::IsInstalled()





* Visibility: **public**
* This method is **static**.




### NeedsUpdated

    mixed Core::NeedsUpdated()





* Visibility: **public**
* This method is **static**.




### GetVersion

    mixed Core::GetVersion()





* Visibility: **public**
* This method is **static**.




### ResolveAsset

    string Core::ResolveAsset(string $asset)

Resolve an asset to a fully-resolved URL.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $asset **string**



### ResolveLink

    string Core::ResolveLink(string $url)

Resolve a url or application path to a fully-resolved URL.

This can also be an already-resolved link.  If so, no action is taken
and the original URL is returned unchanged.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $url **string**



### Redirect

    boolean|null Core::Redirect(string $page, integer $code)

Redirect the user to another page via sending the Location header.

Prevents any POST data from being reloaded.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $page **string** - &lt;p&gt;The page URL to redirect to&lt;/p&gt;
* $code **integer** - &lt;p&gt;The HTTP status code to send to the browser, MUST be 301 or 302.&lt;/p&gt;



### Reload

    mixed Core::Reload()





* Visibility: **public**
* This method is **static**.




### SetMessage

    void Core::SetMessage(string $messageText, string $messageType)

Add a message to the user's stack.

It will be displayed the next time the user (or session) renders the page.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $messageText **string** - &lt;p&gt;The message to send to the user&lt;/p&gt;
* $messageType **string** - &lt;p&gt;The type of message, &quot;success&quot;, &quot;info&quot;, or &quot;error&quot;&lt;/p&gt;



### AddMessage

    mixed Core::AddMessage($messageText, $messageType)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $messageText **mixed**
* $messageType **mixed**



### GetMessages

    array Core::GetMessages(boolean $returnSorted, boolean $clearStack)

Retrieve the messages and optionally clear the message stack.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $returnSorted **boolean**
* $clearStack **boolean**



### SortByKey

    mixed Core::SortByKey($named_recs, $order_by, $rev, $flags)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $named_recs **mixed**
* $order_by **mixed**
* $rev **mixed**
* $flags **mixed**



### ImplodeKey

    string Core::ImplodeKey($glue, $array)

Return a string of the keys of the given array glued together.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $glue **mixed** - &lt;p&gt;string&lt;/p&gt;
* $array **mixed** - &lt;p&gt;array&lt;/p&gt;



### RandomHex

    string Core::RandomHex(integer $length, boolean $casesensitive)

Generate a random hex-deciman value of a given length.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $length **integer**
* $casesensitive **boolean** - &lt;p&gt;[false] Set to true to return a case-sensitive string.
Otherwise the resulting string will simply be all uppercase.&lt;/p&gt;



### FormatSize

    string Core::FormatSize(integer $filesize, integer $round)

Utility function to translate a filesize in bytes into a human-readable version.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $filesize **integer** - &lt;p&gt;Filesize in bytes&lt;/p&gt;
* $round **integer** - &lt;p&gt;Precision to round to&lt;/p&gt;



### GetExtensionFromString

    mixed Core::GetExtensionFromString($str)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $str **mixed**



### GetProfileTimeTotal

    float Core::GetProfileTimeTotal()





* Visibility: **public**
* This method is **static**.




### CheckEmailValidity

    boolean|string Core::CheckEmailValidity(string $email)

Validate an email address.

Provide email address (raw input)
Returns true if the email address has the email
address format and the domain exists.

Copied (almost) verbatim from http://www.linuxjournal.com/article/9585?page=0,3

* Visibility: **public**
* This method is **static**.


#### Arguments
* $email **string** - &lt;p&gt;The email to validate&lt;/p&gt;



### CheckIntGT0Validity

    boolean Core::CheckIntGT0Validity($val)

Simple function to check if a number is an int and greater than 0.

This is useful as a default validation option for model properties.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $val **mixed**



### _AttachCoreJavascript

    mixed Core::_AttachCoreJavascript()

Function that attaches the core javascript to the page.

This should be called automatically from the hook /core/page/preexecute.

* Visibility: **public**
* This method is **static**.




### _AttachCoreStrings

    boolean Core::_AttachCoreStrings()

Add the Core.Strings library to the page



* Visibility: **public**
* This method is **static**.




### _AttachAjaxLinks

    boolean Core::_AttachAjaxLinks()

Add the Core.Ajaxlinks library to the page



* Visibility: **public**
* This method is **static**.




### _AttachLessJS

    boolean Core::_AttachLessJS()

Add the LESS library to the page



* Visibility: **public**
* This method is **static**.




### _AttachJSON

    mixed Core::_AttachJSON()





* Visibility: **public**
* This method is **static**.




### _GetLegalFooterContent

    mixed Core::_GetLegalFooterContent()





* Visibility: **public**
* This method is **static**.




### VersionCompare

    boolean Core::VersionCompare(string $version1, string $version2, string $operation)

Clone of the php version_compare function, with the exception that it treats
version numbers the same that Debian treats them.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $version1 **string** - &lt;p&gt;Version to compare&lt;/p&gt;
* $version2 **string** - &lt;p&gt;Version to compare against&lt;/p&gt;
* $operation **string** - &lt;p&gt;Operation to use or null&lt;/p&gt;



### VersionSplit

    \Core\VersionString Core::VersionSplit(string $version)

Break a version string into the corresponding parts.

Major Version
Minor Version
Point Release
Core Version
Developer-Specific Version
Development Status

Optimized 2013.08.17

* Visibility: **public**
* This method is **static**.


#### Arguments
* $version **string**



### CompareValues

    boolean Core::CompareValues($val1, $val2)

Simple method to compare two values with each other in a more restrictive manner than == but not quite fully typecasted.

This is useful for the scenarios that involve needing to check that "3" == 3, but "" != 0.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $val1 **mixed**
* $val2 **mixed**



### CompareStrings

    boolean Core::CompareStrings($val1, $val2)

Compare two values as strings explictly.

This is useful for numbers that need to behave like strings, ie: postal codes with their leading zeros.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $val1 **mixed**
* $val2 **mixed**



### GenerateUUID

    string Core::GenerateUUID()

Generate a globally unique identifier that can be used as a replacement for an autoinc or similar.

This method IS compatible with multiple servers on a single codebase!

An example of a UUID returned by this function would be: "1-c5dbcaaf9db-8d77"

* Visibility: **public**
* This method is **static**.




### GetSupplementalModels

    mixed Core::GetSupplementalModels($modelname)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $modelname **mixed**



### GetLicensedFeature

    boolean|string Core::GetLicensedFeature(string $featureCode)

Get the requested licensed feature code from the licenser, or false if invalid/not set.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $featureCode **string**



### GetLicensedDump

    array Core::GetLicensedDump()

Get a dump of all licensed features on the site, along with their current value and expiration.

Useful for the system health page and developer debug output.

* Visibility: **public**
* This method is **static**.



