WidgetRequest
===============






* Class name: WidgetRequest
* Namespace: 





Properties
----------


### $parameters

    public mixed $parameters = array()





* Visibility: **public**


Methods
-------


### getParameters

    array WidgetRequest::getParameters()

Get all parameters from the GET or inline variables.

"Core" parameters are returned on a 0-based index, whereas named GET variables are returned with their respective name.

* Visibility: **public**




### getParameter

    null|string WidgetRequest::getParameter($key)

Get a single parameter from the GET or inline variables.



* Visibility: **public**


#### Arguments
* $key **mixed** - &lt;p&gt;string|int The parameter to request&lt;/p&gt;


