HTMLToMD\Elements\DefaultElement
===============

A short teaser of what DefaultElement does.

More lengthy description of what DefaultElement does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: DefaultElement
* Namespace: HTMLToMD\Elements
* This class implements: [HTMLToMD\Elements\ElementInterface](htmltomd_elements_elementinterface.md)




Properties
----------


### $_parentConverter

    protected \HTMLToMD\Converter $_parentConverter





* Visibility: **protected**


### $_node

    protected \DOMNode $_node





* Visibility: **protected**


Methods
-------


### __construct

    mixed HTMLToMD\Elements\ElementInterface::__construct(\DOMNode $node, \HTMLToMD\Converter $converter)





* Visibility: **public**
* This method is defined by [HTMLToMD\Elements\ElementInterface](htmltomd_elements_elementinterface.md)


#### Arguments
* $node **DOMNode**
* $converter **[HTMLToMD\Converter](htmltomd_converter.md)**



### convert

    string HTMLToMD\Elements\ElementInterface::convert()





* Visibility: **public**
* This method is defined by [HTMLToMD\Elements\ElementInterface](htmltomd_elements_elementinterface.md)




### getAttribute

    null|string HTMLToMD\Elements\ElementInterface::getAttribute(string $attribute, mixed $default)





* Visibility: **public**
* This method is defined by [HTMLToMD\Elements\ElementInterface](htmltomd_elements_elementinterface.md)


#### Arguments
* $attribute **string**
* $default **mixed**



### _getContent

    string HTMLToMD\Elements\DefaultElement::_getContent()

Get the standard child content for this node, (or nodeValue if no children).



* Visibility: **protected**




### getPageFooter

    string HTMLToMD\Elements\ElementInterface::getPageFooter()

Get the page footer text, (if any).



* Visibility: **public**
* This method is defined by [HTMLToMD\Elements\ElementInterface](htmltomd_elements_elementinterface.md)



