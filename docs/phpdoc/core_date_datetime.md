Core\Date\DateTime
===============

Class DateTime extends the default DateTime object with Core-specific functionality
such as automatic timezones and automatic localization support.




* Class name: DateTime
* Namespace: Core\Date
* Parent class: DateTime



Constants
----------


### FULLDATE

    const FULLDATE = 'FD'





### SHORTDATE

    const SHORTDATE = 'SD'





### FULLDATETIME

    const FULLDATETIME = 'FDT'





### SHORTDATETIME

    const SHORTDATETIME = 'SDT'





### TIME

    const TIME = 'TIME'





### RELATIVE

    const RELATIVE = 'RELATIVE'





### EPOCH

    const EPOCH = 'U'







Methods
-------


### __construct

    \Core\Date\DateTime Core\Date\DateTime::__construct(string|null $datetime, \DateTimeZone|null $timezone)





* Visibility: **public**


#### Arguments
* $datetime **string|null** - &lt;p&gt;String representation of the date to manipulate&lt;/p&gt;
* $timezone **DateTimeZone|null** - &lt;p&gt;String representation or DateTimeZone object of the timezone, null for automatic&lt;/p&gt;



### getTimezoneName

    string Core\Date\DateTime::getTimezoneName()

Get the timezone name of this datetime object



* Visibility: **public**




### isGMT

    boolean Core\Date\DateTime::isGMT()

Get if this datetime object is GMT/UTC



* Visibility: **public**




### format

    string Core\Date\DateTime::format(string $format, integer|string|null $desttimezone)

Returns date formatted according to given format



* Visibility: **public**


#### Arguments
* $format **string**
* $desttimezone **integer|string|null**



### getRelative

    string Core\Date\DateTime::getRelative($accuracy, $timezone)

Get a string to represent the relative time from right now.

Will return something similar to 'Yesterday at 5:40p' or 'Today at 4:20a', etc...

* Visibility: **public**


#### Arguments
* $accuracy **mixed** - &lt;p&gt;int The level of accuracy,
2 will return today|yesterday|tomorrow,
3 will return up to a week, ie: Monday at 4:40pm
@param $timezone int The timezone to display the result as.&lt;/p&gt;
* $timezone **mixed**



### getDayOfWeek

    integer Core\Date\DateTime::getDayOfWeek()

Get the day of the week of this event, 0 being Sunday and 6 being Saturday.

This is just a shortcut function that calls format('w').

* Visibility: **public**




### nextMonth

    mixed Core\Date\DateTime::nextMonth($jump)

Skip ahead to the "next" month

This is a skip to increase the day to the same day, (if possible), the next month of the gregorian calendar.

If the day does not exist, the closest possible day will be selected. (such as Jan 30th -> Feb 28th)

* Visibility: **public**


#### Arguments
* $jump **mixed** - &lt;p&gt;int Amount of months to jump, (default: 1)&lt;/p&gt;



### prevMonth

    mixed Core\Date\DateTime::prevMonth($jump)

Skip behind to the "previous" month

This is a skip to increase the day to the same day, (if possible), the previous month of the gregorian calendar.

If the day does not exist, the closest possible day will be selected. (such as Mar 30th -> Feb 28th)

* Visibility: **public**


#### Arguments
* $jump **mixed** - &lt;p&gt;int Amount of months to jump, (default: 1)&lt;/p&gt;



### nextYear

    mixed Core\Date\DateTime::nextYear(integer $jump)

Jump the date forward by N year(s)



* Visibility: **public**


#### Arguments
* $jump **integer**



### prevYear

    mixed Core\Date\DateTime::prevYear(integer $jump)

Jump the date backwards by N year(s)



* Visibility: **public**


#### Arguments
* $jump **integer**



### Now

    string Core\Date\DateTime::Now(string $format, integer $timezone)

Shortcut function for getting the time now.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $format **string**
* $timezone **integer**



### NowGMT

    string Core\Date\DateTime::NowGMT(string $format)

Shortcut function for getting the GMT time at "now".



* Visibility: **public**
* This method is **static**.


#### Arguments
* $format **string** - &lt;p&gt;the format to return, by default will return unix timestamp.&lt;/p&gt;



### FormatString

    string Core\Date\DateTime::FormatString($datetime, $format, integer $timezone)

Shortcut function for formatting a timestamp or date string into another format and timezone.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $datetime **mixed**
* $format **mixed**
* $timezone **integer**


