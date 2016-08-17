CoreDateTime
===============

Created by JetBrains PhpStorm.

User: powellc
Date: 10/17/12
Time: 3:09 AM
To change this template use File | Settings | File Templates.


* Class name: CoreDateTime
* Namespace: 





Properties
----------


### $_dt

    private \DateTime $_dt





* Visibility: **private**


Methods
-------


### __construct

    mixed CoreDateTime::__construct($datetime)





* Visibility: **public**


#### Arguments
* $datetime **mixed**



### setDate

    mixed CoreDateTime::setDate($datetime)

Set the data/time of this object.

If a unix timestamp is used, it is automatically set as GMT.
If a formatted date it used, the TIME_DEFAULT_TIMEZONE is used instead.

* Visibility: **public**


#### Arguments
* $datetime **mixed** - &lt;p&gt;string|int&lt;/p&gt;



### getTimezoneName

    mixed CoreDateTime::getTimezoneName()





* Visibility: **public**




### isGMT

    mixed CoreDateTime::isGMT()





* Visibility: **public**




### getFormatted

    mixed CoreDateTime::getFormatted($format, $desttimezone)





* Visibility: **public**


#### Arguments
* $format **mixed**
* $desttimezone **mixed**



### getRelative

    string CoreDateTime::getRelative($dateformat, $timeformat, $accuracy, $timezone)

Get a string to represent the relative time from right now.

Will return something similar to 'Yesterday at 5:40p' or 'Today at 4:20a', etc...

* Visibility: **public**


#### Arguments
* $dateformat **mixed** - &lt;p&gt;string The formatting to use for dates.&lt;/p&gt;
* $timeformat **mixed** - &lt;p&gt;string The formatting to use for times.&lt;/p&gt;
* $accuracy **mixed** - &lt;p&gt;int The level of accuracy,
2 will return today|yesterday|tomorrow,
3 will return up to a week, ie: Monday at 4:40pm
@param $timezone int The timezone to display the result as.&lt;/p&gt;
* $timezone **mixed**



### modify

    boolean CoreDateTime::modify(string $modify)

Alter the timestamp of a DateTime object by incrementing or decrementing
in a format accepted by strtotime().



* Visibility: **public**


#### Arguments
* $modify **string** - &lt;p&gt;A date/time string. Valid formats are explained in &lt;a href=&quot;http://www.php.net/manual/en/datetime.formats.php&quot;&gt;Date and Time Formats&lt;/a&gt;.&lt;/p&gt;



### Now

    string CoreDateTime::Now(string $format, integer $timezone)

Shortcut function for getting the time now.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $format **string**
* $timezone **integer**



### _GetTimezone

    \DateTimeZone CoreDateTime::_GetTimezone(string $timezone)

Get a valid DateTimeZone from its name.  Useful for caching timezone objects.



* Visibility: **private**
* This method is **static**.


#### Arguments
* $timezone **string**


