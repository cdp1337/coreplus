HTMLToMD\Elements\ElementInterface
===============






* Interface name: ElementInterface
* Namespace: HTMLToMD\Elements
* This is an **interface**






Methods
-------


### __construct

    mixed HTMLToMD\Elements\ElementInterface::__construct(\DOMNode $node, \HTMLToMD\Converter $converter)





* Visibility: **public**


#### Arguments
* $node **DOMNode**
* $converter **[HTMLToMD\Converter](htmltomd_converter.md)**



### convert

    string HTMLToMD\Elements\ElementInterface::convert()





* Visibility: **public**




### getAttribute

    null|string HTMLToMD\Elements\ElementInterface::getAttribute(string $attribute, mixed $default)





* Visibility: **public**


#### Arguments
* $attribute **string**
* $default **mixed**



### getPageFooter

    string HTMLToMD\Elements\ElementInterface::getPageFooter()

Get the page footer text, (if any).



* Visibility: **public**



