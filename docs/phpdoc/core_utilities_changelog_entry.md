Core\Utilities\Changelog\Entry
===============

Class Entry description




* Class name: Entry
* Namespace: Core\Utilities\Changelog



Constants
----------


### TYPE_OTHER

    const TYPE_OTHER = 'Change'





### TYPE_BUG

    const TYPE_BUG = 'Bug'





### TYPE_FEATURE

    const TYPE_FEATURE = 'Feature'





### TYPE_PERFORMANCE

    const TYPE_PERFORMANCE = 'Performance'





### TYPE_SECURITY

    const TYPE_SECURITY = 'Security'





Properties
----------


### $_comment

    private mixed $_comment





* Visibility: **private**


### $_type

    private mixed $_type





* Visibility: **private**


Methods
-------


### parseLine

    mixed Core\Utilities\Changelog\Entry::parseLine($line)

Parse a line for either a current CHANGELOG file or a user-submitted line.



* Visibility: **public**


#### Arguments
* $line **mixed**



### appendLine

    mixed Core\Utilities\Changelog\Entry::appendLine($line)

Append a line to this entry.

Useful because changelogs are wordwrapped.

* Visibility: **public**


#### Arguments
* $line **mixed**



### getLine

    string Core\Utilities\Changelog\Entry::getLine()

Get this entry as a single line with the prefix type.



* Visibility: **public**




### getLineFormatted

    mixed Core\Utilities\Changelog\Entry::getLineFormatted()





* Visibility: **public**




### getType

    string Core\Utilities\Changelog\Entry::getType()

Get the type of this entry



* Visibility: **public**




### getComment

    mixed Core\Utilities\Changelog\Entry::getComment()

Get just the comment of this entry



* Visibility: **public**



