Time
===============

[PAGE DESCRIPTION HERE]




* Class name: Time
* Namespace: 



Constants
----------


### TIMEZONE_GMT

    const TIMEZONE_GMT = 0





### TIMEZONE_DEFAULT

    const TIMEZONE_DEFAULT = 100





### TIMEZONE_USER

    const TIMEZONE_USER = 101





### FORMAT_ISO8601

    const FORMAT_ISO8601 = 'c'





### FORMAT_RFC2822

    const FORMAT_RFC2822 = 'r'





### FORMAT_FULLDATETIME

    const FORMAT_FULLDATETIME = self::FORMAT_ISO8601





### FORMAT_EPOCH

    const FORMAT_EPOCH = 'U'





Properties
----------


### $_Instance

    private mixed $_Instance = null





* Visibility: **private**
* This property is **static**.


### $timezones

    private mixed $timezones = array()





* Visibility: **private**


Methods
-------


### __construct

    mixed Time::__construct()





* Visibility: **private**




### _getTimezone

    \DateTimeZone Time::_getTimezone(string $timezone)

Get a valid DateTimeZone from the intiger of it.

Note, these will all be the generic GMT-5 timezones.

* Visibility: **private**


#### Arguments
* $timezone **string**



### _Singleton

    \Time Time::_Singleton()





* Visibility: **private**
* This method is **static**.




### GetCurrentGMT

    string Time::GetCurrentGMT($format)

Will return the current GMT time corrected via the server GMT_OFFSET config setting.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $format **mixed** - &lt;p&gt;string&lt;/p&gt;



### GetCurrent

    string Time::GetCurrent(integer $timezone, $format)

Get the current time for the given timezone formatted as per requested



* Visibility: **public**
* This method is **static**.


#### Arguments
* $timezone **integer** - &lt;p&gt;int value of the timezone requested&lt;/p&gt;
* $format **mixed**



### GetRelativeAsString

    string Time::GetRelativeAsString($time, $timezone, $accuracy, $timeformat, $dateformat)

Get a string to represent the relative time from right now.

Will return something similar to 'Yesterday at 5:40p' or 'Today at 4:20a', etc...

* Visibility: **public**
* This method is **static**.


#### Arguments
* $time **mixed** - &lt;p&gt;int The time, (in GMT), to get the relative from now.&lt;/p&gt;
* $timezone **mixed** - &lt;p&gt;int The timezone to display the result as.&lt;/p&gt;
* $accuracy **mixed** - &lt;p&gt;int The level of accuracy,
                     2 will return today|yesterday|tomorrow,
                     3 will return up to a week, ie: Monday at 4:40pm&lt;/p&gt;
* $timeformat **mixed** - &lt;p&gt;string The formatting to use for times.&lt;/p&gt;
* $dateformat **mixed** - &lt;p&gt;string The formatting to use for dates.&lt;/p&gt;



### FormatGMT

    string Time::FormatGMT($timeInGMT, $timezone, $format)

Format a given GMT time as $format and return in timezone $timezone.

(assumes a corrected GMT value)

* Visibility: **public**
* This method is **static**.


#### Arguments
* $timeInGMT **mixed** - &lt;p&gt;int&lt;/p&gt;
* $timezone **mixed** - &lt;p&gt;int&lt;/p&gt;
* $format **mixed** - &lt;p&gt;string&lt;/p&gt;


