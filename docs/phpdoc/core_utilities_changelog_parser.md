Core\Utilities\Changelog\Parser
===============

Class Parser description




* Class name: Parser
* Namespace: Core\Utilities\Changelog





Properties
----------


### $_file

    private mixed $_file





* Visibility: **private**


### $_name

    private mixed $_name





* Visibility: **private**


### $_sections

    private mixed $_sections





* Visibility: **private**


Methods
-------


### __construct

    mixed Core\Utilities\Changelog\Parser::__construct($name, $file)





* Visibility: **public**


#### Arguments
* $name **mixed**
* $file **mixed**



### exists

    boolean Core\Utilities\Changelog\Parser::exists()

Check if this file exists on the disk



* Visibility: **public**




### changed

    boolean Core\Utilities\Changelog\Parser::changed()

Check if this file has been modified and not saved yet



* Visibility: **public**




### parse

    mixed Core\Utilities\Changelog\Parser::parse()

Parse the given changelog file.



* Visibility: **public**




### getSection

    \Core\Utilities\Changelog\Section Core\Utilities\Changelog\Parser::getSection($version)

Get a section by a particular version number.

Will create a section if it doesn't exist.

* Visibility: **public**


#### Arguments
* $version **mixed**



### getPreviousSection

    null|\Core\Utilities\Changelog\Section Core\Utilities\Changelog\Parser::getPreviousSection($version)

Get the previous changelog set from the version requested.



* Visibility: **public**


#### Arguments
* $version **mixed**



### sort

    mixed Core\Utilities\Changelog\Parser::sort()

Sort the sections.

This is called internally, so you shouldn't need to worry about it.

* Visibility: **public**




### getFilename

    mixed Core\Utilities\Changelog\Parser::getFilename()

Get the filename for this changelog.



* Visibility: **public**




### createInitial

    mixed Core\Utilities\Changelog\Parser::createInitial($version, $message)

Create the initial CHANGELOG file with an optional message.

Will throw an exception if the file already exists!

* Visibility: **public**


#### Arguments
* $version **mixed**
* $message **mixed**



### save

    mixed Core\Utilities\Changelog\Parser::save(null|string $filename)

Save this CHANGELOG back out as the standard format



* Visibility: **public**


#### Arguments
* $filename **null|string** - &lt;p&gt;Set to a string to save as another file instead of the original&lt;/p&gt;



### saveHTML

    mixed Core\Utilities\Changelog\Parser::saveHTML($filename, integer $startinglevel)

Export this CHANGELOG out to an HTML file.

This is considered an export operation because it cannot be read back in as a valid CHANGELOG object,
and therefore does not trigger the changed flag to be dropped.

* Visibility: **public**


#### Arguments
* $filename **mixed**
* $startinglevel **integer**


