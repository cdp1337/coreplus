HTMLToMD\Elements\TableElement
===============

A short teaser of what DefaultElement does.

More lengthy description of what DefaultElement does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: TableElement
* Namespace: HTMLToMD\Elements
* Parent class: [HTMLToMD\Elements\DefaultElement](htmltomd_elements_defaultelement.md)
* This class implements: [HTMLToMD\Elements\ElementInterface](htmltomd_elements_elementinterface.md)




Properties
----------


### $_columnWidths

    private mixed $_columnWidths = array()





* Visibility: **private**


### $_currentRow

    private mixed $_currentRow





* Visibility: **private**


### $_currentCol

    private mixed $_currentCol





* Visibility: **private**


### $_data

    private mixed $_data = array()





* Visibility: **private**


### $_parentConverter

    protected \HTMLToMD\Converter $_parentConverter





* Visibility: **protected**


### $_node

    protected \DOMNode $_node





* Visibility: **protected**


Methods
-------


### convert

    string HTMLToMD\Elements\ElementInterface::convert()





* Visibility: **public**
* This method is defined by [HTMLToMD\Elements\ElementInterface](htmltomd_elements_elementinterface.md)




### _parseInto

    mixed HTMLToMD\Elements\TableElement::_parseInto(\DOMNode $node)

Parse through a child node of this table, usually a tr, thead, or tbody.

This is a recursive safe function.

* Visibility: **private**


#### Arguments
* $node **DOMNode**



### _parseCell

    mixed HTMLToMD\Elements\TableElement::_parseCell(\DOMNode $node)





* Visibility: **private**


#### Arguments
* $node **DOMNode**



### _getCurrentRow

    \HTMLToMD\Elements\TableRow HTMLToMD\Elements\TableElement::_getCurrentRow()





* Visibility: **private**




### _getRow

    \HTMLToMD\Elements\TableRow HTMLToMD\Elements\TableElement::_getRow($rowID)





* Visibility: **private**


#### Arguments
* $rowID **mixed**



### _generateContent

    string HTMLToMD\Elements\TableElement::_generateContent()





* Visibility: **private**




### __construct

    mixed HTMLToMD\Elements\ElementInterface::__construct(\DOMNode $node, \HTMLToMD\Converter $converter)





* Visibility: **public**
* This method is defined by [HTMLToMD\Elements\ElementInterface](htmltomd_elements_elementinterface.md)


#### Arguments
* $node **DOMNode**
* $converter **[HTMLToMD\Converter](htmltomd_converter.md)**



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
* This method is defined by [HTMLToMD\Elements\DefaultElement](htmltomd_elements_defaultelement.md)




### getPageFooter

    string HTMLToMD\Elements\ElementInterface::getPageFooter()

Get the page footer text, (if any).



* Visibility: **public**
* This method is defined by [HTMLToMD\Elements\ElementInterface](htmltomd_elements_elementinterface.md)



