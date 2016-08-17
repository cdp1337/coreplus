Core\Templates\Template
===============

Class Template description




* Class name: Template
* Namespace: Core\Templates
* This is an **abstract** class





Properties
----------


### $_Paths

    private mixed $_Paths = null





* Visibility: **private**
* This property is **static**.


Methods
-------


### Factory

    \Core\Templates\TemplateInterface Core\Templates\Template::Factory(string $filename)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $filename **string** - &lt;p&gt;Filename of the template&lt;/p&gt;



### ResolveFile

    null|string Core\Templates\Template::ResolveFile(string $filename)

Resolve a filename stub to a fully resolved path.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $filename **string** - &lt;p&gt;Filename to resolve&lt;/p&gt;



### GetPaths

    array Core\Templates\Template::GetPaths()

Get an array of all the registered Template paths.



* Visibility: **public**
* This method is **static**.




### RequeryPaths

    mixed Core\Templates\Template::RequeryPaths()





* Visibility: **public**
* This method is **static**.



