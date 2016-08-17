UpdaterHelper
===============

[PAGE DESCRIPTION HERE]




* Class name: UpdaterHelper
* Namespace: 







Methods
-------


### GetUpdates

    array UpdaterHelper::GetUpdates()

Perform a lookup on any repository sites installed and get a list of provided pacakges.



* Visibility: **public**
* This method is **static**.




### InstallComponent

    mixed UpdaterHelper::InstallComponent($name, $version, $dryrun, $verbose)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $name **mixed**
* $version **mixed**
* $dryrun **mixed**
* $verbose **mixed**



### InstallTheme

    mixed UpdaterHelper::InstallTheme($name, $version, $dryrun, $verbose)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $name **mixed**
* $version **mixed**
* $dryrun **mixed**
* $verbose **mixed**



### InstallCore

    mixed UpdaterHelper::InstallCore($version, $dryrun, $verbose)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $version **mixed**
* $dryrun **mixed**
* $verbose **mixed**



### PerformInstall

    mixed UpdaterHelper::PerformInstall($type, $name, $version, $dryrun, $verbose)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $type **mixed**
* $name **mixed**
* $version **mixed**
* $dryrun **mixed**
* $verbose **mixed**



### CheckRequirement

    array UpdaterHelper::CheckRequirement(array $requirement, array $newcomponents, array $allavailable)

Simple function to scan through the components provided for one that
satisfies the requirement.

Returns true if requirement is met with current packages,
Returns false if requirement cannot be met at all.
Returns the component array of an available repository package if that will solve this requirement.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $requirement **array** - &lt;p&gt;Associative array [type, name, version, operation], of requirement to look for&lt;/p&gt;
* $newcomponents **array** - &lt;p&gt;Associatve array [core, components, themes], of currently installed components&lt;/p&gt;
* $allavailable **array** - &lt;p&gt;Indexed array of all available components from the repositories&lt;/p&gt;



### CheckWeekly

    mixed UpdaterHelper::CheckWeekly()

A static function that can be tapped into the weekly cron hook.

This ensures that the update cache is never more than a week old.

* Visibility: **public**
* This method is **static**.




### _PrintHeader

    mixed UpdaterHelper::_PrintHeader($header)





* Visibility: **private**
* This method is **static**.


#### Arguments
* $header **mixed**



### _PrintInfo

    mixed UpdaterHelper::_PrintInfo($line, $timer)





* Visibility: **private**
* This method is **static**.


#### Arguments
* $line **mixed**
* $timer **mixed**


