Core\HealthCheckResult
===============






* Class name: HealthCheckResult
* Namespace: Core



Constants
----------


### RESULT_GOOD

    const RESULT_GOOD = 'GOOD'





### RESULT_WARN

    const RESULT_WARN = 'WARN'





### RESULT_ERROR

    const RESULT_ERROR = 'ERRR'





### RESULT_SKIP

    const RESULT_SKIP = 'SKIP'





Properties
----------


### $result

    public mixed $result = self::RESULT_SKIP





* Visibility: **public**


### $title

    public mixed $title = ''





* Visibility: **public**


### $description

    public mixed $description = ''





* Visibility: **public**


### $link

    public mixed $link = ''





* Visibility: **public**


Methods
-------


### ConstructGood

    \Core\HealthCheckResult Core\HealthCheckResult::ConstructGood($title, $description)

Convenience function to create a new successful check



* Visibility: **public**
* This method is **static**.


#### Arguments
* $title **mixed**
* $description **mixed**



### ConstructWarn

    \Core\HealthCheckResult Core\HealthCheckResult::ConstructWarn($title, $description, $fixLink)

Convenience function to create a new warning check



* Visibility: **public**
* This method is **static**.


#### Arguments
* $title **mixed**
* $description **mixed**
* $fixLink **mixed**



### ConstructError

    \Core\HealthCheckResult Core\HealthCheckResult::ConstructError($title, $description, $fixLink)

Convenience function to create a new error check



* Visibility: **public**
* This method is **static**.


#### Arguments
* $title **mixed**
* $description **mixed**
* $fixLink **mixed**



### ConstructSkip

    \Core\HealthCheckResult Core\HealthCheckResult::ConstructSkip($title, $description)

Convenience function to create a new warning check



* Visibility: **public**
* This method is **static**.


#### Arguments
* $title **mixed**
* $description **mixed**


