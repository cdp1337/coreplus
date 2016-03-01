# {duration} Smarty Function

The smarty {duration} function can return a formatted string of time duration.

This duration is expected to be in seconds, so for ms, do

	{duration $time/1000}
	# yields "10 ms" for example

Other time output suffixes supported are:

* "ns"
* "ms"
* "second"
* "seconds"
* "minute"
* "minutes"
* "hour"
* "hours"