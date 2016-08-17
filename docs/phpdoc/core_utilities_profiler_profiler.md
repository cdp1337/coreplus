Core\Utilities\Profiler\Profiler
===============

Profiler gives a simple performance profiler for scripts and utilities.

<h3>Usage</h3>

<h4>System Profiler</h4>
<p>
Core has a system profiler running from the start of the application.
If FULL_DEBUG is set to true, then any event recorded there will be displayed at the end of the page execution.
</p>

<code>
$profiler = \Core\Utilities\Profiler\Profiler::GetDefaultProfiler();
$profiler->record('my awesome event');
</code>

<h4>Custom Profiler</h4>
<p>
To create a new profiler, (and a new timer), the following code will do that job.
</p>

<code>
$profiler = new \Core\Utilities\Profiler\Profiler('this set');

// Do some logic that takes some amount of time
// ...
// ...
$profiler->record('done with step one');

// More stuff that takes a long time
// ...
// ...
$profiler->record('Finished!');

// Display the overall time!
echo '&lt;h1&gt;Finished in ' . $profiler-&gt;getTimeFormatted() . '&lt;/h1&gt;';

// Or if you want a breakdown of the events themselves...
echo '&lt;pre&gt;' . $profiler-&gt;getEventTimesFormatted() . '&lt;/pre&gt;';
</code>


* Class name: Profiler
* Namespace: Core\Utilities\Profiler





Properties
----------


### $_name

    private mixed $_name





* Visibility: **private**


### $_events

    private mixed $_events = array()





* Visibility: **private**


### $_microtime

    private mixed $_microtime





* Visibility: **private**


### $_DefaultProfiler

    private mixed $_DefaultProfiler





* Visibility: **private**
* This property is **static**.


Methods
-------


### __construct

    mixed Core\Utilities\Profiler\Profiler::__construct($name)





* Visibility: **public**


#### Arguments
* $name **mixed**



### record

    mixed Core\Utilities\Profiler\Profiler::record($event)

Record an event and its profile time from the start of the application.



* Visibility: **public**


#### Arguments
* $event **mixed**



### getTime

    float Core\Utilities\Profiler\Profiler::getTime()

Sometimes you just want to know how many milliseconds passed between the start of the app and now
This is useful for logging utilities.



* Visibility: **public**




### getEvents

    array Core\Utilities\Profiler\Profiler::getEvents()

Get all the recorded events of this profiler as an array.



* Visibility: **public**




### getTimeFormatted

    string Core\Utilities\Profiler\Profiler::getTimeFormatted()

Get the overall execution time of this profiler.

This will be rounded and formatted as such:
"# µs", "# ms", "# s", "# m # s", or "# h # m".

* Visibility: **public**




### getEventTimesFormatted

    string Core\Utilities\Profiler\Profiler::getEventTimesFormatted()

Get the breakdown of recorded events and their time into the profiler operation.



* Visibility: **public**




### GetDefaultProfiler

    \Core\Utilities\Profiler\Profiler Core\Utilities\Profiler\Profiler::GetDefaultProfiler()

Get the first instance of the profiler



* Visibility: **public**
* This method is **static**.



