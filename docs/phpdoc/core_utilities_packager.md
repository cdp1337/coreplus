Core\Utilities\Packager
===============

A short teaser of what Packager does.

More lengthy description of what Packager does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: Packager
* Namespace: Core\Utilities





Properties
----------


### $_type

    private string $_type





* Visibility: **private**


### $_name

    private string $_name





* Visibility: **private**


### $_keyname

    private string $_keyname





* Visibility: **private**


### $_xmlFile

    private string $_xmlFile





* Visibility: **private**


### $_base

    private \Core\Filestore\Directory $_base





* Visibility: **private**


### $_iterator

    private \Core\Filestore\DirectoryIterator $_iterator





* Visibility: **private**


### $_xmlLoader

    private \XMLLoader $_xmlLoader





* Visibility: **private**


### $_licenses

    private array $_licenses





* Visibility: **private**


### $_authors

    private array $_authors





* Visibility: **private**


### $_changelog

    private \Core\Utilities\Changelog\Parser $_changelog





* Visibility: **private**


### $_version

    private string $_version





* Visibility: **private**


### $_gitPaths

    private array $_gitPaths





* Visibility: **private**


### $Denytext

    private mixed $Denytext





* Visibility: **private**
* This property is **static**.


Methods
-------


### __construct

    mixed Core\Utilities\Packager::__construct($type, $name)





* Visibility: **public**


#### Arguments
* $type **mixed**
* $name **mixed**



### getGitBranch

    string Core\Utilities\Packager::getGitBranch()

Get the current GIT branch of the directory the user is CD'd into, (presumably the application directory).



* Visibility: **public**




### getVersion

    string Core\Utilities\Packager::getVersion()

Get the current version of the package,
set from either the developer or pulled from the metafile.



* Visibility: **public**




### setVersion

    mixed Core\Utilities\Packager::setVersion(string $version)

Set the version for this package



* Visibility: **public**


#### Arguments
* $version **string**



### getDescription

    string Core\Utilities\Packager::getDescription()

Get the description for this Package



* Visibility: **public**




### setDescription

    mixed Core\Utilities\Packager::setDescription(string $description)

Set the description for this package



* Visibility: **public**


#### Arguments
* $description **string**



### getLabel

    string Core\Utilities\Packager::getLabel()

Get a human-friendly string for this package.

Will be Core, component [blah], or theme [blah].

* Visibility: **public**




### getType

    string Core\Utilities\Packager::getType()

Get the type of package, "core", "component", "theme".



* Visibility: **public**




### getKeyname

    string Core\Utilities\Packager::getKeyname()

Get the keyname of packager



* Visibility: **public**




### scanInlineDocumentation

    mixed Core\Utilities\Packager::scanInlineDocumentation()

Scan this source package for inline documentation.

This will populate the license and author fields automatically.

* Visibility: **public**




### getChangelog

    \Core\Utilities\Changelog\Parser Core\Utilities\Packager::getChangelog()

Get the raw CHANGELOG object



* Visibility: **public**




### getChangelogSection

    \Core\Utilities\Changelog\Section Core\Utilities\Packager::getChangelogSection(null|string $version)

Get the requested CHANGELOG section by version



* Visibility: **public**


#### Arguments
* $version **null|string**



### isVersionReleased

    boolean Core\Utilities\Packager::isVersionReleased(null|string $version)

Check if the requested version has been marked as released yet



* Visibility: **public**


#### Arguments
* $version **null|string**



### getGitChangesSince

    array Core\Utilities\Packager::getGitChangesSince(string $sinceversion, string $sincedate)

Get all GIT changes since version z.y.x and date xxxx.yy.dd for this package



* Visibility: **public**


#### Arguments
* $sinceversion **string** - &lt;p&gt;Version to ignore, (used to ignore release statements).&lt;/p&gt;
* $sincedate **string** - &lt;p&gt;Date to pull changes since&lt;/p&gt;



### getLatestReleaseInfo

    array Core\Utilities\Packager::getLatestReleaseInfo()

Get the release date and version of the last released version on this component.

If this current version has been released, it'll be that date.
Otherwise, it will be the previous version that has been released.

* Visibility: **public**




### save

    mixed Core\Utilities\Packager::save()

Save this package along with all changes back down to the disk and database.



* Visibility: **public**




### package

    string Core\Utilities\Packager::package(string $packager_name, string $packager_email, boolean $signed)

Create a valid Core package for this source component or theme, optionally signed



* Visibility: **public**


#### Arguments
* $packager_name **string** - &lt;p&gt;Name of packager for this package&lt;/p&gt;
* $packager_email **string** - &lt;p&gt;Email of packager for this package&lt;/p&gt;
* $signed **boolean** - &lt;p&gt;True/False if this will be a GPG signed package&lt;/p&gt;



### gitCommit

    mixed Core\Utilities\Packager::gitCommit()

Perform a GIT commit on all changed resources in this package.



* Visibility: **public**




### _getObject

    \Component_2_1|\Theme\Theme Core\Utilities\Packager::_getObject()

Get the Core object for this package, be it a Component or Theme.



* Visibility: **private**




### _setupComponent

    mixed Core\Utilities\Packager::_setupComponent()





* Visibility: **private**




### _setupCore

    mixed Core\Utilities\Packager::_setupCore()





* Visibility: **private**




### _setupTheme

    mixed Core\Utilities\Packager::_setupTheme()





* Visibility: **private**




### ParseForDocumentation

    array Core\Utilities\Packager::ParseForDocumentation(string $file)

Slightly more advanced function to parse for specific information from file headers.

Will return an array containing any author, license

* Visibility: **public**
* This method is **static**.


#### Arguments
* $file **string**



### GetUniqueAuthors

    array Core\Utilities\Packager::GetUniqueAuthors(array $authors)

Try to intelligently merge duplicate authors, matching a variety of input names.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $authors **array** - &lt;p&gt;&lt;&lt;string&gt;&gt; $authors&lt;/p&gt;


