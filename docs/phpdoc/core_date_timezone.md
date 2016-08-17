Core\Date\Timezone
===============

A short teaser of what Timezone does.

More lengthy description of what Timezone does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: Timezone
* Namespace: Core\Date



Constants
----------


### TIMEZONE_GMT

    const TIMEZONE_GMT = 0





### TIMEZONE_DEFAULT

    const TIMEZONE_DEFAULT = 100





### TIMEZONE_USER

    const TIMEZONE_USER = 101







Methods
-------


### GetTimezone

    \DateTimeZone Core\Date\Timezone::GetTimezone(string|null|\DateTimeZone $timezone)

Get a valid \DateTimeZone from its name.  Useful for caching timezone objects.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $timezone **string|null|DateTimeZone**


