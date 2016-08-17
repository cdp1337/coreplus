Component_2_1
===============






* Class name: Component_2_1
* Namespace: 



Constants
----------


### ERROR_NOERROR

    const ERROR_NOERROR = 0





### ERROR_INVALID

    const ERROR_INVALID = 1





### ERROR_WRONGEXECMODE

    const ERROR_WRONGEXECMODE = 2





### ERROR_MISSINGDEPENDENCY

    const ERROR_MISSINGDEPENDENCY = 4





### ERROR_CONFLICT

    const ERROR_CONFLICT = 8





### ERROR_UPGRADEPATH

    const ERROR_UPGRADEPATH = 16





Properties
----------


### $_xmlloader

    private \XMLLoader $_xmlloader = null

Underlying XML Loader object of the component.xml file.

Responsible for retrieving most information about this component.

* Visibility: **private**


### $_name

    protected string $_name

The name of the component.

Has to be unique, (because the name is a directory in /components)

* Visibility: **protected**


### $_version

    protected string $_version

Version of the component, (propagates to libraries and modules).



* Visibility: **protected**


### $_enabled

    protected boolean $_enabled = false

Is this component explictly disabled?



* Visibility: **protected**


### $_description

    protected string $_description

Description of this library.

As set from the XML file.

* Visibility: **protected**


### $_updateSites

    protected array $_updateSites = array()

Any update sites provided in this library.



* Visibility: **protected**


### $_authors

    protected array $_authors = array()

Array of any authors for the library.

Each element is composed of an array of name, email and url.

* Visibility: **protected**


### $_versionDB

    private string $_versionDB = false

Version of the component, as per the database (installed version).



* Visibility: **private**


### $_execMode

    private string $_execMode = 'WEB'

Each component can have an execution mode, by default it's "web".

This is used because some components will bomb out in CLI mode, and vice versa.

* Visibility: **private**


### $_file

    private \Core\Filestore\File $_file





* Visibility: **private**


### $_permissions

    private array $_permissions = array()

The permissions along with their description that are registered for this component.



* Visibility: **private**


### $_hasview

    private null $_hasview = null

Set to true or false after evaluating.



* Visibility: **private**


### $error

    public integer $error

This is the error code of any errors encountered.



* Visibility: **public**


### $errstrs

    public array $errstrs = array()

Any error messages encountered in this component, mainly while loading.



* Visibility: **public**


### $_loaded

    private boolean $_loaded = false

Only try to load a component only once!



* Visibility: **private**


### $_filesloaded

    private boolean $_filesloaded = false

Only try to load the files for this component once!



* Visibility: **private**


### $_smartyPluginDirectory

    private null $_smartyPluginDirectory = null

The smarty plugin directory cache.  This is to reduce the number of lookups required.



* Visibility: **private**


### $_viewSearchDirectory

    private null $_viewSearchDirectory = null

View search directory cache.  This is to reduce the number of lookups required.



* Visibility: **private**


### $_classlist

    private null $_classlist = null

Array of classes in this component.  This is to reduce the number of lookups required.



* Visibility: **private**


### $_controllerlist

    private null $_controllerlist = null

Array of controllers in this component.

Used to reduce the number of XML lookups required.

* Visibility: **private**


### $_widgetlist

    private null $_widgetlist = null

Array of widgets in this component.  This is to reduce the number of lookups required.



* Visibility: **private**


### $_requires

    private null $_requires = null

Array of require defintions in this component.  This is to reduce the number of lookups required.



* Visibility: **private**


### $_ready

    private boolean $_ready = false

Set to true after all the files have been loaded.

This is done by the Core.

* Visibility: **private**


### $_licenseDBData

    private string $_licenseDBData = null





* Visibility: **private**


### $_licenserFileData

    private null $_licenserFileData = null





* Visibility: **private**


Methods
-------


### __construct

    mixed Component_2_1::__construct($filename)





* Visibility: **public**


#### Arguments
* $filename **mixed**



### load

    void Component_2_1::load()

Load this component's metadata from the XML file.

Will setup the name, version, installed version (if available), and enabled flag (if available).

* Visibility: **public**




### loadSupplementalModels

    mixed Component_2_1::loadSupplementalModels()





* Visibility: **public**




### save

    mixed Component_2_1::save($minified)

Save this component metadata back to its XML file.

Useful in packager scripts.

* Visibility: **public**


#### Arguments
* $minified **mixed**



### savePackageXML

    string|null Component_2_1::savePackageXML(boolean $minified, boolean|string $filename)

Save or get the package XML for this component.  This is useful for the
packager



* Visibility: **public**


#### Arguments
* $minified **boolean**
* $filename **boolean|string**



### getRequires

    array Component_2_1::getRequires()

Get a raw array of the requirements for this component.

Each array index contains 'type', 'name', 'version', and 'operation'.

* Visibility: **public**




### getDescription

    string Component_2_1::getDescription()

Get the description for this component



* Visibility: **public**




### getLogo

    null|\Core\Filestore\File Component_2_1::getLogo()

Get the logo for this component as-per defined in the XML.



* Visibility: **public**




### setDescription

    mixed Component_2_1::setDescription($desc)

Set the description for this component



* Visibility: **public**


#### Arguments
* $desc **mixed** - &lt;p&gt;string&lt;/p&gt;



### getPermissions

    array Component_2_1::getPermissions()

Get the registered permissions for this component.



* Visibility: **public**




### getScreenshots

    array Component_2_1::getScreenshots()

Get all screenshots in this metafile



* Visibility: **public**




### getPagesDefined

    array Component_2_1::getPagesDefined()

Get the pages defined in this component.

These are usually admin-only pages, (but may not be).

Each page is returned with some of its info as a nested array with the
baseurl of the page as the index.

The keys returned are:

title
: The title of the page, usually a "t:STRING_..." string.

group
: The parent link this page falls under, usually a "t:STRING_..." string.

baseurl
: The relative base URL of this link

rewriteurl
: The pretty URL of this link

admin
: 1/0 whether this page is marked as an admin page

selectable
: 1/0 whether this page is marked as a "user-selectable" page.

access
: Access string for this page.

* Visibility: **public**




### getPageCreatesDefined

    mixed Component_2_1::getPageCreatesDefined()





* Visibility: **public**




### setAuthors

    mixed Component_2_1::setAuthors($authors)

Set and override the list of authors for this component.



* Visibility: **public**


#### Arguments
* $authors **mixed** - &lt;p&gt;array Array of authors to set&lt;/p&gt;



### setLicenses

    mixed Component_2_1::setLicenses($licenses)

Set and override the list of licenses for this component.



* Visibility: **public**


#### Arguments
* $licenses **mixed** - &lt;p&gt;array Array of licenses to set&lt;/p&gt;



### loadFiles

    mixed Component_2_1::loadFiles()





* Visibility: **public**




### _setReady

    mixed Component_2_1::_setReady(boolean $status)

Internal method used by the Core to set when a given component has been loaded and is ready for use.



* Visibility: **public**


#### Arguments
* $status **boolean**



### isReady

    mixed Component_2_1::isReady()





* Visibility: **public**




### getLibraryList

    mixed Component_2_1::getLibraryList()





* Visibility: **public**




### getClassList

    array Component_2_1::getClassList()

Get the list of classes provided in this component, (and their filenames)



* Visibility: **public**




### getModelList

    array Component_2_1::getModelList()

Get the list of models provided in this component, (and their filenames)



* Visibility: **public**




### getSupplementalModelList

    array Component_2_1::getSupplementalModelList()

Similar to getModelList, only it returns any supplemental model in this Component.



* Visibility: **public**




### getWidgetList

    array Component_2_1::getWidgetList()

Get an array of widget names provided in this component.



* Visibility: **public**




### getViewClassList

    mixed Component_2_1::getViewClassList()





* Visibility: **public**




### getViewList

    mixed Component_2_1::getViewList()

Get a list of view templates provided by this component.



* Visibility: **public**




### getControllerList

    array Component_2_1::getControllerList()

Get the list of controllers in this component.



* Visibility: **public**




### getSmartyPluginDirectory

    mixed Component_2_1::getSmartyPluginDirectory()

Return the fully resolved name of the smarty plugin directory for
this component (if there is one).

Not many templates will use this function, but it is there for when needed.

* Visibility: **public**




### getSmartyPlugins

    array Component_2_1::getSmartyPlugins()

Get an array of name => call of the registered smarty plugins on this component.



* Visibility: **public**




### getScriptLibraryList

    mixed Component_2_1::getScriptLibraryList()





* Visibility: **public**




### getViewSearchDir

    mixed Component_2_1::getViewSearchDir()





* Visibility: **public**




### getAssetDir

    mixed Component_2_1::getAssetDir()





* Visibility: **public**




### getUserAuthDrivers

    array Component_2_1::getUserAuthDrivers()

Get an array of this component's registered user auth drivers.



* Visibility: **public**




### getIncludePaths

    array Component_2_1::getIncludePaths()





* Visibility: **public**




### getDBSchemaTableNames

    array Component_2_1::getDBSchemaTableNames()

Get an array of the table names in the DB schema.



* Visibility: **public**




### setDBSchemaTableNames

    mixed Component_2_1::setDBSchemaTableNames(array $arr)

Set the DB Schema table names.

Will override any setting of the current dbschema.

* Visibility: **public**


#### Arguments
* $arr **array**



### getVersionInstalled

    mixed Component_2_1::getVersionInstalled()





* Visibility: **public**




### getType

    string Component_2_1::getType()

Components are components, (unless it's the core)



* Visibility: **public**




### getName

    string Component_2_1::getName()

Get this component's name



* Visibility: **public**




### getKeyName

    string Component_2_1::getKeyName()

Get this component's "key" name.

This *must* be the name of the directory it's installed in
and *must not* contain spaces or other weird characters.

* Visibility: **public**




### getVersion

    string Component_2_1::getVersion()

Get this component's version



* Visibility: **public**




### getLicenseData

    array Component_2_1::getLicenseData()

Return the fully populated array with the licensed data and the values from the license



* Visibility: **public**




### setVersion

    void Component_2_1::setVersion($vers)

Set the version of this component

This affects the component.xml metafile of the package.

* Visibility: **public**


#### Arguments
* $vers **mixed** - &lt;p&gt;string&lt;/p&gt;



### setFiles

    mixed Component_2_1::setFiles($files)

Set all files in this component.  Only really usable in the installer.



* Visibility: **public**


#### Arguments
* $files **mixed** - &lt;p&gt;array Array of files to set.&lt;/p&gt;



### setAssetFiles

    mixed Component_2_1::setAssetFiles($files)

Set all asset files in this component.  Only really usable in the installer.



* Visibility: **public**


#### Arguments
* $files **mixed** - &lt;p&gt;array Array of files to set.&lt;/p&gt;



### setViewFiles

    mixed Component_2_1::setViewFiles($files)

Set all asset files in this component.  Only really usable in the installer.



* Visibility: **public**


#### Arguments
* $files **mixed** - &lt;p&gt;array Array of files to set.&lt;/p&gt;



### setRequires

    mixed Component_2_1::setRequires(string $name, string $type, null|string $version, null|string $op)

Set a require in the XML

This is used by the packager.

* Visibility: **public**


#### Arguments
* $name **string**
* $type **string**
* $version **null|string**
* $op **null|string**



### getRawXML

    string Component_2_1::getRawXML()

Get the raw XML of this component, useful for debugging.



* Visibility: **public**




### isValid

    mixed Component_2_1::isValid()





* Visibility: **public**




### isInstalled

    mixed Component_2_1::isInstalled()





* Visibility: **public**




### needsUpdated

    mixed Component_2_1::needsUpdated()





* Visibility: **public**




### getErrors

    mixed Component_2_1::getErrors($glue)





* Visibility: **public**


#### Arguments
* $glue **mixed**



### runRequirementChecks

    mixed Component_2_1::runRequirementChecks()





* Visibility: **public**




### isEnabled

    boolean Component_2_1::isEnabled()

Simple check if this component is currently enabled.



* Visibility: **public**




### isLoadable

    mixed Component_2_1::isLoadable()

Check if this component is loadable in the environment's current state.

This cannot be cached because it's called multiple times in the loader.
ie: com1 needs com2, but com1 is checked first in the loop.

* Visibility: **public**




### getJSLibraries

    mixed Component_2_1::getJSLibraries()

Get every JSLibrary in this component as an object.



* Visibility: **public**




### hasLibrary

    mixed Component_2_1::hasLibrary()





* Visibility: **public**




### hasJSLibrary

    mixed Component_2_1::hasJSLibrary()





* Visibility: **public**




### hasModule

    mixed Component_2_1::hasModule()





* Visibility: **public**




### hasView

    mixed Component_2_1::hasView()





* Visibility: **public**




### install

    boolean Component_2_1::install()

Install this component.

Returns false if nothing changed, else will return an array containing all changes.

* Visibility: **public**




### reinstall

    boolean Component_2_1::reinstall(integer $verbosity)

Reinstall a component with its same version.

Useful for replacing corrupt assets or what not.

Returns false if nothing changed, else will return an array containing all changes.

* Visibility: **public**


#### Arguments
* $verbosity **integer** - &lt;p&gt;0 for standard output, 1 for real-time, 2 for real-time verbose output.&lt;/p&gt;



### upgrade

    boolean Component_2_1::upgrade(boolean $next, boolean $verbose)

Upgrade this component to the newer version, if possible.

Returns false if nothing changed, else will return an array containing all changes.

* Visibility: **public**


#### Arguments
* $next **boolean** - &lt;p&gt;Set to true to run the &quot;next&quot; upgrades as well as any current.&lt;/p&gt;
* $verbose **boolean** - &lt;p&gt;Set to true to enable real-time output&lt;/p&gt;



### queryLicenser

    array Component_2_1::queryLicenser()

Query the registered licenser URL for this Component.



* Visibility: **public**




### _parseWidgets

    boolean Component_2_1::_parseWidgets(boolean $install, integer $verbosity)

Internal function to parse and handle the configs in the component.xml file.

This is used for installations and upgrades.

* Visibility: **public**


#### Arguments
* $install **boolean** - &lt;p&gt;Set to false to force uninstall/disable mode.&lt;/p&gt;
* $verbosity **integer** - &lt;p&gt;(default 0) 0: standard output, 1: real-time, 2: real-time verbose output.&lt;/p&gt;



### _parseDBSchema

    boolean Component_2_1::_parseDBSchema(boolean $install, integer $verbosity)

Internal function to parse and handle the DBSchema in the component.xml file.

This is used for installations and upgrades.

* Visibility: **public**


#### Arguments
* $install **boolean** - &lt;p&gt;Set to false to force uninstall/disable mode.&lt;/p&gt;
* $verbosity **integer** - &lt;p&gt;(default 0) 0: standard output, 1: real-time, 2: real-time verbose output.&lt;/p&gt;



### _parseAssets

    false Component_2_1::_parseAssets(boolean $install, integer $verbosity)

Copy in all the assets for this component into the assets location.

Returns false if nothing changed, else will return an array of all the changes that occured.

* Visibility: **public**


#### Arguments
* $install **boolean** - &lt;p&gt;Set to false to force uninstall/disable mode.&lt;/p&gt;
* $verbosity **integer** - &lt;p&gt;(default 0) 0: standard output, 1: real-time, 2: real-time verbose output.&lt;/p&gt;



### _parseConfigs

    boolean Component_2_1::_parseConfigs(boolean $install, integer $verbosity)

Internal function to parse and handle the configs in the component.xml file.

This is used for installations and upgrades.

Returns false if nothing changed, else will return an int of the number of configuration options changed.

* Visibility: **public**


#### Arguments
* $install **boolean** - &lt;p&gt;Set to false to force uninstall/disable mode.&lt;/p&gt;
* $verbosity **integer** - &lt;p&gt;(default 0) 0: standard output, 1: real-time, 2: real-time verbose output.&lt;/p&gt;



### _parseUserConfigs

    boolean Component_2_1::_parseUserConfigs(boolean $install, integer $verbosity)

Internal function to parse and handle the user configs in the component.xml file.

This is used for installations and upgrades.

Returns false if nothing changed, else will return an int of the number of configuration options changed.

* Visibility: **public**


#### Arguments
* $install **boolean** - &lt;p&gt;Set to false to force uninstall/disable mode.&lt;/p&gt;
* $verbosity **integer** - &lt;p&gt;(default 0) 0: standard output, 1: real-time, 2: real-time verbose output.&lt;/p&gt;



### _parsePages

    boolean Component_2_1::_parsePages(boolean $install, integer $verbosity)

Internal function to parse and handle the configs in the component.xml file.

This is used for installations and upgrades.

* Visibility: **public**


#### Arguments
* $install **boolean** - &lt;p&gt;Set to false to force uninstall/disable mode.&lt;/p&gt;
* $verbosity **integer** - &lt;p&gt;(default 0) 0: standard output, 1: real-time, 2: real-time verbose output.&lt;/p&gt;



### disable

    mixed Component_2_1::disable()

Set this component as disabled in the database.

Hopefully it won't break anything else :p

* Visibility: **public**




### enable

    mixed Component_2_1::enable()

Set this component as enabled in the database.



* Visibility: **public**




### getRootDOM

    \DOMNode Component_2_1::getRootDOM()

Helper function for external classes and scripts to get this component's xml DOM.



* Visibility: **public**




### getXML

    \XMLLoader Component_2_1::getXML()

Get the XML Loader backend of this component.

Useful for manipulating the XML structure.

* Visibility: **public**




### getProvides

    mixed Component_2_1::getProvides()





* Visibility: **public**




### getBaseDir

    string Component_2_1::getBaseDir(mixed|string $prefix)

Get the base directory of this component

Generally /home/foo/public_html/components/componentname/

* Visibility: **public**


#### Arguments
* $prefix **mixed|string** - &lt;p&gt;base directory to use before the directory.&lt;/p&gt;



### getChangedFiles

    array Component_2_1::getChangedFiles()

Function to get any changed files in this component.

A changed file is any file whose md5 doesn't match what's in the component.xml metafile.

* Visibility: **public**




### getChangedTemplates

    array Component_2_1::getChangedTemplates()

Function to get any changed templates in this component.

A changed file is any file whose md5 doesn't match what's in the component.xml metafile.

* Visibility: **public**




### getChangedAssets

    array Component_2_1::getChangedAssets()

Function to get any changed templates in this component.

A changed file is any file whose md5 doesn't match what's in the component.xml metafile.

* Visibility: **public**




### _performInstall

    false Component_2_1::_performInstall(integer $verbosity)

Component installation operations all share common actions, (mostly).

Returns false if nothing changed, else will return an array containing all changes.

* Visibility: **private**


#### Arguments
* $verbosity **integer** - &lt;p&gt;0 for standard output, 1 for real-time, 2 for real-time verbose output.&lt;/p&gt;



### _parseDatasetNode

    mixed Component_2_1::_parseDatasetNode($node, $verbose)

Internal function to parse and handle the dataset in the <upgrade> and <install> tasks.

This is used for installations and upgrades.

Unlike the other parse functions, this handles a single node at a time.

* Visibility: **private**


#### Arguments
* $node **mixed** - &lt;p&gt;DOMElement&lt;/p&gt;
* $verbose **mixed** - &lt;p&gt;bool&lt;/p&gt;



### _includeFileForUpgrade

    mixed Component_2_1::_includeFileForUpgrade($filename, $verbose)





* Visibility: **private**


#### Arguments
* $filename **mixed**
* $verbose **mixed** - &lt;p&gt;bool Set to true for verbose real-time output.&lt;/p&gt;



### _checkUpgradePath

    boolean Component_2_1::_checkUpgradePath()

Helper function to see if there is a valid upgrade path from the current version installed
to the version of the code available.



* Visibility: **private**



