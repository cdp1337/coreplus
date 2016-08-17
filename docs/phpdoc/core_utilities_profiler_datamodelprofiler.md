Core\Utilities\Profiler\DatamodelProfiler
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


* Class name: DatamodelProfiler
* Namespace: Core\Utilities\Profiler





Properties
----------


### $_name

    private mixed $_name





* Visibility: **private**


### $_events

    private mixed $_events = array()





* Visibility: **private**


### $_last

    private mixed $_last = array()





* Visibility: **private**


### $_DefaultProfiler

    private mixed $_DefaultProfiler





* Visibility: **private**
* This property is **static**.


### $_reads

    private mixed $_reads





* Visibility: **private**


### $_writes

    private mixed $_writes





* Visibility: **private**


Methods
-------


### __construct

    mixed Core\Utilities\Profiler\DatamodelProfiler::__construct($name)





* Visibility: **public**


#### Arguments
* $name **mixed**



### readCount

    integer Core\Utilities\Profiler\DatamodelProfiler::readCount()

Get the number of reads that have been performed on this page load.



* Visibility: **public**




### writeCount

    integer Core\Utilities\Profiler\DatamodelProfiler::writeCount()

Get the number of writes that have been performed on this page load.



* Visibility: **public**




### start

    mixed Core\Utilities\Profiler\DatamodelProfiler::start(string $type, string $query)

Start recording a given query.



* Visibility: **public**


#### Arguments
* $type **string** - &lt;p&gt;&quot;read&quot; or &quot;write&quot;, (usually).&lt;/p&gt;
* $query **string** - &lt;p&gt;Human-readable version of the query string.&lt;/p&gt;



### stopSuccess

    mixed Core\Utilities\Profiler\DatamodelProfiler::stopSuccess($count)





* Visibility: **public**


#### Arguments
* $count **mixed**



### stopError

    mixed Core\Utilities\Profiler\DatamodelProfiler::stopError($code, $error)





* Visibility: **public**


#### Arguments
* $code **mixed**
* $error **mixed**



### getEvents

    array Core\Utilities\Profiler\DatamodelProfiler::getEvents()

Get all the recorded events of this profiler as an array.



* Visibility: **public**




### getTimeFormatted

    string Core\Utilities\Profiler\DatamodelProfiler::getTimeFormatted($time)

Get the overall execution time of this profiler.

This will be rounded and formatted as such:
"# µs", "# ms", "# s", "# m # s", or "# h # m".

* Visibility: **public**


#### Arguments
* $time **mixed**



### getEventTimesFormatted

    string Core\Utilities\Profiler\DatamodelProfiler::getEventTimesFormatted()

Get the breakdown of recorded events and their time into the profiler operation.



* Visibility: **public**




### GetDefaultProfiler

    \Core\Utilities\Profiler\DatamodelProfiler Core\Utilities\Profiler\DatamodelProfiler::GetDefaultProfiler()

Get the first instance of the profiler



* Visibility: **public**
* This method is **static**.



