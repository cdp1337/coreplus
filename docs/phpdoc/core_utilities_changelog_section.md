Core\Utilities\Changelog\Section
===============

Class Section description




* Class name: Section
* Namespace: Core\Utilities\Changelog





Properties
----------


### $_name

    private string $_name

The name of the component or theme.



* Visibility: **private**


### $_version

    private  $_version

The version of this changelog entry.



* Visibility: **private**


### $_entries

    private mixed $_entries = array()





* Visibility: **private**


### $_packagername

    private mixed $_packagername





* Visibility: **private**


### $_packageremail

    private mixed $_packageremail





* Visibility: **private**


### $_packageddate

    private mixed $_packageddate





* Visibility: **private**


### $_changed

    public boolean $_changed = false





* Visibility: **public**


### $_lastentry

    private \Core\Utilities\Changelog\Entry $_lastentry

The last entry processed, useful for parseLine and its continuation ability.



* Visibility: **private**


Methods
-------


### parseHeader

    mixed Core\Utilities\Changelog\Section::parseHeader($line)





* Visibility: **public**


#### Arguments
* $line **mixed**



### parseLine

    mixed Core\Utilities\Changelog\Section::parseLine($line)

Parse (and add), a line from the CHANGELOG format.

Can handle being called multiple times for the same continued line.

* Visibility: **public**


#### Arguments
* $line **mixed**



### addLine

    mixed Core\Utilities\Changelog\Section::addLine($line)

Add a single line that's just a plain string.

Meant to be called with user-submitted data.

This can be called with duplicate lines and it will not produce duplicate entries.

* Visibility: **public**


#### Arguments
* $line **mixed**



### clearEntries

    mixed Core\Utilities\Changelog\Section::clearEntries()





* Visibility: **public**




### getVersion

    mixed Core\Utilities\Changelog\Section::getVersion()

Get the version string of this section



* Visibility: **public**




### getReleasedDate

    string Core\Utilities\Changelog\Section::getReleasedDate()

Get the released/packaged date of this changelog section.



* Visibility: **public**




### getReleasedDateUTC

    integer Core\Utilities\Changelog\Section::getReleasedDateUTC()

Get the released/packaged date of this version as a UTC int



* Visibility: **public**




### getPackagerName

    string Core\Utilities\Changelog\Section::getPackagerName()

Get the packager name for this version



* Visibility: **public**




### getPackagerEmail

    string Core\Utilities\Changelog\Section::getPackagerEmail()

Get the packager email for this version



* Visibility: **public**




### markReleased

    mixed Core\Utilities\Changelog\Section::markReleased(string $packager_name, string $packager_email, string|null $date)

Mark this CHANGELOG section as released by the requested user and email, optionally on a given timestamp.



* Visibility: **public**


#### Arguments
* $packager_name **string** - &lt;p&gt;Packager&#039;s name&lt;/p&gt;
* $packager_email **string** - &lt;p&gt;Packager&#039;s email&lt;/p&gt;
* $date **string|null** - &lt;p&gt;Date (in RFC-2822 format), or null for now&lt;/p&gt;



### fetch

    string Core\Utilities\Changelog\Section::fetch()

Fetch this section as a plain string.



* Visibility: **public**




### fetchFormatted

    string Core\Utilities\Changelog\Section::fetchFormatted()

Fetch this section as a fully formatted string.



* Visibility: **public**




### fetchAsHTML

    string Core\Utilities\Changelog\Section::fetchAsHTML(integer|boolean $startinglevel)

Fetch this changelog section as HTML; useful for reports.

If the first parameter is null or false, then no header is returned.
Otherwise, "2" will yield &lt;h2&gt; tags.

* Visibility: **public**


#### Arguments
* $startinglevel **integer|boolean** - &lt;p&gt;The starting &lt;h#&gt; level to start with.&lt;/p&gt;



### getEntriesSorted

    array Core\Utilities\Changelog\Section::getEntriesSorted()

Get the entries of this section sorted by importance.



* Visibility: **public**



